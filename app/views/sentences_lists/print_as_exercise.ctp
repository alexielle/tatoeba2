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

$this->pageTitle = __('Tatoeba list :',true) . ' ' . $list['SentencesList']['name'];
?>

<h1><?php echo $list['SentencesList']['name']; ?></h1>

<ol id="listAsExercise">
<?php
foreach($list['Sentence'] as $sentence){
	echo '<li>';
		echo '<div class="sentence">'.$sentence['text'].'</div>';
		if($displayRomanization AND in_array($sentence['lang'], array('jpn', 'cmn'))){
			echo '<div class="romanization">';
			$kakasi->convert($sentence['text'], 'romaji');
			echo '</div>';
		}
	echo '</li>';
}
?>
</ol>