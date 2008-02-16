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
?>
<?

function ploopi_send_mail($from, $to, $subject, $message, $cc = null, $bcc = null)
{
    // from : Array('name','address')
    // to : Array('name','address')
    // cc : Array('name','address')
    // bcc : Array('name','address')


    if (is_array($to))
    {
        $str_to = '';
        foreach($to as $to_detail)
        {
            if ($str_to != '') $str_to .= ', ';
            $str_to .= "{$to_detail['name']} <{$to_detail['address']}>";
        }
    }
    else
    {
        $str_to = $to;
    }

    if (is_array($from))
    {
        $str_from = '';
        foreach($from as $from_detail)
        {
            if ($str_from != '') $str_from .= ', ';
            $str_from .= "{$from_detail['name']} <{$from_detail['address']}>";
        }
    }
    else
    {
        $str_from = $from;
    }

    if (isset($cc) && is_array($cc))
    {
        $str_cc = '';
        foreach($cc as $cc_detail)
        {
            if ($str_cc != '') $str_cc .= ', ';
            $str_cc .= "{$cc_detail['name']} <{$cc_detail['address']}>";
        }
    }

    if (isset($bcc) && is_array($bcc))
    {
        $str_bcc = '';
        foreach($bcc as $bcc_detail)
        {
            if ($str_bcc != '') $str_bcc .= ', ';
            $str_bcc .= "{$bcc_detail['name']} <{$bcc_detail['address']}>";
        }
    }

    /* configure Content-type to HTML */
    $headers  = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=iso-8859-1\r\n";

    // add "to" to headers
    //if (isset($str_to)) $headers .= "To: $str_to \r\n";
    // add "from" to headers
    if (isset($str_from)) $headers .= "From: $str_from \r\n";
    // add "cc" to headers
    if (isset($str_cc)) $headers .= "Cc: $str_cc \r\n";
    // add "bcc" to headers
    if (isset($str_bcc)) $headers .= "Bcc: $str_bcc \r\n";

    // send mail
    mail($str_to, $subject, $message, $headers);

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


function ploopi_checkdns($hostname, $rectype = '')
{
    if(!empty($hostname))
    {
        if( $rectype == '' ) $recType = "MX";

        exec("nslookup -type=$rectype $hostname", $result);

        // check each line to find the one that starts with the host
        // name. If it exists then the function succeeded.
        foreach ($result as $line)
        {
            if(eregi("^$hostname",$line))
            {
                return true;
            }
        }
        // otherwise there was no mail handler for the domain
        return false;
    }

    return false;
}

function ploopi_verifyemail($email)
{
    // check @ in email
    $array = explode("@", $email);
    if (sizeof($array) != 2) return false;

    $maildomain = $array[1];

    // check . in maildomain
    if (!strstr($maildomain, ".")) return false;

    // check dns
    if (!ploopi_checkdns($maildomain, "MX")) return false;

    return true;
}

?>