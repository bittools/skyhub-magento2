<?php

namespace BitTools\SkyHub\Plugin\Sales;

use BitTools\SkyHub\Api\OrderRepositoryInterface as OrderRelationRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\Data\OrderInterface;

class OrderRepository
{

    /** @var OrderRelationRepositoryInterface */
    protected $orderRelationRepository;


    /**
     * Order constructor.
     *
     * @param OrderRelationRepositoryInterface $orderRelationRepository
     */
    public function __construct(OrderRelationRepositoryInterface $orderRelationRepository)
    {
        $this->orderRelationRepository = $orderRelationRepository;
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
