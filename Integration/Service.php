<?php

namespace BitTools\SkyHub\Integration;

use Magento\Framework\App\Filesystem\DirectoryList;
use BitTools\SkyHub\StoreConfig\Context as ConfigContext;
use BitTools\SkyHub\Helper\Context as HelperContext;
use SkyHub\Api;

class Service
{
    
    /** @var Api */
    protected $api;
    
    /** @var ConfigContext */
    protected $configContext;
    
    /** @var HelperContext */
    protected $helperContext;
    
    /** @var DirectoryList */
    protected $directoryList;
    
    /** @var string  */
    protected $xAccountKey = 'bZa6Ml0zgS';
    
    
    /**
     * Service constructor.
     *
     * @param HelperContext $helperContext
     * @param ConfigContext $configContext
     * @param DirectoryList $directoryList
     */
    public function __construct(
        HelperContext $helperContext,
        ConfigContext $configContext,
        DirectoryList $directoryList
    ) {
        $this->helperContext = $helperContext;
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
        
        $this->renewAuthentication();
        
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
        $this->api = new Api(
            $this->getServiceEmail(),
            $this->getServiceApiKey(),
            $this->xAccountKey
        );
        
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
                    ->setLogFilePath($logFilePath);
            } catch (\Exception $e) {
                $this->helperContext->logger()->critical($e);
            }
        }
        
        return $this;
    }
    
    
    /**
     * This renew authentication was created because of the multi-store purpose.
     * Each store can have a different account set up.
     *
     * @return $this
     */
    public function renewAuthentication()
    {
        $this->api->setAuthentication(
            $this->getServiceEmail(),
            $this->getServiceApiKey()
        );
        
        return $this;
    }
    
    
    /**
     * @return string
     */
    protected function getServiceEmail()
    {
        return $this->configContext->service()->getServiceEmail();
    }
    
    
    /**
     * @return string
     */
    protected function getServiceApiKey()
    {
        return $this->configContext->service()->getServiceApiKey();
    }
}
