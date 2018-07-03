<?php

namespace BitTools\SkyHub\Model;

use BitTools\SkyHub\Helper\Context;
use Magento\Store\Api\Data\StoreInterface;

class StoreIterator implements StoreIteratorInterface
{
    
    /** @var StoreInterface */
    protected $initialStore = null;
    
    /** @var StoreInterface */
    protected $previousStore = null;
    
    /** @var StoreInterface */
    protected $currentStore = null;
    
    /** @var array */
    protected $stores = [];
    
    /** @var Context */
    protected $context;
    
    /** @var \BitTools\SkyHub\StoreConfig\Context */
    protected $configContext;
    
    /** @var \BitTools\SkyHub\Integration\Context */
    protected $integrationContext;
    
    
    public function __construct(
        Context $context,
        \BitTools\SkyHub\StoreConfig\Context $configContext,
        \BitTools\SkyHub\Integration\Context $integrationContext
    ) {
        $this->context            = $context;
        $this->configContext      = $configContext;
        $this->integrationContext = $integrationContext;
    }
    
    
    
    /**
     * @return array
     */
    public function getStores()
    {
        $this->initStores();
        return (array) $this->stores;
    }
    
    
    /**
     * @param object $subject
     * @param string $method
     * @param array  $params
     *
     * @return $this
     *
     * @throws \Exception
     */
    public function iterate($subject, $method, array $params = [])
    {
        $this->initIterator()
            ->initStores();
        
        if (!$this->validateObjectMethod($subject, $method)) {
            return $this;
        }
        
        $eventParams = [
            'iterator' => $this,
            'subject'  => $subject,
            'method'   => $method,
            'params'   => $params,
        ];
        
        $this->context->eventManager()->dispatch('bittools_skyhub_store_iterate_before', $eventParams);
        
        /** @var StoreInterface $store */
        foreach ($this->getStores() as $store) {
            $eventParams['store']          = $this->getCurrentStore();
            $eventParams['initial_store']  = $this->getInitialStore();
            $eventParams['previous_store'] = $this->getPreviousStore();
    
            $this->context->eventManager()->dispatch('bittools_skyhub_store_iterate', $eventParams);
            
            $this->call($subject, $method, $params, $store);
        }
    
        $this->context->eventManager()->dispatch('bittools_skyhub_store_iterate_after', $eventParams);
        
        $this->endIterator();
        
        return $this;
    }
    
    
    /**
     * @param object         $subject
     * @param string         $method
     * @param array          $params
     * @param StoreInterface $store
     * @param bool           $force
     *
     * @return mixed
     */
    public function call($subject, $method, array $params = [], StoreInterface $store, $force = false)
    {
        if (!$this->initialStore) {
            $this->initialStore = $store;
        }
        
        if (!$this->validateStore($store) && !$force) {
            return false;
        }
        
        if (!$this->validateObjectMethod($subject, $method)) {
            return false;
        }
        
        $result = false;
        
        $this->simulateStore($store);
        
        try {
            $params['__store'] = $store;
            $result = call_user_func_array([$subject, $method], $params);
        } catch (\Exception $e) {
            $this->context->logger()->critical($e);
        }
        
        $this->reset();
        
        return $result;
    }
    
    
    /**
     * @param StoreInterface $store
     *
     * @return $this
     */
    public function simulateStore(StoreInterface $store)
    {
        try {
            $this->previousStore = $this->currentStore;
            
            $this->context->storeManager()->setCurrentStore($store);
            
            /** Reinitialize the service parameters. */
            $this->integrationContext
                ->service()
                ->initApi();
            
            $this->currentStore = $store;
        } catch (\Exception $e) {
            $this->context->logger()->critical($e);
        }
        
        return $this;
    }
    
    
    /**
     * @return bool|StoreInterface
     */
    public function getDefaultStore($onlyIfActive = false)
    {
        $store = $this->context->storeManager()->getDefaultStoreView();
        
        if (true === $onlyIfActive) {
            if (!$this->configContext->general()->isModuleEnabled($store->getId())) {
                return false;
            }
        }
        
        return $store;
    }
    
    
    /**
     * @return StoreInterface
     */
    public function getCurrentStore()
    {
        return $this->currentStore;
    }
    
    
    /**
     * @return StoreInterface
     */
    public function getPreviousStore()
    {
        return $this->previousStore;
    }
    
    
    /**
     * @return StoreInterface
     */
    public function getInitialStore()
    {
        return $this->initialStore;
    }
    
    
    /**
     * @return bool
     */
    public function isIterating()
    {
        return (bool) $this->context->registryManager()->registry(self::REGISTRY_KEY);
    }
    
    
    /**
     * @return $this
     */
    protected function initIterator()
    {
        $this->context->registryManager()->register(self::REGISTRY_KEY, true, true);
        return $this;
    }
    
    
    /**
     * @return $this
     */
    protected function endIterator()
    {
        $this->context->registryManager()->unregister(self::REGISTRY_KEY);
        $this->reset();
        
        return $this;
    }
    
    
    /**
     * @return $this
     */
    public function reset()
    {
        $this->simulateStore($this->getInitialStore());
        $this->clear();
        
        return $this;
    }
    
    
    /**
     * @return $this
     */
    protected function clear()
    {
        $this->previousStore = null;
        $this->currentStore  = null;
        
        return $this;
    }
    
    
    /**
     * @param object $subject
     * @param string $method
     *
     * @return bool
     */
    protected function validateObjectMethod($subject, $method)
    {
        if (!is_object($subject)) {
            return false;
        }
        
        if (!method_exists($subject, $method)) {
            return false;
        }
        
        if (!is_callable([$subject, $method])) {
            return false;
        }
        
        return true;
    }
    
    
    /**
     * @return $this
     */
    protected function initStores()
    {
        if (!empty($this->stores)) {
            return $this;
        }
        
        try {
            $this->initialStore = $this->context->storeManager()->getStore();
            $this->currentStore = $this->context->storeManager()->getStore();
            
            /** @var array $stores */
            $stores = $this->context->storeManager()->getStores();
            
            $this->context->eventManager()->dispatch('bittools_skyhub_store_init_stores', [
                'stores' => $stores,
            ]);
            
            /** @var StoreInterface $store */
            foreach ($stores as $store) {
                $this->addStore($store);
            }
        } catch (\Exception $e) {
            $this->context->logger()->critical($e);
        }
        
        return $this;
    }
    
    
    /**
     * @param StoreInterface $store
     *
     * @return $this
     */
    protected function addStore(StoreInterface $store)
    {
        if (!$this->validateStore($store)) {
            return $this;
        }
        
        $this->stores[$store->getId()] = $store;
        return $this;
    }
    
    
    /**
     * @param StoreInterface|\Magento\Store\Model\Store $store
     *
     * @return bool
     */
    protected function validateStore(StoreInterface $store)
    {
        if ($store->getCode() == \Magento\Store\Model\Store::ADMIN_CODE) {
            return false;
        }
        
        if (!$store->isActive()) {
            return false;
        }
        
        if (!$this->configContext->general()->isModuleEnabled($store->getId())) {
            return false;
        }
        
        if (!$this->configContext->service()->isConfigurationOk($store->getId())) {
            return false;
        }
        
        return true;
    }
}
