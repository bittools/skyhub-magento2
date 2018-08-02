<?php

namespace BitTools\SkyHub\Plugin\Sales;

class OrderRepository
{
    /** @var \BitTools\SkyHub\Support\Order\ExtensionAttribute */
    protected $skyhubExtensionAttribute;


    /**
     * OrderRepository constructor.
     *
     * @param \BitTools\SkyHub\Support\Order\ExtensionAttribute $extensionAttribute
     */
    public function __construct(
        \BitTools\SkyHub\Support\Order\ExtensionAttribute $extensionAttribute
    ) {
        $this->skyhubExtensionAttribute = $extensionAttribute;
    }


    /**
     * Set SkyHub data in order extension attributes
     *
     * @param \Magento\Sales\Api\OrderRepositoryInterface $subject
     * @param \Magento\Sales\Api\Data\OrderInterface $result
     *
     * @return mixed
     */
    public function afterGet(
        \Magento\Sales\Api\OrderRepositoryInterface $subject,
        \Magento\Sales\Api\Data\OrderInterface $result
    ) {
//        return $subject;
        return $this->skyhubExtensionAttribute->get($result);
    }



    public function afterGetList(
        \Magento\Sales\Api\OrderRepositoryInterface $subject,
        $entities
    ) {

        foreach ($entities->getItems() as $entity) {
//            $entity = $this->skyhubExtensionAttribute->get($entity);
            $this->afterGet($subject, $entity);
        }

        return $entities;
    }
}
