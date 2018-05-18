<?php

namespace BitTools\SkyHub\StoreConfig;

class Context
{

    /** @var GeneralConfig */
    protected $general;

    /** @var ServiceConfig */
    protected $service;

    /** @var LogConfig */
    protected $log;

    /** @var CatalogConfig */
    protected $catalog;

    /** @var SalesOrderStatus */
    protected $salesOrderStatus;


    /**
     * Context constructor.
     *
     * @param GeneralConfig $generalConfig
     * @param ServiceConfig $serviceConfig
     * @param LogConfig     $logConfig
     * @param CatalogConfig $catalogConfig
     * @param SalesOrderStatus $salesOrderStatus
     */
    public function __construct(
        GeneralConfig $generalConfig,
        ServiceConfig $serviceConfig,
        LogConfig $logConfig,
        CatalogConfig $catalogConfig,
        SalesOrderStatus $salesOrderStatus
    )
    {
        $this->general          = $generalConfig;
        $this->service          = $serviceConfig;
        $this->log              = $logConfig;
        $this->catalog          = $catalogConfig;
        $this->salesOrderStatus = $salesOrderStatus;
    }


    /**
     * @return GeneralConfig
     */
    public function general()
    {
        return $this->general;
    }


    /**
     * @return ServiceConfig
     */
    public function service()
    {
        return $this->service;
    }


    /**
     * @return LogConfig
     */
    public function log()
    {
        return $this->log;
    }


    /**
     * @return CatalogConfig
     */
    public function catalog()
    {
        return $this->catalog;
    }


    /**
     * @return SalesOrderStatus
     */
    public function salesOrderStatus()
    {
        return $this->salesOrderStatus;
    }
}
