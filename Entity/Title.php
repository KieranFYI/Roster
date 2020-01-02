<?php

namespace Kieran\Roster\Entity;
    
use XF\Mvc\Entity\Entity;
use XF\Mvc\Entity\Structure;

class Title extends Entity
{
    public static function getStructure(Structure $structure)
	{
        $structure->table = 'xf_kieran_roster_title';
        $structure->shortName = 'Kieran\Roster:Title';
        $structure->primaryKey = 'title_id';
        $structure->columns = [
			'title_id' => ['type' => self::UINT, 'autoIncrement' => true, 'nullable' => false, 'changeLog' => false],
			'row_id' => ['type' => self::UINT, 'default' => 0], 
			'user_id' => ['type' => self::UINT, 'required' => true], 
			'title' => ['type' => self::STR, 'default' => '', 'maxLength' => 255],
        ];
        $structure->getters = [
		];
		$structure->relations = [
			'Row' => [
				'entity' => 'Kieran\Roster:Row',
				'type' => self::TO_ONE,
				'conditions' => 'row_id',
				'primary' => true
			]
		];
        
        return $structure;
    }
}