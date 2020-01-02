<?php

namespace Kieran\Roster;

use XF\Db\Schema\Create;

class Setup extends \XF\AddOn\AbstractSetup
{
	use \XF\AddOn\StepRunnerInstallTrait;
	use \XF\AddOn\StepRunnerUninstallTrait;

	// php cmd.php xf-addon:install Kieran/Roster
	// php cmd.php xf-addon:build-release Kieran/Roster

	public function installStep1(array $stepParams = [])
	{
		$this->schemaManager()->createTable('xf_kieran_roster_row', function(Create $table)
		{
			$table->addColumn('row_id', 'int')->autoIncrement();
			$table->addColumn('enabled', 'int')->setDefault(0);
			$table->addColumn('description', 'text');
			$table->addColumn('parent_id', 'int')->setDefault(0);
			$table->addColumn('group_id', 'varbinary', 255);
			$table->addColumn('title', 'varchar', 255);
			$table->addColumn('sort_order', 'int', 11)->setDefault(1);
			$table->addColumn('row', 'int', 11)->setDefault(1);
			$table->addColumn('image', 'varchar', 255);
			$table->addPrimaryKey('row_id');
			$table->addUniqueKey(['row_id'], 'row_id');
		});
		
		$this->schemaManager()->createTable('xf_kieran_roster_title', function(Create $table)
		{
			$table->addColumn('title_id', 'int')->autoIncrement();
			$table->addColumn('row_id', 'int')->setDefault(0);
			$table->addColumn('user_id', 'int');
			$table->addColumn('title', 'varchar', 255)->setDefault('');
			$table->addPrimaryKey('title_id');
			$table->addUniqueKey(['title_id', 'row_id'], 'title_id_row_id');
		});
	}
	
	public function upgrade(array $stepParams = [])
	{
	}
	
	public function uninstallStep1(array $stepParams = [])
	{
		$this->schemaManager()->dropTable('xf_kieran_roster_row');
		$this->schemaManager()->dropTable('xf_kieran_roster_title');
	}

}