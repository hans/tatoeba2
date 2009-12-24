<?php
/*
    Tatoeba Project, free collaborativ creation of languages corpuses project
    Copyright (C) 2009  HO Ngoc Phuong Trang (tranglich@gmail.com)
    Copyright (C) 2009  Allan SIMON (allan.simon@supinfo.com)

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

class Sentence extends AppModel{
	var $name = 'Sentence';
    var $actsAs = array("Containable");
	
	const MAX_CORRECTNESS = 6; // This is not much in use. Should probably remove it someday
	
	//var $languages = array('ar', 'bg', 'cs', 'de', 'el', 'en', 'eo', 'es', 'fr', 'he', 'it', 'id', 'jp', 'ko', 'nl', 'pt', 'ru', 'tr', 'uk', 'vn', 'zh', null);
    var $languages = array(
        'ara' ,'bul' ,'deu' ,'ell' ,'eng',
        'epo' ,'spa' ,'fra' ,'heb' ,'ind',
        'jpn' ,'kor' ,'nld' ,'por' ,'rus',
        'vie' ,'cmn' ,'ces' ,'fin' ,'ita',
        'tur' ,'ukr' ,'wuu' ,'swe' ,'zsm',
        'nob' ,null
        );	
	var $validate = array(
		'lang' => array(
			'rule' => array() 	
			// The rule will be defined in the constructor. 
			// I would have declared a const LANGUAGES array 
			// to use it here, but apprently you can't declare 
			// const arrays in PHP.
		),
		'text' => array(
			'rule' => array('minLength', '1')
		)
	);	

	var $hasMany = array('Contribution', 'SentenceComment', 
			'Favorites_users' => array ( 
					'classname'  => 'favorites',
					'foreignKey' => 'favorite_id'  )
			 );
	
	var $belongsTo = array('User');
	
	var $hasAndBelongsToMany = array(
		'Translation' => array(
			'className' => 'Translation',
			'joinTable' => 'sentences_translations',
			'foreignKey' => 'translation_id',
			'associationForeignKey' => 'sentence_id'
		),
		'InverseTranslation' => array(
			'className' => 'InverseTranslation',
			'joinTable' => 'sentences_translations',
			'foreignKey' => 'sentence_id',
			'associationForeignKey' => 'translation_id'
		),
		'SentencesList'
	);
	
	/**
	 * The constructor is here only to set the rule for languages.
	 */
	function __construct() {
		parent::__construct();
		$this->validate['lang']['rule'] = array('inList', $this->languages);
	}
	
	function afterSave($created){
		if(isset($this->data['Sentence']['text'])){
			$whoWhenWhere = array(
				  'user_id' => $this->data['Sentence']['user_id']
				, 'datetime' => date("Y-m-d H:i:s")
				, 'ip' => $_SERVER['REMOTE_ADDR']
			);
			
			$data['Contribution'] = $whoWhenWhere;
			$data['Contribution']['sentence_id'] = $this->id;
			$data['Contribution']['sentence_lang'] = $this->data['Sentence']['lang'];
			$data['Contribution']['text'] = $this->data['Sentence']['text'];
			$data['Contribution']['type'] = 'sentence';
			
			if($created){
				$data['Contribution']['action'] = 'insert';
				
				if(isset($this->data['Translation'])){
					// Translation logs
					$data2['Contribution'] = $whoWhenWhere;
					$data2['Contribution']['sentence_id'] = $this->data['Translation']['Translation'][0];
					$data2['Contribution']['sentence_lang'] = $this->data['Sentence']['sentence_lang'];
					$data2['Contribution']['translation_id'] = $this->id;
					$data2['Contribution']['translation_lang'] = $this->data['Sentence']['lang'];
					$data2['Contribution']['action'] = 'insert';
					$data2['Contribution']['type'] = 'link';
					$contributions[] = $data2;
				}
				if(isset($this->data['InverseTranslation'])){
					// Inverse translation logs
					$data2['Contribution'] = $whoWhenWhere;
					$data2['Contribution']['sentence_id'] = $this->id;
					$data2['Contribution']['sentence_lang'] = $this->data['Sentence']['lang'];
					$data2['Contribution']['translation_id'] = $this->data['Translation']['Translation'][0];
					$data2['Contribution']['translation_lang'] = $this->data['Sentence']['sentence_lang'];
					$data2['Contribution']['action'] = 'insert';
					$data2['Contribution']['type'] = 'link';
					$contributions[] = $data2;
				}
				if(isset($contributions)){
					$this->Contribution->saveAll($contributions);
				}
				
			}else{
				$data['Contribution']['action'] = 'update';
			}
			$this->Contribution->save($data);
		}
	}
	
	function afterDelete(){
		$data['Contribution']['sentence_id'] = $this->data['Sentence']['id'];
		$data['Contribution']['sentence_lang'] = $this->data['Sentence']['lang'];
		$data['Contribution']['text'] = $this->data['Sentence']['text'];
		$data['Contribution']['action'] = 'delete';
		$data['Contribution']['user_id'] = $this->data['User']['id'];
		$data['Contribution']['datetime'] = date("Y-m-d H:i:s");
		$data['Contribution']['ip'] = $_SERVER['REMOTE_ADDR'];
		$data['Contribution']['type'] = 'sentence';
		$this->Contribution->save($data);
		
		foreach($this->data['Translation'] as $translation){
			$data2['Contribution']['sentence_id'] = $this->data['Sentence']['id'];
			$data2['Contribution']['sentence_lang'] = $this->data['Sentence']['lang'];
			$data2['Contribution']['translation_id'] = $translation['id'];
			$data2['Contribution']['translation_lang'] = $translation['lang'];
			$data2['Contribution']['action'] = 'delete';
			$data2['Contribution']['user_id'] = $this->data['User']['id'];
			$data2['Contribution']['datetime'] = date("Y-m-d H:i:s");
			$data2['Contribution']['ip'] = $_SERVER['REMOTE_ADDR'];
			$data2['Contribution']['type'] = 'link';
			$contributions[] = $data2;
			
			$data2['Contribution']['sentence_id'] = $translation['id'];
			$data2['Contribution']['sentence_lang'] = $translation['lang'];
			$data2['Contribution']['translation_id'] = $this->data['Sentence']['id'];
			$data2['Contribution']['translation_lang'] = $this->data['Sentence']['lang'];
			$data2['Contribution']['action'] = 'delete';
			$data2['Contribution']['user_id'] = $this->data['User']['id'];
			$data2['Contribution']['datetime'] = date("Y-m-d H:i:s");
			$data2['Contribution']['ip'] = $_SERVER['REMOTE_ADDR'];
			$data2['Contribution']['type'] = 'link';
			$contributions[] = $data2;
		}
		if(isset($contributions)){
			$this->Contribution->saveAll($contributions);
		}
	}

    /*
    ** search one random chinese/japanese sentence containing $sinogram
    ** return the id of this sentence
    */
    function searchOneExampleSentenceWithSinogram($sinogram){
        $results = $this->query("
        SELECT Sentence.id  FROM sentences AS Sentence 
            JOIN ( SELECT (RAND() *(SELECT MAX(id) FROM sentences)) AS id) AS r2
            WHERE Sentence.id >= r2.id
                AND Sentence.lang IN ( 'jpn','cmn','wuu')
                AND Sentence.text LIKE ('%$sinogram%')
            ORDER BY Sentence.id ASC LIMIT 1
       ");
       
       return $results[0]['Sentence']['id'] ;  
    }
    
    /*
    ** get the highest id for sentences
    */
    function getMaxId(){
        $resultMax = $this->query('SELECT MAX(id) FROM sentences');
        return $resultMax[0][0]['MAX(id)'];
    }
    
    /*
    ** get the id of a random sentence, from a particular language if $lang is set
    */
    function getRandomId($lang = null,$type = null ){
        /*
        ** this query take constant time when lang=null
        ** and linear time when lang is set, so do not touch this request
        */
        if($lang == 'jpn' OR $lang == 'eng'){
		
			$min = ($lang == 'eng') ? 15700 : 74000;
			$max = ($lang == 'eng') ? 74000 : 127300;
			$randId =  rand($min, $max);
            $query = ( "SELECT Sentence.id FROM sentences AS Sentence
                WHERE Sentence.id IN (". ($randId - 1) .",". $randId . ",". ($randId +1) . ")
                AND Sentence.lang = '$lang'
                LIMIT 1 ;
                ;"
            );


		} elseif ( $lang != null AND $lang !='any' ){
            
            $query= ("SELECT Sentence.id FROM sentences AS Sentence
                WHERE Sentence.lang = '$lang'
                ORDER BY RAND(".rand(). ") LIMIT  1"
                );

        } else {

            $query = 'SELECT Sentence.id  FROM sentences AS Sentence
                JOIN ( SELECT (RAND('. rand() .') *(SELECT MAX(id) FROM sentences)) AS id) AS r2
                WHERE Sentence.id >= r2.id
                ORDER BY Sentence.id ASC LIMIT 1' ;
        }

        $results = $this->query($query);
        /*
        while( !isset($results[0])){
            $results = $this->query($query);
        }
        */
        return $results[0]['Sentence']['id']; 
    }

    function getSeveralRandomIds($lang = null , $numberOfIdWanted = 10){
        $ids = array ();

        if (! is_numeric( $numberOfIdWanted )){
            return $ids ;
        }

        if ( $lang == null OR $lang == "any" ){ 

            $query= "SELECT Sentence.id FROM sentences AS Sentence
                ORDER BY RAND(".rand(). ") LIMIT  $numberOfIdWanted";
            
        } else {

            $query= "SELECT Sentence.id FROM sentences AS Sentence
            WHERE Sentence.lang = '$lang'
            ORDER BY RAND(".rand(). ") LIMIT  $numberOfIdWanted";
                                    
        }
        $results =  $this->query($query);

        foreach ($results as $i=>$result){
            $ids[$i] = $result['Sentence']['id'];
        }
         
        
        return $ids ; 

    }

    /*
    ** get the sentence with the given id
    ** TODO : to replace by getSentenceWithId 2
    */
    function getSentenceWithId($id,$type = null){
        if($type == 'translate'){
              $result = $this->find(
                'first',
                array(
                    'conditions' => array ('Sentence.id' => $id),
                    'contain'  => array (
                        'Favorites_users' => array(),
                        'User' =>array(),
                        'SentencesList' => array()
                    )
                )

            );
		} else {

            $this->unbindModel(
                array(
                    'hasAndBelongsToMany' => array('InverseTranslation','Translation')
                )
            );

            $this->id = $id;
            $result = $this->read();
        }
        return $result;

    }



    /*
    ** get all the informations needed to display a sentences in show section
    */
    function getShowSentenceWithId($id){
        $result = $this->find(
            'first',
            array(
                'conditions' => array ('Sentence.id' => $id),
                'contain'  => array (
                    'Favorites_users' => array(),
                    'User' =>array(),
                    'Contribution'
                )
            )

        );
        return $result;
    }

    /*
    ** delete the sentence with the given id
    */

    function delete($id){
        $this->id = $id;
		
		// for the logs
		$this->recursive = 1;
		$this->read();
		$this->data['User']['id'] = $this->Auth->user('id'); 
		
		//$this->Sentence->del($id, true); 
		// TODO : Deleting with del does not delete the right entries in sentences_translations.
		// But I didn't figure out how to solve that =_=;
		// So I'm just going to do something not pretty but whatever, I'm tired!!!
		$this->query('DELETE FROM sentences WHERE id='.$id);
		$this->query('DELETE FROM sentences_translations WHERE sentence_id='.$id);
		$this->query('DELETE FROM sentences_translations WHERE translation_id='.$id);
		
		// need to call afterDelete() manually for the logs
		$this->Sentence->afterDelete();


    }

    /*
    ** Count number of sentences in each language
    */
    function statistics(){

    }

    /*
    ** get number of sentences owned by a given user
    */
    function numberOfSentencesOwnedBy($userId){
        return $this->find(
            'count',
            array(
                 'conditions' => array( 'Sentence.user_id' => $userId)
            )
        );
    }

    /*
    ** get translations of a given sentence
    */
    function getTranslationsOf($id,$excludeId = null){
        $conditions = array (
            'Sentence.id' => $id
        );
        // DA ultimate Query 
        $query = "
                SELECT p1.text AS text, 
                  p2.text AS translation_text,
                  p2.id   AS translation_id,
                  p2.lang AS translation_lang,
                  p2.user_id AS translation_user_id,
                  'Translation' as distance
                FROM sentences AS p1
                LEFT OUTER JOIN sentences_translations AS t ON p1.id = t.sentence_id
                  LEFT OUTER JOIN sentences AS p2 ON t.translation_id = p2.id
                WHERE 
                 p1.id = '$id' 

                UNION

                SELECT p1.text AS text,
                  p2.text AS translation_text,
                  p2.id   AS translation_id,
                  p2.lang AS translation_lang,
                  p2.user_id AS translation_user_id,
                  'IndirectTranslation'  as distance
                FROM sentences AS p1
                LEFT OUTER JOIN sentences_translations AS t ON p1.id = t.sentence_id
                  LEFT OUTER JOIN sentences_translations AS t2 ON t2.sentence_id = t.translation_id
                    LEFT OUTER JOIN sentences AS p2 ON t2.translation_id = p2.id
                WHERE 
                 p1.id != p2.id
                 AND p2.id NOT IN (
                     SELECT sentences_translations.translation_id FROM sentences_translations
                     WHERE sentences_translations.sentence_id = '$id' )
                 AND p1.id = '$id'
        ";

        $results = $this->query($query);
        //pr ( $results ) ;

        $orderedResults = array(
            "Translation" => array() ,
            "IndirectTranslation" => array()
        );
        foreach( $results as $result ){
            $result = $result[0] ;
            array_push(
                $orderedResults[$result['distance']],
                array(
                    'id' => $result['translation_id'],
                    'text' => $result['translation_text'],
                    'user_id' => $result['translation_user_id'],
                    'lang' => $result['translation_lang']
                )
        ); 

        }
        //pr($orderedResults ) ;
        /*
        $result = $this->find(
            'first',
            array(
                'fields' => array('Sentence.id'),
                'conditions' => $conditions,
                'contain' => array(
                    'Translation' => array (
                        'fields' => array (
                            'Translation.id',
                            'Translation.text',
                            'Translation.user_id',
                            'Translation.lang'
                        )
                    )
                )
            )
        );*/
            return $orderedResults;
    }



    /*
    ** retrive tranlations of translations 
    */
    // TODO HACK SPOTTED : we should be able to diretly get 
    // undirect translations without needing to use translations

    function getIndirectTranslations($translations,$id){
        $indirectTranslations = array();
        $directTranslationsId = array($id);
        foreach ($translations as $translation){
            array_push( $directTranslationsId,$translation['id']); 
        }

        foreach ($translations as $translation){

            $temps = $this->getTranslationsOf($translation['id']);
             
            foreach ( $temps as $temp ){
                if (! in_array($temp['id'],$directTranslationsId )) {
                    $indirectTranslations[$temp['id']] = $temp ;
                }
            }

        }

        return $indirectTranslations ;
    }

}
?>
