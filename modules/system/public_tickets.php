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

if (isset($_GET['filtertype'])) $_SESSION['tickets']['filtertype'] = $_GET['filtertype'];
if (!isset($_SESSION['tickets']['filtertype'])) $_SESSION['tickets']['filtertype'] = 'all';
$filtertype = $_SESSION['tickets']['filtertype'];

if (isset($_GET['sort'])) $_SESSION['tickets']['sort'] = $_GET['sort'];
if (!isset($_SESSION['tickets']['sort'])) $_SESSION['tickets']['sort'] = 'datereply';
$sort = $_SESSION['tickets']['sort'];

echo $skin->create_pagetitle(_SYSTEM_LABEL_TICKETS);
echo $skin->open_simplebloc();

$where = '';
switch($filtertype)
{
    case 'all';
    break;

    case 'mytickets':
        $where = " AND u.id = {$_SESSION['ploopi']['userid']} ";
    break;

    case 'tovalidate':
        $where = " AND t.id_user <> {$_SESSION['ploopi']['userid']} AND t.needed_validation > 0 AND t.status < "._PLOOPI_TICKETS_DONE;
    break;

    case 'waitingvalidation':
        $where = " AND t.id_user = {$_SESSION['ploopi']['userid']} AND t.needed_validation > 0 AND t.status < "._PLOOPI_TICKETS_DONE;
    break;
}

$orderby = '';
switch($sort)
{
    case 'dateticket':
        $orderby = " ORDER BY t.timestp DESC ";
    break;

    case 'datereply':
        $orderby = " ORDER BY t.lastreply_timestp DESC, t.timestp DESC ";
    break;
}

// vérification du droit de visualisation des personnes concernées
include_once "./modules/system/class_user.php";
$usr=new user();
$usr->open($_SESSION['ploopi']['userid']);
// liste des users visibles par le user courant
$lstusers=$usr->getusersgroup();
// liste des espaces de travail rattachés
$lstworkspace=array_keys($usr->getworkspaces());


$sql =  "
        SELECT      t.id, t.title,
                    t.message,
                    t.needed_validation,
                    t.delivery_notification,
                    t.timestp,
                    t.lastreply_timestp,
                    t.lastedit_timestp,
                    t.object_label,
                    t.id_object,
                    t.id_record,
                    t.id_module,
                    t.id_workspace,
                    t.parent_id,
                    t.root_id,
                    t.count_read,
                    t.count_replies,
                    t.id_user AS sender_uid,

                    u.id as sender_user_id,
                    IFNULL(u.login, 'system') as login,
                    IFNULL(u.firstname, 'Système') as firstname,
                    IFNULL(u.lastname, '') as lastname,

                    tw.notify,

                    td.id_user,

                    m.label as module_name,
                    mt.label as module_type,
                    
                    o.label as object_name,
                    o.script

        FROM        ploopi_ticket t

        INNER JOIN  ploopi_ticket_dest td
        ON          td.id_ticket = t.id

        LEFT JOIN   ploopi_ticket_watch tw
        ON          tw.id_ticket = t.id
        AND         tw.id_user = {$_SESSION['ploopi']['userid']}

        LEFT JOIN   ploopi_user u
        ON          u.id = t.id_user

        LEFT JOIN   ploopi_module m
        ON          t.id_module = m.id

        LEFT JOIN   ploopi_module_type mt
        ON          mt.id = m.id_module_type
        
        LEFT JOIN   ploopi_mb_object o
        ON          t.id_object = o.id
        AND         m.id_module_type = o.id_module_type

        WHERE       ((t.id_user = {$_SESSION['ploopi']['userid']} AND t.deleted = 0) OR (td.id_user = {$_SESSION['ploopi']['userid']} AND td.deleted = 0))

        {$where}

        group by t.id

        {$orderby}
        ";

$rs = $db->query($sql);

$tickets = array();

while ($fields = $db->fetchrow($rs))
{
    $fields['status'] = _PLOOPI_TICKETS_DONE;
    if (!isset($tickets[$fields['id']])) $tickets[$fields['id']] = $fields;
}

$ticket_list = implode(',',array_keys($tickets));

