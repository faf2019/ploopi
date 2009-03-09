<?php
/*
    Copyright (c) 2002-2007 Netlor
    Copyright (c) 2007-2008 Ovensia
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

/**
 * Gestion du chiffrement/d�chiffrement
 *
 * @package ploopi
 * @subpackage crypt
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author St�phane Escaich
 *
 * @see mcrypt
 * @see _PLOOPI_SECRETKEY
 */

/**
 * Classe de chiffrement/d�chiffrement (bas� sur mcrypt), notamment utilis�e pour chiffrer les URL
 *
 * @package ploopi
 * @subpackage crypt
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author St�phane Escaich
 *
 * @see mcrypt
 * @see _PLOOPI_SECRETKEY
 */

class ploopi_cipher
{
    /**
     * Cl� secr�te
     *
     * @var string
     */

    private $key;

    /*
     * Vecteur d'initialisation
     *
     * @var string
     */

    private $iv;

    /**
     * Pointeur de chiffrement
     *
     * @var resource
     */

    private $cipher;

    /**
     * Constructeur de la classe
     *
     * @param string $key cl� secr�te
     * @param unknown_type $iv vecteur d'initialisation
     * @return ploopi_cipher
     */

    function ploopi_cipher($key = '', $iv = '12345678')
    {
        $this->key = (empty($key)) ? _PLOOPI_SECRETKEY : $key;
        $this->iv = $iv;
        $this->cipher = mcrypt_module_open(MCRYPT_BLOWFISH,'','cbc','');
    }

    /**
     * Chiffre une chaine
     *
     * @param string $str cha�ne � chiffrer
     * @return mixed cha�ne chiffr�e ou false si la cha�ne est vide
     */

    function crypt($str)
    {
        if (!empty($str))
        {
            include_once './include/functions/crypt.php';

            mcrypt_generic_init($this->cipher, $this->key, $this->iv);
            $encrypted = ploopi_base64_encode(mcrypt_generic($this->cipher, $str));
            mcrypt_generic_deinit($this->cipher);
            return($encrypted);
        }
        else return(false);
    }

    /**
     * D�chiffre une cha�ne
     *
     * @param string $encrypted cha�ne chiffr�e
     * @return mixed cha�ne d�chiffr�e ou false si la cha�ne est vide
     */

    function decrypt($encrypted)
    {
        if (!empty($encrypted))
        {
            include_once './include/functions/crypt.php';

            mcrypt_generic_init($this->cipher, $this->key, $this->iv);
            $decoded = ploopi_base64_decode($encrypted);
            $decrypted = (strlen($decoded) > 0) ? rtrim(mdecrypt_generic($this->cipher, $decoded),"\0") : '';
            mcrypt_generic_deinit($this->cipher);
            return($decrypted);
        }
        else return(false);
    }
}
?>
