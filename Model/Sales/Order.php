<?php

namespace BitTools\SkyHub\Model\Sales;

use BitTools\SkyHub\Api\OrderManagementInterface;
use BitTools\SkyHub\Api\OrderRepositoryInterface as OrderRelationRepositoryInterface;
use BitTools\SkyHub\Helper\Context;
use Magento\Framework\Webapi\Exception;
use Magento\Sales\Api\OrderRepositoryInterface;

class Order implements OrderManagementInterface
{
    /**
     * @var Context
     */
    protected $helperContext;

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var OrderRelationRepositoryInterface
     */
    protected $orderRelationRepository;

    public function __construct(
        Context $helperContext,
        OrderRepositoryInterface $orderRepository,
        OrderRelationRepositoryInterface $orderRelationRepository
    )
    {
        $this->helperContext = $helperContext;
        $this->orderRepository = $orderRepository;
        $this->orderRelationRepository = $orderRelationRepository;
    }

    /**
     * {@inheritDoc}
     */
    public function invoice($entityId, $invoiceKey)
    {
        $order = $this->orderRepository->get($entityId);

        /** @var \BitTools\SkyHub\Model\Order $info */
        $info = $order->getExtensionAttributes()->getSkyhubInfo();

        if (!$info) {
            throw new Exception(__('This order doesnt have a SkyHub block info.'));
        }

        if ($info->getInvoiceKey()) {
            throw new Exception(__('This order already have a invoice key.'));
        }

        if (!$info->validateInvoiceKey($invoiceKey)) {
            throw new Exception(__('Invalid invoice key (NF-e Key).'));
        }

        $info->setInvoiceKey($invoiceKey);

        $this->orderRelationRepository->save($info);

        return true;
    }
}
