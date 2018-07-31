<?php

namespace BitTools\SkyHub\Plugin\Sales;

use BitTools\SkyHub\Api\OrderRepositoryInterface as OrderRelationRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\Data\OrderInterface;
use BitTools\SkyHub\Api\Data\OrderInterfaceFactory as SkyhubOrderFactory;
use BitTools\SkyHub\Api\OrderRepositoryInterface as SkyhubOrderRepositoryInterface;
use BitTools\SkyHub\Model\Backend\Session\Quote;

class Order
{

    /** @var OrderRepositoryInterface */
    protected $orderRepository;

    /** @var OrderRelationRepositoryInterface */
    protected $orderRelationRepository;

    /** @var SkyhubOrderFactory */
    protected $skyhubOrderFactory;

    /** @var Quote  */
    protected $quoteSession;

    /** @var SkyhubOrderRepositoryInterface  */
    protected $skyhubOrderRepository;


    /**
     * Order constructor.
     * 
     * @param OrderRepositoryInterface $orderRepository
     * @param SkyhubOrderRepositoryInterface $orderRelationRepository
     * @param SkyhubOrderFactory $skyhubOrderFactory
     * @param SkyhubOrderRepositoryInterface $skyhubOrderRepository
     * @param Quote $quoteSession
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        OrderRelationRepositoryInterface $orderRelationRepository,
        SkyhubOrderFactory $skyhubOrderFactory,
        SkyhubOrderRepositoryInterface $skyhubOrderRepository,
        Quote $quoteSession
    ) {
        $this->orderRepository          = $orderRepository;
        $this->orderRelationRepository  = $orderRelationRepository;
        $this->skyhubOrderFactory       = $skyhubOrderFactory;
        $this->skyhubOrderRepository    = $skyhubOrderRepository;
        $this->quoteSession             = $quoteSession;
    }


    /**
     * Create SkyHub data in extension attributes
     *
     * @param OrderInterface $subject
     * @param OrderInterface $result
     */
    public function afterSave(OrderInterface $subject, OrderInterface $result)
    {
        /** @var \BitTools\SkyHub\Api\Data\OrderInterface $relation */
        $relation = $result->getExtensionAttributes()->getSkyhubInfo();
        $this->orderRelationRepository->save($relation);
    }
}
