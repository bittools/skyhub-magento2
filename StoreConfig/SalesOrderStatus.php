<?php

namespace BitTools\SkyHub\StoreConfig;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Sales\Model\Order;
use Magento\Store\Model\StoreManager;

class SalesOrderStatus extends AbstractConfig
{
    
    /** @var string */
    protected $group = 'sales_order_status';

    /** @var Order\StatusFactory */
    protected $statusFactory;


    /**
     * SalesOrderStatus constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param EncryptorInterface   $encryptor
     * @param Order\StatusFactory  $statusFactory
     * @param StoreManager $storeManager
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        EncryptorInterface $encryptor,
        Order\StatusFactory $statusFactory,
        StoreManager $storeManager
    ) {
        parent::__construct($scopeConfig, $encryptor, $storeManager);

        $this->statusFactory = $statusFactory;
    }


    /**
     * @param null|int $scopeCode
     *
     * @return string
     */
    public function getNewOrdersStatus($scopeCode = null)
    {
        $status = (string) $this->getSkyHubModuleConfig('new_order_status', $this->group, $scopeCode);

        if (empty($status)) {
            $status = $this->getDefaultStatusByState(Order::STATE_NEW);
        }

        return $status;
    }


    /**
     * @param null|int $storeId
     *
     * @return string
     */
    public function getApprovedOrdersStatus($scopeCode = null)
    {
        $status = (string) $this->getSkyHubModuleConfig('approved_order_status', $this->group, $scopeCode);

        if (empty($status)) {
            $status = $this->getDefaultStatusByState(Order::STATE_PROCESSING);
        }

        return $status;
    }


    /**
     * @param null|int $scopeCode
     *
     * @return string
     */
    public function getDeliveredOrdersStatus($scopeCode = null)
    {
        $status = (string) $this->getSkyHubModuleConfig('delivered_order_status', $this->group, $scopeCode);

        if (empty($status)) {
            $status = $this->getDefaultStatusByState(Order::STATE_COMPLETE);
        }

        return $status;
    }


    /**
     * @param null|int $scopeCode
     *
     * @return string
     */
    public function getShipmentExceptionOrderStatus($scopeCode = null)
    {
        $status = (string) $this->getSkyHubModuleConfig('shipment_exception_order_status', $this->group, $scopeCode);

        if (empty($status)) {
            $status = $this->getDefaultStatusByState(Order::STATE_COMPLETE);
        }

        return $status;
    }


    /**
     * @param string $state
     *
     * @return string
     */
    public function getDefaultStatusByState($state)
    {
        /** @var Order\Status $status */
        $status = $this->statusFactory->create();
        $status->loadDefaultByState($state);

        return (string) $status->getId();
    }
}
