<?php

namespace BitTools\SkyHub\Integration\Processor;

use BitTools\SkyHub\Functions;
use BitTools\SkyHub\Integration\Context as IntegrationContext;

abstract class AbstractProcessor
{

    use Functions;


    /** @var IntegrationContext */
    private $integrationContext;


    /**
     * AbstractProcessor constructor.
     *
     * @param IntegrationContext $integrationContext
     */
    public function __construct(IntegrationContext $integrationContext)
    {
        $this->integrationContext = $integrationContext;
    }


    /**
     * @return \Psr\Log\LoggerInterface
     */
    protected function logger()
    {
        return $this->helperContext()->logger();
    }


    /**
     * @return \Magento\Framework\Event\ManagerInterface
     */
    protected function eventManager()
    {
        return $this->helperContext()->eventManager();
    }


    /**
     * @return \Magento\Store\Model\StoreManagerInterface
     */
    protected function storeManager()
    {
        return $this->helperContext()->storeManager();
    }


    /**
     * @return \Magento\Framework\ObjectManagerInterface
     */
    protected function objectManager()
    {
        return $this->helperContext()->objectManager();
    }


    /**
     * @return \BitTools\SkyHub\Helper\Context
     */
    protected function helperContext()
    {
        return $this->integrationContext()->helperContext();
    }


    /**
     * @return IntegrationContext
     */
    protected function integrationContext()
    {
        return $this->integrationContext;
    }
}
