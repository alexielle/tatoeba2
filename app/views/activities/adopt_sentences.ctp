<?php
/**
 * Tatoeba Project, free collaborative creation of multilingual corpuses project
 * Copyright (C) 2011  HO Ngoc Phuong Trang <tranglich@gmail.com>
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

if (empty($lang)){
    $title = __('Orphan sentences', true);
} else {
    $title = format(
        __('Orphan sentences in {language}', true), 
        array('language' => $languages->codeToNameToFormat($lang))
    );
}
$this->set('title_for_layout', $pages->formatTitle($title));
?>
<div id="annexe_content">
    
    <?php $commonModules->createFilterByLangMod(); ?>
    
    <div class="module">
        <h2><?php __('About adoption'); ?></h2>
        <p>
        <?php
        __(
            'Adopting is a way to vote "this sentence is correct". It is also an '.
            'occasion to check the sentence and correct it if there is a mistake.'
        );
        ?>
        </p>
        
        <p>
        <?php
        echo format(
            __(
                'So if you want to help us check and correct sentences, then adopt '.
                '({adoptButton}) any "orphan" sentence you see in your <strong>native '.
                'language</strong>, and correct it if necessary. '.
                'Read <a href="{url}">this</a> for further explanation.', true
            ),
            array(
                'adoptButton' => $html->image('unadopted.svg', array('height' => 16)),
                'url' => 'http://blog.tatoeba.org/2010/04/reliability-of-sentences-how-will-we.html'
            )
        )
        ?>
        </p>
    </div>
    
    <div class="module">
        <h2><?php __('Tips'); ?></h2>
        <p>
        <?php 
        __(
            'If you see another username appear after adopting a sentence, it '.
            'means that someone else adopted the sentence very shortly before '.
            'you did. In such cases, you can try adopting sentences that are '.
            'several pages away from your current page to reduce the chances of '.
            'that happening again.'
        );
        ?>
        </p>
    </div>
</div>

<div id="main_content">
    <div class="module">
    <?php 
    echo $this->Pages->formatTitleWithResultCount($paginator, $title);

    $pagination->display(array($lang));
    
    foreach ($results as $sentence) {
        $sentences->displaySentencesGroup(
            $sentence['Sentence'], 
            array(), 
            null
        );
    }
        
    $pagination->display(array($lang));
    ?>
    </div>
</div>
