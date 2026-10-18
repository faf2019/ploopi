<?php
/*
    Copyright (c) 2007-2026 Ovensia
    Contributors hold Copyright (c) to their code submissions.

    This file is part of Ploopi.

    Ploopi is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    Ploopi is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Ploopi.  If not, see <https://www.gnu.org/licenses/>.
*/

namespace ploopi;

use XMLWriter;

/**
 * Flux de syndication RSS 2.0 ou Atom 1.0 (webedit, GED).
 *
 * Remplace mibe/feedwriter, non maintenu depuis 2023 (obsolète sous PHP 8.4 et
 * 8.5) et sous GPL-3.0. Les identifiants Atom sont ceux que FeedWriter
 * produisait (urn:uuid tiré du md5 du titre du flux, du lien de l'article) : un
 * lecteur de flux ne revoit pas comme neufs les articles déjà lus.
 *
 * Les valeurs se passent brutes, le flux les échappe ; le résumé d'un article
 * est du HTML. Les caractères interdits en XML et l'UTF-8 invalide sont retirés.
 *
 * <code>
 * $objFeed = new ploopi\feed(ploopi\feed::RSS);
 * $objFeed->setTitle('Actualités');
 * $objFeed->setLink(_PLOOPI_BASEPATH);
 * $objFeed->addItem($strTitre, $strUrl, $intTimestampUnix, $strResumeHtml);
 * echo $objFeed->render();
 * </code>
 *
 * @package ploopi
 * @subpackage feed
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

class feed
{
    const RSS = 'rss';
    const ATOM = 'atom';

    private $strFormat;
    private $strTitle = '';
    private $strLink = '';
    private $strDescription = '';
    private $strAuthor = '';
    private $strLanguage = '';
    private $intDate = null;
    private $arrItems = array();

    /**
     * @param string $strFormat feed::RSS ou feed::ATOM (défaut, et pour toute autre valeur)
     */
    public function __construct($strFormat = self::ATOM)
    {
        $this->strFormat = ($strFormat === self::RSS) ? self::RSS : self::ATOM;
    }

    public function setTitle($strTitle) { $this->strTitle = (string) $strTitle; return $this; }
    public function setLink($strLink) { $this->strLink = (string) $strLink; return $this; }
    public function setDescription($strDescription) { $this->strDescription = (string) $strDescription; return $this; }
    public function setAuthor($strAuthor) { $this->strAuthor = (string) $strAuthor; return $this; }
    public function setLanguage($strLanguage) { $this->strLanguage = (string) $strLanguage; return $this; }

    /**
     * @param int $intDate timestamp unix de mise à jour du flux (défaut : maintenant)
     */
    public function setDate($intDate) { $this->intDate = (int) $intDate; return $this; }

    /**
     * Ajoute un article.
     *
     * @param string $strTitle titre
     * @param string $strLink lien absolu, aussi identifiant de l'article
     * @param int $intDate timestamp unix (défaut : maintenant)
     * @param string $strHtml résumé, en HTML (facultatif)
     * @return feed
     */
    public function addItem($strTitle, $strLink, $intDate = null, $strHtml = '')
    {
        $this->arrItems[] = array(
            'title' => (string) $strTitle,
            'link' => (string) $strLink,
            'date' => is_null($intDate) ? time() : (int) $intDate,
            'html' => (string) $strHtml
        );

        return $this;
    }

    /**
     * @param string $strFormat feed::RSS ou feed::ATOM
     * @return string type MIME du flux
     */
    public static function mimetype($strFormat)
    {
        return ($strFormat === self::RSS) ? 'application/rss+xml' : 'application/atom+xml';
    }

    /**
     * Identifiant urn:uuid d'une clé, comme FeedWriter\Feed::uuid().
     *
     * @param string $strKey clé (lien, titre)
     * @return string
     */
    public static function uuid($strKey)
    {
        $strMd5 = md5($strKey);

        return 'urn:uuid:'.substr($strMd5, 0, 8).'-'.substr($strMd5, 8, 4).'-'.substr($strMd5, 12, 4).'-'.substr($strMd5, 16, 4).'-'.substr($strMd5, 20, 12);
    }

    /**
     * @return string le flux XML
     */
    public function render()
    {
        $objXml = new XMLWriter();
        $objXml->openMemory();
        $objXml->setIndent(true);
        $objXml->setIndentString('  ');
        $objXml->startDocument('1.0', 'UTF-8');

        if ($this->strFormat === self::RSS) $this->renderRss($objXml);
        else $this->renderAtom($objXml);

        $objXml->endDocument();

        return $objXml->outputMemory();
    }

    private function renderRss(XMLWriter $objXml)
    {
        $objXml->startElement('rss');
        $objXml->writeAttribute('version', '2.0');
        $objXml->startElement('channel');

        // title, link et description sont obligatoires en RSS 2.0
        self::element($objXml, 'title', $this->title());
        self::element($objXml, 'link', $this->strLink);
        self::element($objXml, 'description', $this->strDescription);
        if ($this->strLanguage !== '') self::element($objXml, 'language', $this->strLanguage);
        self::element($objXml, 'lastBuildDate', date(DATE_RSS, $this->date()));

        foreach ($this->arrItems as $arrItem)
        {
            $objXml->startElement('item');
            self::element($objXml, 'title', $arrItem['title']);
            self::element($objXml, 'link', $arrItem['link']);
            $objXml->startElement('guid');
            $objXml->writeAttribute('isPermaLink', 'true');
            $objXml->text(self::clean($arrItem['link']));
            $objXml->endElement();
            self::element($objXml, 'pubDate', date(DATE_RSS, $arrItem['date']));
            if ($arrItem['html'] !== '') self::element($objXml, 'description', $arrItem['html']);
            $objXml->endElement();
        }

        $objXml->endElement();
        $objXml->endElement();
    }

    private function renderAtom(XMLWriter $objXml)
    {
        $objXml->startElement('feed');
        $objXml->writeAttribute('xmlns', 'http://www.w3.org/2005/Atom');

        self::element($objXml, 'title', $this->title());
        if ($this->strDescription !== '') self::element($objXml, 'subtitle', $this->strDescription);
        if ($this->strLink !== '') self::link($objXml, $this->strLink);
        self::element($objXml, 'updated', date(DATE_ATOM, $this->date()));
        if ($this->strAuthor !== '')
        {
            $objXml->startElement('author');
            self::element($objXml, 'name', $this->strAuthor);
            $objXml->endElement();
        }
        self::element($objXml, 'id', self::uuid($this->title()));

        foreach ($this->arrItems as $arrItem)
        {
            $objXml->startElement('entry');
            self::element($objXml, 'title', $arrItem['title']);
            self::link($objXml, $arrItem['link']);
            self::element($objXml, 'id', self::uuid($arrItem['link']));
            self::element($objXml, 'updated', date(DATE_ATOM, $arrItem['date']));
            if ($arrItem['html'] !== '')
            {
                $objXml->startElement('summary');
                $objXml->writeAttribute('type', 'html');
                $objXml->text(self::clean($arrItem['html']));
                $objXml->endElement();
            }
            $objXml->endElement();
        }

        $objXml->endElement();
    }

    /**
     * @return string titre du flux ; obligatoire, d'où un titre de repli
     */
    private function title()
    {
        return ($this->strTitle !== '') ? $this->strTitle : 'Flux';
    }

    private function date()
    {
        return is_null($this->intDate) ? time() : $this->intDate;
    }

    private static function element(XMLWriter $objXml, $strName, $strValue)
    {
        $objXml->writeElement($strName, self::clean($strValue));
    }

    private static function link(XMLWriter $objXml, $strHref)
    {
        $objXml->startElement('link');
        $objXml->writeAttribute('href', self::clean($strHref));
        $objXml->endElement();
    }

    /**
     * @param string $strValue valeur
     * @return string valeur en UTF-8 valide, sans caractère interdit en XML 1.0
     */
    private static function clean($strValue)
    {
        $strValue = mb_scrub((string) $strValue, 'UTF-8');

        return (string) preg_replace('/[^\x{9}\x{A}\x{D}\x{20}-\x{D7FF}\x{E000}-\x{FFFD}\x{10000}-\x{10FFFF}]/u', '', $strValue);
    }
}
