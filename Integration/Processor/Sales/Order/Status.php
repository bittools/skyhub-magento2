<?php

namespace BitTools\SkyHub\Integration\Processor\Sales\Order;

use BitTools\SkyHub\Model\Config\Source\Skyhub\Status\Type as SkyHubStatusType;
use BitTools\SkyHub\Integration\Processor\AbstractProcessor;
use Magento\Sales\Model\Order;

class Status extends AbstractProcessor
{


    /**
     * @param string $skyhubStatusCode
     * @param string $skyhubStatusType
     * @param Order  $order
     *
     * @return bool|$this
     *
     * @throws \Exception
     */
    public function processOrderStatus($skyhubStatusCode, $skyhubStatusType, Order $order)
    {
        if (!$this->validateOrderStatusType($skyhubStatusType)) {
            return false;
        }

        $state = $this->getStateBySkyhubStatusType($skyhubStatusType);

        if ($order->getState() == $state) {
            return false;
        }

        /**
         * Order is already in the following states:
         *  - complete
         *  - closed
         */
        if ($order->isStateProtected($state)) {
            /** State is protected. */
            return false;
        }

        /**
         * If order is CANCELED in SkyHub.
         */
        if ($state == Order::STATE_CANCELED) {
            try {
                $this->cancelOrder($order);
            } catch (\Exception $e) {
                return false;
            }

            return true;
        }

        /**
         * If order is APPROVED in SkyHub.
         */
        if ($state == Order::STATE_PROCESSING) {
            try {
                $this->invoiceOrder($order);
            } catch (\Exception $e) {
                return false;
            }

            return true;
        }

        $message = __('Change automatically by SkyHub. Status %s, Type %s.', $skyhubStatusCode, $skyhubStatusType);

        $order->setState($state, true, $message);
        $order->save();

        return true;
    }


    /**
     * @param string $skyhubStatusType
     *
     * @return string
     */
    public function getStateBySkyhubStatusType($skyhubStatusType)
    {
        switch ($skyhubStatusType) {
            case SkyHubStatusType::TYPE_APPROVED:
                return Order::STATE_PROCESSING;
            case SkyHubStatusType::TYPE_CANCELED:
                return Order::STATE_CANCELED;
            case SkyHubStatusType::TYPE_DELIVERED:
            case SkyHubStatusType::TYPE_SHIPPED:
                return Order::STATE_COMPLETE;
            case SkyHubStatusType::TYPE_NEW:
            default:
                return Order::STATE_NEW;
        }
    }


    /**
     * @param string $skyhubStatusType
     *
     * @return bool
     */
    public function validateOrderStatusType($skyhubStatusType)
    {
        /** @var SkyHubStatusType $source */
        $source = $this->helperContext()->objectManager()->create(SkyHubStatusType::class);
        $allowedTypes = $source->toArray();

        return isset($allowedTypes[$skyhubStatusType]);
    }


    /**
     * @param Order $order
     * @return bool
     * @throws \Exception
     */
    protected function cancelOrder(Order $order)
    {
        if (!$order->canCancel()) {
            $order->addStatusHistoryComment(__('Order is canceled in SkyHub but could not be canceled in Magento.'));
            $order->save();

            return false;
        }

        $order->addStatusHistoryComment(__('Order canceled automatically by SkyHub.'));
        $order->cancel()->save();

        return true;
    }


    /**
     * @param Order $order
     * @return bool
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Exception
     */
    protected function invoiceOrder(Order $order)
    {
        if (!$order->canInvoice()) {
            $comment = __('This order is APPROVED in SkyHub but cannot be invoiced in Magento.');
            $order->addStatusHistoryComment($comment, true);
            $order->save();

            return false;
        }

        /** @var Order\Invoice $invoice */
        $invoice = $order->prepareInvoice();
        $invoice->register();

        $comment = __('Invoiced automatically via SkyHub.');
        $invoice->addComment($comment);

        $order->setIsInProcess(true);
        $order->setStatus($this->getApprovedOrdersStatus());
        $order->addStatusHistoryComment($comment, true);

        /** @var \Magento\Framework\DB\Transaction $transaction */
        $transaction = $this->helperContext()->objectManager()->create(\Magento\Framework\DB\Transaction::class);
        $transaction->addObject($order)
            ->addObject($invoice)
            ->save();

        return true;
    }
}
