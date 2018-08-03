<?php

namespace BitTools\SkyHub\Support\Api;

interface ExtensionAttributeInterface
{
    /**
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @return \Magento\Sales\Api\Data\OrderInterface
     */
    public function get(\Magento\Sales\Api\Data\OrderInterface $order);


    /**
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @return \Magento\Sales\Api\Data\OrderInterface
     */
    public function save(\Magento\Sales\Api\Data\OrderInterface $order);
}