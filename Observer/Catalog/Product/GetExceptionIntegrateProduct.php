<?php

namespace BitTools\SkyHub\Observer\Catalog\Product;

use BitTools\SkyHub\Observer\Catalog\AbstractCatalog;
use Magento\Framework\Event\Observer;

class GetExceptionIntegrateProduct extends AbstractCatalog
{
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * GetExceptionIntegrateProduct constructor.
     * @param \BitTools\SkyHub\Helper\Context $context
     * @param \BitTools\SkyHub\Model\StoreIteratorInterface $storeIterator
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \BitTools\SkyHub\Integration\Integrator\Sales\Order $orderIntegrator
     * @param \BitTools\SkyHub\Integration\Integrator\Catalog\Product $productIntegrator
     * @param \BitTools\SkyHub\Integration\Integrator\Catalog\Product\Attribute $productAttributeIntegrator
     * @param \BitTools\SkyHub\Integration\Integrator\Catalog\Category $categoryIntegrator
     * @param \BitTools\SkyHub\Api\QueueRepositoryInterface $queueRepository
     * @param \BitTools\SkyHub\Integration\Integrator\Catalog\ProductValidation $productValidation
     * @param \BitTools\SkyHub\Integration\Integrator\Catalog\CategoryValidation $categoryValidation
     * @param \BitTools\SkyHub\Model\ResourceModel\QueueFactory $queueResourceFactory
     * @param \Magento\ConfigurableProduct\Model\Product\Type\ConfigurableFactory $typeConfigurableFactory
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     */
    public function __construct(
        \BitTools\SkyHub\Helper\Context $context,
        \BitTools\SkyHub\Model\StoreIteratorInterface $storeIterator,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \BitTools\SkyHub\Integration\Integrator\Sales\Order $orderIntegrator,
        \BitTools\SkyHub\Integration\Integrator\Catalog\Product $productIntegrator,
        \BitTools\SkyHub\Integration\Integrator\Catalog\Product\Attribute $productAttributeIntegrator,
        \BitTools\SkyHub\Integration\Integrator\Catalog\Category $categoryIntegrator,
        \BitTools\SkyHub\Api\QueueRepositoryInterface $queueRepository,
        \BitTools\SkyHub\Integration\Integrator\Catalog\ProductValidation $productValidation,
        \BitTools\SkyHub\Integration\Integrator\Catalog\CategoryValidation $categoryValidation,
        \BitTools\SkyHub\Model\ResourceModel\QueueFactory $queueResourceFactory,
        \Magento\ConfigurableProduct\Model\Product\Type\ConfigurableFactory $typeConfigurableFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        $this->messageManager = $messageManager;
        parent::__construct(
            $context,
            $storeIterator,
            $orderRepository,
            $productRepository,
            $orderIntegrator,
            $productIntegrator,
            $productAttributeIntegrator,
            $categoryIntegrator,
            $queueRepository,
            $productValidation,
            $categoryValidation,
            $queueResourceFactory,
            $typeConfigurableFactory,
            $timezone
        );
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        if (!$this->canRun()) {
            return;
        }

        if ($this->context->appState()->getAreaCode() != \Magento\Framework\App\Area::AREA_ADMINHTML) {
            return;
        }

        if ($observer->getMethod() != 'prepareIntegrationProduct') {
            return;
        }

        $this->messageManager->addError(
            __('The product cannot be integrated: %1', $observer->getException()->getMessage())
        );
    }
}