<?php

namespace BitTools\SkyHub\Cron\Queue\Catalog;

use BitTools\SkyHub\Cron\Queue\AbstractQueue;
use Magento\Cron\Model\Schedule;
use Magento\Store\Api\Data\StoreInterface;
use SkyHub\Api\Handler\Response\HandlerDefault;
use SkyHub\Api\Handler\Response\HandlerException;

class Category extends AbstractQueue
{
    
    /**
     * @param Schedule $schedule
     *
     * @return mixed|void
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function create(Schedule $schedule)
    {
        if (!$this->canRun($schedule)) {
            return;
        }
        
        /** @var \Magento\Catalog\Model\ResourceModel\Category\Collection $categories */
        $categories = $this->getCategoryCollection();

        if (!$categories->getSize()) {
            $schedule->setMessages(__('No category to be listed right now.'));
            return;
        }
        
        $categoryIds = [];
        
        /** @var \Magento\Catalog\Model\Category $category */
        foreach ($categories as $category) {
            if ($category->getId() == $this->getStore()->getRootCategoryId()) {
                continue;
            }

            $categoryIds[] = $category->getId();
        }
        
        $this->getQueueResource()->queue(
            $categoryIds,
            \BitTools\SkyHub\Model\Entity::TYPE_CATALOG_CATEGORY,
            \BitTools\SkyHub\Model\Queue::PROCESS_TYPE_EXPORT
        );
        
        $schedule->setMessages(
            __('The categories were successfully queued. Category IDs: %1.', implode(',', $categoryIds))
        );
    }
    
    
    /**
     * @param Schedule $schedule
     *
     * @return mixed|void
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(Schedule $schedule)
    {
        $this->processStoreIteration($this, 'executeIntegration', $schedule);
        
        $successQueueIds = $this->extractResultSuccessIds($schedule);
        $failedQueueIds  = $this->extractResultFailIds($schedule);
        $message         = '';

        if (!$successQueueIds && !$failedQueueIds) {
            $message = __(
                'No category was processed at this time. Probably the queue was empty.'
            );
        }

        if ($successQueueIds || $failedQueueIds) {
            $this->getQueueResource()
                ->removeFromQueue($successQueueIds, \BitTools\SkyHub\Model\Entity::TYPE_CATALOG_CATEGORY);

            $message = __('Queue was processed. Success: %1.', implode(',', $successQueueIds));

            if (!empty($failedQueueIds)) {
                $message .= __(' Errors: %1.', implode(',', $failedQueueIds));
            }
        }
        
        $schedule->setMessages($message);
    }
    
    
    /**
     * @param Schedule       $schedule
     * @param StoreInterface $store
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function executeIntegration(Schedule $schedule, StoreInterface $store)
    {
        if (!$this->canRun($schedule)) {
            return;
        }
        
        $categoryIds = (array) $this->getQueueResource()->getPendingEntityIds(
            \BitTools\SkyHub\Model\Entity::TYPE_CATALOG_CATEGORY,
            \BitTools\SkyHub\Model\Queue::PROCESS_TYPE_EXPORT
        );
        
        if (empty($categoryIds)) {
            $schedule->setMessages(__('No category to be integrated right now.'));
            return;
        }
        
        $successQueueIds = [];
        $failedQueueIds  = [];
        
        /** @var int $categoryId */
        foreach ($categoryIds as $categoryId) {
            /** @var \Magento\Catalog\Model\Category $category */
            $category = $this->getCategory($categoryId, $store);
            
            /** @var \BitTools\SkyHub\Integration\Integrator\Catalog\Category $integrator */
            $integrator = $this->createObject(\BitTools\SkyHub\Integration\Integrator\Catalog\Category::class);
            
            /** @var HandlerDefault|HandlerException $response */
            $response = $integrator->createOrUpdate($category);
            
            if ($this->isErrorResponse($response)) {
                $failedQueueIds[] = $categoryId;
                
                $this->getQueueResource()->setFailedEntityIds(
                    $categoryId,
                    \BitTools\SkyHub\Model\Entity::TYPE_CATALOG_CATEGORY,
                    $response->message()
                );
                
                continue;
            }
            
            $successQueueIds[] = $categoryId;
        }
        
        $this->mergeResults($schedule, $successQueueIds, $failedQueueIds);
    }
    
    
    /**
     * @param int                 $categoryId
     * @param StoreInterface|null $store
     *
     * @return \Magento\Catalog\Api\Data\CategoryInterface
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getCategory($categoryId, StoreInterface $store = null)
    {
        $data = [
            'disable_flat' => true,
            'store_id'     => $store->getId()
        ];
        
        /** @var \Magento\Catalog\Api\CategoryRepositoryInterface $repository */
        $repository = $this->createObject(\Magento\Catalog\Api\CategoryRepositoryInterface::class);
        
        /** @var \Magento\Catalog\Api\Data\CategoryInterface $category */
        $category = $repository->get($categoryId, $store->getId());
        $category->setData($data)
            ->load($categoryId);
        
        return $category;
    }
    
    
    /**
     * @return \Magento\Catalog\Model\ResourceModel\Category\Collection
     */
    protected function getCategoryCollection()
    {
        /** @var \Magento\Catalog\Model\ResourceModel\Category\Collection $collection */
        $collection = $this->createObject(\Magento\Catalog\Model\ResourceModel\Category\Collection::class);
        $collection->addFieldToFilter('level', ['gteq' => $this->getRootCategoryLevel()]);
        // $collection->setDisableFlat(true);
        
        return $collection;
    }
    
    
    /**
     * @param array $categoryIds
     *
     * @return array
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function removeRootCategory(array &$categoryIds)
    {
        foreach ($categoryIds as $key => $categoryId) {
            if ($categoryId == $this->getStoreGroup()->getRootCategoryId()) {
                unset($categoryIds[$key]);
            }
        }
        
        return $categoryIds;
    }
    
    
    /**
     * @param Schedule $schedule
     * @param int|null $storeId
     *
     * @return bool
     */
    protected function canRun(Schedule $schedule, $storeId = null)
    {
        if (!$this->cronConfig()->catalogCategory()->isEnabled($storeId)) {
            $schedule->setMessages(__('Catalog Category Cron is Disabled'));
            return false;
        }
        
        return parent::canRun($schedule, $storeId);
    }


    /**
     * @return int
     */
    protected function getRootCategoryLevel()
    {
        return 2;
    }
}