if (!empty($ticket_list))
{
    // get dest users & state for all tickets
    $sql =  "
            SELECT      td.id_ticket,
                        ts.status,
                        ts.timestp,
                        u.id,
                        u.login,
                        u.firstname,
                        u.lastname

            FROM        ploopi_ticket_dest td

            LEFT JOIN   ploopi_ticket_status ts
            ON          ts.id_ticket = td.id_ticket
            AND         ts.id_user = td.id_user

            LEFT JOIN   ploopi_user u
            ON          td.id_user = u.id

            WHERE       td.id_ticket IN ({$ticket_list})
            ";

    $rs = $db->query($sql);

    while ($fields = $db->fetchrow($rs))
    {
        if (!isset($tickets[$fields['id_ticket']]['dest'][$fields['id']]))
        {
            $tickets[$fields['id_ticket']]['dest'][$fields['id']] = (!is_null($fields['login'])) ? array( 'login' => $fields['login'], 'firstname' => $fields['firstname'], 'lastname' => $fields['lastname']) : array( 'login' => '<i>Supprimé</i>', 'firstname' => '', 'lastname' => '<i>Supprimé</i>');
        }

        $tickets[$fields['id_ticket']]['dest'][$fields['id']]['status'][$fields['status']] = $fields['timestp'];

        if (empty($fields['status'])) $fields['status'] = _PLOOPI_TICKETS_NONE;

        if (empty($tickets[$fields['id_ticket']]['dest'][$fields['id']]['final_status'])) $tickets[$fields['id_ticket']]['dest'][$fields['id']]['final_status'] = _PLOOPI_TICKETS_NONE;

        if ($fields['status'] > $tickets[$fields['id_ticket']]['dest'][$fields['id']]['final_status']) $tickets[$fields['id_ticket']]['dest'][$fields['id']]['final_status'] = $fields['status'];
    }

    foreach($tickets as $ticket)
    {
        foreach($ticket['dest'] as $dest)
        {
            if ($dest['final_status'] < $tickets[$ticket['id']]['status']) $tickets[$ticket['id']]['status'] = $dest['final_status'];
        }
    }
}

if ($filtertype == 'tovalidate' || $filtertype == 'waitingvalidation')
{
    //echo "test";
}
?>


<div id="system_tickets_titlebar">
    <?
    $nb_tickets_page = 20;
    $numrows = sizeof($tickets);
    $nbpage = ($numrows - $numrows % $nb_tickets_page) / $nb_tickets_page + ($numrows % $nb_tickets_page > 0);
    if (isset($_GET['page'])) $page = $_GET['page'];
    else $page = 1;

    if ($nbpage>0)
    {
        ?>
        <div style="float:right;">
            <div style="float:left;">page :&nbsp;</div>
            <?
            for ($p = 1; $p <= $nbpage; $p++)
            {
                ?>
                <a class="system_tickets_page<? if ($p==$page) echo '_sel'; ?>" href="<? echo "{$scriptenv}?ploopi_mainmenu="._PLOOPI_MENU_MYWORKSPACE."&op=tickets&page={$p}"; ?>"><? echo $p; ?></a>
                <?
            }
            ?>
        </div>
        <?
    }
    ?>


    <div>
        <a id="system_tickets_new" href="javascript:void(0);" onclick="javascript:ploopi_tickets_new(event, null, null, null, true);">
            <p class="ploopi_va">
               <img src="<? echo $_SESSION['ploopi']['template_path']; ?>/img/system/email.gif" /><span>&nbsp;Nouveau ticket</span>
            </p>
        </a>
        
        <div style="clear:both;">
            <b>Filtre:</b>
            <a <? if ($filtertype=='all') echo 'style="font-weight:bold;"'; ?> href="<? echo ploopi_urlencode("{$scriptenv}?ploopi_mainmenu="._PLOOPI_MENU_MYWORKSPACE."&op=tickets&filtertype=all"); ?>">Tous</a>&nbsp;-
            <a <? if ($filtertype=='mytickets') echo 'style="font-weight:bold;"'; ?> href="<? echo ploopi_urlencode("{$scriptenv}?ploopi_mainmenu="._PLOOPI_MENU_MYWORKSPACE."&op=tickets&filtertype=mytickets"); ?>">Mes tickets</a>&nbsp;-
            <a <? if ($filtertype=='waitingvalidation') echo 'style="font-weight:bold;"'; ?> href="<? echo ploopi_urlencode("{$scriptenv}?ploopi_mainmenu="._PLOOPI_MENU_MYWORKSPACE."&op=tickets&filtertype=waitingvalidation"); ?>">Tickets en attente de validation</a>&nbsp;-
            <a <? if ($filtertype=='tovalidate') echo 'style="font-weight:bold;"'; ?> href="<? echo ploopi_urlencode("{$scriptenv}?ploopi_mainmenu="._PLOOPI_MENU_MYWORKSPACE."&op=tickets&filtertype=tovalidate"); ?>">Tickets à valider</a>
        </div>
        <div>
            <b>Tri:</b>
            <a <? if ($sort=='dateticket') echo 'style="font-weight:bold;"'; ?> href="<? echo ploopi_urlencode("{$scriptenv}?ploopi_mainmenu="._PLOOPI_MENU_MYWORKSPACE."&op=tickets&sort=dateticket"); ?>">Date des messages</a>&nbsp;-
            <a <? if ($sort=='datereply') echo 'style="font-weight:bold;"'; ?> href="<? echo ploopi_urlencode("{$scriptenv}?ploopi_mainmenu="._PLOOPI_MENU_MYWORKSPACE."&op=tickets&sort=datereply"); ?>">Date des réponses</a>&nbsp;
        </div>
    </div>


