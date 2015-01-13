<?php
/**
 * Tatoeba Project, free collaborative creation of multilingual corpuses project
 * Copyright (C) 2009-2011  HO Ngoc Phuong Trang <tranglich@gmail.com>
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
 * Controller for links between sentences. Links specify which sentences are
 * translations of which other sentences.
 *
 * @category Links
 * @package  Controllers
 * @author   HO Ngoc Phuong Trang <tranglich@gmail.com>
 * @license  Affero General Public License
 * @link     http://tatoeba.org
 */
class LinksController extends AppController
{

    private function _renderTranslationsOf($sentenceId, $message)
    {
        $this->loadModel('Sentence');
        // TODO: Fix issue #510 permanently by revisiting the next three lines.
        // Currently, linking a sentence causes translations in all languages
        // to be displayed. But commenting out the next sentence and uncommenting
        // the next two causes the "show_translations_into_lang" cookie to
        // determine the translations in which they're displayed, even when this
        // is not appropriate. Probably, this function needs another parameter
        // and the caller needs to set this after deciding whether or not to check the
        // cookie.
        $alltranslations = $this->Sentence->getTranslationsOf($sentenceId);
        //  $showTranslationsInto = $this->Session->read('show_translations_into_lang');
        //$alltranslations = $this->Sentence->getTranslationsOf($sentenceId, $showTranslationsInto);
        $translations = $alltranslations['Translation'];
        $indirectTranslations = $alltranslations['IndirectTranslation'];

        $this->set('sentenceId', $sentenceId);
        $this->set('translations', $translations);
        $this->set('indirectTranslations', $indirectTranslations);
        $this->set('message', $message);
        $this->render('/sentences/translations_group');
    }

    /**
     * Link sentences.
     *
     * @param int $sentenceId    Id of the sentence.
     * @param int $translationId Id of the translation to link to.
     *
     * @return void
     */
    public function add($sentenceId, $translationId) 
    {
        $sentenceId = Sanitize::paranoid($sentenceId);
        $translationId = Sanitize::paranoid($translationId);
        
        $saved = $this->Link->add($sentenceId, $translationId);
        
        if ($saved) {
            $flashMessage = format(
                __(
                    'Sentences #{firstNumber} and #{secondNumber} are now '.
                    'direct translations of each other.',
                    true
                ),
                array('firstNumber' => $sentenceId, 'secondNumber' => $translationId)
            );
        } else {
            $flashMessage = __(
                'An error occurred while saving. '.
                'Please try again or contact us to report this.',
                true
            );
        }

        $this->set('saved', $saved);

        if ($this->RequestHandler->isAjax()) {
            if (isset($this->params['form']['returnTranslations'])
                && (bool)$this->params['form']['returnTranslations'])
                $this->_renderTranslationsOf($sentenceId, $flashMessage);
        } else {
            $this->flash($flashMessage, '/sentences/show/'.$sentenceId);
        }
    }


    /**
     * Unlink sentences.
     *
     * @param int $sentenceId    Id of the sentence.
     * @param int $translationId Id of the translation to unlink.
     *
     * @return void
     */
    public function delete($sentenceId, $translationId)
    {
        $sentenceId = Sanitize::paranoid($sentenceId);
        $translationId = Sanitize::paranoid($translationId);

        $saved = $this->Link->deletePair($sentenceId, $translationId);

        if ($saved) {
            $flashMessage = format(
                __(
                    'Sentences #{firstNumber} and #{secondNumber} are no longer '.
                    'direct translations of each other.',
                    true
                ),
                array('firstNumber' => $sentenceId, 'secondNumber' => $translationId)
            );
        } else {
            $flashMessage = __(
                'An error occurred while unlinking. '.
                'Please try again or contact us to report this.',
                true
            );
        }

        $this->set('saved', $saved);

        if ($this->RequestHandler->isAjax()) {
            if (isset($this->params['form']['returnTranslations'])
                && (bool)$this->params['form']['returnTranslations'])
                $this->_renderTranslationsOf($sentenceId, $flashMessage);
        } else {
            $this->flash($flashMessage, '/sentences/show/'.$sentenceId);
        }
    }

}
?>
