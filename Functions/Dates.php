<?php

namespace BitTools\SkyHub\Functions;

trait Dates
{
    
    /**
     * Simple sql format date
     *
     * @param bool $dayOnly
     *
     * @return string
     */
    protected function now($dayOnly = false)
    {
        return date($dayOnly ? 'Y-m-d' : 'Y-m-d H:i:s');
    }
}
