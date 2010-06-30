<?php
/**
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
 *
 * PHP version 5 
 *
 * @category PHP
 * @package  Tatoeba
 * @author   Allan Simon <allan.simon@supinfo.com>
 * @author   HO Ngoc Phuong Trang <tranglich@gmail.com>
 * @license  Affero General Public License
 * @link     http://tatoeba.org
 */

/**
 * Model Class which represent sentences
 *
 * @category PHP
 * @package  Tatoeba
 * @author   Allan Simon <allan.simon@supinfo.com>
 * @author   HO Ngoc Phuong Trang <tranglich@gmail.com>
 * @license  Affero General Public License
 * @link     http://tatoeba.org
*/
class Sentence extends AppModel
{

    public $name = 'Sentence';
    public $actsAs = array("Containable", "Sphinx");
    public static $romanji = array('furigana' => 1, 'mix' => 2, 'romanji' => 3);

    // This is not much in use. Should probably remove it someday
    const MAX_CORRECTNESS = 6; 
    
    public $languages = array(
        'ara', 'bul', 'deu', 'ell', 'eng',
        'epo', 'spa', 'fra', 'heb', 'ind',
        'jpn', 'kor', 'nld', 'por', 'rus',
        'vie', 'cmn', 'ces', 'fin', 'ita',
        'tur', 'ukr', 'wuu', 'swe', 'zsm',
        'nob', 'est', 'kat', 'pol', 'swh',
        'lat', 'arz', 'bel', 'hun', 'isl',
        'sqi', 'yue', 'afr', 'fao', 'fry',
        'uig', 'uzb', 'bre', 'ron', 'non',
        'srp', 'yid', 'tat', 'pes', 'nan',
        'eus', 'slk', 'dan',
        null
        );    
    public $validate = array(
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

    public $hasMany = array(
        'Link',
        'Contribution',
        'SentenceComment', 
        'Favorites_users' => array ( 
            'classname'  => 'favorites',
            'foreignKey' => 'favorite_id'
        ),
        'SentenceAnnotation'
    );
    
    public $belongsTo = array('User','TagsSentences');
    
    public $hasAndBelongsToMany = array(
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
        'SentencesList',
        'Tag' => array(
            'className' => 'Tag',
            'joinTable' => 'tags_sentences',
            'foreignKey' => 'sentence_id',
            'associationForeignKey' => 'tag_id'
        ),


    );
    
    /**
     * The constructor is here only to set the rule for languages.
     * 
     * @return void
     */
    public function __construct() 
    {
        parent::__construct();
        $this->validate['lang']['rule'] = array('inList', $this->languages);
    }

    /**
     * Called after a sentence is saved.
     * 
     * @param bool $created true if a new line has been created.
     *                      false if a line has been updated.
     * 
     * @return void
     */
    public function afterSave($created)
    {
        if (isset($this->data['Sentence']['text'])) {
            // --- Logs for sentence ---
            $sentenceLang =  $this->data['Sentence']['lang'];
            $sentenceAction = 'update';
            $sentenceText = $this->data['Sentence']['text'];
            if ($created) {
                $sentenceAction = 'insert';
                $this->incrementStatistics($sentenceLang);
            }
            
            $this->Contribution->saveSentenceContribution(
                $this->id, $sentenceLang, $sentenceText, $sentenceAction
            );
        }
    }
    
    /**
     * Call after a deletion.
     *
     * @return void
     */
    public function afterDelete()
    {
        $action = 'delete';
        
        // --- Logs for sentence ---
        $sentenceLang = $this->data['Sentence']['lang'];
        $sentenceId = $this->data['Sentence']['id']; 
        $sentenceText = $this->data['Sentence']['text'];
        $this->Contribution->saveSentenceContribution(
            $sentenceId, $sentenceLang, $sentenceText, 'delete'
        );
        
        // --- Logs for links ---
        foreach ($this->data['Translation'] as $translation) {
            $this->Contribution->saveLinkContribution(
                $sentenceId, $translation['id'], $action
            );
            $this->Contribution->saveLinkContribution(
                $translation['id'], $sentenceId, $action
            );
        }
        
        // Decrement statistics
        $this->decrementStatistics($sentenceLang);
    }

    /**
     * Search one random chinese/japanese sentence containing $sinogram.
     *
     * @param string $sinogram Sinogram to search an example sentence containing it.
     *
     * @return int The id of this sentence.
     */
    public function searchOneExampleSentenceWithSinogram($sinogram)
    {
        $results = $this->query(
            "SELECT Sentence.id  FROM sentences AS Sentence 
                JOIN ( SELECT (RAND() *(SELECT MAX(id) FROM sentences)) AS id) AS r2
                WHERE Sentence.id >= r2.id
                    AND Sentence.lang IN ( 'jpn','cmn','wuu')
                    AND Sentence.text LIKE ('%$sinogram%')
                ORDER BY Sentence.id ASC LIMIT 1
            "
        );
       
        return $results[0]['Sentence']['id'] ;  
    }
    
    /**
     * Get the highest id for sentences.
     *
     * @return int The highest sentence id.
     */
    public function getMaxId()
    {
        $resultMax = $this->query('SELECT MAX(id) FROM sentences');
        return $resultMax[0][0]['MAX(id)'];
    }
    
    /**
     * Get the id of a random sentence, from a particular language if $lang is set.
     *
     * @param string $lang Restrict random id from the specified code lang.
     *
     * @return int A random id.
     */
    public function getRandomId($lang = null)
    {
        /*
        ** this query take constant time when lang=null
        ** and linear time when lang is set, so do not touch this request
        */
        if ( $lang == "und" ) {
            $lang = null ;
        }
        
        if ($lang == 'jpn' OR $lang == 'eng') {
        
            $min = ($lang == 'eng') ? 15700 : 74000;
            $max = ($lang == 'eng') ? 74000 : 127300;
            $randId =  rand($min, $max);
            $query = ( "SELECT Sentence.id FROM sentences AS Sentence
                WHERE Sentence.id 
                    IN (". ($randId - 1) .",". $randId . ",". ($randId +1) . ")
                AND Sentence.lang = '$lang'
                LIMIT 1 ;
                ;"
            );


        } elseif ( $lang != null AND $lang !='any' ) {
            
            $query= ("SELECT Sentence.id FROM sentences AS Sentence
                WHERE Sentence.lang = '$lang'
                ORDER BY RAND(".rand(). ") LIMIT  1"
                );

        } else {

            $query = 'SELECT Sentence.id  FROM sentences AS Sentence
                JOIN ( 
                    SELECT (
                        RAND('. rand() .') * (SELECT MAX(id) FROM sentences)
                        ) AS id
                    ) AS r2
                WHERE Sentence.id >= r2.id
                ORDER BY Sentence.id ASC LIMIT 1' ;
        }

        $results = $this->query($query);
        
        return $results[0]['Sentence']['id']; 
    }
    
    /**
     * Request for several random sentence id.
     *
     * @param string $lang             Langguage of the sentences we want.
     * @param int    $numberOfIdWanted Number of ids needed.
     *
     * @return array An array of ids.
     */
    public function getSeveralRandomIds($lang = null,  $numberOfIdWanted = 10)
    {
        $ids = array ();
        // exit if we don't have good params
        if (!is_numeric($numberOfIdWanted)) {
            return $ids ;
        }
        
        // if we don't a specific lang
        if ($lang == null OR $lang == "any" OR $lang == 'und') { 

            $query= "SELECT Sentence.id FROM sentences AS Sentence
                ORDER BY RAND(". rand() .") LIMIT  $numberOfIdWanted";
        
       
        } else { // restrict to a specific lang

            $query= "SELECT Sentence.id FROM sentences AS Sentence
            WHERE Sentence.lang = '$lang'
            ORDER BY RAND(".rand(). ") LIMIT  $numberOfIdWanted";
                                    
        }

        $results =  $this->query($query);

        // transform results in simple array of ids
        foreach ($results as $i=>$result) {
            $ids[$i] = $result['Sentence']['id'];
        }
         
        
        return $ids ; 

    }


    /**
     * Get all the informations needed to display a sentences in show section.
     *
     * @param int $id Id of the sentence asked.
     *
     * @return array Information about the sentence.
     */
    public function getSentenceWithId($id)
    {
        $result = $this->find(
            'first',
            array(
                    'conditions' => array ('Sentence.id' => $id),
                    'contain'  => array (
                        'Favorites_users' => array(
                            'fields' => array()
                        ),
                        'User'            => array(
                            'fields' => array('username')
                        ),
                        'SentencesList'   => array(
                            'fields' => array('id')
                        )
                    ),
                    'fields' => array(
                        'text',
                        'lang',
                        'user_id',
                        'hasaudio',
                    )
            )
        );
        if ($result == null) {
            return;
        }
        $result['Sentence'];
        
       
        $this->generateRomanization($result['Sentence']);
        $this->generateAlternateScript($result['Sentence']);
        $this->generateScript($result['Sentence']);
                
        return $result;
    }
    
    /**
     * Get sentences with specified ids as well as their translations.
     *
     * @param array  $ids              Ids of the sentences.
     * @param string $translationsLang Language of the translations.
     *
     * @return array
     */
    public function getSentencesWithIds($ids, $translationsLang = null)
    {
        $translationsConditions = array();
        if ($translationsLang != null) {
            $translationsConditions["Translation.lang"] = $translationsLang;
        }
        
        $sentences = $this->find(
            'all', 
            array(
                "conditions" => array("Sentence.id" => $ids),
                "contain" => array(
                    "Favorites_users" => array(
                        'fields' => array()
                    ),
                    "User" => array(
                        "fields" => array("username")
                    ),
                    "Translation" => array(
                        "fields" => array(
                            "id",
                            "lang",
                            "text",
                            "hasaudio"
                        ),
                        "conditions" => $translationsConditions
                    )
                )
            )
        );
        
        $results = array();
        foreach ($sentences as $sentence) {
            // Romanization for original sentence
            $this->generateRomanization($sentence['Sentence']);
            // simplified/traditional
            $this->generateAlternateScript($sentence['Sentence']);
            $this->generateScript($sentence['Sentence']);

            // Romanization for translations
            $translations = array();
            foreach ($sentence['Translation'] as $translation) {
                
                $this->generateRomanization($translation);
                
                $translations[] = $translation;
            }

            $sentence['Translation'] = $translations;

            
            // TODO Perhaps add romanization for indirect translations
            
            $results[] = $sentence;
        }
        
        return $results;
    }
    
    /**
     * Delete the sentence with the given id.
     *
     * @param int $id     Id of the sentence to be deleted.
     * @param int $userId Id of the user who deleted the sentence. Used for logs.
     *
     * @return void
     */
    public function delete($id, $userId)
    {
        //TODO  why ?
        $this->id = $id;
        $id = Sanitize::paranoid($id);  
        // for the logs
        $this->data = $this->find(
            'first',
            array(
                'condition' => array('Sentence.id' => $id)
               ,  'contain' => array ('Translation', 'User')
            )
        );

        $this->data['User']['id'] = $userId; 
        
        //$this->Sentence->del($id, true); 
        // TODO : Deleting with del does not delete the right entries in
        // sentences_translations.
        // But I didn't figure out how to solve that =_=;
        // So I'm just going to do something not pretty but whatever, I'm tired!!!
        $this->query('DELETE FROM sentences WHERE id='.$id);
        $this->query('DELETE FROM sentences_translations WHERE sentence_id='.$id);
        $this->query('DELETE FROM sentences_translations WHERE translation_id='.$id);
        
        // need to call afterDelete() manually for the logs
        $this->afterDelete();

    }

    /**
     * Count number of sentences in each language.
     *
     * @return array [lang => number of sentences in this lang]
     */
    public function getStatistics()
    {
        $query = "
            SELECT ifnull(lang, 'und') as lang,  numberOfSentences
                FROM langStats 
                ORDER BY numberOfSentences DESC ;
        ";

        $results = $this->query($query);

        // cakephp doesn't like use of AS 
        foreach ($results as $i=>$result) {
            $results[$i]['langStats']['lang'] = $result[0]['lang'];
        }
        return $results ;
    }


    /**
     * Add one in stats of a given language.
     *
     * @param string $lang Language to be incremented.
     * 
     * @return void
     */
    public function incrementStatistics($lang)
    {
        $lang = Sanitize::paranoid($lang);
        $endOfQuery = "lang = '$lang'";

        if ($lang == '' or $lang == null) {
            $endOfQuery = 'lang is null';
        }

        $query = "
            UPDATE langStats SET numberOfSentences = numberOfSentences + 1
                WHERE $endOfQuery ;
        ";
        $this->query($query);
    }

    /**
     * Decrement stats of a given language.
     *
     * @param string $lang Language to be decremented.
     *
     * @return void
     */
    public function decrementStatistics($lang)
    {

        $lang = Sanitize::paranoid($lang);
        $endOfQuery = "lang = '$lang'";

        if ($lang == '' or $lang == null) {
            $endOfQuery = 'lang is null';
        }

        $query = "
            UPDATE langStats SET numberOfSentences = numberOfSentences - 1
                WHERE $endOfQuery ;
        ";
        $this->query($query);
    }

    /**
     * Get number of sentences owned by a given user.
     *
     * @param int $userId Id of the user we want number of sentences of
     *
     * @return array TODO should return an int
     */
    public function numberOfSentencesOwnedBy($userId)
    {
        return $this->find(
            'count',
            array(
                'conditions' => array(
                    'Sentence.user_id' => $userId
                ),
                'contain' => array()
            )
        );
    }

    /**
     * Get translations of a given sentence and translations of translations.
     *
     * @param int    $id   Id of the sentence we want translations of.
     * @param string $lang To filter translations only in some language
     *
     * @return array Array of translations (direct and indirect).
     */
    public function getTranslationsOf($id,$lang = null)
    {
        
        $id = Sanitize::paranoid($id);
        $lang = Sanitize::paranoid($lang);
        if ( ! is_numeric($id) ) {
            return array();
        }

        $optionalWhere = "";
        if (!empty($lang) && $lang != "und") {
            $optionalWhere = "AND p2.lang = '$lang'";    
        }

        // DA ultimate Query 

        $direcTranslationsQuery = "
            SELECT
              p2.text AS translation_text,
              p2.hasaudio AS hasaudio,
              p2.id   AS translation_id,
              p2.lang AS translation_lang,
              p2.user_id AS translation_user_id,
              'Translation' as distance
            FROM sentences_translations AS t 
              LEFT  JOIN sentences AS p2 ON t.translation_id = p2.id
            WHERE 
                t.sentence_id IN ($id) $optionalWhere
        ";

        // query use to retrieve sentence which are already direct
        // translations
        $subQuery = "
            SELECT sentences_translations.translation_id
            FROM sentences_translations
            WHERE sentences_translations.sentence_id IN ( $id ) 
        ";

        $indirectTranslationQuery = "
         SELECT 
              p2.text AS translation_text,
              p2.hasaudio AS hasaudio,
              p2.id   AS translation_id,
              p2.lang AS translation_lang,
              p2.user_id AS translation_user_id,
              'IndirectTranslation'  as distance
            FROM sentences_translations AS t
                LEFT JOIN sentences_translations AS t2
                    ON t2.sentence_id = t.translation_id
                LEFT JOIN sentences AS p2
                    ON t2.translation_id = p2.id
            WHERE 
                t.sentence_id != p2.id
                AND p2.id NOT IN ( $subQuery )
                AND t.sentence_id IN ( $id )
                $optionalWhere
        "; 

        $query = "
            $direcTranslationsQuery 
            UNION
            $indirectTranslationQuery
        ";

        $results = $this->query($query);

        $orderedResults = array(
            "Translation" => array(), 
            "IndirectTranslation" => array()
        );
        foreach ($results as $result) {
            $result = $result[0] ;
            if ($result['translation_id']) { // need to check this because
                // for sentences without translations it would otherwise
                // return an empty translation array.

                $translation = array(
                    'id' => $result['translation_id'],
                    'text' => $result['translation_text'],
                    'user_id' => $result['translation_user_id'],
                    'lang' => $result['translation_lang'],
                    'hasaudio' => $result['hasaudio'],
                );

                $this->generateRomanization($translation);

                array_push(
                    $orderedResults[$result['distance']],
                    $translation 
                );     
            }
        }

        return $orderedResults;
    }

    
    
    /**
     * Count number of sentences with unknown language.
     *
     * @param int $userId id of the user we want the unknown language sentences.
     * 
     * @return int Number of sentences.
     */
    public function numberOfUnknownLanguageForUser($userId)
    {
        // Need to do custom query because there is no way to say 
        //  `Sentence`.`lang` = '' OR `Sentence`.`lang` IS NULL
        // with CakePHP, it seems.

        $userId = Sanitize::paranoid($userId);
        $count = $this->query(
            "
            SELECT COUNT(*) AS `count` 
            FROM `sentences` AS `Sentence` 
            WHERE `Sentence`.`user_id` = $userId 
              AND `Sentence`.`lang` IS NULL;
            "
        );
        return $count[0][0]['count'];
    }
    
    /**
     * Retrieve sentences with unknown language.
     *
     * @param int $userId The user id.
     *
     * @return array Array of the sentences with uknown languages of the user.
     */
    public function sentencesWithUnknownLanguageForUser($userId)
    {
        // Need to do custom query because there is no way to say 
        //  `Sentence`.`lang` = '' OR `Sentence`.`lang` IS NULL
        // with CakePHP, it seems.
        // TODO it's possible google => "complex query cakephp"
        $userId = Sanitize::paranoid($userId);
        $sentences = $this->query(
            "
            SELECT * FROM `sentences` AS `Sentence` 
            WHERE `Sentence`.`user_id` = $userId 
              AND `Sentence`.`lang` IS NULL
        "
        );
        return $sentences;
        
    }

    /**
     * get romanization for several sentences
     * it wrap the getRomanization method
     *
     * @param array &$sentenceArray an array "a la cakephp", the array should
     *                              have a 'text' and 'lang' field, and will add
     *                              a 'romanization' field if the language need it
     * 
     * @return void
     */

    public function generateRomanization(&$sentenceArray)
    {
   
        // TODO : need to replace it by something more general
        // like $romanisableArray, that way we will not need to
        // change several time the same things
        if (in_array($sentenceArray['lang'], array('wuu','cmn','jpn'))) {
            $sentenceArray['romanization'] = $this->getRomanization(
                $sentenceArray['text'],
                $sentenceArray['lang']
            );
            
            // getRomanization (just above) returns hiragana for the Japanese.
            // Here we also get the romaji.
            if ($sentenceArray['lang'] == 'jpn') {
                $sentenceArray['romaji'] = $this->getJapaneseRomanization2(
                    $sentenceArray['text'],
                    Sentence::$romanji['romanji']
                );
            }
            
        }

    }

    /**
     * get romanization or equivalent of a sentence
     *
     * @param string $text text to be romanized
     * @param string $lang lang to know which method apply
     *
     * @return string romanisation of the text
     */
    public function getRomanization($text,$lang)
    {

        $romanization = '';

        if ($lang == "wuu") {
            $romanization = $this->getShanghaineseRomanization($text);
        } elseif ($lang == "jpn") {
            $romanization = $this->getJapaneseRomanization2(
                $text, Sentence::$romanji['mix']
            ); 
                                                // 1 is for "hiragana"
        } elseif ($lang == "cmn") {
            // important to add this line before escaping a
            // utf8 string, workaround for an apache/php bug  
            setlocale(LC_CTYPE, "fr_FR.UTF-8");
            $text = escapeshellarg($text); 
            
            $romanization =  exec("adso -i $text -y");
            /*
            $curl = curl_init();
            curl_setopt (
                $curl,
                CURLOPT_URL,
                "http://adsotrans.com/popup/pinyin.php?text=".$text
            );
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            $result = curl_exec ($curl);
            $pinyin = substr($result, 14);
            $romanization = substr($pinyin, 0, -44);
            */

        }
        return $romanization;
    }

    /**
     * wrap the detectScript method,
     * it will add a 'script' field if the sentence need it
     *
     * @param array &$sentenceArray Sentence array a la cakephp with a 'lang'
     *                              and 'text' field
     *
     * @return void
     */

    public function generateScript(&$sentenceArray)
    {
        $sentenceArray['script'] = '';

        if ($sentenceArray['lang'] === 'cmn') {
            $sentenceArray['script'] = $this->detectScript(
                $sentenceArray['text']
            );
        }

    }

    /**
     * detect script of a chinese sentence, will return either
     * 'simplified' 'traditional' or ''
     *
     * @param string $text Text to detect script
     *
     * @return string
     */
    public function detectScript($text)
    {

        // important to add this line before escaping a
        // utf8 string, workaround for an apache/php bug  
        setlocale(LC_CTYPE, "fr_FR.UTF-8");
        $text = escapeshellarg($text); 
        $script =  exec("adso --rscript -i $text");
        
        if ($script == 'simplified' || $script == 'traditional') {
            return $script;
        } else {
            return '';
        }
    }

    /**
     * wrap the getOtherScriptVersion method
     * it will add a 'alternateScript' field if the sentence
     * language need it
     *
     * @param array &$sentenceArray Sentence array a la cakephp with a 'lang'
     *                              and 'text' field
     *
     * @return void
     */

    public function generateAlternateScript(&$sentenceArray)
    {

        if ($sentenceArray['lang'] === 'cmn') {
            $sentenceArray['alternateScript'] = $this->getOtherScriptVersion(
                $sentenceArray['text']
            );
        }
    }

    /**
     * convert a chinese text from traditional to simplified
     * and vice versa
     *
     * @param string $chineseText chinese text to switch
     *
     * @return string
     */

    public function getOtherScriptVersion($chineseText)
    {

        // important to add this line before escaping a
        // utf8 string, workaround for an apache/php bug  
        setlocale(LC_CTYPE, "fr_FR.UTF-8");
        $chineseText = escapeshellarg($chineseText);
        $convertedText =  exec("adso --switch-script -cn -i $chineseText");
        return $convertedText;
    }
    
    
    /**
     * get "romanisation" of the $text sentences in japanese
     * into romaji or furigana depending of $type value
     *
     * @param string $text text to romanized
     * @param string $type type of romanization to apply
     *
     * @return string romanized japanese text
     */
    public function getJapaneseRomanization2($text, $type)
    {
        //TODO type = 1 ???  can you replace it by much more
        // evident constant

        // important to add this line before escaping a
        // utf8 string, workaround for an apache/php bug  
        setlocale(LC_CTYPE, "fr_FR.UTF-8");
        $text = escapeshellarg($text);
        
        $text = nl2br($text);
        
        $Owakati = exec(
            "export LC_ALL=fr_FR.UTF-8 ; ".
            "echo $text | ".
            "mecab -Owakati"
        );
        
        $Oyomi = exec(
            "export LC_ALL=fr_FR.UTF-8 ; ".
            "echo $text | ".
            "mecab -Owakati | ".
            "mecab -Oyomi"
        );
        
        if (empty($Oyomi)) {
            return '';
        }
        
        $katakana = array(
        "ァ","ア","ィ","イ","ゥ","ウ","ェ","エ","ォ","オ",
        "カ","ガ","キ","ギ","ク","グ","ケ","ゲ","コ","ゴ",
        "サ","ザ","シ","ジ","ス","ズ","セ","ゼ","ソ","ゾ",
        "タ","ダ","チ","ヂ","ッ","ツ","ヅ","テ","デ","ト",
        "ド","ナ","ニ","ヌ","ネ","ノ","ハ","バ","パ","ヒ",
        "ビ","ピ","フ","ブ","プ","ヘ","ベ","ペ","ホ","ボ",
        "ポ","マ","ミ","ム","メ","モ","ャ","ヤ","ュ","ユ",
        "ョ","ヨ","ラ","リ","ル","レ","ロ","ヮ","ワ","ヲ",
        "ン","ヴ","ヵ","ヶ",
        "。","、","？","！","「","」"
        );
        
        $hiragana = array(
        "ぁ","あ","ぃ","い","ぅ","う","ぇ","え","ぉ","お",
        "か","が","き","ぎ","く","ぐ","け","げ","こ","ご",
        "さ","ざ","し","じ","す","ず","せ","ぜ","そ","ぞ",
        "た","だ","ち","ぢ","っ","つ","づ","て","で","と",
        "ど","な","に","ぬ","ね","の","は","ば","ぱ","ひ",
        "び","ぴ","ふ","ぶ","ぷ","へ","べ","ぺ","ほ","ぼ",
        "ぽ","ま","み","む","め","も","ゃ","や","ゅ","ゆ",
        "ょ","よ","ら","り","る","れ","ろ","ゎ","わ","を",
        "ん","ゔ","ゕ","ゖ",
        "。","、","？","！","「","」"
        );
        
        $kata = array(
        "キャ","キュ","キョ","ギャ","ギュ","ギョ","シャ",
        "シュ","ショ","ジャ","ジュ","ジョ","チャ","チュ",
        "チョ","ニャ","ニュ","ニョ","ヒャ","ヒュ","ヒョ",
        "ビャ","ビュ","ビョ","ピャ","ピュ","ピョ","ミャ",
        "ミュ","ミョ","リャ","リュ","リョ",
        
        "ウィ","ウェ","ウォ","ヴァ","ヴィ","ヴ","ヴェ",
        "ヴォ","シェ","ジェ","チェ","ツァ","ツィ","ツェ",
        "ツォ","デュ","ティ","トゥ","テュ","ディ","ドゥ",
        "ファ","フィ","フェ","フォ","フュ",
        
        "ァ","ア","ィ","イ","ゥ","ウ","ェ","エ","ォ","オ",
        "カ","ガ","キ","ギ","ク","グ","ケ","ゲ","コ","ゴ",
        "サ","ザ","シ","ジ","ス","ズ","セ","ゼ","ソ","ゾ",
        "タ","ダ","チ","ヂ","ッ","ツ","ヅ","テ","デ","ト",
        "ド","ナ","ニ","ヌ","ネ","ノ","ハ","バ","パ","ヒ",
        "ビ","ピ","フ","ブ","プ","ヘ","ベ","ペ","ホ","ボ",
        "ポ","マ","ミ","ム","メ","モ","ャ","ヤ","ュ","ユ",
        "ョ","ヨ","ラ","リ","ル","レ","ロ","ヮ","ワ","ヲ",
        "ン","ヴ","ヵ","ヶ",
        
        "。","、","？","！","「","」"
        );
        
        $romanji = array(
        "kya","kyu","kyo","gya","gyu","gyo","sha","shu","sho",
        "ja","ju","jo","cha","chu","cho","nya","nyu","nyo",
        "hya","hyu","hyo","bya","byu","byo","pya","pyu","pyo",
        "mya","myu","myo","rya","ryu","ryo",
        
        "wi","we","wo","va","vi","vu","vr","vo","she","je",
        "che","tsa","tsi","tse","tso","dyu","ti","tu","tyu","di",
        "du","fa","fi","fe","fo","fyu",
        
        "a","a","i","i","u","u","e","e","o","o",
        "ka","ga","ki","gi","ku","gu","ke","ge","ko","go",
        "sa","za","shi","ji","su","zu","se","ze","so","zo",
        "ta","da","chi","ji","","tsu","zu","te","de","to",
        "do","na","ni","nu","ne","no","ha","ba","pa","hi",
        "bi","pi","fu","bu","pu","he","be","pe","ho","bo",
        "po","ma","mi","mu","me","mo","ya","ya","yu","yu",
        "yo","yo","ra","ri","ru","re","ro","wa","wa","wo",
        "n","","","",
        
        ".",", ","?","!","\"","\""
        );
        
        $Owakati = explode(' ', $Owakati);
        $Oyomi = explode(' ', $Oyomi);
        $romanization = array();
        
        if ($type == Sentence::$romanji['furigana']) {
            foreach ($Owakati as $i=>$word) {
                preg_match_all('/./u', $word, $char);
                if (in_array($char[0][0], $katakana)) {
                    array_push($romanization, $word);
                } else {
                    array_push(
                        $romanization,
                        str_replace($katakana, $hiragana, $Oyomi[$i])
                    );
                }
            }
        } elseif ($type == Sentence::$romanji['mix']) {
            foreach ($Owakati as $i=>$word) {
                preg_match_all('/./u', $word, $chars);
                $char = $chars[0][0];
                if (in_array($char, $katakana) || in_array($char, $hiragana)) {
                    array_push(
                        $romanization,
                        $word
                    );
                } else {
                    $translatedWord = str_replace($katakana, $hiragana, $Oyomi[$i]);
                    array_push(
                        $romanization,
                        $word."[$translatedWord]"
                    );
                }
            }
        } elseif ($type == Sentence::$romanji['romanji']) {
            foreach ($Owakati as $i=>$word) {
                array_push(
                    $romanization,
                    str_replace($kata, $romanji, $Oyomi[$i])
                );
            }
        } else {
            $romanization = array();
        }
        
        return implode(" ", $romanization);
    }

    /**
     * Get sentences to display in map.
     *
     * @param int $start Id from where to start.
     * @param int $end   Id from where to end.
     *
     * @return array
     */
    public function getSentencesForMap($start, $end)
    {
        return $this->find(
            'all',
            array(
                'fields' => array('Sentence.id', 'Sentence.lang'),
                'order' => 'Sentence.id',
                'conditions' => array(
                    'Sentence.id >' => $start, 'Sentence.id <=' => $end
                ),
                'contain' => array()
            )
        );
    }

    /**
     * Return previous and following sentence id
     *
     * @param int    $sourceId The sentence id to take as starting point
     * @param string $lang     Will return the next and following sentence id
     *                         in this language
     *
     * @return array
     */
    public function getNeighborsSentenceIds($sourceId, $lang = null)
    {
        $conditions = array();
        if (!empty($lang) && $lang != 'und') {
            $conditions["Sentence.lang"] = $lang;
        }

        $this->id = $sourceId;
        $neighborsCake = $this->find(
            'neighbors',
            array(
                'fields' => array("id"),
                'conditions' => $conditions,
                'contain'=> array()   
            )
        );
        
        $neighbors = array(
            "prev" => $neighborsCake['prev']['Sentence']['id'],
            "next" => $neighborsCake['next']['Sentence']['id'],
        );
        
        return $neighbors; 
    }

    /**
     * Return email of owner of the sentence.
     *
     * @param int $sentenceId Id of the sentence.
     *
     * @return array
     */
    public function getEmailFromSentence($sentenceId)
    {
        $sentence = $this->find(
            'first',
            array(
                'fields' => array(),
                'conditions' => array('Sentence.id' => $sentenceId),
                'contain' => array(
                    'User' => array(
                        'fields' => array('email'),
                        'conditions' => array('send_notifications' => 1)
                    )
                )
            )
        );
        
        return $sentence['User']['email'];
    }

    /**
     * Return IPA of a shanghainese text
     *
     * @param string $shanghaineseText text in shanghainese
     *
     * @return string
     */

    public function getShanghaineseRomanization($shanghaineseText)
    {
        $ipaFile = fopen(
            "http://static.tatoeba.org/data/shanghainese2IPA2.txt",
            "r"
        );

        $ipaArray = array();
        $sinogramsArray = array();

        // the file is tab separated value
        // we create two array one with characters, the other
        // with the IPA
        while ($line = fgets($ipaFile)) {
            $arrayLine = explode("\t", $line);
            // there's some blank line in this file so mustn't
            // handle them
            if (count($arrayLine) > 1) {
                array_push($ipaArray, str_replace("\n", ".", $arrayLine[1]));
                array_push($sinogramsArray, $arrayLine[0]);
            }
        }

        $ipaSentence = str_replace($sinogramsArray, $ipaArray, $shanghaineseText);
        return $ipaSentence;

    }
    
    /**
     * Return all tags on a given sentence
     *
     * @param int $sentenceId The sentence which we want the tags
     *
     * @return array
     */
    public function getAllTagsOnSentence($sentenceId)
    {
        return $this->TagsSentences->getAllTagsOnSentence($sentenceId);
    }
    
    /**
     * Add translation to sentence with given id. Adding a translation means adding
     * a new sentence, and two links.
     *
     * @param int    $sentenceId
     * @param string $translationText
     * @param string $translationLang
     * @TODO finish the doc plz
     *
     * @return boolean 
     */
    public function saveTranslation($sentenceId, $translationText, $translationLang)
    {
        // saving translation
        $sentenceSaved = $this->saveNewSentence(
            $translationText,
            $translationLang,
            CurrentUser::get('id')
        );
         
        // saving links
        if ($sentenceSaved) {
            $this->Link->add($sentenceId, $this->id);
        }
        
        return $sentenceSaved; // The most important is that the sentence is saved.
                               // Never mind for the links.
    }


    /**
     * Add a new sentence in the database
     * 
     * @param string $text   The text of the sentence
     * @param string $lang   The lang of the sentence
     * @param int    $userId The id of the user who added this sentence
     *
     * @return bool
     */
    public function saveNewSentence($text, $lang, $userId)
    {
        $data['Sentence']['id'] = null;
        $data['Sentence']['text'] = $text;
        $data['Sentence']['lang'] = $lang;
        $data['Sentence']['user_id'] = $userId;
        $sentenceSaved = $this->save($data);
    
        return $sentenceSaved;
    }

    /**
     * Add a new sentence and a translation in the database
     * 
     * @param string $sentenceText    The text of the sentence
     * @param string $sentenceLang    The lang of the sentence
     * @param string $translationText The text of the translation
     * @param string $translationLang The lang of the translation
     * @param int    $userId          The id of the user who added them
     *
     * @return bool
     */
    
    public function saveNewSentenceWithTranslation(
        $sentenceText,
        $sentenceLang,
        $translationText,
        $translationLang,
        $userId
    ) {
        // saving sentence
        $sentenceSaved = $this->saveNewSentence(
            $sentenceText,
            $sentenceLang,
            $userId
        );
        $sentenceId = $this->id;
        // saving translation
        $translationSaved = $this->saveNewSentence(
            $translationText,
            $translationLang,
            $userId
        );
         
        $translationId = $this->id;
        // saving links
        if ($sentenceSaved && $translationSaved) {
            $this->Link->add($sentenceId, $translationId);
        }
 

    }

    /**
     * wrapping function
     *
     * @param int $id Sentence id.
     *
     * @return array
     */
    public function getContributionsRelatedToSentence($id)
    {
        return $this->Contribution->getContributionsRelatedToSentence($id);

    }

    /**
     * wrapping function
     *
     * @param int $id Sentence id.
     *
     * @return array
     */
    public function getCommentsForSentence($id)
    {
        return $this->SentenceComment->getCommentsForSentence($id);
    }
    
    
    /**
     * Set owner for a sentence.
     *
     * @param int $sentenceId Id of the sentence.
     * @param int $userId     Id of the user.
     * 
     * @return bool
     */
    public function setOwner($sentenceId, $userId)
    {
        $this->id = $sentenceId;
        $currentOwner = $this->getOwnerIdOfSentence($sentenceId);
        if (empty($currentOwner)) {
            $this->saveField('user_id', $userId);
            return true;
        }
        return false;
    }
    
    
    /**
     * Unset owner for a sentence.
     *
     * @param int $sentenceId Id of the sentence.
     * @param int $userId     Id of the user.
     * 
     * @return bool
     */
    public function unsetOwner($sentenceId, $userId)
    {
        $this->id = $sentenceId;
        $currentOwner = $this->getOwnerIdOfSentence($sentenceId);
        if ($currentOwner == $userId) {
            $this->saveField('user_id', null);
            return true;
        }
        return false;
    }
    
    
    /**
     * Return sentence owner's id.
     *
     * @param int $sentenceId Id of the sentence.
     * 
     * @return int
     */
    public function getOwnerIdOfSentence($sentenceId)
    {
        $sentence = $this->find(
            'first',
            array(
                'conditions' => array(
                    'id' => $sentenceId
                ),
                'fields' => array('user_id'),
                'contain' => array()
            )
        );
        
        return $sentence['Sentence']['user_id'];
    }
    
    
    /**
     * Change language of a sentence.
     *
     * @param int $sentenceId Id of the sentence.
     * @param int $prevLang   Previous language. Used for decrementing.
     * @param int $newLang    New Language.
     *
     * @return string
     */
    public function changeLanguage($sentenceId, $prevLang, $newLang)
    {
        $ownerId = $this->getOwnerIdOfSentence($sentenceId);
        $currentUserId = CurrentUser::get('id');
        
        if ($ownerId == $currentUserId || CurrentUser::isModerator()) {
            $this->id = $sentenceId;
            $this->saveField('lang', $newLang);
            
            $this->Contribution->updateLanguage($id, $newLang);        
            $this->incrementStatistics($newLang);
            $this->decrementStatistics($prevLang);
            
            return $newLang;
        }
        
        return $prevLang;
    }
}
?>
