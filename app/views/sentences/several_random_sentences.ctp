<?php
/**
 * Tatoeba Project, free collaborative creation of multilingual corpuses project
 * Copyright (C) 2009  HO Ngoc Phuong Trang <tranglich@gmail.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * PHP version 5
 *
 * @category PHP
 * @package  Tatoeba
 * @author   HO Ngoc Phuong Trang <tranglich@gmail.com>
 * @license  Affero General Public License
 * @link     http://tatoeba.org
 */

$this->pageTitle = 'Tatoeba - ' . __('Random sentences', true);

?>
<div id="annexe_content">
       <div class="module">
            <h2><?php __("For serial translators"); ?></h2>
            <?php 
            echo '<p>';
            __(
                'Translating sentences one by one is too slow for you? '.
                'You want to increase your rank in Tatoeba, or the rank of '.
                'your language, at the speed of light? So this is for you!'
            );
            echo '</p>';
            
            echo '<p>';
            __("Just Keep in mind our server is not as fast as you ;-)");
            echo '</p>';

            $numberOfSentencesWanted = array (5 => 5 , 10 => 10 , 15 => 15);
            $selectedLanguage = $session->read('random_lang_selected');
            
            echo $form->create(
                'Sentence', 
                array(
                    "action" => "several_random_sentences",
                    "type" => "post"
                )
            );

            echo '<fieldset class="select">';
            echo '<label>' . __('Quantity', true) . '</label> ';
            echo $form->select(
                'numberWanted', 
                $numberOfSentencesWanted, 
                $lastNumberChosen,
                null,
                false
            );
            echo '</fieldset>';


            echo '<fieldset class="select">';
            echo '<label>' . __('Language', true) . '</label> ';
            echo $form->select(
                'into', 
                $languages->languagesArray(), 
                $selectedLanguage,
                null,
                false
            );
            echo '</fieldset>';

            echo '<fieldset class="submit">';
            echo '<input type="submit" value="'.__('show random sentences', true).'"/>';
            echo '</fieldset>';
            echo $form->end();
            ?>
        </div>
    </div>    
<div id="main_content">
    <div class="module">
    <h2><?php __('Random sentences'); ?></h2>
        <?php
        foreach ($allSentences as $index=>$sentence) {
                    echo '<div class="sentences_set searchResult">';
                    // sentence menu (translate, edit, comment, etc)
                    $specialOptions =  $sentence['Sentence']['specialOptions'] ;
                    // TODO set up a better mechanism
                    $specialOptions['belongsTo']= $sentence['User']['username']; 
                    $sentences->displayMenu(
                        $sentence['Sentence']['id'],
                        $sentence['Sentence']['lang'],
                        $specialOptions,
                        null,
                        $sentence['Sentence']['script']
                    );

                    // sentence and translations
                    // TODO set up a better mechanism
                    $sentence['User']['canEdit'] = $specialOptions['canEdit']; 
                    $sentence['User']['canLinkAndUnlink'] 
                        = $specialOptions['canLinkAndUnlink']; 
                    $sentences->displayGroup(
                        $sentence['Sentence'],
                        $sentence['Translations'],
                        $sentence['User'],
                        $sentence['IndirectTranslations']
                    );
                    echo '</div>';
        }
        ?>
    </div>
</div>