</div>

<div class="system_tickets_row_title">

    <div style="float:left;width:20px;text-align:center;"><input type="checkbox" class="checkbox" onclick="javascript:ploopi_checkall(document.form_tickets_delete,'tickets_delete_id', this.checked);"></div>
    <div style="float:left;width:20px;">&nbsp;</div>
    <div style="float:left;width:20px;">&nbsp;</div>
    <div style="float:right;width:160px;">Posté le</div>
    <div style="float:right;width:180px;">Emetteur</div>
    <div style="float:right;width:160px;">Derniere réponse</div>
    <div style="float:right;width:40px;">Vu</div>
    <div style="float:right;width:40px;">Rep</div>
    <div style="float:right;width:20px;">A</div>
    <div>Titre</div>
</div>

<form name="form_tickets_delete" action="<? echo $scriptenv; ?>" method="post">
<input type="hidden" name="ploopi_op" value="tickets_delete">
    <?

    $todaydate = ploopi_timestamp2local(ploopi_createtimestamp());
    if (!sizeof($tickets))
    {
        $color = (!isset($color) || $color == $skin->values['bgline2']) ? $skin->values['bgline1'] : $skin->values['bgline2'];
        ?>
        <div class="system_tickets_row" style="background-color:<? echo $color; ?>;text-align:center;">
        Aucun ticket
        </div>
        <?
    }

    reset($tickets);
    for ($i=0; $i<($page-1)*$nb_tickets_page; $i++) next($tickets);

    $ticket = current($tickets);
    for  ($i=0; $i<$nb_tickets_page && !empty($ticket); $i++)
    {
        $fields = $ticket;
        
        $object_script = str_replace(
                                        array(
                                            '<IDRECORD>',
                                            '<IDMODULE>',
                                            '<IDWORKSPACE>'
                                        ),
                                        array(
                                            $fields['id_record'],
                                            $fields['id_module'],
                                            $fields['id_workspace']
                                        ),
                                        $fields['script']
                            );

        $color = (!isset($color) || $color == $skin->values['bgline2']) ? $skin->values['bgline1'] : $skin->values['bgline2'];
        $timestp = ploopi_timestamp2local($fields['timestp']);
        $timestp['date'] = ($todaydate['date'] == $timestp['date'])  ? "Aujourd'hui" : $timestp['date'];
        $fields['lastreply_timestp'];
        $lastreply_timestp = ploopi_timestamp2local($fields['lastreply_timestp']);
        $lastreply_timestp['date'] = ($todaydate['date'] == $lastreply_timestp['date'])  ? "Aujourd'hui" : $lastreply_timestp['date'];

        ?>
        <div class="system_tickets_row" style="background-color:<? echo $color; ?>;">
            <div class="system_tickets_head">
                <div class="system_tickets_user_puce">
                    <?
                    if (!($fields['needed_validation'] > 0 && $fields['sender_uid'] != $_SESSION['ploopi']['userid'] && !isset($tickets[$fields['id']]['dest'][$_SESSION['ploopi']['userid']]['status'][_PLOOPI_TICKETS_DONE])))
                    {
                        ?><input type="checkbox" class="checkbox" name="tickets_delete_id[]" value="<? echo $fields['id']; ?>"><?
                    }
                    ?>
                </div>
                <div style="overflow:auto;" onclick="javascript:system_tickets_display(<? echo $fields['id']; ?>,<? echo (empty($fields['status'])) ? 0 : 1; ?>,1,'<? echo $_SESSION['ploopi']['template_path']; ?>');">
                    <div class="system_tickets_user_puce" id="watch_notify_<? echo $fields['id']; ?>">
                    <?
                    if ($fields['notify'] == '0')
                    {
                        ?><img src="<? echo $_SESSION['ploopi']['template_path']; ?>/img/system/email_read.png" /><?
                    }
                    else
                    {
                        ?><img src="<? echo $_SESSION['ploopi']['template_path']; ?>/img/system/email_new.png" /><?
                    }
                    ?>
                    </div>
                    <div class="system_tickets_user_puce"><?

                    //if ($fields['needed_validation'] >  0 && $fields['sender_uid'] == $_SESSION['ploopi']['userid'])

                    if ($fields['sender_uid'] == $_SESSION['ploopi']['userid'])
                    {
                        $username = "<span style=\"font-style:italic;\">{$fields['firstname']} {$fields['lastname']}</span>";
                        switch($fields['status'])
                        {
                            case _PLOOPI_TICKETS_NONE:
                                $puce = 'red';
                            break;

                            case _PLOOPI_TICKETS_OPENED:
                                $puce = 'blue';
                            break;

                            case _PLOOPI_TICKETS_DONE:
                                $puce = 'green';
                            break;
                        }

                        ?><img src="<? echo $_SESSION['ploopi']['template_path']; ?>/img/system/p_<? echo $puce; ?>.png" /><?
                    }
                    else
                    {
                        $username = "{$fields['firstname']} {$fields['lastname']}";
                        
                        if (is_null($fields['sender_user_id'])) // system ticket
                        {
                            $username = "<i>{$username}</i>";
                        }
                        
                        if ($fields['needed_validation'] == 1 && $fields['sender_uid'] != $_SESSION['ploopi']['userid'] && !isset($tickets[$fields['id']]['dest'][$_SESSION['ploopi']['userid']]['status'][_PLOOPI_TICKETS_DONE]))
                        {
                            ?><img src="<? echo $_SESSION['ploopi']['template_path']; ?>/img/system/attention.png" alt="vous devez valider ce message !"><?
                        }
                    }
                    ?></div>

                    <div class="system_tickets_date"><? echo $timestp['date']; ?> à <? echo $timestp['time']; ?></div>
                    <div class="system_tickets_sender"><? echo $username; ?></div>
                    <div class="system_tickets_date"><? echo $lastreply_timestp['date']; ?> à <? echo $lastreply_timestp['time']; ?></div>
                    <div class="system_tickets_count"><? echo $fields['count_read']; ?></div>
                    <div class="system_tickets_count"><? echo $fields['count_replies']; ?></div>
                    <div class="system_tickets_attachment">
                        <?
                        if ($fields['id_record'] != '')
                        {
                            ?>
                            <img src="<? echo $_SESSION['ploopi']['template_path']; ?>/img/tickets/attachment.png" />
                            <?
                        }
                        ?>
                    </div>
                    <div class="system_tickets_title" id="tickets_title_<? echo $fields['id']; ?>" <? if (empty($fields['status'])) echo 'style="font-weight:bold;"'; ?>>
                        <? echo ploopi_strcut($fields['title'],100); ?>
                    </div>
                </div>
            </div>


            <div class="system_tickets_detail"  id="tickets_detail_<? echo $fields['id'];?>">
                <div class="system_tickets_detail_content">
                    <?
                    if (isset($fields['dest']))
                    {
                        ?>
                        <div class="system_tickets_user">
                            <b>Destinataires:</b>
                            <?
                            foreach ($fields['dest'] as $iddest => $dest)
                            {
                                $puce = '';
                                $strdate = '';

                                if ($done = isset($tickets[$fields['id']]['dest'][$iddest]['status'][_PLOOPI_TICKETS_DONE]))
                                {
                                    $ldate = ploopi_timestamp2local($tickets[$fields['id']]['dest'][$iddest]['status'][_PLOOPI_TICKETS_DONE]);
                                    $strdate = "<br />(validé le {$ldate['date']} à {$ldate['time']})";
                                    $puce = 'green';
                                }
                                elseif ($opened = isset($tickets[$fields['id']]['dest'][$iddest]['status'][_PLOOPI_TICKETS_OPENED]))
                                {
                                    $ldate = ploopi_timestamp2local($tickets[$fields['id']]['dest'][$iddest]['status'][_PLOOPI_TICKETS_OPENED]);
                                    $strdate = "<br />(lu le {$ldate['date']} à {$ldate['time']})";
                                    $puce = 'blue';
                                }
                                else
                                {
                                    $puce = 'red';
                                    $strdate = '';
                                }

                                ?>
                                    <div class="system_tickets_user_detail">
                                        <div style="clear:both;float:left;"><img src="<? echo $_SESSION['ploopi']['template_path']; ?>/img/system/p_<? echo $puce; ?>.png"></div>
                                        <div style="float:left;"><? echo "{$dest['firstname']} {$dest['lastname']}{$strdate}"; ?></div>
                                    </div>
                                <?
                            }
                            ?>
                        </div>
                        <?
                    }
                    ?>

                    <div class="system_tickets_message">
                        <?
                        echo ploopi_make_links($fields['message']);
                        if ($fields['lastedit_timestp'])
                        {
                            $lastedit_local = ploopi_timestamp2local($fields['lastedit_timestp']);
                            echo "<i>Dernière modification le {$lastedit_local['date']} à {$lastedit_local['time']}</i>";
                        }

                        if ($fields['needed_validation'] > 0 && $_SESSION['ploopi']['userid'] == $tickets[$fields['id']]['id_user'] && !isset($tickets[$fields['id']]['dest'][$_SESSION['ploopi']['userid']]['status'][_PLOOPI_TICKETS_DONE]))
                        {
                            ?>
                            <a href="<? echo ploopi_urlencode("admin-light.php?ploopi_op=tickets_validate&ticket_id={$fields['id']}"); ?>" class="system_tickets_tovalidate">
                                <div class="system_tickets_tovalidate_msg">
                                    <img src="<? echo $_SESSION['ploopi']['template_path']; ?>/img/system/attention.png">
                                    <span>L'expéditeur vous demande de valider ce message</span>
                                </div>
                                <div class="system_tickets_tovalidate_button">
                                    <p class="ploopi_va">
                                    <img src="<? echo $_SESSION['ploopi']['template_path']; ?>/img/system/email_validate.png">Valider
                                    </p>
                                </div>
                            </a>
                            <?
                        }
                        ?>
                    </div>
                </div>
                <?
                if ($fields['id_record'] != '')
                {
                    // on cherche si on fonction de validation d'objet existe pour ce module
                    ploopi_init_module($fields['module_type']);
                    
                    $boolRecordIsEnabled = true;
                    $funcRecordIsEnabled = "{$fields['module_type']}_record_isenabled";
                    if (function_exists($funcRecordIsEnabled))
                    {
                        // si la fonction existe, on l'appelle pour chaque enregistrement
                        $boolRecordIsEnabled = $funcRecordIsEnabled($fields['id_object'], $fields['id_record'], $fields['id_module']);
                    }
                    ?>
                    <div class="system_tickets_buttons">
                        <p class="ploopi_va">
                            <img src="<? echo $_SESSION['ploopi']['template_path']; ?>/img/tickets/attachment.png" />
                            <span><strong>Objet lié</strong>: </span>
                            <?
                            if($boolRecordIsEnabled)
                            {
                                ?>
                                <a href="<? echo ploopi_urlencode("{$scriptenv}?ploopi_mainmenu=1&{$object_script}"); ?>"><img src="<? echo $_SESSION['ploopi']['template_path']; ?>/img/system/link.png"><? echo "{$fields['module_name']} / {$fields['object_name']} <b>\"{$fields['object_label']}\"</b>"; ?></a>
                                <?
                            }
                            else
                            {
                                ?>
                                <span style="font-weight:bold;color:#a60000;">Vous ne pouvez pas ouvrir cet objet</span>
                                <?
                            }
                            ?>
                        </p>
                    </div>
                    <?
                }
                ?>
                <div class="system_tickets_buttons">
                    <p class="ploopi_va">
                        <a href="javascript:void(0);" onclick="javascript:ploopi_showpopup('','400',event,'click','system_popupticket');ploopi_xmlhttprequest_todiv('admin-light.php','ploopi_op=tickets_replyto&ticket_id=<? echo $fields['id']; ?>','','system_popupticket');"><img src="<? echo $_SESSION['ploopi']['template_path']; ?>/img/system/email_reply.png">Répondre</a>
                        <a href="javascript:void(0);" onclick="javascript:ploopi_showpopup('','400',event,'click','system_popupticket');ploopi_xmlhttprequest_todiv('admin-light.php','ploopi_op=tickets_replyto&ticket_id=<? echo $fields['id']; ?>&quoted=true','','system_popupticket');"><img src="<? echo $_SESSION['ploopi']['template_path']; ?>/img/system/email_quote.png">Citer</a>
                        <?
                        if ($fields['sender_uid'] == $_SESSION['ploopi']['userid'])
                        {
                            ?>
                            <a href="javascript:void(0);" onclick="javascript:ploopi_showpopup('','400',event,'click','system_popupticket');ploopi_xmlhttprequest_todiv('admin-light.php','ploopi_op=tickets_modify&ticket_id=<? echo $fields['id']; ?>','','system_popupticket');"><img src="<? echo $_SESSION['ploopi']['template_path']; ?>/img/system/email_modify.png">Modifier</a>
                            <?
                        }

                        if (!($fields['needed_validation'] > 0 && $fields['sender_uid'] != $_SESSION['ploopi']['userid'] && !isset($tickets[$fields['id']]['dest'][$_SESSION['ploopi']['userid']]['status'][_PLOOPI_TICKETS_DONE])))
                        {
                            ?>
                            <a href="javascript:ploopi_confirmlink('<? echo "{$scriptenv}?ploopi_op=tickets_delete&ticket_id={$fields['id']}"; ?>','Êtes-vous certain de vouloir supprimer ce ticket ?');"><img src="<? echo $_SESSION['ploopi']['template_path']; ?>/img/system/email_delete.png">Supprimer</a>
                            <?
                        }
                        ?>
                    </p>
                </div>
                <div id="tickets_responses_<? echo $fields['id'];?>"></div>
            </div>
        </div>
        <?
        next($tickets);
        $ticket = current($tickets);
    }
    ?>
