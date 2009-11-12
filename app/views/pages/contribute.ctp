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
?>


<div id="annexe_content">	
	<div class="module">
	<h2><?php __("Tip"); ?></h2>
	<?php __("You can add sentences that you do not know how to translate. Perhaps someone else will know!"); ?>
	</div>
	
	<div class="module">
	<h2><?php __("Good translations"); ?></h2>
	<?php __("We know it's difficult, but do NOT translate word for word!"); ?>
	</div>
	
	<div class="module">
	<h2><?php __("Multiple translations"); ?></h2>
	<?php __("If you feel there are several possible translations, note that for a same sentence, you can add several translations in the same language."); ?>
	</div>
</div>

<div id="main_content">
	<?php 
	if($session->read('Auth.User.id')){ 
	?>
	
		<div class="module">
			<?php
			echo '<h2>';
			__('Add new sentences');
			echo '</h2>';
			
			echo '<div class="sentences_set">';
				echo '<div class="new">';
				echo $form->create('Sentence', array("action" => "add", "class" => "add"));
				echo $form->input('text', array("label" => __('Sentence : ', true)));
				echo $form->end('OK');
				echo '</div>';
			echo '</div>';
			?>
		</div>
			
		<div class="module">
			<h2><?php 
			echo sprintf(
				__('Translate (%s) or adopt sentences (%s)',true),
				$html->image('translate.png'),
				$html->image('adopt.png')
			); 
			?></h2>
			<p><?php __("It's easy, try it out below with the random sentence.") ?></p>
		</div>
		
		<div class="module">
			<?php echo $this->element('random_sentence'); ?>
		</div>
		
	<?php
	}else{
	?>
		
		<div class="module">
		<h2><?php __('We need your help!'); ?></h2>
		
		<?php __('There are three ways you can contribute:') ?>
		<ul>
			<li>
			<?php 
			__('By adding new sentences.');
			echo ' '.$html->link(__('Learn more...',true), array("controller"=>"pages", "action"=>"about"));
			?>
			</li>
			<li>
			<?php 
			__('By translating existing sentences.'); 
			echo ' '.$html->link(__('Learn more...',true), array("controller"=>"pages", "action"=>"about"));
			?>
			</li>
			<li>
			<?php 
			__('By adopting sentences.'); 
			echo ' '.$html->link(__('Learn more...',true), array("controller"=>"pages", "action"=>"about"));
			?>
			</li>
		</ul>
		
		<?php
		__('Interested? Then please, register.');
		echo ' '.$html->link(
			'gros bouton register',
			array("controller" => "users", "action" => "register")
		);
		?>
		</div>
		
	<?php
	}
	?>
</div>

