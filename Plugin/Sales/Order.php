<?php

namespace BitTools\SkyHub\Plugin\Sales;

class Order
{

    /** @var ExtensionAttribute */
    protected $skyhubExtensionAttribute;


    /**
     * Order constructor.
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
     * @param \Magento\Sales\Api\Data\OrderInterface $subject
     * @param \Magento\Sales\Api\Data\OrderInterface $result
     *
     * @return \Magento\Sales\Api\Data\OrderInterface
     */
    public function afterLoad(
        \Magento\Sales\Api\Data\OrderInterface $subject,
        \Magento\Sales\Api\Data\OrderInterface $result
    ) {
        return $this->skyhubExtensionAttribute->get($result);
    }
}