</form>

<p class="ploopi_va" style="padding:2px;">
    <img src="<? echo $_SESSION['ploopi']['template_path']; ?>/img/system/arrow_all.png" /><span>&nbsp;</span><a href="javascript:void(0);" onclick="javascript:if (confirm('Êtes-vous certain de vouloir supprimer les tickets cochés ?')) document.form_tickets_delete.submit();">Supprimer les tickets sélectionnés</a>
    <span>&nbsp;&nbsp;//&nbsp;Légende:&nbsp;&nbsp;</span>
    <img src="<? echo $_SESSION['ploopi']['template_path']; ?>/img/system/email_read.png" /><span>&nbsp;déjà vu&nbsp;&nbsp;</span>
    <img src="<? echo $_SESSION['ploopi']['template_path']; ?>/img/system/email_new.png" /><span>&nbsp;nouveau&nbsp;&nbsp;</span>
    <img src="<? echo $_SESSION['ploopi']['template_path']; ?>/img/system/attention.png" /><span>&nbsp;à valider&nbsp;&nbsp;</span>
    <img src="<? echo $_SESSION['ploopi']['template_path']; ?>/img/system/p_red.png" /><span>&nbsp;non lu&nbsp;&nbsp;</span>
    <img src="<? echo $_SESSION['ploopi']['template_path']; ?>/img/system/p_blue.png" /><span>&nbsp;lu&nbsp;&nbsp;</span>
    <img src="<? echo $_SESSION['ploopi']['template_path']; ?>/img/system/p_green.png" /><span>&nbsp;validé&nbsp;&nbsp;</span>
    <img src="<? echo $_SESSION['ploopi']['template_path']; ?>/img/tickets/attachment.png" /><span>&nbsp;objet lié&nbsp;&nbsp;</span>
</p>
<? echo $skin->close_simplebloc(); ?>
