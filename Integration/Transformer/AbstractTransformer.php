<?php

namespace BitTools\SkyHub\Integration\Transformer;

use BitTools\SkyHub\Functions;
use BitTools\SkyHub\Integration\Context;

abstract class AbstractTransformer implements TransformerInterface
{
    
    use Functions;
    
    
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
