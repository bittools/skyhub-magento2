<?php

namespace BitTools\SkyHub\Block\System\Config\Cron;

use BitTools\SkyHub\Block\System\Config\AbstractConfig;

class Heartbeat extends AbstractConfig
{
    
    /** @var \BitTools\SkyHub\Cron\HeartbeatFactory */
    protected $heartbeatFactory;
    
    /** @var bool */
    protected $isOlderThanOneHour = null;
    
    
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Module\ModuleListInterface $moduleList,
        \BitTools\SkyHub\Cron\HeartbeatFactory $heartbeatFactory,
        array $data = []
    )
    {
        parent::__construct($context, $moduleList, $data);
        
        $this->heartbeatFactory = $heartbeatFactory;
    }
    
    
    /**
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     *
     * @return \Magento\Framework\Phrase|mixed
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $style = null;
        
        if ($this->isOlderThanOneHour()) {
            $style    = 'color:red;';
            $message  = 'Your cron seems to be not working properly because the heartbeat is older than one hour.';
            $message .= ' Please check your cron configuration.';
        }
    
        if (!$this->isOlderThanOneHour()) {
            $style   = 'color:green;';
            $message = 'Your cron seems to be working properly.';
        }
    
        $message = __($message);
        
        return "<p style='{$style}'>$message</p>";
    }
    
    
    /**
     * @return bool
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function isOlderThanOneHour()
    {
        if (is_null($this->isOlderThanOneHour)) {
            $this->isOlderThanOneHour = (bool) $this->getHeartbeatModel()->isHeartbeatOlderThanOneHour();
        }
        
        return $this->isOlderThanOneHour;
    }
    
    
    /**
     * @return \BitTools\SkyHub\Cron\Heartbeat
     */
    protected function getHeartbeatModel()
    {
        return $this->heartbeatFactory->create();
    }
}
