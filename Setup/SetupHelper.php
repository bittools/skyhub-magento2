<?php

namespace BitTools\SkyHub\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

trait SetupHelper
{
    
    /**
     * @param SchemaSetupInterface $setup
     * @param Table                $table
     * @param string|array         $field
     * @param string               $type
     *
     * @return $this
     * @throws \Zend_Db_Exception
     */
    protected function addTableIndex(
        SchemaSetupInterface $setup,
        Table $table,
        $field,
        $type = AdapterInterface::INDEX_TYPE_INDEX
    )
    {
        $idxName = $setup->getIdxName($table->getName(), $field, $type);
        
        $table->addIndex(
            $idxName,
            'entity_type',
            ['type' => $type]
        );
        
        return $this;
    }
    
    
    /**
     * @param SchemaSetupInterface $setup
     * @param Table                $table
     * @param string               $column
     * @param string               $referenceTable
     * @param string               $referenceColumn
     *
     * @return $this
     * @throws \Zend_Db_Exception
     */
    protected function addTableForeignKey(
        SchemaSetupInterface $setup,
        Table $table,
        $column,
        $referenceTable,
        $referenceColumn
    )
    {
        $fkName = $setup->getFkName($table->getName(), $column, $setup->getTable($referenceTable), $referenceColumn);
        
        $table->addForeignKey(
            $fkName,
            $column,
            $setup->getTable($referenceTable),
            $referenceColumn
        );
        
        return $this;
    }
}
