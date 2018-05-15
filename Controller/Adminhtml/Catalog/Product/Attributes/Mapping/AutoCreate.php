<?php

/**
 * Proudly powered by Magentor CLI!
 * Version v0.1.0
 * Official Repository: http://github.com/tiagosampaio/magentor
 *
 * @author Tiago Sampaio <tiago@tiagosampaio.com>
 */

namespace BitTools\SkyHub\Controller\Adminhtml\Catalog\Product\Attributes\Mapping;

use BitTools\SkyHub\Model\Catalog\Product\Attributes\Mapping;

class AutoCreate extends AbstractMapping
{
    
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'BitTools_SkyHub::skyhub_product_attributes_mapping_save';
    
    
    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     *
     * @throws \Exception
     */
    public function execute()
    {
        $mappingId = $this->getRequest()->getParam('id');
    
        try {
            /** @var Mapping $mapping */
            $mapping = $this->productAttributeMappingRepository->get($mappingId);
            $mapping->setData('attribute_id', null);
        } catch (\Exception $e) {
            return $this->redirectIndex();
        }
    
        $attribute = $this->loadProductAttribute($mapping->getSkyhubCode());
    
        if ($attribute) {
            $mapping->setAttributeId((int) $attribute->getId());
        
            $this->messageManager
                ->addWarningMessage(__('There was already an attribute with the code "%1".', $mapping->getSkyhubCode()))
                ->addSuccessMessage(__('The attribute was only mapped automatically.'));
        }
        
        if (!$attribute) {
            $config = [
                'label'           => $mapping->getSkyhubLabel(),
                'type'            => 'varchar',
                'input'           => 'text',
                'required'        => 0,
                'visible_on_front'=> 0,
                'filterable'      => 0,
                'searchable'      => 0,
                'comparable'      => 0,
                'user_defined'    => 1,
                'is_configurable' => 0,
                'global'          => \Magento\Catalog\Model\ResourceModel\Eav\Attribute::SCOPE_GLOBAL,
                'note'            => sprintf(
                    '%s. %s.',
                    'Created automatically by BSeller SkyHub module.',
                    $mapping->getSkyhubDescription()
                ),
            ];
    
            $installConfig = (array) $mapping->getAttributeInstallConfig();
    
            foreach ($installConfig as $configKey => $itemValue) {
                if (is_null($itemValue)) {
                    continue;
                }
        
                $config[$configKey] = $itemValue;
            }
            
            /** @var \BitTools\SkyHub\Helper\Catalog\Product\Attribute $helper */
            $helper = $this->_objectManager
                ->create(\BitTools\SkyHub\Helper\Catalog\Product\Attribute::class);
            
            /** @var \Magento\Eav\Model\Entity\Attribute $attribute */
            $attribute = $helper->createProductAttribute($mapping->getSkyhubCode(), (array) $config);
    
            if (!$attribute || !$attribute->getId()) {
                $this->messageManager->addErrorMessage(__('There was a problem when trying to create the attribute.'));
                return $this->redirectIndex();
            }
    
            $mapping->setAttributeId((int) $attribute->getId());
        }
        
        $this->productAttributeMappingRepository->save($mapping);
    
        $message = __(
            'The attribute "%1" was created in Magento and associated to SkyHub attribute "%2" automatically.',
            $attribute->getAttributeCode(),
            $mapping->getSkyhubCode()
        );
        
        $this->messageManager->addSuccessMessage($message);
        
        return $this->redirectIndex();
    }
    
    
    /**
     * @param string $code
     *
     * @return \Magento\Eav\Model\Entity\Attribute|null
     */
    protected function loadProductAttribute($code)
    {
        /** @var \Magento\Eav\Model\AttributeRepository $repository */
        $repository = $this->_objectManager->create(\Magento\Eav\Model\AttributeRepository::class);
        
        try {
            /** @var \Magento\Eav\Model\Entity\Attribute $attribute */
            $attribute = $repository->get(\Magento\Catalog\Model\Product::ENTITY, $code);
        } catch (\Exception $e) {
            return null;
        }
        
        return $attribute;
    }
}
