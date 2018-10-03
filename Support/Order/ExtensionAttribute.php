<?php

namespace BitTools\SkyHub\Support\Order;

use BitTools\SkyHub\Support\Api\ExtensionAttributeInterface;

class ExtensionAttribute implements ExtensionAttributeInterface
{

    /** @var \Magento\Sales\Api\OrderRepositoryInterface  */
    protected $orderRelationRepository;

    /** @var \BitTools\SkyHub\Api\Data\OrderInterfaceFactory  */
    protected $skyhubOrderFactory;

    /** @var \BitTools\SkyHub\Model\Backend\Session\Quote  */
    protected $quoteSession;

    /** @var \BitTools\SkyHub\Api\OrderRepositoryInterface  */
    protected $skyhubOrderRepository;

    /** @var \Magento\Sales\Api\Data\OrderExtensionFactory  */
    protected $orderExtensionFactory;

    /**
     * @var \Magento\Framework\App\State
     */
    protected $appState;

    /**
     * ExtensionAttribute constructor.
     *
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRelationRepository
     * @param \BitTools\SkyHub\Api\Data\OrderInterfaceFactory $skyhubOrderFactory
     * @param \BitTools\SkyHub\Api\OrderRepositoryInterface $skyhubOrderRepository
     * @param \BitTools\SkyHub\Model\Backend\Session\Quote $quoteSession
     */
    public function __construct(
        \Magento\Sales\Api\OrderRepositoryInterface $orderRelationRepository,
        \BitTools\SkyHub\Api\Data\OrderInterfaceFactory $skyhubOrderFactory,
        \BitTools\SkyHub\Api\OrderRepositoryInterface $skyhubOrderRepository,
        \BitTools\SkyHub\Model\Backend\Session\Quote $quoteSession,
        \Magento\Sales\Api\Data\OrderExtensionFactory $orderExtensionFactory,
        \Magento\Framework\App\State $appState
    ) {
        $this->orderRelationRepository  = $orderRelationRepository;
        $this->skyhubOrderFactory       = $skyhubOrderFactory;
        $this->skyhubOrderRepository    = $skyhubOrderRepository;
        $this->quoteSession             = $quoteSession;
        $this->orderExtensionFactory    = $orderExtensionFactory;
        $this->appState                 = $appState;
    }


    /**
     * Get SkyHub order data and set it into order extension attributes
     *
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     *
     * @return \Magento\Sales\Api\Data\OrderInterface
     */
    public function get(\Magento\Sales\Api\Data\OrderInterface $order)
    {
        /** @var \BitTools\SkyHub\Api\OrderRepositoryInterface $relation */
        $relation = $this->skyhubOrderRepository->getByOrderId($order->getEntityId());

        /** @var OrderExtensionInterface $extensionAttributes */
        $extensionAttributes = $order->getExtensionAttributes();

        if ($extensionAttributes) {
            $order->getExtensionAttributes()->setSkyhubInfo($relation);
            return $order;
        }

        $extensionAttributes = $this->orderExtensionFactory->create();
        $extensionAttributes->setSkyhubInfo($relation);
        $order->setExtensionAttributes($extensionAttributes);

        return $order;
    }


    /**
     * Save SkyHub order data custom extension attribute table
     *
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     *
     * @return \Magento\Sales\Api\Data\OrderInterface
     * @throws \Exception
     */
    public function save(\Magento\Sales\Api\Data\OrderInterface $order)
    {
        if ($order->getPayment()->getMethod() != \BitTools\SkyHub\Model\Payment\Method\Standard::CODE) {
            return $order;
        }

        try {

            $extensionAttribute = $order->getExtensionAttributes();
            if (!$extensionAttribute) {
                return $order;
            }

            /** @var \BitTools\SkyHub\Api\Data\OrderInterface $relation */
            $relation = $extensionAttribute->getSkyhubInfo();

            if (!$relation) {

                /** @var \BitTools\SkyHub\Model\Backend\Session\Quote $sessionQuote */
                $sessionQuote = $this->quoteSession->getQuote();
                if (!$sessionQuote || !$sessionQuote->getSkyhubCode()) {
                    return $order;
                }

                $relation = $this->skyhubOrderFactory->create();
                $relation->setOrderId($order->getId())
                    ->setStoreId($order->getStoreId())
                    ->setCode($sessionQuote->getSkyhubCode())
                    ->setChannel($sessionQuote->getSkyhubChannel())
                    ->setInterest($sessionQuote->getSkyhubInterest())
                    ->setDataSource(json_encode($sessionQuote->getSkyhubData()));

            }

            $this->skyhubOrderRepository->save($relation);
            $order->getExtensionAttributes()->setSkyhubInfo($relation);

        } catch (\Magento\Framework\Exception\AlreadyExistsException $e) {
            throw new \Exception($e);
        }

        return $order;
    }
}