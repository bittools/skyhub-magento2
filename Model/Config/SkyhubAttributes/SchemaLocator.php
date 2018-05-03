<?php

namespace BitTools\SkyHub\Model\Config\SkyhubAttributes;

use Magento\Framework\Config\SchemaLocatorInterface;
use Magento\Framework\Module\Dir;
use Magento\Framework\Module\Dir\Reader;

class SchemaLocator implements SchemaLocatorInterface
{
    
    /**
     * XML schema for config file.
     */
    const CONFIG_FILE_SCHEMA = 'skyhub.xsd';
    
    
    /**
     * Path to corresponding XSD file with validation rules for merged config
     *
     * @var string
     */
    protected $schema = null;
    
    
    /**
     * Path to corresponding XSD file with validation rules for separate config files
     * @var string
     */
    protected $perFileSchema = null;
    
    
    /**
     * @param Reader $moduleReader
     */
    public function __construct(Reader $moduleReader)
    {
        $configDir = $moduleReader->getModuleDir(Dir::MODULE_ETC_DIR, 'BitTools_SkyHub');
        
        $this->schema        = $configDir . DIRECTORY_SEPARATOR . self::CONFIG_FILE_SCHEMA;
        $this->perFileSchema = $configDir . DIRECTORY_SEPARATOR . self::CONFIG_FILE_SCHEMA;
    }
    
    
    /**
     * {@inheritdoc}
     */
    public function getSchema()
    {
        return $this->schema;
    }
    
    
    /**
     * {@inheritdoc}
     */
    public function getPerFileSchema()
    {
        return $this->perFileSchema;
    }
}
