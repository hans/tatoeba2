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

/**
 * Model for sentence comments.
 *
 * @category SentenceComments
 * @package  Models
 * @author   HO Ngoc Phuong Trang <tranglich@gmail.com>
 * @license  Affero General Public License
 * @link     http://tatoeba.org
 */
class SentenceComment extends AppModel
{
    public $actsAs = array('Containable');
    public $belongsTo = array('Sentence', 'User');

    /**
     * Get number of sentences owned by a user.
     *
     * @param int $userId Id of the user.
     *
     * @return array
     */
    public function numberOfCommentsOwnedBy($userId)
    {
        return $this->find(
            'count',
            array(
                'conditions' => array( 'SentenceComment.user_id' => $userId),
                'contain' => array()
             )
        );

    }

    /**
     * get number of comments posted on all the sentences owned by
     * a specified user
     *
     * @param int $userId Id of the user.
     *
     * @return int
     */

    public function numberOfCommentsOnSentencesOf($userId)
    {
        return $this->find(
            'count',
            array(
                'conditions' => array('Sentence.user_id' => $userId),
                'contain' => array(
                    'Sentence' => array(
                    )
                )
            )
        );

    }
    
    /**
     * Return comments for given sentence.
     *
     * @param int $sentenceId Id of the sentence.
     *
     * @return array
     */
    public function getCommentsForSentence($sentenceId)
    {
        return $this->find(
            'all', 
            array(
                'conditions' => array('SentenceComment.sentence_id' => $sentenceId),
                'order' => 'SentenceComment.created',
                'contain' => array(
                    'User' => array(
                        'fields' => array(
                            'id',
                            'username',
                            'image'
                        )
                    )
                )
            )
        );
    }
    
    /**
     * Return latest comments.
     *
     * @param int $limit Number of comments to be retrieved.
     *
     * @return array
     */
    public function getLatestComments($limit)
    {
        return $this->find(
            'all',
            array(
                'order' => 'SentenceComment.created DESC',
                'limit' => $limit,
                'conditions' => array('hidden' => 0),
                'contain' => array(
                    'User' => array(
                        'fields' => array(
                            'id',
                            'username',
                            'image'
                        )
                    ),
                    'Sentence' => array(
                        'User' => array('username'),
                        'fields' => array('id', 'text', 'lang')
                    )
                )
            )
        );
    }
    
    /**
     * Return emails of users who posted a comment on the sentence
     * and who didn't disable notification.
     *
     * @param int $sentenceId Id of the sentence.
     *
     * @return array
     */
    public function getEmailsFromComments($sentenceId)
    {
        $emails = array();
        $comments = $this->find(
            'all',
            array(
                'fields' => array(),
                'conditions' => array('SentenceComment.sentence_id' => $sentenceId),
                'contain' => array (
                    'User' => array(
                        'fields' => array('email'),
                        'conditions' => array('send_notifications' => 1)
                    )    
                )
            )
        );
        foreach ($comments as $comment) {
            $emails[] = $comment['User']['email'];
        }
        $emails = array_unique($emails);
        return $emails;
    }

    /**
     * Retrieve the id of one comment's owner
     *
     * @param int $commentId Id of the comment
     *
     * @return int The owner id
     */

    public function getOwnerIdOfComment($commentId)
    {
        $result = $this->find(
            "first",
            array(
                'fields' => array('SentenceComment.user_id'),
                'conditions' => array('SentenceComment.id' => $commentId),
                'contain' => array()
            )
        );

        return $result['SentenceComment']['user_id'];
    }
    
    
    /**
     * Overridden paginateCount method, for optimization purpose.
     *
     * @param array $conditions
     * @param int   $recursive
     * @param array $extra
     *
     * @return int
     */
    function paginateCount($conditions = null, $recursive = 0, $extra = array()) {
        $contain = array();
        foreach ($conditions as $key => $value) {
            if (strpos($key, "SentenceComment") === false) {
                $tmp = explode('.', $key);
                $model = $tmp[0];
                $contain[] = $model;
            }
        }
        
        $result = $this->find(
            'count',
            array(
                'contain' => $contain,
                'conditions' => $conditions
            )
        );
        
        return $result;
    }
}
?>
