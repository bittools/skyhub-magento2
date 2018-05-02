<?php

/**
 * Proudly powered by Magentor CLI!
 * Version v0.1.0
 * Official Repository: http://github.com/tiagosampaio/magentor
 *
 * @author Tiago Sampaio <tiago@tiagosampaio.com>
 */

namespace BitTools\SkyHub\Setup;

use BitTools\SkyHub\Model\Queue;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class InstallSchema implements InstallSchemaInterface
{
    
    use SetupHelper;
    
    
    /**
     * @param SchemaSetupInterface   $setup
     * @param ModuleContextInterface $context
     *
     * @throws \Zend_Db_Exception
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        
        // $this->updateTablesColumns($setup);
        $this->installProductsAttributeMappingTable($setup);
        $this->installEntityIdTable($setup);
        $this->installQueueTable($setup);
        
        $installer->endSetup();
    }
    
    
    /**
     * @param SchemaSetupInterface $setup
     *
     * @return $this
     *
     * @throws \Zend_Db_Exception
     */
    protected function installProductsAttributeMappingTable(SchemaSetupInterface $setup)
    {
        $tableName = $setup->getTable('bittools_skyhub_product_attributes_mapping');
        
        /** @var Table $table */
        $table = $setup->getConnection()
            ->newTable($tableName)
            ->addColumn('id', Table::TYPE_INTEGER, 10, [
                'identity' => true,
                'unsigned' => true,
                'primary' => true,
                'nullable' => false,
            ])
            ->addColumn('skyhub_code', Table::TYPE_TEXT, 255, [
                'nullable' => false,
            ])
            ->addColumn('skyhub_label', Table::TYPE_TEXT, 255, [
                'nullable' => true,
            ])
            ->addColumn('skyhub_description', Table::TYPE_TEXT, null, [
                'nullable' => true,
            ])
            ->addColumn('enabled', Table::TYPE_BOOLEAN, 1, [
                'nullable' => false,
                'default' => true,
            ])
            ->addColumn('cast_type', Table::TYPE_TEXT, 255, [
                'nullable' => false,
            ])
            ->addColumn('validation', Table::TYPE_TEXT, null, [
                'nullable' => true,
            ])
            ->addColumn('attribute_id', Table::TYPE_INTEGER, 255, [
                'nullable' => true,
                'default' => null,
            ])
            ->addColumn('required', Table::TYPE_BOOLEAN, 1, [
                'nullable' => false,
                'default' => true,
            ])
            ->addColumn('editable', Table::TYPE_BOOLEAN, 1, [
                'nullable' => false,
                'default' => true,
            ])
            ->addColumn('created_at', Table::TYPE_DATETIME, null, [
                'nullable' => true,
                'unsigned' => true,
            ])
            ->setComment('SkyHub Product Attributes Mapping.')
        ;;
        
        /** Add Unique Index */
        $this->addTableIndex($setup, $table, ['skyhub_code', 'attribute_id'], AdapterInterface::INDEX_TYPE_UNIQUE);
        
        /** Add Foreign Key */
        $this->addTableForeignKey($setup, $table, 'attribute_id', 'eav_attribute', 'attribute_id');
        
        $setup->getConnection()->createTable($table);
        
        return $this;
    }
    
    
    /**
     * @param SchemaSetupInterface $setup
     *
     * @return $this
     *
     * @throws \Zend_Db_Exception
     */
    protected function installEntityIdTable(SchemaSetupInterface $setup)
    {
        $tableName = $setup->getTable('bittools_skyhub_entity_id');
        
        /** @var Table $table */
        $table = $setup->getConnection()
            ->newTable($tableName)
            ->addColumn('id', Table::TYPE_INTEGER, 10, [
                'identity' => true,
                'unsigned' => true,
                'primary' => true,
                'nullable' => false,
            ])
            ->addColumn('entity_id', Table::TYPE_INTEGER, 10, [
                'nullable' => false,
                'primary' => true,
            ])
            ->addColumn('entity_type', Table::TYPE_TEXT, 255, [
                'nullable' => false,
            ])
            ->addColumn('store_id', Table::TYPE_INTEGER, 10, [
                'unsigned' => true,
                'nullable' => false,
                'primary' => true,
                'default' => 0,
            ])
            ->addColumn('editable', Table::TYPE_BOOLEAN, 1, [
                'nullable' => false,
                'default' => true,
            ])
            ->addColumn('created_at', Table::TYPE_DATETIME, null, [
                'nullable' => true,
                'unsigned' => true,
            ])
        ;
        
        /** Add Store ID foreign key. */
        $this->addTableForeignKey($setup, $table, 'store_id', 'core_store', 'store_id');
        
        /** Add indexes */
        $this->addTableIndex($setup, $table, 'entity_id')
            ->addTableIndex($setup, $table, 'entity_type')
            ->addTableIndex($setup, $table, ['entity_id', 'entity_type'], AdapterInterface::INDEX_TYPE_UNIQUE);
        
        $setup->getConnection()->createTable($table);
        
        return $this;
    }
    
    
    /**
     * @param SchemaSetupInterface $setup
     *
     * @return $this
     *
     * @throws \Zend_Db_Exception
     */
    protected function installQueueTable(SchemaSetupInterface $setup)
    {
        $tableName = $setup->getTable('bittools_skyhub_queue');
        
        /** @var Table $table */
        $table = $setup->getConnection()
            ->newTable($tableName)
            ->addColumn('entity_id', Table::TYPE_INTEGER, 10, [
                'nullable' => true,
            ])
            ->addColumn('reference', Table::TYPE_TEXT, 255, [
                'nullable' => true,
            ])
            ->addColumn('entity_type', Table::TYPE_TEXT, 255, [
                'nullable' => true,
            ])
            ->addColumn('status', Table::TYPE_INTEGER, 2, [
                'nullable' => false,
                'default' => 0,
            ])
            ->addColumn('process_type', Table::TYPE_INTEGER, 2, [
                'nullable' => false,
                'default' => Queue::PROCESS_TYPE_EXPORT,
            ])
            ->addColumn('messages', Table::TYPE_TEXT, null, [
                'nullable' => true,
            ])
            ->addColumn('additional_data', Table::TYPE_TEXT, null, [
                'nullable' => true,
            ])
            ->addColumn('can_process', Table::TYPE_INTEGER, 1, [
                'nullable' => false,
                'default' => 0,
            ])
            ->addColumn('store_id', Table::TYPE_INTEGER, 10, [
                'unsigned' => true,
                'nullable' => false,
                'primary' => true,
                'default' => 0,
            ])
            ->addColumn('process_after', Table::TYPE_DATETIME, null, [
                'nullable' => true,
                'unsigned' => true,
            ], 'Schedule the process to run after this time if needed.')
            ->addColumn('created_at', Table::TYPE_DATETIME, null, [
                'nullable' => true,
                'unsigned' => true,
            ])
            ->addColumn('updated_at', Table::TYPE_DATETIME, null, [
                'nullable' => true,
                'unsigned' => true,
            ])
        ;
        
        $this->addTableForeignKey($setup, $table, 'store_id', 'core_store', 'store_id');
        
        $this->addTableIndex($setup, $table, 'entity_id')
            ->addTableIndex($setup, $table, 'entity_type')
            ->addTableIndex(
                $setup,
                $table,
                ['entity_id', 'entity_type', 'store_id'],
                AdapterInterface::INDEX_TYPE_UNIQUE
            );
        
        $setup->getConnection()->createTable($table);
        
        return $this;
    }
    
    
    /**
     * @param SchemaSetupInterface $setup
     *
     * @return $this
     */
    protected function updateTablesColumns(SchemaSetupInterface $setup)
    {
        foreach ($this->getUpdatableTables($setup) as $tableName => $columns) {
            foreach ($columns as $columnName => $definition) {
                $setup->getConnection()
                    ->addColumn($setup->getTable($tableName), $columnName, $definition);
            }
        }
        
        return $this;
    }
    
    
    /**
     * @param SchemaSetupInterface $setup
     *
     * @return array
     */
    protected function getUpdatableTables(SchemaSetupInterface $setup)
    {
        return [
            $setup->getTable('sales_quote') => [
                'bseller_skyhub_interest' => $this->getInterestField()
            ],
            $setup->getTable('sales_order') => [
                'bseller_skyhub' => [
                    'type' => Table::TYPE_BOOLEAN,
                    'nullable' => true,
                    'default' => false,
                    'comment' => 'If Order Was Created By BSeller SkyHub',
                ],
                'bseller_skyhub_code' => [
                    'type' => Table::TYPE_TEXT,
                    'size' => 255,
                    'nullable' => true,
                    'default' => false,
                    'after' => 'bseller_skyhub',
                    'comment' => 'SkyHub Code',
                ],
                'bseller_skyhub_channel' => [
                    'type' => Table::TYPE_TEXT,
                    'size' => 255,
                    'nullable' => true,
                    'default' => false,
                    'after' => 'bseller_skyhub_code',
                    'comment' => 'SkyHub Code',
                ],
                'bseller_skyhub_invoice_key' => [
                    'type' => Table::TYPE_TEXT,
                    'size' => 255,
                    'nullable' => true,
                    'default' => null,
                    'after' => 'bseller_skyhub_channel',
                    'comment' => 'SkyHub Invoice Key',
                ],
                'bseller_skyhub_interest' => array_merge($this->getInterestField(), [
                    'after' => 'bseller_skyhub_invoice_key'
                ]),
            ]
        ];
    }
    
    
    /**
     * @return array
     */
    protected function getInterestField()
    {
        return [
            'type' => Table::TYPE_DECIMAL,
            'length' => '12,4',
            'nullable' => false,
            'default' => '0.0000',
            'comment' => 'SkyHub Interest Amount',
        ];
    }
}
