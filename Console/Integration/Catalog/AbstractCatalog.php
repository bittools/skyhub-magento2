<?php

namespace BitTools\SkyHub\Console\Integration\Catalog;

use BitTools\SkyHub\Console\Integration\AbstractIntegration;
use Symfony\Component\Console\Input\InputOption;

abstract class AbstractCatalog extends AbstractIntegration
{
    
    /** @var string */
    const INPUT_KEY_LIMIT = 'limit';
    
    
    /**
     * @return InputOption
     */
    protected function getLimitOption()
    {
        return new InputOption(
            self::INPUT_KEY_LIMIT,
            'l',
            InputOption::VALUE_OPTIONAL,
            'The limit for the process.',
            500
        );
    }
}
