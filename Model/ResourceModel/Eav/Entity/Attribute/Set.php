<?php

namespace BitTools\SkyHub\Model\ResourceModel\Eav\Entity\Attribute;

use BitTools\SkyHub\Helper\Context;

class Set extends \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set
{
    
    /** @var Context */
    protected $helperContext;
    
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\GroupFactory $attrGroupFactory,
        \Magento\Eav\Model\Config $eavConfig,
        Context $helperContext,
        $connectionName = null
    ) {
        parent::__construct($context, $attrGroupFactory, $eavConfig, $connectionName);
        $this->helperContext = $helperContext;
    }
    
    
    /**
     * @param     $entityTypeId
     * @param     $groupName
     * @param     $groupCode
     * @param int $sortOrder
     *
     * @return bool
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function setupEntityAttributeGroups($entityTypeId, $groupName, $groupCode, $sortOrder = 900)
    {
        /** @var \Magento\Framework\DB\Select $select */
        $select = $this->getConnection()
            ->select()
            ->from(['sets' => $this->getMainTable()], 'attribute_set_id')
            ->joinLeft(
                ['groups' => $this->getTable('eav_attribute_group')],
                "sets.attribute_set_id = groups.attribute_set_id AND groups.attribute_group_code='{$groupCode}'",
                'attribute_group_id'
            )
            ->where('entity_type_id = ?', (int) $entityTypeId)
            ->where('ISNULL(attribute_group_id)');
        
        try {
            $results = (array) $this->getConnection()->fetchAll($select);
        } catch (\Exception $e) {
            $this->helperContext->logger()->critical($e);
            return false;
        }
        
        if (empty($results)) {
            return true;
        }
        
        $groups = [];
        
        /** @var array $result */
        foreach ($results as $result) {
            $setId = (int) $result['attribute_set_id'];
            
            if (empty($setId)) {
                continue;
            }
            
            $groups[] = [
                'attribute_set_id'     => $setId,
                'attribute_group_name' => $groupName,
                'attribute_group_code' => $groupCode,
                'sort_order'           => (int) $sortOrder,
            ];
        }
        
        try {
            $this->beginTransaction();
            
            if (!empty($groups)) {
                $this->getConnection()->insertMultiple($this->getTable('eav_attribute_group'), $groups);
            }
            
            $this->commit();
            
            return true;
        } catch (\Exception $e) {
            $this->helperContext->logger()->critical($e);
            $this->rollBack();
        }
        
        return false;
    }
}
