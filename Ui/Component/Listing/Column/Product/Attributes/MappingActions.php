<?php

namespace BitTools\SkyHub\Ui\Component\Listing\Column\Product\Attributes;

use BitTools\SkyHub\Functions;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;

class MappingActions extends Column
{
    
    use Functions;
    
    
    /** @var string */
    const URL_PATH_EDIT        = 'bittools_skyhub/catalog_product_attributes_mapping/edit';
    const URL_PATH_CREATE      = 'bittools_skyhub/catalog_product_attributes_mapping/autoCreate';
    const URL_PATH_UNASSOCIATE = 'bittools_skyhub/catalog_product_attributes_mapping/unassociate';
    
    
    /** @var string */
    protected $editUrl;
    
    /** @var string */
    protected $createUrl = self::URL_PATH_CREATE;
    
    /** @var string */
    protected $unnassociateUrl = self::URL_PATH_UNASSOCIATE;
    
    /** @var UrlInterface */
    protected $urlBuilder;
    
    
    /**
     * MappingActions constructor.
     *
     * @param ContextInterface   $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface       $urlBuilder
     * @param array              $components
     * @param array              $data
     * @param string             $editUrl
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = [],
        $editUrl = self::URL_PATH_EDIT
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->editUrl = $editUrl;
        
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }
    
    
    /**
     * @param array $dataSource
     *
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (!isset($dataSource['data']['items'])) {
            return $dataSource;
        }
        
        $items = (array) $dataSource['data']['items'];
        
        /** @var array $item */
        foreach ($items as &$item) {
            $id          = $item['id'];
            $attributeId = $item['attribute_id'];
            $editable    = $item['editable'];
            
            if (!$editable) {
                continue;
            }
    
            if ($attributeId) {
                $item[$this->getName()]['unassociate'] = [
                    'href'  => $this->urlBuilder->getUrl($this->unnassociateUrl, ['id' => $id]),
                    'label' => __('Remove Association')
                ];
                
                continue;
            }
            
            $item[$this->getName()]['create_automatically'] = [
                'href'  => $this->urlBuilder->getUrl($this->createUrl, ['id' => $id]),
                'label' => __('Create & Associate')
            ];
        }
    
        $dataSource['data']['items'] = $items;
        
        return $dataSource;
    }
}
