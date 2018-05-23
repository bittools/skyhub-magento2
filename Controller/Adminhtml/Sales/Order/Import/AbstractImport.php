<?php

namespace BitTools\SkyHub\Controller\Adminhtml\Sales\Order\Import;

use BitTools\SkyHub\Controller\Adminhtml\AbstractController;
use Magento\Backend\App\Action\Context as BackendContext;
use BitTools\SkyHub\Helper\Context as HelperContext;
use BitTools\SkyHub\Integration\Integrator\Sales\OrderFactory as OrderIntegratorFactory;

abstract class AbstractImport extends AbstractController
{
    
    /** @var OrderIntegratorFactory */
    protected $orderIntegratorFactory;
    
    
    public function __construct(
        BackendContext $context,
        HelperContext $helperContext,
        OrderIntegratorFactory $orderIntegratorFactory
    )
    {
        parent::__construct($context, $helperContext);
        
        $this->orderIntegratorFactory = $orderIntegratorFactory;
    }
    
    
    /**
     * @return \BitTools\SkyHub\Integration\Integrator\Sales\Order
     */
    protected function getOrderIntegrator()
    {
        return $this->orderIntegratorFactory->create();
    }
}
