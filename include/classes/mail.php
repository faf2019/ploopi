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
 * Fonctions d'envoi de mail
 *
 * @package ploopi
 * @subpackage mail
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

abstract class mail
{

    /**
     * Envoie un mail via la fonction interne de PHP. Gère les emetteurs multiples, les destinataires multiples, le CC multiple, le BCC multiple, le REPLYTO multiple, les pièces jointes, les messages au format HTML.
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
     * @return boolean true si le mail est envoyé.
     */

    public static function send($from, $to, $subject, $message, $cc = null, $bcc = null, $replyto = null, $files = null, $html = true)
    {
        // from : Array('name','address')
        // to : Array('name','address')
        // cc : Array('name','address')
        // bcc : Array('name','address')
        // replyto : Array('name','address')
        // files : Array

        $subject = mb_encode_mimeheader($subject);

        $crlf = "\r\n";

        $str_to = '';
        if (is_array($to))
        {
            foreach($to as $detail)
            {
                if (self::check($detail['address']))
                {
                    if ($str_to != '') $str_to .= ', ';
                    $str_to .= mb_encode_mimeheader($detail['name'])." <{$detail['address']}>";
                }
            }
        }
        else
        {
            if (self::check($to)) $str_to = $to;
        }

        $str_from = '';
        if (is_array($from))
        {
            $detail = current($from);
            $detail['address'] = trim(current(explode(',', $detail['address'])));
            if (self::check($detail['address'])) $str_from = mb_encode_mimeheader($detail['name'])." <{$detail['address']}>";
        }
        else
        {
            $from = trim(current(explode(',', $from)));
            if (self::check($from)) $str_from = $from;
        }

        $str_cc = '';
        if (isset($cc) && is_array($cc))
        {
            foreach($cc as $detail)
            {
                if (self::check($detail['address']))
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
                if (self::check($detail['address']))
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
                if (self::check($detail['address']))
                {
                    if ($str_replyto != '') $str_replyto .= ', ';
                    $str_replyto .= mb_encode_mimeheader($detail['name'])." <{$detail['address']}>";
                }
            }
        }
        else
        {
            if (self::check($replyto)) $str_replyto = $replyto;
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

            if ($html) $msg .= "Content-type: text/html; charset=utf-8{$crlf}{$crlf}";
            else $msg .= "Content-type: text/plain; charset=utf-8{$crlf}{$crlf}";

            $msg .= "$message{$crlf}{$crlf}";

            foreach($files as $filename)
            {
                if (file_exists($filename) && is_readable($filename))
                {
                    $mime_type = fs::getmimetype($filename);
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
            if ($html) $headers .= "Content-type: text/html; charset=utf-8{$crlf}{$crlf}";
            else $headers .= "Content-type: text/plain; charset=utf-8{$crlf}{$crlf}";

            $msg = $message;
        }

         // send mail
        return mail($str_to, $subject, $msg, $headers);

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
     * @return mixed true si le message est envoyé, le message d'erreur sinon
     */

    public static function send_smtp($from, $to, $subject, $message, $params = null, $cc = null, $bcc = null, $replyto = null, $files = null, $html = true)
    {
        $objMail = \Mail::factory('smtp', array (
            'host' => isset($params['host']) ? $params['host'] : 'localhost',
            'port' => isset($params['port']) ? $params['port'] : 25,
            'auth' => isset($params['auth']) ? $params['auth'] : false,
            'username' => isset($params['username']) ? $params['username'] : '',
            'password' => isset($params['password']) ? $params['password'] : '',
        ));

        $str_to = '';
        if (is_array($to))
        {
            foreach($to as $detail)
            {
                if (self::check($detail['address']))
                {
                    if ($str_to != '') $str_to .= ', ';
                    $str_to .= mb_encode_mimeheader($detail['name'])." <{$detail['address']}>";
                }
            }
        }
        else
        {
            if (self::check($to)) $str_to = $to;
        }

        $str_from = '';
        if (is_array($from))
        {
            foreach($from as $detail)
            {
                if (self::check($detail['address']))
                {
                    if ($str_from != '') $str_from .= ', ';
                    $str_from .= mb_encode_mimeheader($detail['name'])." <{$detail['address']}>";
                }
            }
        }
        else
        {
            if (self::check($from)) $str_from = $from;
        }

        $str_cc = '';
        if (isset($cc) && is_array($cc))
        {
            foreach($cc as $detail)
            {
                if (self::check($detail['address']))
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
                if (self::check($detail['address']))
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
                if (self::check($detail['address']))
                {
                    if ($str_replyto != '') $str_replyto .= ', ';
                    $str_replyto .= mb_encode_mimeheader($detail['name'])." <{$detail['address']}>";
                }
            }
        }
        else
        {
            if (self::check($replyto)) $str_replyto = $replyto;
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
        $arrHeaders['X-Sender'] = mb_encode_mimeheader(empty($_SERVER['HTTP_HOST']) ? (empty($_SERVER['SERVER_NAME']) ? 'cli' : $_SERVER['SERVER_NAME']) : $_SERVER['HTTP_HOST']);
        $arrHeaders['X-Mailer'] = 'PHP/Ploopi';
        $arrHeaders['Organization'] = mb_encode_mimeheader(isset($_SESSION) && isset($_SESSION['ploopi']['workspaces'][$_SESSION['ploopi']['workspaceid']]['label']) ? $_SESSION['ploopi']['workspaces'][$_SESSION['ploopi']['workspaceid']]['label'] : $arrHeaders['X-Sender']);

        $arrMimeParams = array(
            'text_encoding' => '7bit',
            'text_charset'  => mb_internal_encoding(),
            'html_charset'  => mb_internal_encoding(),
            'head_charset'  => mb_internal_encoding()
        );

        // Création du message
        $objMessage = new \Mail_mime();

        // Intégration des pièces jointes
        if (!empty($files))
        {
            foreach($files as $filename)
            {
                if (file_exists($filename) && is_readable($filename)) {
                    // Traitement spécial des images pour ajout du Content-ID
                    // http://pear.php.net/package/Mail_Mime/docs/latest/Mail_Mime/Mail_mime.html#methodaddHTMLImage
                    if (in_array(fs::file_getextension($filename), array('png', 'jpg', 'jpeg', 'gif'))) {
                        $objMessage->addHTMLImage($filename, fs::getmimetype($filename), basename($filename), true, basename($filename));
                    }
                    else $objMessage->addAttachment($filename);
                }
            }
        }

        // Contenu du message
        if ($html) $objMessage->setHTMLBody($message);

        // Ajout de la version txt
        $objMessage->setTXTBody(
            html_entity_decode(
                strip_tags(
                    str_replace('<br />', "\n",
                        preg_replace(array(
                            '@<script[^>]*?>.*?</script>@xmsi',  // Strip out javascript
                            '@<style[^>]*?>.*?</style>@xmsi',    // Strip style tags properly
                            '@<![\s\S]*?--[ \t\n\r]*>@xmsi'      // Strip multi-line comments including CDATA
                        ), '', $message)
                    )
                ),
                ENT_COMPAT,
                mb_internal_encoding()
            )
        );

        if (!empty($str_cc)) $str_to .= ', '.$str_cc;
        if (!empty($str_bcc)) $str_to .= ', '.$str_bcc;

        $body = $objMessage->get($arrMimeParams);
        $headers = $objMessage->headers($arrHeaders);
        $mail = $objMail->send($str_to, $headers, $body);

        return \PEAR::isError($mail) ? $mail->getMessage() : true;
    }

    /**
     * Génère une version HTML d'un tableau php multidimensionnel (formulaire par exemple)
     *
     * @param array $form tableau à convertir au format HTML
     * @return string code HTML du tableau
     */

    public static function form2html($form)
    {
        $content = '';

        foreach($form as $field => $value)
        {
            if (is_array($value))
            {
                $content.=  "
                        <tr>
                            <th>{$field}</th>
                            <td><table>".mail::form2html($value)."</table></td>
                        </tr>
                        ";
            }
            else
            {
                $content.=  "
                        <tr>
                            <th>{$field}</th>
                            <td>{$value}</td>
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

    public static function send_form($from, $to, $subject, $form, $cc = null, $bcc = null, $replyto = null)
    {
        $content = mail::form2html($form);

        $message =  "
                    <html>
                        <head>
                            <title>{$subject}</title>
                        </head>
                        <body>
                            <table class=\"ploopi_array\">
                                {$content}
                            </table>
                        </body>
                    </html>
                    ";

        return(mail::send($from, $to, $subject, $message, $cc, $bcc, $replyto));
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

    public static function check($email)
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
}
