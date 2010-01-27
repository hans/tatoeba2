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
?>

<div id="annexe_content">
	<div class="module">
	<h2><?php __('Language not listed?') ?></h2>
	<?php
		echo '<p>';
		__('If your language is not in the list, don\'t hesitate to contact me : trang.dictionary.project@gmail.com.');
		echo '</p>';
	?>
	</div>
</div>

<div id="main_content">
	<div class="module">
		<?php
		echo '<h2>';
		__('Unknown language');
		echo '</h2>';
		
		echo '<p>';
		__('The language of the following sentences could not be detected, you have to specify it manually. ');
		echo '</p>';
		if(count($unknownLangSentences) > 0){
			$langArray = $languages->languagesArray();
            // TODO : hack spotted should have a direct method to retrieve languages array without "und"
            unset($langArray['und']);// to avoid possibility to set a sentence as "any languages" :p

			asort($langArray);
			echo $form->create('Sentence', array('action'=>'set_languages'));
			echo '<ul>';
			foreach($unknownLangSentences as $i=>$sentence){
				echo '<li>';
				echo $form->input('Sentence.'.$i.'.id', array("value" => $sentence['Sentence']['id']));
				echo $form->select(
                    'Sentence.'.$i.'.lang',
                     $langArray,
                     null,
                     null,
                     false 
                    );
				echo ' ';
				echo Sanitize::html($sentence['Sentence']['text']);
				echo '</li>';
			}
			echo '</ul>';
			echo $form->end(__('save',true));
		}else{
			echo '<p><em>';
			__('You don\'t have any sentence which language is unknown.');
			echo '</em></p>';
		}
		?>

	</div>
</div>
