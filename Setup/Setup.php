<?php

namespace BitTools\SkyHub\Setup;

trait Setup
{
    
    /** @var \Magento\Framework\Setup\ModuleDataSetupInterface|\Magento\Framework\Setup\SchemaSetupInterface */
    protected $setup;
    
    
    /**
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected function getConnection()
    {
        return $this->setup()->getConnection();
    }
    
    
    /**
     * @return \Magento\Framework\Setup\ModuleDataSetupInterface|\Magento\Framework\Setup\SchemaSetupInterface
     */
    protected function setup()
    {
        return $this->setup;
    }
    
    
    /**
     * @param string $tableName
     *
     * @return string
     */
    protected function getTable($tableName)
    {
        return $this->setup()->getTable($tableName);
    }
}
