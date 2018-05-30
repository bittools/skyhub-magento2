<?php

namespace BitTools\SkyHub\Plugin\Sales;

use BitTools\SkyHub\Api\OrderRepositoryInterface as OrderRelationRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\Data\OrderInterface;

class Order
{

    /** @var \BitTools\SkyHub\Model\ResourceModel\OrderFactory */
    protected $orderRepository;

    /** @var OrderRelationRepositoryInterface */
    protected $orderRelationRepository;


    /**
     * Order constructor.
     *
     * @param OrderRelationRepositoryInterface $orderRelationRepository
     * @param OrderRepositoryInterface         $orderRepository
     */
    public function __construct(
        OrderRelationRepositoryInterface $orderRelationRepository,
        OrderRepositoryInterface $orderRepository
    )
    {
        $this->orderRelationRepository = $orderRelationRepository;
        $this->orderRepository         = $orderRepository;
    }


    /**
     * @param OrderRepositoryInterface $subject
     * @param OrderInterface           $result
     *
     * @return OrderInterface
     */
    public function afterGet(OrderRepositoryInterface $subject, OrderInterface $result)
    {
        /** @var \BitTools\SkyHub\Api\Data\OrderInterface $relation */
        $relation = $this->orderRelationRepository->getByOrderId($result->getEntityId());
        $result->getExtensionAttributes()->setSkyhubInfo($relation);

        return $result;
    }
}
