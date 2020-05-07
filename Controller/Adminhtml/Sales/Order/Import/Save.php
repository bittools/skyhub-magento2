<?php

namespace BitTools\SkyHub\Controller\Adminhtml\Sales\Order\Import;

use BitTools\SkyHub\Exceptions\UnprocessableException;
use Magento\Sales\Api\Data\OrderInterface;

class Save extends AbstractImport
{

    /**
     * @throws \Exception
     * @return \Magento\Framework\Controller\Result\Redirect
     *
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Redirect $redirectResult */
        $redirectResult = $this->createRedirectResult();
        $redirectResult->setPath('*/*/manual');

        $storeId = $this->getRequest()->getParam('store_id');
        $references = (array)explode(PHP_EOL, $this->getRequest()->getParam('reference'));

        if (empty($references)) {
            $this->messageManager->addWarningMessage(__('No order reference was informed.'));
            return $redirectResult;
        }

        $references = array_unique(
            array_map(
                function (&$value) {
                    return trim($value);
                },
                $references
            )
        );

        /** @var string $reference */
        foreach ($references as $reference) {
            $this->importOrder($reference, $storeId);
        }

        $this->messageManager->addNoticeMessage(__('The process is finished.'));

        return $redirectResult;
    }

    /**
     * @param string $referenceCode
     * @param null|int $storeId
     *
     * @throws \Exception
     * @return bool
     *
     */
    protected function importOrder($referenceCode, $storeId = null)
    {
        $this->helperContext->storeManager()->setCurrentStore($storeId);

        /** @var array|bool $orderData */
        $orderData = $this->getOrderIntegrator()->order($referenceCode);

        if (!$orderData) {
            $this->messageManager->addWarningMessage(
                $this->message->getNonExistentOrderMessage($referenceCode)
            );

            return false;
        }

        try {
            /** @var bool|OrderInterface $order */
            $order = $this->getOrderProcessor()->createOrder($orderData);
        } catch (UnprocessableException $e) {
            $order = false;
        }

        if (!$order || !$order->getEntityId()) {
            $this->messageManager->addWarningMessage($this->message->getNotCreatedOrderMessage($referenceCode));
            return false;
        }

        //@todo remove from queue

        $this->messageManager->addSuccessMessage($this->message->getOrderCreationMessage($order, $referenceCode));
        return true;
    }
}
