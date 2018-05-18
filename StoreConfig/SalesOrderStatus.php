<?php

namespace BitTools\SkyHub\StoreConfig;


use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Sales\Model\Order;

class SalesOrderStatus extends AbstractConfig
{

    /** @var Order\StatusFactory */
    protected $statusFactory;


    /**
     * SalesOrderStatus constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param EncryptorInterface   $encryptor
     * @param Order\StatusFactory  $statusFactory
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        EncryptorInterface $encryptor,
        Order\StatusFactory $statusFactory
    )
    {
        parent::__construct($scopeConfig, $encryptor);

        $this->statusFactory = $statusFactory;
    }


    /**
     * @param null|int $storeId
     *
     * @return string
     */
    public function getNewOrdersStatus($storeId = null)
    {
        $status = (string) $this->getSkyHubModuleConfig('new_order_status', 'sales_order_status', $storeId);

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
    public function getApprovedOrdersStatus($storeId = null)
    {
        $status = (string) $this->getSkyHubModuleConfig('approved_order_status', 'sales_order_status', $storeId);

        if (empty($status)) {
            $status = $this->getDefaultStatusByState(Order::STATE_PROCESSING);
        }

        return $status;
    }


    /**
     * @param null|int $storeId
     *
     * @return string
     */
    public function getDeliveredOrdersStatus($storeId = null)
    {
        $status = (string) $this->getSkyHubModuleConfig('delivered_order_status', 'sales_order_status', $storeId);

        if (empty($status)) {
            $status = $this->getDefaultStatusByState(Order::STATE_COMPLETE);
        }

        return $status;
    }


    /**
     * @param null|int $storeId
     *
     * @return string
     */
    public function getShipmentExceptionOrderStatus($storeId = null)
    {
        $status = (string) $this->getSkyHubModuleConfig(
            'shipment_exception_order_status', 'sales_order_status', $storeId
        );

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
