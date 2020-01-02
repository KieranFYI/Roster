<?php

namespace Kieran\Roster\Admin\Controller;

use XF\Mvc\ParameterBag;

class Roster extends \XF\Admin\Controller\AbstractController
{

	public function actionIndex(ParameterBag $params)
	{
		return $this->view('Kieran\Roster:Roster', 'kieran_roster', []);
	}

	public function actionRows(ParameterBag $params)
	{
		return $this->rerouteController('Kieran\Roster:Rows', 'index', $params);
	}
}