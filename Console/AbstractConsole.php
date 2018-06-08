<?php

namespace BitTools\SkyHub\Console;

use BitTools\SkyHub\Helper\Context;
use Symfony\Component\Console\Style\StyleInterface;

abstract class AbstractConsole extends \Symfony\Component\Console\Command\Command
{

    /** @var Context */
    protected $context;

    /** @var StyleInterface */
    protected $style;


    /**
     * AbstractIntegration constructor.
     *
     * @param Context     $context
     * @param null|string $name
     */
    public function __construct(Context $context, $name = null)
    {
        parent::__construct($name);
        $this->context = $context;
    }
}
