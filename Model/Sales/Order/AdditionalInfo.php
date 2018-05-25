<?php

namespace BitTools\SkyHub\Model\Sales\Order;

use BitTools\SkyHub\Api\Data\SalesOrderExtensionAttributeInterface;
use BitTools\SkyHub\Helper\Context;

class AdditionalInfo implements SalesOrderExtensionAttributeInterface
{

    /** @var array */
    protected $extensionAttributes;

    /** @var int */
    protected $storeId;

    /** @var int */
    protected $orderId;

    /** @var string */
    protected $code;

    /** @var string */
    protected $channel;

    /** @var string */
    protected $invoiceKey;

    /** @var float */
    protected $interest;

    /** @var Context */
    protected $context;


    public function __construct(Context $context)
    {
        $this->context = $context;
    }


    /**
     * @return int
     */
    public function getStoreId()
    {
        return $this->storeId;
    }


    /**
     * @param int $storeId
     *
     * @return $this
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function setStoreId($storeId = null)
    {
        $this->storeId = (int) $this->getStore($storeId)->getId();
        return $this;
    }


    /**
     * @return int
     */
    public function getOrderId()
    {
        return $this->orderId;
    }


    /**
     * @param int $orderId
     *
     * @return $this
     */
    public function setOrderId($orderId)
    {
        $this->orderId = $orderId;
        return $this;
    }


    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }


    /**
     * @param string $code
     *
     * @return $this
     */
    public function setCode($code)
    {
        $this->code = $code;
        return $this;
    }


    /**
     * @return string
     */
    public function getChannel()
    {
        return $this->channel;
    }


    /**
     * @param string $channel
     *
     * @return $this
     */
    public function setChannel($channel)
    {
        $this->channel = $channel;
        return $this;
    }


    /**
     * @return string
     */
    public function getInvoiceKey()
    {
        return $this->invoiceKey;
    }


    /**
     * @param string $invoiceKey
     *
     * @return $this
     */
    public function setInvoiceKey($invoiceKey)
    {
        $this->invoiceKey = $invoiceKey;
        return $this;
    }


    /**
     * @return float
     */
    public function getInterest()
    {
        return $this->interest;
    }


    /**
     * @param float $interest
     *
     * @return $this
     */
    public function setInterest($interest)
    {
        $this->interest = (float) $interest;
        return $this;
    }


    /**
     * @param array $extensionAttributes
     *
     * @return $this
     */
    public function setExtensionAttributes(array $extensionAttributes)
    {
        $this->extensionAttributes = $extensionAttributes;
        return $this;
    }


    /**
     * @return array
     */
    public function getExtensionAttributes()
    {
        return $this->extensionAttributes;
    }


    /**
     * @param null|int $storeId
     *
     * @return \Magento\Store\Api\Data\StoreInterface
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getStore($storeId = null)
    {
        return $this->context->storeManager()->getStore($storeId);
    }
}
