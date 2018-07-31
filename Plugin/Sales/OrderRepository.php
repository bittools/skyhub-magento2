<?php

namespace BitTools\SkyHub\Plugin\Sales;

use BitTools\SkyHub\Api\OrderRepositoryInterface as OrderRelationRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\Data\OrderInterface;
use BitTools\SkyHub\Api\Data\OrderInterfaceFactory as SkyhubOrderFactory;
use BitTools\SkyHub\Api\OrderRepositoryInterface as SkyhubOrderRepositoryInterface;
use BitTools\SkyHub\Model\Backend\Session\Quote;

class OrderRepository
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
     * OrderRepository constructor.
     *
     * @param OrderRelationRepositoryInterface $orderRelationRepository
     * @param OrderInterfaceFactory $orderFactory
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


    /**
     * Create SkyHub data in extension attributes
     *
     * @param OrderRepositoryInterface $subject
     * @param OrderInterface $result

     * @return OrderInterface
     * @throws \Exception
     */
    public function afterSave(OrderRepositoryInterface $subject, OrderInterface $result)
    {
        try {

            $relation = $result->getExtensionAttributes()->getSkyhubInfo();

            if (!$relation) {

                /** @var BitTools\SkyHub\Model\Backend\Session\Quote $sessionQuote */
                $sessionQuote = $this->quoteSession->getQuote();

                if (!$sessionQuote) {
                    return $result;
                }

                /** @var \BitTools\SkyHub\Api\Data\OrderInterface $relation */
                $relation = $this->skyhubOrderFactory->create();
                $relation->setOrderId($result->getId())
                    ->setStoreId($result->getStoreId())
                    ->setCode($sessionQuote->getSkyhubCode())
                    ->setChannel($sessionQuote->getSkyhubChannel())
                    ->setInterest($sessionQuote->getSkyhubInterest())
                    ->setDataSource(json_encode($sessionQuote->getSkyhubData()));

            }

            $this->skyhubOrderRepository->save($relation);
            $result->getExtensionAttributes()->setSkyhubInfo($relation);

            return $result;

        } catch (\Magento\Framework\Exception\AlreadyExistsException $e) {
            throw new \Exception($e);
        }

    }
}
