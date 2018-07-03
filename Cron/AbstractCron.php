<?php

namespace BitTools\SkyHub\Cron;

use BitTools\SkyHub\Functions;
use Magento\Cron\Model\Schedule;
use Magento\Store\Api\Data\StoreInterface;

abstract class AbstractCron
{
    
    use Functions;
    
    /** @var \Magento\Framework\App\State */
    protected $state;
    
    /** @var Context */
    protected $context;
    
    /** @var \BitTools\SkyHub\StoreConfig\Context */
    protected $configContext;
    
    /** @var \BitTools\SkyHub\Model\StoreIteratorInterface */
    protected $storeIterator;
    
    /** @var \Magento\Store\Api\GroupRepositoryInterface */
    protected $groupRepository;
    
    
    public function __construct(
        Context $context,
        \BitTools\SkyHub\StoreConfig\Context $configContext,
        \BitTools\SkyHub\Model\StoreIteratorInterface $storeIterator,
        \Magento\Store\Api\GroupRepositoryInterface $groupRepository,
        \Magento\Framework\App\State $state
    ) {
        $this->context         = $context;
        $this->configContext   = $configContext;
        $this->storeIterator   = $storeIterator;
        $this->groupRepository = $groupRepository;
        $this->state           = $state;
    }
    
    
    /**
     * @param Schedule $schedule
     * @param int|null $storeId
     *
     * @return bool
     */
    protected function canRun(Schedule $schedule, $storeId = null)
    {
        $isEnabled = $this->configContext->general()->isModuleEnabled($storeId);
        
        /**
         * If a Store ID is specified it needs to be privileged.
         */
        if (!empty($storeId) && $this->getStore($storeId)) {
            if (!$isEnabled) {
                return false;
            }
            
            return true;
        }
        
        /**
         * Otherwise checks if any store is activated to use the module.
         */
        if (!$isEnabled && empty($this->storeIterator->getStores())) {
            $schedule->setMessages(__('Module is not enabled in configuration.'));
            return false;
        }
        
        return true;
    }
    
    
    /**
     * Get the current Store. Please, consider the Store Iterator.
     *
     * @param int|null $storeId
     *
     * @return bool|StoreInterface
     */
    protected function getStore($storeId = null)
    {
        try {
            /** @var StoreInterface $store */
            $store = $this->context->helperContext()->storeManager()->getStore($storeId);
            
            if ($store && $store->getId()) {
                return $store;
            }
        } catch (\Exception $e) {
            $this->context
                ->helperContext()
                ->logger()
                ->critical($e);
        }
        
        return false;
    }
    
    
    /**
     * @return int
     */
    protected function getStoreId()
    {
        return $this->getStore()->getId();
    }
    
    
    /**
     * @param \SkyHub\Api\Handler\Response\HandlerInterface $response
     *
     * @return bool
     */
    protected function isErrorResponse($response)
    {
        if (!$response) {
            return true;
        }
        
        if ($response->invalid()) {
            return true;
        }
        
        if ($response->exception()) {
            return true;
        }
        
        return false;
    }
    
    
    /**
     * Process the iteration for any class that needs to be iterated through the stores.
     * If false is returned you need to skip the original call.
     *
     * E.g.:
     *
     * public function execute(Mage_Cron_Model_Schedule $schedule)
     * {
     *     if ($this->processIteration($this, 'execute', $schedule)) {
     *         return;
     *     }
     *
     *     ...
     * }
     *
     * @param object      $object
     * @param string      $method
     * @param array|mixed $params
     *
     * @return bool
     */
    protected function processStoreIteration($object, $method, $params = null)
    {
        if (!is_array($params)) {
            $params = [$params];
        }
        
        if (!$this->storeIterator->isIterating()) {
            $this->storeIterator->iterate($object, $method, $params);
            return true;
        }
        
        return false;
    }
    
    
    /**
     * @param Schedule $schedule
     * @param array    $successIds
     * @param array    $failIds
     *
     * @return $this
     */
    protected function mergeResults(Schedule $schedule, array $successIds = [], array $failIds = [])
    {
        $successQueueIds = (array) $schedule->getData('success_queue_ids');
        $failedQueueIds  = (array) $schedule->getData('failed_queue_ids');
        
        $successQueueIds = array_unique(array_merge($successQueueIds, $successIds));
        $failedQueueIds  = array_unique(array_merge($failedQueueIds, $failIds));
        
        $schedule->setData('success_queue_ids', $successQueueIds);
        $schedule->setData('failed_queue_ids', $failedQueueIds);
        
        $byStore     = (array) $schedule->getData('by_store');
        $dataByStore = [
            $this->getStoreId() => [
                'success_queue_ids' => $successIds,
                'failed_queue_ids'  => $failIds,
            ]
        ];
        
        $schedule->setData('by_store', array_merge_recursive($byStore, $dataByStore));
        
        return $this;
    }
    
    
    /**
     * @param Schedule $schedule
     *
     * @return array
     */
    protected function extractResultSuccessIds(Schedule $schedule)
    {
        $successQueueIds = (array) $schedule->getData('success_queue_ids');
        return $successQueueIds;
    }
    
    
    /**
     * @param Schedule $schedule
     *
     * @return array
     */
    protected function extractResultFailIds(Schedule $schedule)
    {
        $failQueueIds = (array) $schedule->getData('failed_queue_ids');
        return $failQueueIds;
    }
    
    
    /**
     * @param string $class
     *
     * @return mixed
     */
    protected function createObject($class)
    {
        return $this->context
            ->helperContext()
            ->objectManager()
            ->create($class);
    }
    
    
    /**
     * @param null|int $storeId
     *
     * @return \Magento\Store\Api\Data\GroupInterface
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getStoreGroup($storeId = null)
    {
        $store = $this->context
            ->helperContext()
            ->storeManager()
            ->getStore($storeId);
        
        /** @var \Magento\Store\Api\Data\GroupInterface $group */
        $group = $this->groupRepository->get($store->getStoreGroupId());
        
        return $group;
    }
    
    
    /**
     * @return Config
     */
    protected function cronConfig()
    {
        return $this->context->cronConfig();
    }
    
    
    /**
     * @return $this
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Exception
     */
    protected function initArea()
    {
        try {
            $this->state->getAreaCode();
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_CRONTAB);
        } catch (\Exception $e) {
            $this->context->helperContext()->logger()->critical($e);
            throw $e;
        }
        
        return $this;
    }
}
