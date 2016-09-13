<?php
/*
    Copyright (c) 2007-2016 Ovensia
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
    along with Ploopi; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

namespace ovensia\ploopi;

use ovensia\ploopi;

/**
 * Classe de gestion des événements du calendrier
 */
class calendarEvent
{
    /**
     * Heure de début au format timestamp
     */
    private $intTimestpBegin;

    /**
     * Heure de fin au format timestamp
     */
    private $intTimestpEnd;

    /**
     * Titre
     */
    private $strTitle;

    /**
     * Contenu
     */
    private $strContent;

    /**
     * Canal de rattachement
     */
    private $strChannelId;

    /**
     * Options de l'événement
     *
     * @var array
     */

    private $arrOptions;


    /**
     * Constructeur de la classe
     *
     * @param int $intTimestpBegin Heure de début au format timestamp
     * @param int $intTimestpEnd Heure de fin au format timestamp
     * @param string $strTitle Titre
     * @param string $strContent Contenu
     * @param string $strChannelId Id du canal de rattachement
     * @param array $arrOptions sarray('strColor', 'strOnClick', 'strHref', 'strOnClose', 'strStyle')
     * @return calendarEvent
     *
     * Informations détaillées pour $arrOption :
     * string 'strColor' Couleur au format #RRGGBB
     * string 'strLabel' Contenu à afficher au survol (popup)
     * string 'strOnClick' Fonction javascript à exécuter sur l'événement "onclick"
     * string 'strHref' Lien href sur l'événement
     * string 'strOnClose' Fonction javascript à exécuter sur l'événement "onclose"
     * string 'strStyle' Styles complémentaires à appliquer
     */

    public function __construct($intTimestpBegin, $intTimestpEnd, $strTitle, $strContent, $strChannelId = null, $arrOptions = array())
    {
        $this->intTimestpBegin = $intTimestpBegin;
        $this->intTimestpEnd = $intTimestpEnd;
        $this->strTitle = $strTitle;
        $this->strContent = $strContent;
        $this->strChannelId = $strChannelId;

        $this->arrOptions = array(
            'strColor' => null,
            'strLabel' => null,
            'strHref' => null,
            'strOnClick' => null,
            'strOnClose' => null,
            'arrOnDrop' => null,
            'strStyle' => null
        );

        $this->setOptions($arrOptions);
    }

    /**
     * Permet de définir les options :
     *
     * @param array $arrOptions tableau des options à modifier
     */

    public function setOptions($arrOptions)
    {
        $this->arrOptions = array_merge($this->arrOptions, $arrOptions);
    }

    public function getOptions()
    {
        return $this->arrOptions;
    }

    /**
     * Getter par défaut
     *
     * @param string $strName nom de la propriété à lire
     * @return string valeur de la propriété si elle existe
     */
    public function __get($strName)
    {
        if (isset($this->{$strName})) return $this->{$strName};
        elseif (isset($this->arrOptions[$strName])) return $this->arrOptions[$strName];
        else return null;
    }
}
