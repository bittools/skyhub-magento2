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
use Magento\Framework\Module\Setup\Migration;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Sales\Model\Order;
use BitTools\SkyHub\Model\Config\SkyhubAttributes\Data as SkyhubConfigData;

class InstallData implements InstallDataInterface
{
    
    use Functions;
    
    /** @var SkyhubConfigData */
    protected $skyhubConfigData;
    
    
    /**
     * InstallData constructor.
     *
     * @param SkyhubConfigData $configData
     */
    public function __construct(SkyhubConfigData $configData)
    {
        $this->skyhubConfigData = $configData;
    }
    
    
    
    /**
     * @inheritdoc
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
    
        /**
         * Install bseller_skyhub_product_attributes_mapping data.
         */
        $this->installSkyHubRequiredAttributes($setup);
    
//        $this->createAssociatedSalesOrderStatuses($this->getStatuses());
        
        /**
         * @todo Add your logic right here...
         */

        $installer->endSetup();
    }
    
    
    /**
     * @param ModuleDataSetupInterface $setup
     */
    protected function installSkyHubRequiredAttributes(ModuleDataSetupInterface $setup)
    {
        $attributes = (array)  $this->skyhubConfigData->getAttributes();
        $table      = (string) $setup->getTable('bittools_skyhub_product_attributes_mapping');
    
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
            ];
        
            $installConfig = (array) $this->arrayExtract($data, 'attribute_install_config', []);
            $magentoCode   = $this->arrayExtract($installConfig, 'attribute_code');
        
            /** @var \Magento\Eav\Model\Entity\Attribute $attribute */
            if ($attribute = $this->getAttributeByCode($magentoCode)) {
                $attributeData['attribute_id'] = $attribute->getId();
            }
        
            $setup->getConnection()->beginTransaction();
        
            try {
                /** @var \Magento\Framework\DB\Select $select */
                $select = $setup->getConnection()
                    ->select()
                    ->from($table, 'id')
                    ->where('skyhub_code = :skyhub_code')
                    ->limit(1);
            
                $id = $setup->getConnection()->fetchOne($select, [':skyhub_code' => $skyhubCode]);
            
                if ($id) {
                    $setup->getConnection()->update($table, $attributeData, "id = {$id}");
                    $setup->getConnection()->commit();
                    continue;
                }
    
                $setup->getConnection()->insert($table, $attributeData);
                $setup->getConnection()->commit();
            } catch (\Exception $e) {
                $setup->getConnection()->rollBack();
            }
        }
    }
    
    
    /**
     * @param array $states
     *
     * @return $this
     */
    public function createAssociatedSalesOrderStatuses(array $states = [])
    {
        return $this;
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
}
