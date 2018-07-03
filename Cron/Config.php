<?php

namespace BitTools\SkyHub\Cron;

class Config
{
    
    /** @var Context */
    protected $context;
    
    /** @var Config\Catalog\Product */
    protected $productConfig;
    
    /** @var Config\Catalog\Product\Attribute */
    protected $productAttributeConfig;
    
    /** @var Config\Catalog\Category */
    protected $categoryConfig;
    
    /** @var Config\Sales\Order */
    protected $orderConfig;
    
    /** @var Config\Sales\Order\Status */
    protected $orderStatusConfig;
    
    /** @var Config\Queue */
    protected $queueConfig;
    
    
    public function __construct(
        Config\Catalog\Product $productConfig,
        Config\Catalog\Product\Attribute $productAttributeConfig,
        Config\Catalog\Category $categoryConfig,
        Config\Sales\Order $orderConfig,
        Config\Sales\Order\Status $orderStatusConfig,
        Config\Queue $queueConfig
    ) {
        $this->productConfig          = $productConfig;
        $this->productAttributeConfig = $productAttributeConfig;
        $this->categoryConfig         = $categoryConfig;
        $this->orderConfig            = $orderConfig;
        $this->orderStatusConfig      = $orderStatusConfig;
        $this->queueConfig            = $queueConfig;
    }
    
    
    /**
     * @return Config\Catalog\Product\Attribute
     */
    public function catalogProductAttribute()
    {
        return $this->productAttributeConfig;
    }
    
    
    /**
     * @return Config\Catalog\Product
     */
    public function catalogProduct()
    {
        return $this->productConfig;
    }
    
    
    /**
     * @return Config\Catalog\Category
     */
    public function catalogCategory()
    {
        return $this->categoryConfig;
    }
    
    
    /**
     * @return Config\Sales\Order\Status
     */
    public function salesOrderStatus()
    {
        return $this->orderStatusConfig;
    }
    
    
    /**
     * @return Config\Sales\Order
     */
    public function salesOrderQueue()
    {
        return $this->orderConfig;
    }
    
    
    /**
     * @return Config\Queue
     */
    public function queueClean()
    {
        return $this->queueConfig;
    }
}
