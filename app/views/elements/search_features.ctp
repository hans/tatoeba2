<?php
/**
 * Tatoeba Project, free collaborative creation of multilingual corpuses project
 * Copyright (C) 2010  HO Ngoc Phuong Trang <tranglich@gmail.com>
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
?>
<div class="module">
    <h2><?php __('Search features'); ?></h2>
    <p>
    <?php
    echo sprintf(
        __('The search is powered by <a href="%s">Sphinx</a>.', true),
        'http://www.sphinxsearch.com/'
    );
    ?>
    </p>

    <p>
    <?php
    echo sprintf(
        __(
            'You can learn about advanced search features <a href="%s">here</a>.', 
            true
        ),
        'http://sphinxsearch.com/docs/current.html#boolean-syntax'
    );
    ?>
    </p>
</div>


<div class="module">
    <h2>
    <?php __('About recently added sentences'); ?>
    </h2>
    
    <p>
    <?php
    __(
        'You may not be able to find sentences that have been added recently '.
        'because they have not been indexed yet. Indexation of sentences is not '.
        '(yet) executed on-the-fly, only every week.'
    );
    ?>
    </p>
</div>