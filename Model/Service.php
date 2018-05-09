<?php

namespace BitTools\SkyHub\Model;

use Magento\Framework\App\Filesystem\DirectoryList;
use BitTools\SkyHub\StoreConfig\Config;
use SkyHub\Api;

class Service
{
    
    /** @var Api */
    protected $api;
    
    /** @var Config */
    protected $config;
    
    /** @var DirectoryList */
    protected $directoryList;
    
    
    /**
     * Service constructor.
     *
     * @param Config        $config
     * @param DirectoryList $directoryList
     */
    public function __construct(Config $config, DirectoryList $directoryList)
    {
        $this->config        = $config;
        $this->directoryList = $directoryList;
        
        $this->initApi();
    }
    
    
    /**
     * @return Api
     */
    public function api()
    {
        if (!$this->api) {
            $this->initApi();
        }
        
        return $this->api;
    }
    
    
    /**
     * @return \SkyHub\Api\Service\ServiceAbstract
     */
    public function apiService()
    {
        return $this->api()->service();
    }
    
    
    /**
     * @return $this
     */
    public function initApi()
    {
        $email  = $this->config->service()->getServiceEmail();
        $apiKey = $this->config->service()->getServiceApiKey();
        
        $this->api = new Api($email, $apiKey, 'bZa6Ml0zgS');
        
        if ($this->config->log()->isEnabled()) {
            /**
             * If the log does not work properly for any case it can't stop the integration process.
             */
            try {
                $logFileName = $this->config->log()->getFilename();
                $logFilePath = $this->directoryList->getPath(DirectoryList::VAR_DIR);
    
                $this->apiService()
                    ->setLogAllowed(true)
                    ->setLogFileName($logFileName)
                    ->setLogFilePath($logFilePath)
                ;
            } catch (\Exception $e) {
                /** @todo Create the error logic here if really necessary. */
            }
        }
        
        return $this;
    }
}
