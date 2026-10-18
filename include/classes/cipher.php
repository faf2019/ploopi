<?php
/*
    Copyright (c) 2007-2018 Ovensia
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

namespace ploopi;

use ploopi;

/**
 * Gestion du chiffrement/déchiffrement (basé sur openssl), notamment utilisée pour chiffrer les URL
 *
 * @package ploopi
 * @subpackage crypt
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 *
 * @see openssl
 * @see _PLOOPI_SECRETKEY
 */

class cipher
{
    /**
     * Clé secrète
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
     * Longueur du vecteur d'initialisation
     */

    private $len;

    /**
     * Instance de la classe (singleton)
     *
     * @var cipher
     */

    private static $objInstance;

    /**
     * Constructeur de la classe
     *
     * @param string $key clé secrète
     * @param string $iv vecteur d'initialisation
     */

    public function __construct($key = _PLOOPI_SECRETKEY, $iv = _PLOOPI_CIPHER_IV, $c = _PLOOPI_CIPHER)
    {
        $this->cipher = $c;
        $this->key = $key;
        $this->len = openssl_cipher_iv_length($this->cipher);
        $this->iv = substr($iv, 0, $this->len);
    }

    /**
     * Méthode singleton
     *
     * @return cipher
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
     * @param string $str chaîne à chiffrer
     * @return mixed chaîne chiffrée ou false si la chaîne est vide
     */

    function crypt($str)
    {
        if (!empty($str))
        {
            /**
             * Vecteur d'initialisation tiré au hasard pour chaque message.
             * Avec l'IV fixe de _PLOOPI_CIPHER_IV, le chiffrement était déterministe : deux
             * URL identiques produisaient le même cryptogramme, et deux URL partageant un
             * préfixe produisaient des cryptogrammes partageant un préfixe, ce qui laisse
             * lire la structure des URL et rejouer des comparaisons.
             * L'IV n'est pas un secret : il est transporté en tête du message, comme avant.
             */
            $iv = $this->len > 0 ? random_bytes($this->len) : '';

            $ciphertext_raw = openssl_encrypt($str, $this->cipher, $this->key, $options = OPENSSL_RAW_DATA, $iv);
            $hmac = hash_hmac('sha256', $iv.$ciphertext_raw, $this->getmackey(), $as_binary = true);

            return crypt::base64_encode($iv.$hmac.$ciphertext_raw);
        }
        else return(false);
    }

    /**
     * Renvoie la clé d'authentification, dérivée de la clé secrète.
     *
     * La même clé servait au chiffrement et au calcul de l'empreinte. Les séparer est la
     * règle : deux usages, deux clés.
     *
     * @return string clé binaire
     */

    private function getmackey()
    {
        return hash_hmac('sha256', 'ploopi/cipher/mac', $this->key, true);
    }

    /**
     * Déchiffre une chaîne
     *
     * @param string $encrypted chaîne chiffrée
     * @return mixed chaîne déchiffrée ou false si la chaîne est vide
     */

    function decrypt($strEncrypted)
    {
        if (empty($strEncrypted)) return false;

        $c = crypt::base64_decode($strEncrypted);

        if (strlen($c) <= $this->len + 32) return false;

        $iv = substr($c, 0, $this->len);
        $hmac = substr($c, $this->len, $sha2len = 32);
        $ciphertext_raw = substr($c, $this->len + $sha2len);

        /**
         * L'empreinte couvre l'IV en plus du cryptogramme, sans quoi l'IV transporté serait
         * modifiable sans invalider le message.
         *
         * Le format précédent (empreinte sur le seul cryptogramme, clé secrète directe) n'est
         * plus accepté : son IV n'étant pas authentifié, il laissait altérer le premier bloc
         * du texte clair. Les URL chiffrées antérieures à septembre 2026 sont donc refusées.
         */
        if (!hash_equals($hmac, hash_hmac('sha256', $iv.$ciphertext_raw, $this->getmackey(), true))) return false;

        // L'IV utilisé est celui transporté par le message
        return openssl_decrypt($ciphertext_raw, $this->cipher, $this->key, $options = OPENSSL_RAW_DATA, $iv);
    }
}
