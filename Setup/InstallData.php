<?php

/**
 * Proudly powered by Magentor CLI!
 * Version v0.1.0
 * Official Repository: http://github.com/tiagosampaio/magentor
 *
 * @author Tiago Sampaio <tiago@tiagosampaio.com>
 */

namespace BitTools\SkyHub\Setup;

use BitTools\SkyHub\Functions;
use BitTools\SkyHub\Model\Catalog\Product\Attributes\Mapping;
use Magento\Catalog\Model\Product;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Sales\Model\Order;
use BitTools\SkyHub\Model\Config\SkyhubAttributes\Data as SkyhubConfigData;
use Magento\Eav\Model\ResourceModel\Entity\AttributeFactory;

class InstallData implements InstallDataInterface
{
    
    use Functions, Setup;
    
    /** @var SkyhubConfigData */
    protected $skyhubConfigData;
    
    /** @var AttributeFactory */
    protected $attributeFactory;
    
    
    /**
     * InstallData constructor.
     *
     * @param SkyhubConfigData $configData
     */
    public function __construct(SkyhubConfigData $configData, AttributeFactory $attributeFactory)
    {
        $this->skyhubConfigData = $configData;
        $this->attributeFactory = $attributeFactory;
    }
    
    
    
    /**
     * @inheritdoc
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $this->setup = $setup;
        $this->setup()->startSetup();
    
        /**
         * Install bseller_skyhub_product_attributes_mapping data.
         */
        $this->installSkyHubRequiredAttributes();
        $this->createAssociatedSalesOrderStatuses($this->getStatuses());
    
        $this->setup()->endSetup();
    }
    
    
    /**
     * Install SkyHub required attributes.
     *
     * @return $this
     */
    protected function installSkyHubRequiredAttributes()
    {
        $attributes = (array)  $this->skyhubConfigData->getCatalogProductAttributes();
        $table      = (string) $this->getTable('bittools_skyhub_product_attributes_mapping');
    
        /** @var array $attribute */
        foreach ($attributes as $identifier => $data) {
            $skyhubCode  = $this->arrayExtract($data, 'code');
            $label       = $this->arrayExtract($data, 'label');
            $castType    = $this->arrayExtract($data, 'cast_type', Mapping::DATA_TYPE_STRING);
            $description = $this->arrayExtract($data, 'description');
            $validation  = $this->arrayExtract($data, 'validation');
            $enabled     = (bool) $this->arrayExtract($data, 'required', true);
            $required    = (bool) $this->arrayExtract($data, 'required', true);
            $editable    = (bool) $this->arrayExtract($data, 'editable', true);
            $createdAt   = $this->now();
        
            if (empty($skyhubCode) || empty($castType)) {
                continue;
            }
        
            $attributeData = [
                'skyhub_code'        => $skyhubCode,
                'skyhub_label'       => $label,
                'skyhub_description' => $description,
                'enabled'            => $enabled,
                'cast_type'          => $castType,
                'validation'         => $validation,
                'required'           => $required,
                'editable'           => $editable,
                'created_at'         => $createdAt,
            ];
        
            $installConfig = (array) $this->arrayExtract($data, 'attribute_install_config', []);
            $magentoCode   = $this->arrayExtract($installConfig, 'attribute_code');
        
            /** @var int $attributeId */
            if ($attributeId = (int) $this->getAttributeIdByCode($magentoCode)) {
                $attributeData['attribute_id'] = $attributeId;
            }
    
            $this->getConnection()->beginTransaction();
        
            try {
                /** @var \Magento\Framework\DB\Select $select */
                $select = $this->getConnection()
                    ->select()
                    ->from($table, 'id')
                    ->where('skyhub_code = :skyhub_code')
                    ->limit(1);
            
                $id = $this->getConnection()->fetchOne($select, [':skyhub_code' => $skyhubCode]);
            
                if ($id) {
                    $this->getConnection()->update($table, $attributeData, "id = {$id}");
                    $this->getConnection()->commit();
                    continue;
                }
    
                $this->getConnection()->insert($table, $attributeData);
                $this->getConnection()->commit();
            } catch (\Exception $e) {
                $this->getConnection()->rollBack();
            }
        }
        
        return $this;
    }
    
    
    /**
     * @param array $states
     *
     * @return $this
     */
    public function createAssociatedSalesOrderStatuses(array $states = [])
    {
        foreach ($states as $stateCode => $statuses) {
            $this->createSalesOrderStatus($stateCode, $statuses);
        }
        
        return $this;
    }
    
    
    /**
     * @param string $state
     * @param array  $status
     *
     * @return $this
     */
    public function createSalesOrderStatus($state, array $status)
    {
        foreach ($status as $statusCode => $statusLabel) {
            $statusData = [
                'status' => $statusCode,
                'label'  => $statusLabel
            ];
            
            $this->getConnection()->insertOnDuplicate($this->getSalesOrderStatusTable(), $statusData, [
                'status', 'label'
            ]);
            
            $this->associateStatusToState($state, $statusCode);
        }
        
        return $this;
    }
    
    
    /**
     * @param string $state
     * @param string $status
     * @param int    $isDefault
     *
     * @return $this
     */
    public function associateStatusToState($state, $status, $isDefault = 0)
    {
        $associationData = [
            'status'     => (string) $status,
            'state'      => (string) $state,
            'is_default' => (int)    $isDefault,
        ];
        
        $this->getConnection()
            ->insertOnDuplicate($this->getSalesOrderStatusStateTable(), $associationData, [
                'status',
                'state',
                'is_default',
            ]);
        
        return $this;
    }
    
    
    /**
     * @param $code
     *
     * @return int|null
     */
    protected function getAttributeIdByCode($code)
    {
        $attributeId = null;
        
        try {
            /** @var \Magento\Eav\Model\ResourceModel\Entity\Attribute $attribute */
            $attribute   = $this->attributeFactory->create();
            $attributeId = $attribute->getIdByCode(Product::ENTITY, $code);
        } catch (\Exception $e) {
        }
        
        return $attributeId;
    }
    
    
    /**
     * @return array
     */
    protected function getStatuses()
    {
        $statuses = [
            Order::STATE_COMPLETE => [
                'customer_delivered' => 'Delivered to Customer',
                'shipment_exception' => 'Shipment Exception',
            ]
        ];
        
        return $statuses;
    }
    
    
    /**
     * @return string
     */
    protected function getSalesOrderStatusTable()
    {
        return $this->getTable('sales_order_status');
    }
    
    
    /**
     * @return string
     */
    protected function getSalesOrderStatusStateTable()
    {
        return $this->getTable('sales_order_status_state');
    }
}
