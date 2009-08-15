<?php
class FavoritesController extends AppController{

	var $name = 'Favorites' ;
	var $paginate = array('limit' => 50); 

	function beforeFilter() {
	    parent::beforeFilter();
		
		// setting actions that are available to everyone, even guests
	}


	function add_favorite ($sentence_id){
	        $user_id =$this->Auth->user('id');
		if ( $user_id != NULL ){
	
			$result = $this->Favorite->query("SELECT id FROM sentences WHERE ID= $sentence_id");

			if ( $result != NULL ){ 
				
				$this->Favorite->habtmAdd ('Favorite' , $sentence_id , $this->Auth->user('id') );
				
				$this->set('user_id' , $user_id );
				$this->set('sentence_id' , $result[0]["sentences"]["id"] ) ;

			}
		}
	}

	function remove_favorite ($sentence_id){
	        	
	        $user_id =$this->Auth->user('id');

		if ( $user_id != NULL ){

			$result = $this->Favorite->query("SELECT id FROM sentences WHERE ID= $sentence_id");

			if ( $result != NULL ){ 
				$this->Favorite->habtmDelete ('Favorite' , $sentence_id , $this->Auth->user('id') );
				
				$this->set('user_id' , $user_id );
				$this->set('sentence_id' , $result[0]["sentences"]["id"] ) ;

			}
		}
	}


}
?>
