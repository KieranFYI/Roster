<?php

namespace Kieran\Roster\Pub\Controller;

use XF\Mvc\ParameterBag;

class Roster extends \XF\Pub\Controller\AbstractController
{
	public function actionIndex(ParameterBag $params)
	{
		if ($params->row_id) {
			$row = $this->assertRowExists($params->row_id);
			if (!$row->enabled || $row->parent_id) {
				return $this->noPermission();
			}

			$viewParams = [
				'rows' => [$row],
				'single' => true,
			];
		} else {
			$viewParams = [
				'rows' => $this->finder('Kieran\Roster:Row')->where('parent_id', 0)->order('sort_order', 'asc')->where('enabled', 1)->fetch(),
			];
		}

		return $this->view('Kieran\Roster:Roster', 'kieran_roster', $viewParams);
	}

	public function actionEdit(ParameterBag $params) {
		if (!\XF::visitor()->hasPermission('roster', 'edit')) {
			return $this->noPermission();
        }
        
		$row = $this->assertRowExists($params->row_id);
		$user = $this->assertUserExists($params->user_id);

		return $this->view('Kieran\Roster:Roster', 'kieran_roster_titles_edit', ['row' => $row, 'user' => $user]);
	}

	public function actionSave(ParameterBag $params) {
		if (!\XF::visitor()->hasPermission('roster', 'edit')) {
			return $this->noPermission();
        }
        
		$row = $this->assertRowExists($params->row_id);
		$user = $this->assertUserExists($params->user_id);

        $newTitle = $input = $this->filter('title', 'str');
        $title = $row->getTitle($user->user_id);
        if (!$title) {
            $title = $this->em()->create('Kieran\Roster:Title');
            $title->row_id = $row->row_id;
            $title->user_id = $user->user_id;
        }

        if (empty($newTitle) && !$title->isInsert()) {
            $title->delete();
        } else if ($title->title != $title) {
            $title->title = $newTitle;
            $title->save();
        }

		return $this->redirect($this->buildLink('roster', $row));
	}

	protected function assertRowExists($id, $with = null, $phraseKey = null)
	{
		return $this->assertRecordExists('Kieran\Roster:Row', $id, $with, $phraseKey);
    }
    
	protected function assertUserExists($id, $with = null, $phraseKey = null)
	{
		return $this->assertRecordExists('XF:User', $id, $with, $phraseKey);
	}
}