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

$uiLanguages = Configure::read('UI.languages');
$currentLang = $this->params['lang'];
$currentUrl = $this->params['url']['url'];

foreach ($uiLanguages as $langs) {
    
    if ($langs[1] != null) {
        $newUrl = $currentUrl;
        $pos = strpos($currentUrl, $currentLang);
        if ($pos !== false) {
            $newUrl = substr_replace(
                $currentUrl, 
                $langs[0], 
                $pos, 
                strlen($currentLang)
            );
        }
        if ($newUrl[0] == '/') {
            $newUrl = substr($newUrl, 1);
        }
        $alternateURL = "http://".TATOEBA_DOMAIN.'/'.$newUrl;
        ?>
        <link rel="alternate" 
              hreflang="<?php echo $langs[1]; ?>" 
              href="<?php echo $alternateURL; ?>">
        <?php   
    }

}
?>