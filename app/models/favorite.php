<?php
class Favorite extends AppModel{
	var $name = 'Favorite';
	var $useTable = 'sentences';

	var $actsAs = array('ExtendAssociations');


	var $hasAndBelongsToMany = array(
		'Favorite' => array(
			'className' => 'Favorite',
			'joinTable' => 'favorites_users',
			'foreignKey' => 'favorite_id',
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

}
?>