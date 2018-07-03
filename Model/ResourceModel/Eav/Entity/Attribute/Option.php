<?php

namespace BitTools\SkyHub\Model\ResourceModel\Eav\Entity\Attribute;

class Option extends \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option
{
    
    /**
     * @param int|\Magento\Eav\Model\Entity\Attribute $attribute
     * @param int                                     $optionId
     * @param int|\Magento\Store\Model\Store          $store
     *
     * @return mixed|null
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getAttributeOptionText($attribute, $optionId, $store = null)
    {
        if ($attribute instanceof \Magento\Eav\Model\Entity\Attribute) {
            $attribute = $attribute->getId();
        }
        
        /** @var int|null $store */
        $store = $this->getStore($store);
        
        /** @var \Magento\Framework\DB\Select $select */
        $select = $this->getConnection()
            ->select()
            ->from(['o' => $this->getMainTable()])
            ->joinInner(
                ['ov' => $this->getTable('eav_attribute_option_value')],
                "o.option_id = ov.option_id",
                ['store_id', 'value']
            )
            ->where('o.attribute_id = ?', (int) $attribute)
            ->where('ov.store_id IN (?)', (array) $this->filterIds([0, $store]))
            ->where('o.option_id = ?', (int) $optionId)
            ->order('ov.store_id DESC');
        
        try {
            $results = $this->getConnection()->fetchAll($select);
            
            if (empty($results)) {
                return null;
            }
            
            foreach ((array) $results as $result) {
                $resultStoreId = $result['store_id'];
                if ($resultStoreId === $store) {
                    return $this->returnValue($result);
                }
            }
            
            return $this->returnValue((array) array_pop($results));
        } catch (\Exception $e) {
            /** @var \Psr\Log\LoggerInterface $logger */
            $logger = $this->objectManager()->get(\Psr\Log\LoggerInterface::class);
            $logger->critical($e);
        }
        
        return null;
    }
    
    
    /**
     * @param array $data
     *
     * @return mixed
     */
    protected function returnValue(array $data)
    {
        return $data['value'];
    }
    
    
    /**
     * @param array $ids
     *
     * @return array
     */
    protected function filterIds(array $ids)
    {
        $ids = array_unique($ids);
        return $ids;
    }
    
    
    /**
     * \Magento\Store\Model\Store|int|null $store
     *
     * @return int|null
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getStore($store = null)
    {
        if (empty($store)) {
            /** @var \Magento\Store\Model\StoreManagerInterface $storeManager */
            $storeManager = $this->objectManager()->create(\Magento\Store\Model\StoreManagerInterface::class);
            $store = $storeManager->getStore()->getId();
        }
    
        if ($store instanceof \Magento\Store\Model\Store) {
            $store = (int) $store->getId();
        }
        
        return $store;
    }
    
    
    /**
     * @return \Magento\Framework\ObjectManager\ObjectManager
     */
    protected function objectManager()
    {
        return \Magento\Framework\App\ObjectManager::getInstance();
    }
}
