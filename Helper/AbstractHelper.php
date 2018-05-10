<?php

namespace BitTools\SkyHub\Helper;

abstract class AbstractHelper
{
    
    /** @var Context */
    protected $context;
    
    
    /**
     * AbstractHelper constructor.
     *
     * @param Context $context
     */
    public function __construct(Context $context)
    {
        $this->context = $context;
    }
}
