<?php

namespace BitTools\SkyHub\Plugin\Sales;

use BitTools\SkyHub\Api\OrderRepositoryInterface as OrderRelationRepositoryInterface;
use Magento\Sales\Api\Data\OrderInterface;

class Order
{
    
    /** @var OrderRelationRepositoryInterface */
    protected $orderRelationRepository;


    /**
     * Order constructor.
     *
     * @param OrderRelationRepositoryInterface $orderRelationRepository
     */
    public function __construct(
        OrderRelationRepositoryInterface $orderRelationRepository
    ) {
        $this->orderRelationRepository = $orderRelationRepository;
    }
    
    
    /**
     * @param OrderInterface $subject
     * @param OrderInterface $result
     *
     * @return OrderInterface
     */
    public function afterSave(OrderInterface $subject, OrderInterface $result)
    {
        /** @var \BitTools\SkyHub\Api\Data\OrderInterface $relation */
        $relation = $result->getExtensionAttributes()->getSkyhubInfo();
        $this->orderRelationRepository->save($relation);
        
        return $result;
    }
}
