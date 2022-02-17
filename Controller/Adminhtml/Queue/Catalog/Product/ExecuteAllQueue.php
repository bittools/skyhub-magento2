<?php
/**
 * BSeller Platform | B2W - Companhia Digital
 *
 * Do not edit this file if you want to update this module for future new versions.
 *
 * @copyright Copyright (c) 2022 B2W Digital - BSeller Platform. (http://www.bseller.com.br)
 *
 * Access https://ajuda.skyhub.com.br/hc/pt-br/requests/new for questions and other requests.
 */

namespace BitTools\SkyHub\Controller\Adminhtml\Queue\Catalog\Product;

use BitTools\SkyHub\Model\Queue as QueueModel;

/**
 * ExecuteAllQueue class
 */
class ExecuteAllQueue extends ExecuteQueue
{
    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $integrableStatuses = [
            QueueModel::STATUS_PENDING,
            QueueModel::STATUS_RETRY
        ];
        $collection = $this->collectionFactory->create();
        $collection->getSelect()
            ->where('status IN (?)', $integrableStatuses)
            ->where('can_process = ?', 1)
            ->where('process_type = ?', (int)\BitTools\SkyHub\Model\Queue::PROCESS_TYPE_EXPORT)
            ->where('process_after <= ?', date('Y-m-d H:i:s'))
            ->where('entity_type = ?', (string) \BitTools\SkyHub\Model\Entity::TYPE_CATALOG_PRODUCT);
        $collection->load();
        return $this->executeQueueSelected($collection);
    }
}