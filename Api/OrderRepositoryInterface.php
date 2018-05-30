<?php

namespace BitTools\SkyHub\Api;

interface OrderRepositoryInterface
{

    /**
     * @param Data\OrderInterface $order
     *
     * @return Data\OrderInterface
     */
    public function save(Data\OrderInterface $order);


    /**
     * @param int $orderId
     * @return Data\OrderInterface
     */
    public function getByOrderId($orderId);
}
