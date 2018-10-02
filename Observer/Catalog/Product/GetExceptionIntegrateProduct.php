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
        parent::__construct($context, $storeIterator, $orderRepository, $productRepository, $orderIntegrator, $productIntegrator, $productAttributeIntegrator, $categoryIntegrator, $queueRepository, $productValidation, $categoryValidation, $queueResourceFactory, $typeConfigurableFactory, $timezone);
    }

    public function execute(Observer $observer)
    {
        if (!$this->canRun()) {
            return;
        }

        if ($this->context->appState()->getAreaCode() != \Magento\Framework\App\Area::AREA_ADMINHTML) {
            return;
        }

        $msg = __('The product cannot be integrated: %1', $observer->getException()->getMessage());
        if ($observer->getMethod() == 'prepareIntegrationProduct') {
            $this->messageManager->addError($msg);
        }

    }
}