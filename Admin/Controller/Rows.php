<?php

namespace Kieran\Roster\Admin\Controller;

use XF\Mvc\ParameterBag;
use Kieran\Roster\Entity\Row;

class Rows extends \XF\Admin\Controller\AbstractController
{

	public function actionIndex(ParameterBag $params)
	{
		return $this->view('Kieran\Roster:Rows', 'kieran_roster_rows_list', [
			'rows' => $this->finder('Kieran\Roster:Row')->order('sort_order', 'asc')->where('parent_id', 0)->fetch()
		]);
	}

	public function actionToggle()
	{
		/** @var \XF\ControllerPlugin\Toggle $plugin */
		$plugin = $this->plugin('XF:Toggle');
		return $plugin->actionToggle('Kieran\Roster:Row', 'enabled');
	}

	public function actionDelete(ParameterBag $params) {
		$row = $this->assertRowExists($params->row_id);
		$row->delete();
	}

	protected function rowAddEdit(Row $row) {
		$viewParams = [
			'row' => $row,
			'topRows' => $this->finder('Kieran\Roster:Row')->where('parent_id', 0)->fetch(),
			'groups' => $this->finder('XF:UserGroup')->fetch(),
		];
		return $this->view('Kieran\Roster:Row\Add', 'kieran_roster_rows_edit', $viewParams);
	}

	public function actionEdit(ParameterBag $params) {
		$row = $this->assertRowExists($params->row_id);
		return $this->rowAddEdit($row);
	}

	public function actionAdd(ParameterBag $params) {
		$row = $this->em()->create('Kieran\Roster:Row');

		return $this->rowAddEdit($row);
	}

	public function actionSave(ParameterBag $params) {
		$this->assertPostOnly();

		if ($params->row_id)
		{
			$row = $this->assertRowExists($params->row_id);
		}
		else
		{
			$row = $this->em()->create('Kieran\Roster:Row');
		}

		$this->rowSaveProcess($row)->run();

		return $this->redirect($this->buildLink('roster/rows'));
	}

	public function rowSaveProcess(Row $row) {
		$form = $this->formAction();

		$input = $this->filter([
			'enabled' => 'uint',
			'title' => 'str',
			'sort_order' => 'uint',
			'parent_id' => 'uint',
			'group_id' => 'array',
			'row' => 'uint',
			'description' => 'str',
			'image' => 'str',
		]);

		$urlValidator = \XF::app()->validator('Url');
		$input['image'] = $urlValidator->coerceValue($input['image']);
		$urlValidator->setOption('allow_empty', true);

		if (!$urlValidator->isValid($input['image']))
		{
			throw $this->exception($this->error(\XF::phrase('please_enter_valid_url')));
		}

		if ($row->Children) {
			$input['parent_id'] = 0;
			$input['row'] = 1;
		} else {
			if (!$this->finder('Kieran\Roster:Row')->where('row_id', $input['parent_id'])->fetch()) {
				$input['parent_id'] = 0;
				$input['row'] = 1;
			}
		}
		
		$form->basicEntitySave($row, $input);

		return $form;
	}

	protected function assertRowExists($id, $with = null, $phraseKey = null)
	{
		return $this->assertRecordExists('Kieran\Roster:Row', $id, $with, $phraseKey);
	}
	
}