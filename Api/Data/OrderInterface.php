<?php

namespace BitTools\SkyHub\Api\Data;

interface OrderInterface
{
    
    /**
     * @return int
     */
    public function getId();

    /**
     * @return int
     */
    public function getStoreId();

    /**
     * @return \Magento\Store\Api\Data\StoreInterface
     */
    public function getStore();

    /**
     * @return int
     */
    public function getOrderId();

    /**
     * @return string
     */
    public function getCode();

    /**
     * @return string
     */
    public function getChannel();

    /**
     * @return string
     */
    public function getInvoiceKey();

    /**
     * @return string
     */
    public function getDataSource();

    /**
     * @return float
     */
    public function getInterest();

    /**
     * @param int $storeId
     *
     * @return $this
     */
    public function setStoreId($storeId);


    /**
     * @param int $orderId
     *
     * @return $this
     */
    public function setOrderId($orderId);


    /**
     * @param string $code
     *
     * @return $this
     */
    public function setCode($code);


    /**
     * @param string $channel
     *
     * @return $this
     */
    public function setChannel($channel);


    /**
     * @param string $invoiceKey
     *
     * @return $this
     */
    public function setInvoiceKey($invoiceKey);


    /**
     * @param string $dataSource
     *
     * @return $this
     */
    public function setDataSource($dataSource);


    /**
     * @param float $interest
     *
     * @return $this
     */
    public function setInterest($interest = 0.0000);
}
