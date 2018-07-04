<?php

namespace Davidrobert\ResponseToReview\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface
{
	public function install(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {
	    $setup->startSetup();

	    $connection = $setup->getConnection();

	    $tableReview = $setup->getTable( 'review_detail' );

	    if ($connection->isTableExists($tableReview) == true) {

		    $connection->addColumn(
			    $tableReview,
			    'response_review',
			    [
				    'type'     => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
				    'length'   => 255,
				    'nullable' => true,
				    'comment'  => 'Admin response to a client review',
			    ]
		    );

	    }

        $setup->endSetup();
    }
}
