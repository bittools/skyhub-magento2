<?php

/**
 * Proudly powered by Magentor CLI!
 * Version v0.1.0
 * Official Repository: http://github.com/tiagosampaio/magentor
 *
 * @author Tiago Sampaio <tiago@tiagosampaio.com>
 */

namespace BitTools\SkyHub\Model;

use BitTools\SkyHub\Api\Data\OrderInterface;
use BitTools\SkyHub\Model\ResourceModel\Order as ResourceModel;
use Magento\Framework\Model\AbstractModel;
use Magento\Store\Model\StoreManagerInterface;

class Order extends AbstractModel implements OrderInterface
{

    const KEY_STORE_ID    = 'store_id';
    const KEY_ORDER_ID    = 'order_id';
    const KEY_CODE        = 'code';
    const KEY_CHANNEL     = 'channel';
    const KEY_INTEREST    = 'interest';
    const KEY_INVOICE_KEY = 'invoice_key';
    const KEY_DATA_SOURCE = 'data_source';
    
    
    /** @var string */
    protected $_eventPrefix = 'bittools_skyhub_order_relation';
    
    /** @var string */
    protected $_eventObject = 'order_relation';
    
    /** @var \Magento\Store\Model\StoreManagerInterface */
    protected $storeManager;
    
    /** @var \Magento\Sales\Api\OrderRepositoryInterface */
    protected $orderRepository;
    
    /** @var \Magento\Sales\Model\Order */
    protected $order;
    
    
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        
        $this->storeManager    = $storeManager;
        $this->orderRepository = $orderRepository;
    }


    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(ResourceModel::class);
    }

    /**
     * @return int
     */
    public function getStoreId()
    {
        return $this->_getData(self::KEY_STORE_ID);
    }

    /**
     * @return int
     */
    public function getOrderId()
    {
        return $this->_getData(self::KEY_ORDER_ID);
    }
    
    
    /**
     * @return \Magento\Sales\Api\Data\OrderInterface|\Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        if (!$this->order) {
            $this->order = $this->orderRepository->get($this->getOrderId());
        }
        
        return $this->order;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->_getData(self::KEY_CODE);
    }

    /**
     * @return string
     */
    public function getChannel()
    {
        return $this->_getData(self::KEY_CHANNEL);
    }

    /**
     * @return string
     */
    public function getInvoiceKey()
    {
        return $this->_getData(self::KEY_INVOICE_KEY);
    }

    /**
     * @return string
     */
    public function getDataSource()
    {
        return $this->_getData(self::KEY_DATA_SOURCE);
    }

    /**
     * @return float
     */
    public function getInterest()
    {
        return $this->_getData(self::KEY_INTEREST);
    }

    /**
     * @param int $storeId
     *
     * @return $this
     */
    public function setStoreId($storeId)
    {
        $this->setData(self::KEY_STORE_ID, $storeId);
        return $this;
    }

    /**
     * @param int $orderId
     *
     * @return $this
     */
    public function setOrderId($orderId)
    {
        $this->setData(self::KEY_ORDER_ID, $orderId);
        return $this;
    }

    /**
     * @param string $code
     *
     * @return $this
     */
    public function setCode($code)
    {
        $this->setData(self::KEY_CODE, $code);
        return $this;
    }

    /**
     * @param string $channel
     *
     * @return $this
     */
    public function setChannel($channel)
    {
        $this->setData(self::KEY_CHANNEL, $channel);
        return $this;
    }
    
    
    /**
     * @param string $invoiceKey
     *
     * @param bool   $graceful
     *
     * @return $this
     *
     * @throws \Exception
     */
    public function setInvoiceKey($invoiceKey, $graceful = false)
    {
        if (!$this->validateInvoiceKey($invoiceKey)) {
            if (!$graceful) {
                throw new \Exception(__('Invoice key number is not valid.'));
            }
            
            return $this;
        }
        
        $this->setData(self::KEY_INVOICE_KEY, $invoiceKey);
        return $this;
    }
    
    
    /**
     * @param null|string $invoiceKey
     *
     * @return bool
     */
    public function validateInvoiceKey($invoiceKey = null)
    {
        if (empty($invoiceKey)) {
            $invoiceKey = $this->_getData(self::KEY_INVOICE_KEY);
        }
    
        if (!preg_match('/.*?([0-9]{44}).*?/', $invoiceKey)) {
            return false;
        }
        
        return true;
    }
    

    /**
     * @param string $dataSource
     *
     * @return $this
     */
    public function setDataSource($dataSource)
    {
        $this->setData(self::KEY_DATA_SOURCE, $dataSource);
        return $this;
    }

    /**
     * @param float $interest
     *
     * @return $this
     */
    public function setInterest($interest = 0.0000)
    {
        $this->setData(self::KEY_INTEREST, (float) $interest);
        return $this;
    }
    
    
    /**
     * @return int|\Magento\Store\Api\Data\StoreInterface
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStore()
    {
        return $this->storeManager->getStore($this->getStoreId());
    }
}
