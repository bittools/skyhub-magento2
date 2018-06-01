<?php

namespace BitTools\SkyHub\Controller\Adminhtml\Integrate\Catalog;

use BitTools\SkyHub\Integration\Integrator\Catalog\Product as Integrator;
use BitTools\SkyHub\Controller\Adminhtml\Integrate\AbstractIntegrate;
use BitTools\SkyHub\Helper\Context as HelperContext;
use Magento\Backend\App\Action\Context as BackendContext;
use SkyHub\Api\Handler\Response\HandlerInterface;

class Product extends AbstractIntegrate
{

    /** @var Integrator */
    protected $integrator;


    /**
     * Product constructor.
     *
     * @param BackendContext $backendContext
     * @param HelperContext  $helperContext
     * @param Integrator     $integrator
     */
    public function __construct(BackendContext $backendContext, HelperContext $helperContext, Integrator $integrator)
    {
        parent::__construct($backendContext, $helperContext);
        $this->integrator = $integrator;
    }


    /**
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $productId = (int) $this->getRequest()->getParam('id');

        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->createRedirectResult();
        $resultRedirect->setPath('catalog/product/edit', ['id' => $productId]);

        if (!$productId) {
            $this->messageManager->addWarningMessage(__('The product ID must be provided.'));
            $resultRedirect->setPath('catalog/product');
            return $resultRedirect;
        }

        /** @var HandlerInterface $response */
        $response = $this->integrator->createOrUpdateById($productId);

        if (false === $response) {
            $this->messageManager->addErrorMessage(__('The product ID %1 cannot be integrated.', $productId));
            return $resultRedirect;
        }

        if ($response->success()) {
            $this->messageManager->addSuccessMessage(__('The product was successfully integrated.'));
        }

        if ($response->exception()) {
            $this->messageManager->addErrorMessage(__('The product cannot be integrated.'));
        }

        return $resultRedirect;
    }
}
