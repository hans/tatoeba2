<?php
/*
    Tatoeba Project, free collaborative creation of multilingual corpuses project
    Copyright (C) 2009  HO Ngoc Phuong Trang <tranglich@gmail.com>

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

App::import('Core', 'Sanitize');

class SentencesHelper extends AppHelper {

	var $helpers = array('Html', 'Form', 'Kakasi', 'Javascript', 'Menu', 'Languages');
	
	/**
	 * Display a single sentence.
	 */
	function displaySentence($sentence) {
		echo '<div class="original sentence">';
		// Sentence
		echo '<span class="correctness'.$sentence['correctness'].' '.$sentence['lang'].'">';
		echo $this->Html->link($sentence['text'], array("controller" => "sentences", "action" => "show", $sentence['id']));
		echo '</span> ';
		
		$this->displayRomanization($sentence);
		
		echo '</div>';
	}
		
		
	/**
	 * Display romanization.
	 */
	function displayRomanization($sentence){
	    if (isset($sentence['romanization'])){
            echo '<div class="romanization">';
                echo $sentence['romanization'];
		    echo '</div>';
        }
    }
	
	
	/**
	 * Display romaji.
	 */
	function displayRomaji($text){
		echo '<div class="romanization" title="'.__('WARNING : the romaji is automatically generated and is not always reliable.',true).'">';
			$this->Kakasi->convert($text, 'romaji');
		echo '</div>';
	}
	
	
	/**
	 * Display pinyin.
	 */
	function displayPinyin($text){
		echo '<div class="romanization">';
        
		/* IF ADSO.SH DONT WORK OR IF YOU WANT TO SET UP ADSO
        $curl = curl_init();
		curl_setopt ($curl, CURLOPT_URL, "http://adsotrans.com/popup/pinyin.php?text=".$text);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		$result = curl_exec ($curl);
		$pinyin = substr($result, 14);
		$pinyin = substr($pinyin, 0, -44);
        */
        escapeshellarg($text); 
        system("echo `adso.sh -i $text -y`");
		echo '</div>';
	}

	
	
	/**
	 * Display a single sentence for edit in place.
	 */
	function displayEditableSentence($sentence) {
		echo '<div id="_'.$sentence['id'].'" class="original sentence mine">';
			// info icon
			echo $this->Html->link(
				$this->Html->image('info.png')
				, array(
					"controller"=>"sentences"
					, "action"=>"show"
					, $sentence['id']
				)
				, array("escape"=>false, "class"=>"infoIcon")
			);
			
			// Language flag
			$this->displayLanguageFlag($sentence['id'], $sentence['lang'], true);
			
			// Sentence
			echo '<div id="'.$sentence['lang'].'_'.$sentence['id'].'" class="editable editableSentence correctness'.$sentence['correctness'].'">';
			echo $sentence['text'];
			echo '</div> ';
			
			$this->displayRomanization($sentence);
			
		echo '</div>';
	}
	
	
	/**
	 * Display sentence in list.
	 */
	function displaySentenceInList($sentence, $translationsLang = null) {
		// Sentence
		echo '<span id="'.$sentence['lang'].$sentence['id'].'" class="sentenceInList '.$sentence['lang'].'">';
		echo $this->Html->link($sentence['text'], array("controller" => "sentences", "action" => "show", $sentence['id']));
		echo '</span> ';
		$this->displayRomanization($sentence);
		
		// Translations
		if($translationsLang != null){
			foreach($sentence['Translation'] as $translation){
				if($translation['lang'] == $translationsLang){
					echo '<span id="'.$translation['lang'].$translation['id'].'" class="translationInList '.$translation['lang'].'">';
					echo $this->Html->link($translation['text'], array("controller" => "sentences", "action" => "show", $translation['id']));
					echo '</span> ';
				}
			}
		}
	}
	
	
	/**
	 * Diplay a sentence and its translations.
	 */
	function displayGroup($sentence, $translations, $user = null, $indirectTranslations = array(), $inBrowseMode = false) {
		echo '<div class="sentence">';
		// Sentence
		$this->Javascript->link('jquery.jeditable.js', false);
		$this->Javascript->link('sentences.edit_in_place.js', false);
		
		$editable = '';
		$editableSentence = '';
		$editableFlag = false;
		$tooltip = __('This sentence does not belong to anyone. If you would like to edit it, you have to adopt it first.', true);
       
		if($user != null){
			if(isset($user['canEdit']) AND $user['canEdit']){
				$editable = 'editable';
				$editableSentence = 'editableSentence';
				$editableFlag = true;
			}
			if(isset($user['username']) AND $user['username'] != ''){
				$tooltip = __('This sentence belongs to :', true) .' '.$user['username'];
			}
		}
		
		// Original sentence
		echo '<div id="_'.$sentence['id'].'_original" class="original">';
			// language flag
			$this->displayLanguageFlag($sentence['id'], $sentence['lang'], $editableFlag);
			
			// sentence text
            if($inBrowseMode){
				// TODO : HACK SPOTTED id is made of lang + id  and then is used in edit_in_place 
				echo '<div id="'.$sentence['lang'].'_'.$sentence['id'].'" class="'.$editable.' '.$editableSentence.'" title="'.$tooltip.'">'
                    .Sanitize::html($sentence['text']).'</div> ';
			}else{
				echo $this->Html->link(
					$sentence['text']
					, array(
						"controller"=>"sentences"
						, "action"=>"show"
						, $sentence['id']
					)
				);
			}
			
			
			// romanization
			$this->displayRomanization($sentence);
		echo '</div>';

        echo "\n";
		
		// To add new translations
		echo '<ul id="translation_for_'.$sentence['id'].'" class="addTranslations"><li></li></ul>';
		
		// Translations
		echo '<ul id="_'.$sentence['id'].'_translations" class="translations">';
           echo '<li></li>';
		if(count($translations) > 0){
			// direct translations
			$this->displayTranslations($translations, 'show');
			
			// indirect translations
			$this->displayIndirectTranslations($indirectTranslations, 'show');
		}
		echo '</ul>';
		
		echo '</div>';

        echo "\n";
    }
	 
	/**
	 * Display direct translations.
	 */
	function displayTranslations($translations, $action){

        $controller = "sentences" ;
        if (preg_match("/sentence_comments|contributions/", $this->params['controller'])){
            $controller = $this->params['controller'];
        }
		
		foreach($translations as $translation){
        
			echo '<li class="direct translation">';
			// translation icon
			echo $this->Html->link(
				$this->Html->image(
					'direct_translation.png',
					array(
						"alt"=>__('Show',true),
						"title"=>__('Show',true)
					)
				),
				array(
					"controller" => $controller,
					"action" => $action,
					$translation['id']
				),
				array("escape"=>false, "class"=>"info")
			);
			
			// language flag
			$this->displayLanguageFlag($translation['id'], $translation['lang']);
			
			//translation and romanization
			// translation text
                    echo '<div >';
                    echo $this->Html->link(
                        $translation['text'],
                        array(
                            "controller" => "sentences",
                            "action" => $action,
                            $translation['id']
                        ),
                        array("escape"=>false)
                    );
                    echo '</div>';

			$this->displayRomanization($translation);
			
			echo '</li>';
		}
	}
	
	/**
	 * Display indirect translations, that is to say translations of translations.
	 */
	function displayIndirectTranslations($indirectTranslations, $action){
        if(count($indirectTranslations) > 0){
            foreach($indirectTranslations as $translation){
                    echo '<li class="indirect translation">';
                    
					// translation icon
                    echo $this->Html->link(
                        $this->Html->image(
                            'indirect_translation.png',
                            array(
                                "alt"=>__('Show',true),
                                "title"=>__('Show',true)
                            )
                        ),
                        array(
                            "controller" => "sentences",
                            "action" => $action,
                            $translation['id']
                        ),
                        array("escape"=>false, "class"=>"info")
                    );
                    
                    // language flag
                    $this->displayLanguageFlag($translation['id'], $translation['lang']);
                    
                    // translation text
                    echo '<div title="'.__('indirect translation',true).'">';
                    echo $this->Html->link(
                        $translation['text'],
                        array(
                            "controller" => "sentences",
                            "action" => $action,
                            $translation['id']
                        ),
                        array("escape"=>false)
                    );
                    echo '</div>';
                    
                    echo '</li>';
            }
        }
	}
	
	
	
	/**
	 * Sentence options (translate, edit, correct, comments, logs, edit, ...)
	 */
	function displayMenu($id, $lang, $specialOptions, $score = null){		
		echo '<ul class="menu" id="_'. $id .'" lang="'.$lang.'">';
			// score
			if($score != null){
				echo '<li class="score">';
				echo intval($score * 100);
				echo '%';
				echo '</li>';
			}
			
			// owner
			if(isset($specialOptions['belongsTo'])){
				echo '<li class="belongsTo">';
					$user = $this->Html->link($specialOptions['belongsTo'], array("controller" => "user", "action" => "profile", $specialOptions['belongsTo']));
                    echo sprintf ( __('belongs to %s', true),  $user);
				echo '</li>';
			}
			
			// translate
			if($specialOptions['canTranslate']){
				$this->Javascript->link('sentences.add_translation.js', false);
				$this->Menu->translateButton();
			}
			
			// adopt
			if(isset($specialOptions['canAdopt']) AND $specialOptions['canAdopt'] == true){
				$this->Menu->adoptButton($id);
                echo "\n";
			}
			
			// let go
			if(isset($specialOptions['canLetGo']) AND $specialOptions['canLetGo'] == true){
				$this->Menu->letGoButton($id);
                echo "\n";
			}
			
			// favorite
			if(isset($specialOptions['canFavorite']) AND $specialOptions['canFavorite'] == true){
				$this->Javascript->link('favorites.add.js', false);
				$this->Menu->favoriteButton($id);
                echo "\n";
			}
			
			// unfavorite
			if(isset($specialOptions['canUnFavorite']) AND $specialOptions['canUnFavorite'] == true){
				$this->Javascript->link('favorites.add.js', false);
				$this->Menu->unfavoriteButton($id);
                echo "\n";
			}
			
			// add to list
			if(isset($specialOptions['canAddToList']) AND $specialOptions['canAddToList'] == true){
				$this->Javascript->link('sentences_lists.menu.js', false);
				$this->Javascript->link('jquery.impromptu.js', false);
				$lists = $this->requestAction('/sentences_lists/choices'); // this is probably not the best solution...
				
				$this->Menu->addToListButton();
				
				echo '<li style="display:none" class="addToList'.$id.'">';
					// select list
					echo '<select class="listOfLists" id="listSelection'.$id.'">';
					echo '<option value="-1">';
					__('Add to new list...');
					echo '</option>';
					
					echo '<option value="-2">';
					__('Manage lists...');
					echo '</option>';
					
					echo '<option value="0">--------------</option>';
					
					// user's lists
					foreach($lists as $list){
						if(!in_array($list['SentencesList']['id'], $specialOptions['belongsToLists']) AND !$list['SentencesList']['is_public']){
							echo '<option value="'.$list['SentencesList']['id'].'">';
							echo $list['SentencesList']['name'];
							echo '</option>';
						}
					}

					echo '<option value="0">--------------</option>';
					
					// public lists
					foreach($lists as $list){
						if(!in_array($list['SentencesList']['id'], $specialOptions['belongsToLists']) AND $list['SentencesList']['is_public']){
							echo '<option value="'.$list['SentencesList']['id'].'">';
							echo $list['SentencesList']['name'];
							echo '</option>';
						}
					}
					
					echo '</select>';
					// ok button
					echo '<input type="button" value="ok" class="addToListButton" />';
				echo '</li>';
                echo "\n";
			}			
			
			// delete
			if(isset($specialOptions['canDelete']) AND $specialOptions['canDelete'] == true){
				$this->Menu->deleteButton($id);
			}
			
            echo "<li>";
			echo $this->Html->image('loading-small.gif', array("id"=>"_".$id."_in_process", "style"=>"display:none"));
			echo $this->Html->image('valid_16x16.png', array("id"=>"_".$id."_valid", "style" =>"display:none"));			
            echo "</li>";
			
		echo '</ul>';
	}
	
	/**
	 * Language flag.
	 */
	function displayLanguageFlag($id, $lang, $editable = false){
		if($lang == '') $lang = 'unknown_lang';
		
		$class = '';
		if($editable){
			$this->Javascript->link('sentences.change_language.js', false);
			$class = 'editableFlag';
			
			// language select
			$langArray = $this->Languages->languagesArray();
			asort($langArray);
			echo $this->Form->select(
                'selectLang_'.$id,
                $langArray,
                'und',
                array("class"=>"selectLang"),
                false
            );
		}
		
		echo $this->Html->image(
			  $lang.'.png'
			, array("class" => "languageFlag ".$class)
		);
		
	}
}
?>
