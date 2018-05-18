<?php

namespace BitTools\SkyHub\Ui\Component\Listing\Column;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\Escaper;
use Magento\Store\Model\System\Store as SystemStore;

abstract class AbstractOptions implements OptionSourceInterface
{

    /** @var array */
    protected $options;

    /** @var SystemStore */
    protected $systemStore;

    /** @var Escaper */
    protected $escaper;


    /**
     * AbstractOptions constructor.
     *
     * @param SystemStore $systemStore
     * @param Escaper     $escaper
     */
    public function __construct(SystemStore $systemStore, Escaper $escaper)
    {
        $this->systemStore = $systemStore;
        $this->escaper     = $escaper;
    }
}
