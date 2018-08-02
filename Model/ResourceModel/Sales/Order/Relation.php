<?php

namespace BitTools\SkyHub\Model\ResourceModel\Sales\Order;

use Magento\Framework\Model\ResourceModel\Db\VersionControl\RelationInterface;

/**
 * Class Relation
 */
class Relation implements RelationInterface
{

    /** @var ExtensionAttribute */
    protected $skyhubExtensionAttribute;


    /**
     * Relation constructor.
     *
     * @param \BitTools\SkyHub\Support\Order\ExtensionAttribute $extensionAttribute
     */
    public function __construct(
        \BitTools\SkyHub\Support\Order\ExtensionAttribute $extensionAttribute
    ) {
        $this->skyhubExtensionAttribute = $extensionAttribute;
    }


    /**
     * Save relations for Order (SkyHub data)
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return void
     * @throws \Exception
     */
    public function processRelation(\Magento\Framework\Model\AbstractModel $object)
    {
        $this->skyhubExtensionAttribute->save($object);
    }
}