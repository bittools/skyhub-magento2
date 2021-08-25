<?php

/**
 * BitTools Platform | B2W - Companhia Digital
 *
 * Do not edit this file if you want to update this module for future new versions.
 *
 * @category  BitTools
 * @package   BitTools_SkyHub
 *
 * @copyright Copyright (c) 2021 B2W Digital - BitTools Platform.
 *
 * Access https://ajuda.skyhub.com.br/hc/pt-br/requests/new for questions and other requests.
 */

namespace BitTools\SkyHub\Controller\Adminhtml\Sales\Order\Invoicedxml;

use BitTools\SkyHub\Controller\Adminhtml\AbstractController;
use Magento\Framework\Message\ManagerInterface as MessageManager;
use Magento\Backend\App\Action\Context as BackendContext;
use BitTools\SkyHub\Helper\Context as HelperContext;
use Magento\Sales\Api\OrderRepositoryInterface;
use BitTools\SkyHub\Integration\Integrator\Sales\Order\InvoicedXml;
use Magento\Framework\Controller\ResultFactory;

/**
 * UploadXml class
 */
class Uploadxml extends AbstractController
{
    /**
     * @var MessageManager
     */
    protected $messageManager;

    /**
     * @var InvoicedXml
     */
    protected $invoicedXmlIntegrator;

    /**
     * @var ResultFactory
     */
    protected $resultFactory;

    /**
     * AbstractController constructor.
     *
     * @param BackendContext $context
     * @param HelperContext $helperContext
     * @param OrderRepositoryInterface $orderRepository
     * @param MessageManager $messageManager
     * @param InvoicedXml $invoicedXmlIntegrator
     * @param ResultFactory $resultFactory
     */
    public function __construct(
        BackendContext $context,
        HelperContext $helperContext,
        OrderRepositoryInterface $orderRepository,
        MessageManager $messageManager,
        InvoicedXml $invoicedXmlIntegrator,
        ResultFactory $resultFactory
    ) {
        parent::__construct($context, $helperContext);
        $this->messageManager = $messageManager;
        $this->helperContext = $helperContext;
        $this->orderRepository = $orderRepository;
        $this->invoicedXmlIntegrator = $invoicedXmlIntegrator;
        $this->resultFactory = $resultFactory;
    }

    /**
     * Execute controller
     */
    public function execute()
    {
        try {
            $order = $this->getOrder();

            if (!$_FILES['file']) {
                throw new \Exception(__('File is required.'));
            }

            if (!$this->getRequest()->has('volume_qty')) {
                throw new \Exception(__('Volume Qty is required.'));
            }
    
            $bsellerSkyhubCode = $this->getRequest()->getPost('bseller_skyhub_code');
            /** @var \BitTools\SkyHub\Api\Data\OrderInterface $info */
            $info = $this->getOrder()->getExtensionAttributes()->getSkyhubInfo();
            if ($info->getCode() != $bsellerSkyhubCode) {
                throw new \Exception(__('Order Id not found.'));
            }

            $file = $_FILES['file'];
            $fileName = $file['name'];
            $pathFile = $file['tmp_name'];
            $volumeQty = $this->getRequest()->getPost('volume_qty');
            $this->helperContext->storeManager()->setCurrentStore($order->getStoreId());
            
            $this->invoicedXmlIntegrator->sendInvoiceXml(
                $bsellerSkyhubCode,
                $volumeQty,
                $pathFile,
                $fileName
            );

            $extensionAttributes = $order->getExtensionAttributes();
            $extensionAttributes->getSkyhubInfo()->setSkyhubNfeXml(true)->save();
            $order->setExtensionAttributes($extensionAttributes);
            $order->save();
            $this->messageManager->addSuccess(__('Nfe XML send to SkyHub.'));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }
        $orderId = (int) $this->getRequest()->getParam('order_id');
        $redirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $redirect->setPath('sales/order/view', [
            'order_id' => $orderId
        ]);
        return $redirect;
    }

    /**
     * @return bool|\Magento\Sales\Model\Order
     */
    protected function getOrder()
    {
        $orderId = (int) $this->getRequest()->getParam('order_id');
        
        if (!$orderId) {
            return false;
        }
    
        try {
            /** @var \Magento\Sales\Model\Order $order */
            $order = $this->orderRepository->get($orderId);            
            return $order;
        } catch (\Exception $e) {
            throw new \Exception(__('Order Id not found.'));
        }
    }
}
