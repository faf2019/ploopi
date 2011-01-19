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
 * Fonctions d'envoi de mail
 *
 * @package ploopi
 * @subpackage mail
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * Envoie un mail. Gère les emetteurs multiples, les destinataires multiples, le CC multiple, le BCC multiple, le REPLYTO multiple, les pièces jointes, les messages au format HTML.
 *
 * @param mixed $from tableau indexé contenant les emetteurs, chaque emetteur est défini par Array('name' => '', 'address' => ''). Accepte aussi une chaine contenant une adresse email.
 * @param mixed $to tableau indexé contenant les destinataires, chaque destinataire est défini par Array('name' => '', 'address' => ''). Accepte aussi une chaine contenant une adresse email.
 * @param string $subject le sujet du message.
 * @param string $message le contenu du message.
 * @param mixed $cc tableau indexé contenant les destinataires en copie, chaque destinataire est défini par Array('name' => '', 'address' => ''). Accepte aussi une chaine contenant une adresse email.
 * @param mixed $bcc tableau indexé contenant les destinataires en copie cachée, chaque destinataire est défini par Array('name' => '', 'address' => ''). Accepte aussi une chaine contenant une adresse email.
 * @param mixed $replyto tableau indexé contenant les destinataires de la réponse, chaque destinataire est défini par Array('name' => '', 'address' => ''). Accepte aussi une chaine contenant une adresse email.
 * @param array $files tableau indexé de chemins vers des fichiers à joindre au message.
 * @param boolean $html true si le message doit être envoyé au format HTML.
 *
 * @see ploopi_checkemail
 * @see mail
 */

function ploopi_send_mail($from, $to, $subject, $message, $cc = null, $bcc = null, $replyto = null, $files = null, $html = true)
{
    // from : Array('name','address')
    // to : Array('name','address')
    // cc : Array('name','address')
    // bcc : Array('name','address')
    // replyto : Array('name','address')
    // files : Array

    $crlf = "\r\n";

    $str_param = '';

    $str_to = '';
    if (is_array($to))
    {
        foreach($to as $detail)
        {
            if (ploopi_checkemail($detail['address']))
            {
                if ($str_to != '') $str_to .= ', ';
                $str_to .= mb_encode_mimeheader($detail['name'])." <{$detail['address']}>";
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
                else $str_param = "-f{$detail['address']}";
                $str_from .= mb_encode_mimeheader($detail['name'])." <{$detail['address']}>";
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
                $str_cc .= mb_encode_mimeheader($detail['name'])." <{$detail['address']}>";
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
                $str_bcc .= mb_encode_mimeheader($detail['name'])." <{$detail['address']}>";
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
                $str_replyto .= mb_encode_mimeheader($detail['name'])." <{$detail['address']}>";
            }
        }
    }
    else
    {
        if (ploopi_checkemail($replyto)) $str_replyto = $replyto;
    }

    $headers = '';

    // add "from" to headers
    if (!empty($str_from)) $headers .= "From: {$str_from} {$crlf}";
    // add "reply_to" to headers

    // add "reply_to" to headers
    if (!empty($str_replyto))
    {
        $headers .= "Reply-to: {$str_replyto} {$crlf}";
        $headers .= "Return-Path: {$str_replyto} {$crlf}";
    }
    else
    {
        $headers .= "Reply-To: {$str_from} {$crlf}";
        $headers .= "Return-Path: {$str_from} {$crlf}";
    }

    // add "cc" to headers
    if (!empty($str_cc)) $headers .= "Cc: {$str_cc} {$crlf}";
    // add "bcc" to headers
    if (!empty($str_bcc)) $headers .= "Bcc: {$str_bcc} {$crlf}";

    $domain = mb_encode_mimeheader(empty($_SERVER['HTTP_HOST']) ? $_SERVER['SERVER_NAME'] : $_SERVER['HTTP_HOST']);
    $organization = mb_encode_mimeheader(isset($_SESSION['ploopi']['workspaces'][$_SESSION['ploopi']['workspaceid']]['label']) ? $_SESSION['ploopi']['workspaces'][$_SESSION['ploopi']['workspaceid']]['label'] : $domain);

    $headers .= "Date: ".date('r')."{$crlf}";
    $headers .= "X-Priority: 1{$crlf}";
    $headers .= "X-Sender: <{$domain}>{$crlf}";
    $headers .= "X-Mailer: PHP/Ploopi "._PLOOPI_VERSION."{$crlf}";
    $headers .= "X-auth-smtp-user: {$str_from}{$crlf}";
    $headers .= "X-abuse-contact: abuse@{$domain}{$crlf}";
    $headers .= "Organization: {$organization}{$crlf}";
    $headers .= "MIME-Version: 1.0{$crlf}";

    $msg = '';

    if (!empty($files)) // Create multipart mail
    {
        $boundary = md5(uniqid(microtime(), true));
        $headers .= "Content-type: multipart/mixed;boundary={$boundary}{$crlf}{$crlf}";

        $msg .= "--{$boundary}{$crlf}";

        if ($html) $msg .= "Content-type: text/html; charset=iso-8859-1{$crlf}{$crlf}";
        else $msg .= "Content-type: text/plain; charset=iso-8859-1{$crlf}{$crlf}";

        $msg .= "$message{$crlf}{$crlf}";

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

                $msg .= "--{$boundary}{$crlf}";
                $msg .= "Content-type:{$mime_type};name=".mb_encode_mimeheader(basename($filename))."{$crlf}";
                $msg .= "Content-transfer-encoding:base64{$crlf}{$crlf}";
                $msg .= "{$content}{$crlf}{$crlf}";
            }
        }

        $msg .= "--{$boundary}--";
    }
    else
    {
        if ($html) $headers .= "Content-type: text/html; charset=iso-8859-1{$crlf}{$crlf}";
        else $headers .= "Content-type: text/plain; charset=iso-8859-1{$crlf}{$crlf}";

        $msg = $message;
    }

     // send mail
    mail($str_to, $subject, $msg, $headers, $str_param);

}


