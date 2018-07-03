<?php

namespace BitTools\SkyHub\Block\System\Config\Cron;

use BitTools\SkyHub\Block\System\Config\AbstractConfig;

class Heartbeat extends AbstractConfig
{
    
    /** @var \BitTools\SkyHub\Cron\HeartbeatFactory */
    protected $heartbeatFactory;
    
    /** @var \BitTools\SkyHub\Cron\Heartbeat */
    protected $lastHeartbeat;
    
    /** @var bool */
    protected $isOlderThanOneHour = null;
    
    
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Module\ModuleListInterface $moduleList,
        \BitTools\SkyHub\Cron\HeartbeatFactory $heartbeatFactory,
        array $data = []
    ) {
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
            $style   = 'background-color:orangered;';
            
            $message  = 'Your cron seems to be not working properly because the last heartbeat is older than 1 hour.';
            $message .= ' Please check your cron configuration.';
            
            $message = __($message);
        }
    
        if (!$this->isOlderThanOneHour()) {
            $style   = 'background-color:lightseagreen;';
            $time    = (int) abs($this->getHeartbeatModel()->getLastHeartbeatTimeInMinutes());
            
            $message = 'Your cron seems to be working properly.';
            
            if ($time >= 1) {
                $message .= ' Last heartbeat executed %1 minute(s) ago.';
            } else {
                $message .= ' Last heartbeat executed less than 1 minute ago.';
            }
            
            $message = __($message, $time);
        }
        
        return "<p style='padding:10px 10px; border-radius:5px; color:#fff; {$style}'>$message</p>";
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
        if (!$this->lastHeartbeat) {
            $this->lastHeartbeat = $this->heartbeatFactory->create();
        }
        
        return $this->lastHeartbeat;
    }
}
