<?php
$oXML = new XMLWriter();
$oXML->openURI('php://output');

$oXML->startDocument('1.0');

$oXML->startElement('tatoeba');
$oXML->writeAttribute('version', '1.0');

	$oXML->startElement('error');
		$oXML->writeAttribute('code', $code);
		$oXML->writeAttribute('message', $message);
	$oXML->endElement();

	$oXML->startElement('sentence');
		$oXML->writeAttribute('lang', $sentence['Sentence']['lang']);

		$oXML->startElement('id');
		$oXML->text($sentence['Sentence']['id']);
		$oXML->endElement();

		/*
		$oXML->startElement('language');
		$oXML->text($sentence['Sentence']['lang']);
		$oXML->endElement();
		*/
		
		$oXML->startElement('text');
			// $oXML->writeAttribute('dir', 'ltr|rtl')
			// $oXML->writeAttribute('lang', $sentence['Sentence']['lang'])
		$oXML->text($sentence['Sentence']['text']);
		$oXML->endElement();

		$translations = array_merge($translations['Translation'], $translations['IndirectTranslation']);

		if(count($translations) > 0){
			$oXML->startElement('translations');

			foreach ($translations as $translation){
				$oXML->startElement('translation');
						$oXML->writeAttribute('lang', $translation['lang']);
						
						$oXML->startElement('id');
							$oXML->text($translation['id']);
						$oXML->endElement();

						$oXML->startElement('text');
							// $oXML->writeAttribute('dir', 'ltr|rtl');
							// $oXML->writeAttribute('lang', $translation['lang']);
						$oXML->text($translation['text']);
						$oXML->endElement();

				$oXML->endElement();
			}

			$oXML->endElement();

		}
		
		$oXML->startElement('adopted');
		$oXML->text(empty($sentence['Sentence']['user_id']) ? 'true' : 'false');
		$oXML->endElement();

		$oXML->startElement('added');
		$oXML->text($sentence['Sentence']['created']);
		$oXML->endElement();

		$oXML->startElement('modified');
		$oXML->text($sentence['Sentence']['modified']);
		$oXML->endElement();

		/*
		$oXML->startElement('comments_count');
		$oXML->text($sentence['Sentence']['']);
		$oXML->endElement();

		$oXML->startElement('translation_count');
		$oXML->text($sentence['Sentence']['']);
		$oXML->endElement();

		$oXML->startElement('favorited');
		$oXML->text($sentence['Sentence']['']);
		$oXML->endElement();
		*/

	$oXML->endElement();

	if(!empty($sentence['Sentence']['user_id'])){
		$oXML->startElement('owner');

			$oXML->startElement('id');
			$oXML->text($sentence['User']['id']);
			$oXML->endElement();

			$oXML->startElement('username');
			$oXML->text($sentence['User']['username']);
			$oXML->endElement();

			$oXML->startElement('name');
			$oXML->text($sentence['User']['name']);
			$oXML->endElement();

			$oXML->startElement('url');
			$oXML->text($sentence['User']['homepage']);
			$oXML->endElement();

			$oXML->startElement('email');
			$oXML->text($sentence['User']['email']);
			$oXML->endElement();

			$oXML->startElement('joined');
			$oXML->text($sentence['User']['since']);
			$oXML->endElement();

			/*
			$oXML->startElement('country');
			$oXML->text($sentence['Country']['name']);
			$oXML->endElement();
			*/

		$oXML->endElement();
	}

$oXML->endElement();

$oXML->endDocument();

$oXML->flush();
?>