/**
 * Envoie un mail via un serveur SMTP. Gère les emetteurs multiples, les destinataires multiples, le CC multiple, le BCC multiple, le REPLYTO multiple, les pièces jointes, les messages au format HTML.
 *
 * @param mixed $from tableau indexé contenant les emetteurs, chaque emetteur est défini par Array('name' => '', 'address' => ''). Accepte aussi une chaine contenant une adresse email.
 * @param mixed $to tableau indexé contenant les destinataires, chaque destinataire est défini par Array('name' => '', 'address' => ''). Accepte aussi une chaine contenant une adresse email.
 * @param string $subject le sujet du message.
 * @param string $message le contenu du message.
 * @param array $params paramètres de connexion au serveur smtp ('host' => string, 'auth' => bool, 'username' => string, 'password' => string)
 * @param mixed $cc tableau indexé contenant les destinataires en copie, chaque destinataire est défini par Array('name' => '', 'address' => ''). Accepte aussi une chaine contenant une adresse email.
 * @param mixed $bcc tableau indexé contenant les destinataires en copie cachée, chaque destinataire est défini par Array('name' => '', 'address' => ''). Accepte aussi une chaine contenant une adresse email.
 * @param mixed $replyto tableau indexé contenant les destinataires de la réponse, chaque destinataire est défini par Array('name' => '', 'address' => ''). Accepte aussi une chaine contenant une adresse email.
 * @param array $files tableau indexé de chemins vers des fichiers à joindre au message.
 * @param boolean $html true si le message doit être envoyé au format HTML.
 *
 * @see ploopi_checkemail
 * @see mail
 */

