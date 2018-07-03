<?php

namespace BitTools\SkyHub\Integration\Processor\Sales\Order;

use BitTools\SkyHub\Model\Config\Source\Skyhub\Status\Type as SkyHubStatusType;
use BitTools\SkyHub\Integration\Processor\AbstractProcessor;
use BitTools\SkyHub\Integration\Context as IntegrationContext;
use BitTools\SkyHub\StoreConfig\Context as ConfigContext;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Framework\DB\Transaction as DBTransaction;

class Status extends AbstractProcessor
{

    /** @var OrderRepositoryInterface */
    protected $orderRepository;

    /** @var ConfigContext */
    protected $configContext;


    public function __construct(
        ConfigContext $configContext,
        IntegrationContext $integrationContext,
        OrderRepositoryInterface $orderRepository
    ) {
        parent::__construct($integrationContext);

        $this->orderRepository = $orderRepository;
        $this->configContext   = $configContext;
    }


    /**
     * @param string               $skyhubStatusCode
     * @param string               $skyhubStatusType
     * @param Order|OrderInterface $order
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

        $message = __('Change automatically by SkyHub. Status %1, Type %2.', $skyhubStatusCode, $skyhubStatusType);

        $order->setState($state)
            ->setData('is_updated', true)
            ->addStatusHistoryComment($message, true);
        
        $this->orderRepository->save($order);

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
            $this->orderRepository->save($order);

            return false;
        }

        $order->addStatusHistoryComment(__('Order canceled automatically by SkyHub.'));
        $order->cancel();

        $this->orderRepository->save($order);

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
            $this->orderRepository->save($order);

            return false;
        }

        /** @var Order\Invoice $invoice */
        $invoice = $order->prepareInvoice();
        $invoice->register();

        $comment = __('Invoiced automatically via SkyHub.');
        $invoice->addComment($comment);

        /** @var string $approvedOrdersStatus */
        $approvedOrdersStatus = $this->configContext->salesOrderStatus()->getApprovedOrdersStatus();
        
        $order->setIsInProcess(true);
        $order->setStatus($approvedOrdersStatus);
        $order->addStatusHistoryComment($comment, true);

        $this->getTransaction()
            ->addObject($order)
            ->addObject($invoice)
            ->save();

        return true;
    }


    /**
     * @return DBTransaction
     */
    protected function getTransaction()
    {
        /** @var DBTransaction $transaction */
        $transaction = $this->helperContext()->objectManager()->create(DBTransaction::class);
        return $transaction;
    }
}
