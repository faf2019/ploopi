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

class ploopi_cipher
{
    var $key, $iv, $cipher;

    function ploopi_cipher($key = '', $iv = '12345678')
    {
        $this->key = (empty($key)) ? _PLOOPI_SECRETKEY : $key;
        $this->iv = $iv;
        $this->cipher = mcrypt_module_open(MCRYPT_BLOWFISH,'','cbc','');
    }

    function crypt($cc)
    {
        if (!empty($cc))
        {
            mcrypt_generic_init($this->cipher, $this->key, $this->iv);
            $encrypted = ploopi_base64_encode(mcrypt_generic($this->cipher,$cc));
            mcrypt_generic_deinit($this->cipher);
            return($encrypted);
        }
        else return(false);
    }

    function decrypt($encrypted)
    {
        if (!empty($encrypted))
        {
            mcrypt_generic_init($this->cipher, $this->key, $this->iv);
            $decrypted = rtrim(mdecrypt_generic($this->cipher,ploopi_base64_decode($encrypted)),"\0");
            mcrypt_generic_deinit($this->cipher);
            return($decrypted);
        }
        else return(false);
    }
}
?>