function ploopi_send_mail_smtp($from, $to, $subject, $message, $params = null, $cc = null, $bcc = null, $replyto = null, $files = null, $html = true)
{
    require_once 'Mail.php';
    require_once 'Mail/mime.php';

    $objMail = Mail::factory('smtp', array (
        'host' => isset($params['host']) ? $params['host'] : 'localhost',
        'auth' => isset($params['auth']) ? $params['auth'] : false,
        'username' => isset($params['username']) ? $params['username'] : '',
        'password' => isset($params['password']) ? $params['password'] : '',
    ));

    $str_to = '';
    if (is_array($to))
    {
        foreach($to as $detail)
        {
            if (ploopi_checkemail($detail['address']))
            {
                if ($str_to != '') $str_to .= ', ';
                $str_to .= mb_encode_mimeheader($detail['name'])." <{$detail['address']}>";
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
                $str_from .= mb_encode_mimeheader($detail['name'])." <{$detail['address']}>";
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
                $str_cc .= mb_encode_mimeheader($detail['name'])." <{$detail['address']}>";
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
                $str_bcc .= mb_encode_mimeheader($detail['name'])." <{$detail['address']}>";
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
                $str_replyto .= mb_encode_mimeheader($detail['name'])." <{$detail['address']}>";
            }
        }
    }
    else
    {
        if (ploopi_checkemail($replyto)) $str_replyto = $replyto;
    }


    if (empty($str_replyto)) $str_replyto = $str_from;


    // Initialisation des headers
    $arrHeaders = array();

    $arrHeaders['From'] = $str_from;
    $arrHeaders['To'] = $str_to;
    $arrHeaders['Subject'] = mb_encode_mimeheader($subject);
    $arrHeaders['Reply-to'] = $str_replyto;
    $arrHeaders['Return-path'] =  $str_replyto;

    if (!empty($str_cc)) $arrHeaders['Cc'] = $str_cc;
    if (!empty($str_bcc)) $arrHeaders['Bcc'] = $str_bcc;

    $arrHeaders['Date'] = date('r');
    $arrHeaders['X-Priority'] = 1;
    $arrHeaders['X-Sender'] = "<{$domain}>";
    $arrHeaders['X-Mailer'] = 'PHP/Ploopi';

    // Création du message
    $objMessage = new Mail_mime();

    // Intégration des pièces jointes
    if (!empty($files))
    {
        foreach($files as $filename)
        {
            if (file_exists($filename) && is_readable($filename)) $objMessage->addAttachment($filename);
        }
    }

    // Contenu du message
    if ($html)
    {
        $objMessage->setHTMLBody('<html>'.$message.'</html>');
        $objMessage->setTXTBody('Ce message est en HTML');
    }
    else $objMessage->setTXTBody(html_entity_decode(strip_tags($message)));


    $mail = $objMail->send($str_to, $objMessage->headers($arrHeaders), $objMessage->get());

    return PEAR::isError($mail);
}


/**
 * Génère une version HTML d'un tableau php multidimensionnel (formulaire par exemple)
 *
 * @param array $form tableau à convertir au format HTML
 * @return string code HTML du tableau
 */

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

/**
 * Envoie un formulaire (ou un tableau) par mail. Gère les emetteurs multiples, les destinataires multiples, le CC multiple, le BCC multiple, le REPLYTO multiple
 *
 * @param mixed $from tableau indexé contenant les emetteurs, chaque emetteur est défini par Array('name' => '', 'address' => ''). Accepte aussi une chaine contenant une adresse email.
 * @param mixed $to tableau indexé contenant les destinataires, chaque destinataire est défini par Array('name' => '', 'address' => ''). Accepte aussi une chaine contenant une adresse email.
 * @param string $subject le sujet du message.
 * @param string $message le contenu du message.
 * @param mixed $cc tableau indexé contenant les destinataires en copie, chaque destinataire est défini par Array('name' => '', 'address' => ''). Accepte aussi une chaine contenant une adresse email.
 * @param mixed $bcc tableau indexé contenant les destinataires en copie cachée, chaque destinataire est défini par Array('name' => '', 'address' => ''). Accepte aussi une chaine contenant une adresse email.
 * @param mixed $replyto tableau indexé contenant les destinataires de la réponse, chaque destinataire est défini par Array('name' => '', 'address' => ''). Accepte aussi une chaine contenant une adresse email.
 */

function ploopi_send_form($from, $to, $subject, $form, $cc = null, $bcc = null, $replyto = null)
{
    $content = ploopi_form2html($form);

    $message =  "
                <html>
                    <head>
                        <title>{$subject}</title>
                    </head>
                    <body>
                        <table cellpadding=\"3\" cellspacing=\"1\" bgcolor=\"#000000\">
                            {$content}
                        </table>
                    </body>
                </html>
                ";

    return(ploopi_send_mail($from, $to, $subject, $message, $cc, $bcc, $replyto));
}

/**
 * Valide une adresse email (format uniquement) selon les RFC 2822 et 1035
 *
 * @param string $email adresse email à valider
 * @return boolean true si l'adresse est considérée comme valide
 *
 * @copyright  bobocop (arobase) bobocop (point) cz
 *
 * @link http://www.faqs.org/rfcs/rfc2822.html
 * @link http://www.faqs.org/rfcs/rfc1035.html
 * @link http://atranchant.developpez.com/code/validation/
 */

function ploopi_checkemail($email)
{
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