<?php

namespace BitTools\SkyHub\Console;

use BitTools\SkyHub\Helper\Context;
use Symfony\Component\Console\Style\StyleInterface;

abstract class AbstractConsole extends \Symfony\Component\Console\Command\Command
{

    /** @var \Magento\Framework\App\State */
    private $state;
    
    /** @var Context */
    protected $context;

    /** @var StyleInterface */
    protected $style;
    
    /** @var \BitTools\SkyHub\Cron\Queue\Catalog\Product\AttributeFactory */
    protected $productAttributeFactory;

    /**
     * AbstractConsole constructor.
     * @param \Magento\Framework\App\State                                 $state
     * @param \BitTools\SkyHub\Cron\Queue\Catalog\Product\AttributeFactory $productAttributeFactory
     * @param Context                                                      $context
     * @param null                                                         $name
     */
    public function __construct(
        \Magento\Framework\App\State $state,
        \BitTools\SkyHub\Cron\Queue\Catalog\Product\AttributeFactory $productAttributeFactory,
        Context $context,
        $name = null
    ) {
        parent::__construct($name);
        
        $this->state   = $state;
        $this->context = $context;
        $this->productAttributeFactory = $productAttributeFactory;
    }

    /**
     * @return \Magento\Framework\ObjectManagerInterface
     */
    protected function objectManager()
    {
        return $this->context->objectManager();
    }
}
