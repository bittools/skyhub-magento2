<?php

namespace BitTools\SkyHub\Helper;

use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\ObjectManagerInterface;

class Context implements \Magento\Framework\ObjectManager\ContextInterface
{
    
    /** @var ManagerInterface */
    protected $eventManager;
    
    /** @var ObjectManagerInterface */
    protected $objectManager;
    
    
    public function __construct(
        ManagerInterface $eventManager,
        ObjectManagerInterface $objectManager
    )
    {
        $this->eventManager  = $eventManager;
        $this->objectManager = $objectManager;
    }
    
    
    /**
     * @return ManagerInterface
     */
    public function eventManager()
    {
        return $this->eventManager;
    }
    
    
    /**
     * @return ObjectManagerInterface
     */
    public function objectManager()
    {
        return $this->objectManager;
    }
}
