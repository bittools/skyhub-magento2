<?php

namespace BitTools\SkyHub\Ui\Component\Listing\Column\Queue;

use BitTools\SkyHub\Functions;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;

class Actions extends Column
{
    
    use Functions;
    
    
    /** @var string */
    const URL_PATH_EDIT   = 'bittools_skyhub/catalog_product_attributes_mapping/edit';
    const URL_PATH_CREATE = 'bittools_skyhub/catalog_product_attributes_mapping/autoCreate';
    const URL_PATH_DELETE = 'bittools_skyhub/queue/delete';
    
    
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
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
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
            $id = $item['id'];
    
            $item[$this->getName()]['delete'] = [
                'href'  => $this->urlBuilder->getUrl(self::URL_PATH_DELETE, ['id' => $id]),
                'label' => __('Delete')
            ];
        }
    
        $dataSource['data']['items'] = $items;
        
        return $dataSource;
    }
}
