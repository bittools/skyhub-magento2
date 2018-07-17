<?php

/**
 * Proudly powered by Magentor CLI!
 * Version v0.1.0
 * Official Repository: http://github.com/tiagosampaio/magentor
 *
 * @author Tiago Sampaio <tiago@tiagosampaio.com>
 */

namespace BitTools\SkyHub\Model\Customer\Attributes;

use BitTools\SkyHub\Api\Data\CustomerAttributeMappingInterface;
use BitTools\SkyHub\Helper\Context;
use BitTools\SkyHub\Model\Config\SkyhubAttributes\Data as SkyHubConfig;
use BitTools\SkyHub\Model\ResourceModel\Customer\Attributes\Mapping as ResourceModel;
use Magento\Framework\Model\AbstractModel;
use Magento\Eav\Model\Entity\Attribute as EntityAttribute;

/**
 * @method $this setSkyhubCode(string $code)
 * @method $this setSkyhubLabel(string $label)
 * @method $this setSkyhubDescription(string $description)
 * @method $this setAttributeId(int $id)
 * @method $this setEditable(bool $flag)
 * @method $this setCastType(string $type)
 *
 * @method string getSkyhubCode()
 * @method string getSkyhubLabel()
 * @method string getSkyhubDescription()
 * @method int    getAttributeId()
 * @method bool   getEditable()
 * @method string getCastType()
 */
class Mapping extends AbstractModel implements CustomerAttributeMappingInterface
{
    
    const DATA_TYPE_STRING   = 'string';
    const DATA_TYPE_BOOLEAN  = 'boolean';
    const DATA_TYPE_DECIMAL  = 'decimal';
    const DATA_TYPE_INTEGER  = 'integer';
    
 
    /** @var Context */
    protected $helperContext;

    /** @var SkyHubConfig  */
    protected $skyhubConfig;
    
    
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        Context $helperContext,
        SkyHubConfig $skyhubConfig,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    )
    {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        
        $this->helperContext = $helperContext;
        $this->skyhubConfig  = $skyhubConfig;
    }
    
    
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(ResourceModel::class);
    }
    
    /**
     * @return EntityAttribute
     */
    public function getAttribute()
    {
        if ($this->hasData('attribute')) {
            return $this->getData('attribute');
        }
        
        /** @var EntityAttribute $attribute */
        $attribute = $this->helperContext->objectManager()->create(EntityAttribute::class);
        
        if ($this->getAttributeId()) {
            $attribute->load((int) $this->getAttributeId());
            $this->setData('attribute', $attribute);
        }
        
        return $attribute;
    }

    /**
     * @return EntityAttribute
     */
    public function getAttributeByCode($attributeCode)
    {
        /** @var EntityAttribute $attribute */
        $attribute = $this->helperContext->objectManager()->create(EntityAttribute::class);
        $attribute->load($attributeCode, 'attribute_code');

        return $attribute;
    }
    
    /**
     * @return string
     */
    public function getSkyhubLabelTranslated()
    {
        return __($this->getSkyhubLabel());
    }
    
    
    /**
     * @return string
     */
    public function getDataType()
    {
        $type = $this->getCastType();
        
        if (!$type || !in_array($type, $this->getValidDataTypes())) {
            $type = self::DATA_TYPE_STRING;
        }
        
        return $type;
    }
    
    
    /**
     * @return array
     */
    public function getAttributeInstallConfig()
    {
        $config = (array) $this->skyhubConfig->getAttributeInstallConfig($this->getSkyhubCode(), 'customer');
        
        foreach ($config as $key => $value) {
            $config[$key] = ('' == $value) ? null : $value;
        }
        
        return $config;
    }
    
    
    /**
     * @param string|int|bool|float $value
     *
     * @return bool|float|int|string
     */
    public function castValue($value)
    {
        switch ($this->getDataType()) {
            case self::DATA_TYPE_INTEGER:
                return (int) $value;
                break;
            case self::DATA_TYPE_DECIMAL:
                return (float) $value;
                break;
            case self::DATA_TYPE_BOOLEAN:
                return (bool) $value;
                break;
            case self::DATA_TYPE_STRING:
                return (string) $value;
                break;
            default:
                return $value;
        }
    }
    
    /**
     * @return array
     */
    protected function getValidDataTypes()
    {
        return [
            self::DATA_TYPE_BOOLEAN,
            self::DATA_TYPE_DECIMAL,
            self::DATA_TYPE_INTEGER,
            self::DATA_TYPE_STRING,
        ];
    }
}
