<?php

namespace BitTools\SkyHub\Console\Integration\Catalog;

use BitTools\SkyHub\Helper\Context;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;

abstract class AbstractCatalog extends Command
{
    
    /** @var string */
    const INPUT_KEY_STORE_ID = 'store_id';
    
    /** @var Context */
    protected $context;
    
    
    /**
     * AbstractCatalog constructor.
     *
     * @param null|string $name
     * @param Context     $context
     */
    public function __construct($name = null, Context $context)
    {
        parent::__construct($name);
        
        $this->context = $context;
    }
    
    
    /**
     * @param null|int|string $storeId
     *
     * @return $this
     */
    protected function prepareStore($storeId = null)
    {
        if (!$storeId) {
            return $this;
        }
        
        try {
            $this->context->storeManager()->setCurrentStore($this->getStore($storeId));
        } catch (\Exception $e) {
            $this->context->logger()->critical($e);
        }
        
        return $this;
    }
    
    
    /**
     * @param null|int|string $storeId
     *
     * @return \Magento\Store\Api\Data\StoreInterface
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getStore($storeId = null)
    {
        return $this->context->storeManager()->getStore($storeId);
    }
    
    
    /**
     * @return InputOption
     */
    protected function getStoreIdOption()
    {
        return new InputOption(
            self::INPUT_KEY_STORE_ID,
            's',
            InputOption::VALUE_OPTIONAL,
            'The store ID',
            \Magento\Store\Model\Store::DEFAULT_STORE_ID
        );
    }
}
