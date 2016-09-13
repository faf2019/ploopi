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

namespace ovensia\ploopi;

use ovensia\ploopi;

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

class cipher
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


    private static $objInstance;

    /**
     * Constructeur de la classe
     *
     * @param string $key cl� secr�te
     * @param string $iv vecteur d'initialisation
     * @return ploopi_cipher
     */

    public function __construct($key = _PLOOPI_SECRETKEY, $iv = _PLOOPI_CIPHER_IV, $c = _PLOOPI_CIPHER)
    {
        $this->cipher = mcrypt_module_open($c,'','cbc','');
        $this->key = substr($key, 0, mcrypt_enc_get_key_size($this->cipher));
        $this->iv = substr($iv, 0, mcrypt_enc_get_block_size($this->cipher));
    }

    /**
     * M�thode singleton
     */

    public static function singleton()
    {
        if (!isset(self::$objInstance)) {
            $c = __CLASS__;
            self::$objInstance = new $c;
        }

        return self::$objInstance;
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
            mcrypt_generic_init($this->cipher, $this->key, $this->iv);
            $encrypted = crypt::base64_encode(mcrypt_generic($this->cipher, gzcompress($str)));
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

    function decrypt($strEncrypted)
    {
        if (empty($strEncrypted)) return(false);

        $strDecoded = crypt::base64_decode($strEncrypted);
        mcrypt_generic_init($this->cipher, $this->key, $this->iv);
        error::unset_handler();
        $strDecoded = strlen($strDecoded) > 0 ? gzuncompress(mdecrypt_generic($this->cipher, $strDecoded)) : '';
        error::set_handler();
        mcrypt_generic_deinit($this->cipher);

        return($strDecoded);
    }
}
