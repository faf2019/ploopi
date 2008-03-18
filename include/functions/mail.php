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

function ploopi_send_mail($from, $to, $subject, $message, $cc = null, $bcc = null, $replyto = null, $files = null, $html = true)
{
    // from : Array('name','address')
    // to : Array('name','address')
    // cc : Array('name','address')
    // bcc : Array('name','address')
    // replyto : Array('name','address')
    // files : Array


    $str_to = '';
    if (is_array($to))
    {
        foreach($to as $detail)
        {
            if (ploopi_checkemail($detail['address']))
            {
                if ($str_to != '') $str_to .= ', ';
                $str_to .= "{$detail['name']} <{$detail['address']}>";
            }
        }
    }
    else
    {
        if (ploopi_checkemail($to)) $str_to = $to;
    }

    $str_from = '';
    if (is_array($from))
    {
        foreach($from as $detail)
        {
            if (ploopi_checkemail($detail['address']))
            {
                if ($str_from != '') $str_from .= ', ';
                $str_from .= "{$detail['name']} <{$detail['address']}>";
            }
        }
    }
    else
    {
        if (ploopi_checkemail($from)) $str_from = $from;
    }

    $str_cc = '';
    if (isset($cc) && is_array($cc))
    {
        foreach($cc as $detail)
        {
            if (ploopi_checkemail($detail['address']))
            {
                if ($str_cc != '') $str_cc .= ', ';
                $str_cc .= "{$detail['name']} <{$detail['address']}>";
            }
        }
    }

    $str_bcc = '';
    if (isset($bcc) && is_array($bcc))
    {
        foreach($bcc as $detail)
        {
            if (ploopi_checkemail($detail['address']))
            {
                if ($str_bcc != '') $str_bcc .= ', ';
                $str_bcc .= "{$detail['name']} <{$detail['address']}>";
            }
        }
    }

    $str_replyto = '';
    if (is_array($replyto))
    {
        foreach($replyto as $detail)
        {
            if (ploopi_checkemail($detail['address']))
            {
                if ($str_replyto != '') $str_replyto .= ', ';
                $str_replyto .= "{$detail['name']} <{$detail['address']}>";
            }
        }
    }
    else
    {
        if (ploopi_checkemail($replyto)) $str_replyto = $replyto;
    }
    
    $headers = '';

    // add "from" to headers
    if (!empty($str_from)) $headers .= "From: $str_from \r\n";
    // add "reply_to" to headers
    if (!empty($str_replyto)) $headers .= "Reply-to: $str_replyto \r\n";
    // add "cc" to headers
    if (!empty($str_cc)) $headers .= "Cc: $str_cc \r\n";
    // add "bcc" to headers
    if (!empty($str_bcc)) $headers .= "Bcc: $str_bcc \r\n";
    
    
    $headers .= "Date: ".date('r')."\r\n"; 
    $headers .= "X-Priority: 1\r\n";
    $headers .= "X-Mailer: Ploopi "._PLOOPI_VERSION."\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    
    $msg = '';
    
    if (!empty($files)) // Create multipart mail
    {
        $boundary = md5(uniqid(microtime(), true));
        $headers .= "Content-type: multipart/mixed;boundary={$boundary}\r\n";
        $headers .= "\r\n";
        
        $msg .= "--{$boundary}\r\n";
        
        if ($html) $msg .= "Content-type: text/html; charset=iso-8859-1\r\n\r\n";
        else $msg .= "Content-type: text/plain; charset=iso-8859-1\r\n\r\n";

        $msg .= "$message\r\n";
        
        foreach($files as $filename)
        {
            if (file_exists($filename) && is_readable($filename))
            {
                $mime_type = ploopi_getmimetype($filename);
                $file_size = filesize($filename);
            
                $handle = fopen($filename, 'r');
                $content = fread($handle, $file_size);
                $content = chunk_split(base64_encode($content));
                $f = fclose($handle);
            
                $msg .= "--{$boundary}\r\n";
                $msg .= "Content-type:{$mime_type};name=".basename($filename)."\r\n";
                $msg .= "Content-transfer-encoding:base64\r\n";
                $msg .= "{$content}\r\n";
            }
        }
        
        $msg .= "--{$boundary}--";
    }
    else
    {
        if ($html) $headers .= "Content-type: text/html; charset=iso-8859-1\r\n\r\n";
        else $headers .= "Content-type: text/plain; charset=iso-8859-1\r\n\r\n";
        //$header .= 'Content-transfer-encoding:8bit'."\r\n";

        $msg = $message;
    }

    $headers .= "\r\n";
    
     // send mail
    mail($str_to, $subject, $msg, $headers);

}

function ploopi_form2html($form)
{
    $content = '';

    foreach($form as $field => $value)
    {
        if (is_array($value))
        {
            $content.=  "
                    <tr bgcolor='#ffffff'>
                        <td align='left'><b>$field</b></td>
                        <td align='left' valign='top'>
                        <table cellpadding='3' cellspacing='1' bgcolor='#000000'>".ploopi_form2html($value)."</table>
                        </td>
                    </tr>
                    ";
        }
        else
        {
            $content.=  "
                    <tr bgcolor='#ffffff'>
                        <td align='left' valign='top'><b>$field</b></td>
                        <td align='left'>$value</td>
                    </tr>
                    ";
        }
    }

    return($content);
}

function ploopi_send_form($from, $to, $subject, $form, $cc = null, $bcc = null)
{

    // form['field'] = value

    $content = ploopi_form2html($form);

    /* message */
    $message =  "
            <html>
            <head>
            <title>$subject</title>
            </head>
            <body>
            <table cellpadding='3' cellspacing='1' bgcolor='#000000'>
            $content
            </table>
            </body>
            </html>
            ";

    return(ploopi_send_mail($from, $to, $subject, $message, $cc = null, $bcc = null));
}


function ploopi_checkemail($email)
{
    // Auteur : bobocop (arobase) bobocop (point) cz
    // Traduction des commentaires par mathieu
    // http://atranchant.developpez.com/code/validation/
    
    // Le code suivant est la version du 2 mai 2005 qui respecte les RFC 2822 et 1035
    // http://www.faqs.org/rfcs/rfc2822.html
    // http://www.faqs.org/rfcs/rfc1035.html
    
    $atom   = '[-a-z0-9!#$%&\'*+\\/=?^_`{|}~]';   // caractères autorisés avant l'arobase
    $domain = '([a-z0-9]([-a-z0-9]*[a-z0-9]+)?)'; // caractères autorisés après l'arobase (nom de domaine)
                                   
    $regex = '/^' . $atom . '+' .   // Une ou plusieurs fois les caractères autorisés avant l'arobase
    '(\.' . $atom . '+)*' .         // Suivis par zéro point ou plus
                                    // séparés par des caractères autorisés avant l'arobase
    '@' .                           // Suivis d'un arobase
    '(' . $domain . '{1,63}\.)+' .  // Suivis par 1 à 63 caractères autorisés pour le nom de domaine
                                    // séparés par des points
    $domain . '{2,63}$/i';          // Suivi de 2 à 63 caractères autorisés pour le nom de domaine
    
    return (preg_match($regex, $email));
}

?>