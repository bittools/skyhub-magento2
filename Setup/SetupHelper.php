<?php

namespace BitTools\SkyHub\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

trait SetupHelper
{
    
    use Setup;
    
    
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
        Table $table,
        $field,
        $type = AdapterInterface::INDEX_TYPE_INDEX
    ) {
        $table->addIndex(
            $this->setup()->getIdxName($table->getName(), $field, $type),
            $field,
            [
                'type' => $type
            ]
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
        Table $table,
        $column,
        $referenceTable,
        $referenceColumn,
        $onDelete = Table::ACTION_CASCADE
    ) {
        $fkName = $this->setup
            ->getFkName($table->getName(), $column, $this->setup->getTable($referenceTable), $referenceColumn);
        
        $table->addForeignKey(
            $fkName,
            $column,
            $this->setup->getTable($referenceTable),
            $referenceColumn,
            $onDelete
        );
        
        return $this;
    }
}
