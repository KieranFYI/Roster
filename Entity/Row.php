<?php

namespace Kieran\Roster\Entity;
    
use XF\Mvc\Entity\Entity;
use XF\Mvc\Entity\Structure;

class Row extends Entity
{

	public function getSpacing($children) {
		if (count($children) == 1) {
			return 'memberOverviewBlocks--w25';
		} else if (count($children) == 2) {
			return 'memberOverviewBlocks--w50';
		} else {
			return 'memberOverviewBlocks--w100';
		}
	}

	protected function _preDelete()
	{
		foreach ($this->Children as $value) {
			$value->delete();
		}

		foreach ($this->Titles as $value) {
			$value->delete();
		}
	}

	public function getUsers() {
		if (!count($this->group_id)) {
			return [];
		}
		if ($this->Parent != null && count($this->Parent->Users)) {
			$users = [];
			foreach($this->Parent->Users as $user) {
				$userGroups = array_merge($user->secondary_group_ids, [$user->user_group_id]);

				if (!array_diff($this->group_id, $userGroups)) {
					$users[] = $user;
				}
			}

			return $users;
		}

		$finder = $this->finder('XF:User');

		$sql = [];
        foreach ($this->group_id as $value) {
            $sql[] = 'find_in_set(' . intval($value) . ', CONCAT(secondary_group_ids, ",", user_group_id))';
        }

        $finder->whereSql(implode(' AND ', $sql));

		return $finder->fetch();
	}

	public function getChildren($enabled = true) {
		$finder = $this->finder('Kieran\Roster:Row')->where('parent_id', $this->row_id)->order('sort_order', 'asc');

		if ($enabled) {
			$finder->where('enabled', 1);
		}
		$finder = $finder->fetch();

		$children = [];

		foreach ($finder as $value) {
			$children[$value->row][] = $value;
		}

		return $children;
	}

	public function getProxyImage() {
		return $this->app()->proxy()->generate('image', $this->image);
	}

	public function getTitle($user_id) {
		foreach ($this->Titles as $title) {
			if ($title->user_id == $user_id) {
				return $title;
			}
		}
		return null;
	}

    public static function getStructure(Structure $structure)
	{
        $structure->table = 'xf_kieran_roster_row';
        $structure->shortName = 'Kieran\Roster:Row';
        $structure->primaryKey = 'row_id';
        $structure->columns = [
			'row_id' => ['type' => self::UINT, 'autoIncrement' => true, 'nullable' => false, 'changeLog' => false],
			'description' => ['type' => self::STR, 'default' => ''],
			'enabled' => ['type' => self::UINT, 'default' => 0], 
			'parent_id' => ['type' => self::UINT, 'default' => 0], 
			'group_id' => ['type' => self::LIST_COMMA, 'default' => [],
				'list' => ['type' => 'posint', 'unique' => true, 'sort' => SORT_NUMERIC]
			], 
			'title' => ['type' => self::STR, 'maxLength' => 255, 'required' => true],
			'sort_order' => ['type' => self::UINT, 'default' => 1],
			'row' => ['type' => self::UINT, 'default' => 1],
			'image' => ['type' => self::STR, 'default' => '', 'maxLength' => '255'],
        ];
        $structure->getters = [
        	'Users' => true,
        	'Children' => true,
        	'proxy_image' => true,
		];
		$structure->relations = [
			'Parent' => [
				'entity' => 'Kieran\Roster:Row',
				'type' => self::TO_ONE,
				'conditions' => [
					['row_id', '=', '$parent_id'],
				],
				'order' => ['sort_order', 'asc'],
				'primary' => true
			],
			'Titles' => [
				'entity' => 'Kieran\Roster:Title',
				'type' => self::TO_MANY,
				'conditions' => 'row_id',
				'primary' => 'user_id',
			],
		];
        
        return $structure;
    }
}