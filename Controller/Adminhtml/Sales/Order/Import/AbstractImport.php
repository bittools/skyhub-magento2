<?php

namespace BitTools\SkyHub\Controller\Adminhtml\Sales\Order\Import;

use BitTools\SkyHub\Controller\Adminhtml\AbstractController;
use BitTools\SkyHub\Helper\Sales\Order\Created\Message;
use Magento\Backend\App\Action\Context as BackendContext;
use BitTools\SkyHub\Helper\Context as HelperContext;
use BitTools\SkyHub\Integration\Integrator\Sales\OrderFactory as OrderIntegratorFactory;
use BitTools\SkyHub\Integration\Processor\Sales\OrderFactory as OrderProcessorFactory;

abstract class AbstractImport extends AbstractController
{
    
    /** @var OrderIntegratorFactory */
    protected $orderIntegratorFactory;
    
    /** @var OrderProcessorFactory */
    protected $orderProcessorFactory;
    
    /** @var Message */
    protected $message;
    
    
    public function __construct(
        BackendContext $context,
        HelperContext $helperContext,
        OrderIntegratorFactory $orderIntegratorFactory,
        OrderProcessorFactory $orderProcessorFactory,
        Message $message
    ) {
        parent::__construct($context, $helperContext);
        
        $this->orderIntegratorFactory = $orderIntegratorFactory;
        $this->orderProcessorFactory  = $orderProcessorFactory;
        $this->message                = $message;
    }
    
    
    /**
     * @return \BitTools\SkyHub\Integration\Integrator\Sales\Order
     */
    protected function getOrderIntegrator()
    {
        return $this->orderIntegratorFactory->create();
    }
    
    
    /**
     * @return \BitTools\SkyHub\Integration\Processor\Sales\Order
     */
    protected function getOrderProcessor()
    {
        return $this->orderProcessorFactory->create();
    }
}
