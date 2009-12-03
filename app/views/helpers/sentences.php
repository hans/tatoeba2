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
		
		$this->displayRomanization($sentence['lang'], $sentence['text']);
		
		echo '</div>';
	}
		
		
	/**
	 * Display romanization.
	 */
	function displayRomanization($sentenceLang, $sentenceText){
		if($sentenceLang == 'jp'){
			$this->displayRomaji($sentenceText);
		}
		if($sentenceLang == 'zh'){
			$this->displayPinyin($sentenceText);
		}
	}
	
	
	/**
	 * Display romaji.
	 */
	function displayRomaji($text){
		echo '<span class="romaji" title="'.__('WARNING : the romaji is automatically generated and is not always reliable.',true).'">';
			$this->Kakasi->convert($text, 'romaji');
		echo '</span>';
	}
	
	
	/**
	 * Display pinyin.
	 */
	function displayPinyin($text){
		echo '<span class="pinyin">';
		$curl = curl_init();
		curl_setopt ($curl, CURLOPT_URL, "http://adsotrans.com/popup/pinyin.php?text=".$text);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		$result = curl_exec ($curl);
		$pinyin = substr($result, 14);
		$pinyin = substr($pinyin, 0, -44);
		echo $pinyin;
		echo '</span>';
	}

	
	
	/**
	 * Display a single sentence for edit in place.
	 */
	function displayEditableSentence($sentence) {
		echo '<div id="'.$sentence['id'].'" class="editable original sentence">';
			// Language flag
			$this->displayLanguageFlag($sentence['id'], $sentence['lang'], true);
			
			// Sentence
			echo '<span id="'.$sentence['lang'].$sentence['id'].'" class="editableSentence correctness'.$sentence['correctness'].'">';
			echo $sentence['text'];
			echo '</span> ';
			
			$this->displayRomanization($sentence['lang'], $sentence['text']);
			
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
		$this->displayRomanization($sentence['lang'], $sentence['text']);
		
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
	function displayGroup($sentence, $translations, $user = null) {
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
		echo '<div id="'.$sentence['id'].'" class="original">';
			// language flag
			$this->displayLanguageFlag($sentence['id'], $sentence['lang'], $editableFlag);
			
			// sentence text
			echo '<div title="'.$tooltip.'" class="'.$editable.' correctness'.$sentence['correctness'].'">';
				echo '<span id="'.$sentence['lang'].$sentence['id'].'" class="'.$editableSentence.'">'.$sentence['text'].'</span> ';
				$this->displayRomanization($sentence['lang'], $sentence['text']);
			echo '</div>';
		echo '</div>';
		
		// To add new translations
		echo '<ul id="translation_for_'.$sentence['id'].'" class="addTranslations"></ul>';
		
		// Translations
		echo '<ul id="'.$sentence['id'].'_translations" class="translations">';
		if(count($translations) > 0){
			// direct translations
			$this->displayTranslations($translations, 'show');
			
			// indirect translations
			$this->displayIndirectTranslations($sentence, 'show');
		}
		echo '</ul>';
		
		echo '</div>';
    }
	 
	/**
	 * Display direct translations.
	 */
	function displayTranslations($translations, $action){			
		$controller = (preg_match("/sentence_comments|contributions/", $this->params['controller'])) ? $this->params['controller'] : "sentences";
		
		foreach($translations as $translation){
			echo '<li class="direct translation">';
			// hidden 'info button'
			echo $this->Html->link(
				$this->Html->image(
					'info.png',
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
				array("escape"=>false)
			);
			
			// language flag
			$this->displayLanguageFlag($translation['id'], $translation['lang']);
			
			//translation and romanization
			echo '<div>' . $translation['text'] . '</div>';
			$this->displayRomanization($translation['lang'], $translation['text']);
			
			echo '</li>';
		}
	}
	
	/**
	 * Display indirect translations, that is to say translations of translations.
	 */
	function displayIndirectTranslations($sentence, $action){
		if(isset($sentence['Translation'])){
			$translations = $sentence['Translation'];
			$translationsIds = array($sentence['Sentence']['id']);
			$indirectTranslations = array();
			
			foreach($translations as $translation){
				$translationsIds[] = $translation['id'];
				if(isset($translation['IndirectTranslation'])){
					foreach($translation['IndirectTranslation'] as $indirectTranslation){
						if($indirectTranslation['id'] != $sentence['Sentence']['id']){
							$indirectTranslations[] = $indirectTranslation;
						}
					}
				}
			}
			
			if(count($indirectTranslations) > 0){
				foreach($indirectTranslations as $translation){
					if(!in_array($translation['id'], $translationsIds)){
						echo '<li class="indirect translation">';
						echo $this->Html->link(
							$this->Html->image(
								'info.png',
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
							array("escape"=>false)
						);
						
						// language flag
						$this->displayLanguageFlag($translation['id'], $translation['lang']);
						
						// translation text
						echo '<div title="'.__('indirect translation',true).'">' . $translation['text'] . '</div>';
						
						echo '</li>';
					}
				}
			}
		}
	}
	
	
	/**
	 * Display sentences, direct translations and indirect ones. User can also enter the id
	 * of another sentence if he wants to link that sentence to the current sentence.
	 */
	function displayForLink($sentence, $translations){
		echo '<div class="sentence">';
		
			// Sentence
			echo '<div class="original correctness'.$sentence['correctness'].'">';
			echo '<span class="'.$sentence['lang'].'">'.$sentence['text'].'</span>';
			echo '</div>';
			
			echo '<ul class="translations">';
			if(count($translations) > 0){
				// direct translations
				$this->displayTranslations($translations, 'link');
				
				// indirect translations
				$this->displayIndirectTranslations($sentence, 'link');
			}
			echo '</ul>';
		echo '</div>';
	}
	
	
	/**
	 * Sentence options (translate, edit, correct, comments, logs, edit, ...)
	 */
	function displayMenu($id, $lang, $specialOptions, $score = null){		
		echo '<ul class="menu" id="'. $id .'" lang="'.$lang.'">';
			if($score != null){
				echo '<li class="score">';
				echo intval($score * 100);
				echo '%';
				echo '</li>';
			}
			
			$this->Menu->showButton($id);
			
			// translate link
			if($specialOptions['canTranslate']){
				$this->Javascript->link('sentences.add_translation.js', false);
				$this->Menu->translateButton();
			}
			
			// "link" link => everyone can see
			// echo '<li class="'.$this->optionClass('link').'">';
			// echo $this->Html->link(
				// __('Link',true),
				// array(
					// "controller" => "sentences",
					// "action" => "link",
					// $id
				// ));
			// echo '</li>';
			
			// adopt
			if(isset($specialOptions['canAdopt']) AND $specialOptions['canAdopt'] == true){
				$this->Menu->adoptButton($id);
			}
			
			// let go
			if(isset($specialOptions['canLetGo']) AND $specialOptions['canLetGo'] == true){
				$this->Menu->letGoButton($id);
			}
			
			// comments link
			$this->Menu->commentsButton($id);
			
			// favorite link
			if(isset($specialOptions['canFavorite']) AND $specialOptions['canFavorite'] == true){
				$this->Javascript->link('favorites.add.js', false);
				$this->Menu->favoriteButton($id);
			}
			
			// unfavorite link
			if(isset($specialOptions['canUnFavorite']) AND $specialOptions['canUnFavorite'] == true){
				$this->Javascript->link('favorites.add.js', false);
				$this->Menu->unfavoriteButton($id);
			}
			
			// add to list
			if(isset($specialOptions['canAddToList']) AND $specialOptions['canAddToList'] == true){
				$this->Javascript->link('sentences_lists.menu.js', false);
				$this->Javascript->link('jquery.impromptu.js', false);
				$lists = $this->requestAction('/sentences_lists/choices'); // this is probably not the best solution...
				
				$this->Menu->addToListButton();
				
				echo '<span style="display:none" class="addToList'.$id.'">';
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
				echo '</span>';
			}			
			
			// delete link
			if(isset($specialOptions['canDelete']) AND $specialOptions['canDelete'] == true){
				$this->Menu->deleteButton($id);
			}
			
			echo $this->Html->image('loading-small.gif', array("id"=>$id."_in_process", "style"=>"display:none"));
			echo $this->Html->image('valid_16x16.png', array("id"=>$id."_valid", "style" =>"display:none"));
			
			if(isset($specialOptions['belongsTo'])){
				echo '<li class="belongsTo">- ';
				echo __('belongs to ', true);
				echo $specialOptions['belongsTo'];
				echo '</li>';
			}
			
		echo '</ul>';
	}
	
	/**
	 * Language flag.
	 */
	function displayLanguageFlag($id, $lang, $editable = false){
		
		$class = '';
		if($editable){
			$this->Javascript->link('sentences.change_language.js', false);
			$class = 'editableFlag';
			
			// language select
			$langArray = $this->Languages->languagesArray();
			asort($langArray);
			echo $this->Form->select('selectLang_'.$id, $langArray, null, array("class"=>"selectLang"));
		}
		
		echo $this->Html->image(
			  $lang.'.png'
			, array("class" => "languageFlag ".$class)
		);
		
	}
}
?>
