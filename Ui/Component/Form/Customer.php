<?php

namespace BitTools\SkyHub\Ui\Component\Form;

use BitTools\SkyHub\Model\ResourceModel\Customer\Attributes\Mapping\CollectionFactory;
use Magento\Framework\App\Request\DataPersistorInterface;

class Customer extends DataProvider
{
    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param DataPersistorInterface $dataPersistor
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        DataPersistorInterface $dataPersistor,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $collectionFactory->create();
        $this->dataPersistor = $dataPersistor;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->meta = $this->prepareMeta($this->meta);
    }

    /**
     * Prepares Meta
     *
     * @param array $meta
     * @return array
     */
    public function prepareMeta(array $meta)
    {
        return $meta;
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $items = $this->collection->getItems();
        /** @var $page \Magento\Cms\Model\Page */
        foreach ($items as $object) {
            $this->loadedData[$object->getId()] = $object->getData();
        }

        $data = $this->dataPersistor->get('customer_attributes_mapping');
        if (!empty($data)) {
            $object = $this->collection->getNewEmptyItem();
            $object->setData($data);
            $this->loadedData[$object->getId()] = $object->getData();
            $this->dataPersistor->clear('customer_attributes_mapping');
        }

        return $this->loadedData;
    }
}