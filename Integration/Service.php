<?php

namespace BitTools\SkyHub\Integration;

use Magento\Framework\App\Filesystem\DirectoryList;
use BitTools\SkyHub\StoreConfig\Context as ConfigContext;
use SkyHub\Api;

class Service
{
    
    /** @var Api */
    protected $api;
    
    /** @var ConfigContext */
    protected $configContext;
    
    /** @var DirectoryList */
    protected $directoryList;
    
    
    /**
     * Service constructor.
     *
     * @param ConfigContext $config
     * @param DirectoryList $directoryList
     */
    public function __construct(ConfigContext $configContext, DirectoryList $directoryList)
    {
        $this->configContext = $configContext;
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
        $email  = $this->configContext->service()->getServiceEmail();
        $apiKey = $this->configContext->service()->getServiceApiKey();
        
        $this->api = new Api($email, $apiKey, 'bZa6Ml0zgS');
        
        if ($this->configContext->log()->isEnabled()) {
            /**
             * If the log does not work properly for any case it can't stop the integration process.
             */
            try {
                $logFileName = $this->configContext->log()->getFilename();
                $logFilePath = $this->directoryList->getPath(DirectoryList::LOG);
    
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
