<?php

namespace BitTools\SkyHub\Model\Backend\Session;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\GroupManagementInterface;

class Quote extends \Magento\Backend\Model\Session\Quote
{
    public function __construct(
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\Session\SidResolverInterface $sidResolver,
        \Magento\Framework\Session\Config\ConfigInterface $sessionConfig,
        \Magento\Framework\Session\SaveHandlerInterface $saveHandler,
        \Magento\Framework\Session\ValidatorInterface $validator,
        \Magento\Framework\Session\StorageInterface $storage,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
        \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory,
        \Magento\Framework\App\State $appState,
        CustomerRepositoryInterface $customerRepository,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        GroupManagementInterface $groupManagement,
        \Magento\Quote\Model\QuoteFactory $quoteFactory
    ) {
        $this->customerRepository = $customerRepository;
        $this->quoteRepository = $quoteRepository;
        $this->_orderFactory = $orderFactory;
        $this->_storeManager = $storeManager;
        $this->groupManagement = $groupManagement;
        $this->quoteFactory = $quoteFactory;
        parent::__construct(
            $request,
            $sidResolver,
            $sessionConfig,
            $saveHandler,
            $validator,
            $storage,
            $cookieManager,
            $cookieMetadataFactory,
            $appState,
            $customerRepository,
            $quoteRepository,
            $orderFactory,
            $storeManager,
            $groupManagement,
            $quoteFactory
        );

        $this->clear();
    }

    /**
     * @return $this
     */
    public function clear()
    {
        $this->_quote = null;
        $this->_order = null;
        $this->_store = null;

        $this->clearStorage();

        return $this;
    }
}
