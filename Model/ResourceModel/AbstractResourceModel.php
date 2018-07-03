<?php


namespace BitTools\SkyHub\Model\ResourceModel;

use BitTools\SkyHub\Helper\Context as HelperContext;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

abstract class AbstractResourceModel extends AbstractDb
{
    
    /** @var HelperContext */
    protected $helperContext;
    
    
    /**
     * Queue constructor.
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        HelperContext $helperContext
    ) {
        parent::__construct($context);
        $this->helperContext = $helperContext;
    }
    
    
    /**
     * @return \Magento\Store\Model\StoreManagerInterface
     */
    protected function storeManager()
    {
        return $this->helperContext->storeManager();
    }
    
    
    /**
     * @return \Magento\Framework\ObjectManagerInterface
     */
    protected function objectManager()
    {
        return $this->helperContext->objectManager();
    }
    
    
    /**
     * @return \Psr\Log\LoggerInterface
     */
    protected function logger()
    {
        return $this->helperContext->logger();
    }
}
