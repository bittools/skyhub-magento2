<?php

namespace BitTools\SkyHub\Integration\Transformer;

use BitTools\SkyHub\Integration\Context;

abstract class AbstractTransformer implements TransformerInterface
{
    
    /** @var Context */
    protected $context;
    
    
    /**
     * AbstractTransformer constructor.
     *
     * @param Context $context
     */
    public function __construct(Context $context)
    {
        $this->context = $context;
    }
}
