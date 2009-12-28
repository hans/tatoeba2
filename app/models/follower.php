<?php
/*
    Tatoeba Project, free collaborativ creation of languages corpuses project
    Copyright (C) 2009  TATOEBA Project(should be changed)

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU Affero General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU Affero General Public License for more details.

    You should have received a copy of the GNU Affero General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

class Follower extends AppModel{
	var $name = 'Follower';
	var $useTable = 'users';

	var $actsAs = array('ExtendAssociations');

	var $hasAndBelongsToMany = array(
		'User' => array(
			'className' => 'User',
			'joinTable' => 'followers_users',
			'foreignKey' => 'follower_id',
			'associationForeignKey' => 'user_id',
			'conditions' => '',
			'order' => '',
			'limit' => '',
			'unique' => true,
			'finderQuery' => '',
			'deleteQuery' => '',
			'insertQuery' => ''
		)
	);

	function get_followers($userId){
		return $this->find(
			'all',
			array(
				'conditions' => array('Follower.user_id' => $userId),
				'limit'=> 10
			)
		);
	}

	function get_following($userId){
		return $this->find(
			'all',
			array(
				'conditions' => array('Follower.follower_id' => $userId),
				'limit'=> 10,
			)
		);
	}
}
?>
