<?php
/*
    Copyright (c) 2007-2026 Ovensia
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
 * Mon espace › Messages, gabarits de génération 4 : boîtes, tri, liste des messages
 * dépliables (destinataires, message, réponses), actions.
 *
 * public_tickets.php charge les messages ($tickets, $filtertype, $sort, $strSearch) puis inclut
 * ce fichier. Composition et réponse : fenêtres historiques (ploopi.tickets.create,
 * ploopi.tickets.open) ; ouverture et réponses : opérations tickets_open et
 * tickets_open_responses (include/p4.js).
 *
 * @package system
 * @subpackage public
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

use ploopi\system\ui;

$intPerPage = 20;
$intPages = max(1, (int) ceil(sizeof($tickets) / $intPerPage));
$intPage = (isset($_GET['page']) && is_numeric($_GET['page'])) ? min(max(1, (int) $_GET['page']), $intPages) : 1;

$arrToday = ploopi\date::timestamp2local(ploopi\date::createtimestamp());
$funcDate = function($strTs) use ($arrToday)
{
    if (empty($strTs)) return '';
    $arrLocal = ploopi\date::timestamp2local($strTs);
    return ($arrLocal['date'] == $arrToday['date'] ? _SYSTEM_MSG_TODAY : $arrLocal['date']).' '.substr($arrLocal['time'], 0, 5);
};

$intUserId = $_SESSION['ploopi']['userid'];

echo ui::pagehead(_PLOOPI_LABEL_MYTICKETS, _SYSTEM_MSG_SUBTITLE, array(
    array('label' => _PLOOPI_LABEL_NEWTICKET, 'icon' => 'send', 'class' => 'primary', 'attrs' => array('data-p4-ticket-new' => true))
));

$arrBoxes = array(
    'incomingbox' => array(_SYSTEM_LABEL_TICKETS_INCOMINGBOX, ploopi\crypt::urlencode('admin.php?op=tickets&filtertype=incomingbox'), 'inbox'),
    'sendbox' => array(_SYSTEM_LABEL_TICKETS_SENDBOX, ploopi\crypt::urlencode('admin.php?op=tickets&filtertype=sendbox'), 'send'),
    'all' => array(_SYSTEM_LABEL_TICKETS_ALL, ploopi\crypt::urlencode('admin.php?op=tickets&filtertype=all'), 'archive')
);

$arrSorts = array(
    ploopi\crypt::urlencode('admin.php?op=tickets&sort=datereply') => _SYSTEM_MSG_SORT_REPLY,
    ploopi\crypt::urlencode('admin.php?op=tickets&sort=dateticket') => _SYSTEM_MSG_SORT_TICKET
);
?>
<div class="p4-row p4-wrap p4-ui p4-tickets-bar">
    <?php echo ui::seg($arrBoxes, $filtertype, _SYSTEM_MSG_BOXES); ?>
    <form class="p4-row p4-tickets-search" action="<?php echo ploopi\crypt::urlencode('admin.php?op=tickets'); ?>" method="post" role="search">
        <div class="p4-input-icon">
            <?php echo ui::icon('search', 'p4-icon-sm'); ?>
            <input class="p4-input" type="search" name="tickets_search" value="<?php echo ui::e($strSearch); ?>" maxlength="100" placeholder="<?php echo ui::e(_SYSTEM_MSG_SEARCH); ?>" aria-label="<?php echo ui::e(_SYSTEM_MSG_SEARCH); ?>" />
        </div>
        <?php if ($strSearch !== '') echo ui::button(array('label' => _SYSTEM_MSG_SEARCH_RESET, 'icon' => 'x', 'class' => 'ghost sm', 'href' => ploopi\crypt::urlencode('admin.php?op=tickets&tickets_search_reset=1'))); ?>
    </form>
    <span class="p4-grow"></span>
    <label class="p4-sr-only" for="p4_tickets_sort"><?php echo ui::e(_SYSTEM_MSG_SORT); ?></label>
    <?php echo ui::select('p4_tickets_sort', $arrSorts, $sort == 'dateticket' ? key(array_slice($arrSorts, 1, 1, true)) : key($arrSorts), array('data-p4-nav' => true, 'class' => 'p4-select p4-select-inline')); ?>
</div>
<?php
if ($strSearch !== '') echo '<p class="p4-text-sm p4-muted p4-tickets-found p4-ui" role="status">'.ui::e(str_replace(array('<NB>', '<TERM>'), array(sizeof($tickets), $strSearch), _SYSTEM_MSG_SEARCH_FOUND)).'</p>';

echo ui::card_open('', '', array(), array('class' => 'p4-card-flush p4-tickets-card'));

if (empty($tickets))
{
    if ($strSearch !== '') echo '<div data-p4-tickets-nomatch>'.ui::emptystate('search', _SYSTEM_MSG_SEARCH_NONE).'</div>';
    else echo ui::emptystate('mail-opened', _SYSTEM_LABEL_NOTICKETS);
    echo ui::card_close();
    echo ui::script();
    return;
}
?>
<form action="<?php echo ploopi\crypt::urlencode('admin.php'); ?>" method="post" data-p4-confirm="<?php echo ui::e(_PLOOPI_LABEL_TICKET_CONFIRMDELETE_CHECKED); ?>">
    <input type="hidden" name="ploopi_op" value="tickets_delete" />
    <ul class="p4-tickets">
        <?php
        foreach(array_slice($tickets, ($intPage - 1) * $intPerPage, $intPerPage, true) as $intId => $arrTicket)
        {
            $intId = (int) $arrTicket['id'];
            $booMine = $arrTicket['sender_uid'] == $intUserId;
            $booToValidate = $arrTicket['needed_validation'] > 0 && !$booMine && !isset($arrTicket['dest'][$intUserId]['status'][ploopi\ticket::DONE]);
            // Comme l'écran historique : nouveau tant que le suivi n'est pas à 0 (ou absent)
            $booNew = (string) $arrTicket['notify'] !== '0';
            $booUnread = empty($arrTicket['status']);
            $booSystem = is_null($arrTicket['sender_user_id']);

            $strSender = trim("{$arrTicket['firstname']} {$arrTicket['lastname']}");
            $strInitials = $booSystem ? 'P' : mb_strtoupper(mb_substr($arrTicket['firstname'], 0, 1).mb_substr($arrTicket['lastname'], 0, 1));

            // État de lecture par les destinataires (messages envoyés)
            $arrStates = array(ploopi\ticket::NONE => array('ko', _SYSTEM_MSG_STATE_UNREAD), ploopi\ticket::OPENED => array('info', _SYSTEM_MSG_STATE_READ), ploopi\ticket::DONE => array('ok', _SYSTEM_MSG_STATE_DONE));
            $arrState = isset($arrStates[$arrTicket['status']]) ? $arrStates[$arrTicket['status']] : $arrStates[ploopi\ticket::NONE];
            ?>
            <li class="p4-ticket<?php if ($booNew) echo ' p4-ticket-new'; ?>" data-p4-ticket-item="<?php echo $intId; ?>">
                <div class="p4-ticket-head">
                    <span class="p4-ticket-chk">
                        <?php if (!$booToValidate) { ?><input type="checkbox" name="tickets_delete_id[]" value="<?php echo $intId; ?>" aria-label="<?php echo ui::e(_SYSTEM_USERS_SELECT.' : '.strip_tags($arrTicket['title'])); ?>" /><?php } ?>
                    </span>
                    <button type="button" class="p4-ticket-toggle" data-p4-ticket="<?php echo $intId; ?>" aria-expanded="false" aria-controls="tickets_detail_<?php echo $intId; ?>">
                        <span class="p4-avatar p4-av-<?php echo $booSystem ? 0 : ($strInitials === '' ? 0 : ord($strInitials[0])) % 5; ?>"><?php echo ui::e($strInitials); ?></span>
                        <span class="p4-ticket-main">
                            <span class="p4-ticket-from"><?php echo ui::e($booMine ? _SYSTEM_MSG_FROMME : ($booSystem ? _SYSTEM_MSG_FROMSYSTEM : $strSender)); ?><?php if ($booMine && !empty($arrTicket['dest'])) echo ' '.ui::icon('arrow-right', 'p4-icon-xs').' '.ui::e(str_replace('<NB>', sizeof($arrTicket['dest']), _SYSTEM_MSG_RECIPIENTS_COUNT)); ?></span>
                            <span class="p4-ticket-title<?php if ($booUnread) echo ' p4-unread'; ?>" id="tickets_title_<?php echo $intId; ?>"><?php echo ui::e(html_entity_decode(strip_tags($arrTicket['title']), ENT_QUOTES, 'UTF-8')); ?></span>
                        </span>
                        <span class="p4-ticket-meta">
                            <?php
                            if ($booToValidate) echo ui::badge(_SYSTEM_MSG_TOVALIDATE, 'warning');
                            if ($booMine) echo '<span class="p4-dot p4-dot-'.$arrState[0].'" title="'.ui::e($arrState[1]).'"></span>';
                            if ($arrTicket['id_record'] != '') echo ui::icon('link', 'p4-icon-sm p4-muted');
                            if ($arrTicket['count_replies'] > 0) echo '<span class="p4-ticket-count" title="'.ui::e(_SYSTEM_MSG_REPLIES).'">'.ui::icon('arrow-back-up', 'p4-icon-xs').(int) $arrTicket['count_replies'].'</span>';
                            ?>
                            <time class="p4-ticket-date"><?php echo ui::e($funcDate($sort == 'dateticket' || empty($arrTicket['lastreply_timestp']) ? $arrTicket['timestp'] : $arrTicket['lastreply_timestp'])); ?></time>
                            <span class="p4-ticket-unreaddot" id="watch_notify_<?php echo $intId; ?>" title="<?php echo ui::e(_SYSTEM_MSG_NEW); ?>"></span>
                        </span>
                    </button>
                </div>

                <div class="p4-ticket-detail" id="tickets_detail_<?php echo $intId; ?>" hidden>
                    <?php
                    if (!empty($arrTicket['dest']))
                    {
                        echo '<div class="p4-ticket-dest"><span class="p4-text-xs p4-muted">'.ui::e(_PLOOPI_LABEL_TICKET_RECIPIENTS).'</span><div class="p4-chips">';
                        foreach($arrTicket['dest'] as $intDest => $arrDest)
                        {
                            if (isset($arrDest['status'][ploopi\ticket::DONE])) { $arrLocal = ploopi\date::timestamp2local($arrDest['status'][ploopi\ticket::DONE]); $strState = 'ok'; $strTitle = str_replace(array('<DATE>', '<TIME>'), array($arrLocal['date'], $arrLocal['time']), _SYSTEM_MSG_DONE_ON); }
                            elseif (isset($arrDest['status'][ploopi\ticket::OPENED])) { $arrLocal = ploopi\date::timestamp2local($arrDest['status'][ploopi\ticket::OPENED]); $strState = 'info'; $strTitle = str_replace(array('<DATE>', '<TIME>'), array($arrLocal['date'], $arrLocal['time']), _SYSTEM_MSG_READ_ON); }
                            else { $strState = 'ko'; $strTitle = _SYSTEM_MSG_STATE_UNREAD; }

                            echo '<span class="p4-badge" title="'.ui::e($strTitle).'"><span class="p4-dot p4-dot-'.$strState.'"></span>'.ui::e(strip_tags(trim("{$arrDest['firstname']} {$arrDest['lastname']}"))).'</span>';
                        }
                        echo '</div></div>';
                    }
                    ?>
                    <div class="p4-ticket-message p4-legacy"><?php echo ploopi\str::make_links($arrTicket['message']); ?></div>
                    <?php
                    if ($arrTicket['lastedit_timestp'])
                    {
                        $arrLocal = ploopi\date::timestamp2local($arrTicket['lastedit_timestp']);
                        echo '<p class="p4-text-xs p4-muted">'.ui::e(str_replace(array('<DATE>', '<TIME>'), array($arrLocal['date'], $arrLocal['time']), _SYSTEM_MSG_EDITED_ON)).'</p>';
                    }

                    // Validation demandée par l'expéditeur
                    if ($arrTicket['needed_validation'] > 0 && $intUserId == $arrTicket['id_user'] && !isset($arrTicket['dest'][$intUserId]['status'][ploopi\ticket::DONE]))
                    {
                        echo ui::callout('warning', ui::e(_SYSTEM_MSG_VALIDATE_ASKED).' '.ui::button(array('label' => _SYSTEM_MSG_VALIDATE, 'icon' => 'check', 'class' => 'primary sm', 'href' => ploopi\crypt::urlencode("admin-light.php?ploopi_op=tickets_validate&ticket_id={$intId}"))));
                    }

                    // Objet lié
                    if ($arrTicket['id_record'] != '')
                    {
                        ploopi\module::init($arrTicket['module_type']);

                        $booEnabled = true;
                        $strFunc = "{$arrTicket['module_type']}_record_isenabled";
                        if (function_exists($strFunc)) $booEnabled = $strFunc($arrTicket['id_object'], $arrTicket['id_record'], $arrTicket['id_module']);

                        $strScript = str_replace(array('<IDRECORD>', '<IDMODULE>', '<IDWORKSPACE>'), array($arrTicket['id_record'], $arrTicket['id_module'], $arrTicket['id_workspace']), $arrTicket['script']);
                        $strObject = "{$arrTicket['module_name']} / {$arrTicket['object_name']} « {$arrTicket['object_label']} »";

                        echo '<p class="p4-ticket-object">'.ui::icon('link', 'p4-icon-sm').($booEnabled ? '<a href="'.ui::e(ploopi\crypt::urlencode("admin.php?ploopi_mainmenu=1&{$strScript}")).'">'.ui::e($strObject).'</a>' : '<span class="p4-text-danger">'.ui::e(_SYSTEM_MSG_OBJECT_DENIED).'</span>').'</p>';
                    }

                    $arrActs = array(
                        array('label' => _SYSTEM_MSG_REPLY, 'icon' => 'arrow-back-up', 'class' => 'sm', 'attrs' => array('data-p4-ticket-open' => "ploopi_op=tickets_replyto&ticket_id={$intId}")),
                        array('label' => _SYSTEM_MSG_QUOTE, 'icon' => 'notes', 'class' => 'ghost sm', 'attrs' => array('data-p4-ticket-open' => "ploopi_op=tickets_replyto&ticket_id={$intId}&quoted=true"))
                    );
                    if ($booMine) $arrActs[] = array('label' => _SYSTEM_LABEL_MODIFY, 'icon' => 'pencil', 'class' => 'ghost sm', 'attrs' => array('data-p4-ticket-open' => "ploopi_op=tickets_modify&ticket_id={$intId}"));
                    if (!$booToValidate) $arrActs[] = array('label' => _SYSTEM_LABEL_DELETE, 'icon' => 'trash', 'class' => 'danger-ghost sm', 'href' => ploopi\crypt::urlencode("admin.php?ploopi_op=tickets_delete&ticket_id={$intId}"), 'confirm' => _PLOOPI_LABEL_TICKET_CONFIRMDELETE);

                    echo '<div class="p4-row p4-wrap p4-ticket-actions">'.ui::actions($arrActs).'</div>';
                    ?>
                    <div class="p4-ticket-responses" id="tickets_responses_<?php echo $intId; ?>"></div>
                </div>
            </li>
            <?php
        }
        ?>
    </ul>
    <div class="p4-card-footer p4-row p4-wrap">
        <label class="p4-row p4-text-sm"><input type="checkbox" data-p4-checkall-box="input[name='tickets_delete_id[]']" /><?php echo ui::e(_SYSTEM_USERS_SELECTALL); ?></label>
        <?php echo ui::button(array('label' => _PLOOPI_LABEL_TICKET_DELETE_CHECKED, 'icon' => 'trash', 'class' => 'danger-ghost sm', 'type' => 'submit')); ?>
        <span class="p4-grow"></span>
        <?php
        if ($intPages > 1)
        {
            echo '<nav class="p4-pager" aria-label="'.ui::e(_SYSTEM_MSG_PAGES).'">';
            for ($intP = 1; $intP <= $intPages; $intP++) echo '<a class="p4-btn p4-btn-sm'.($intP == $intPage ? ' p4-active' : '').'" href="'.ui::e(ploopi\crypt::urlencode("admin.php?op=tickets&page={$intP}")).'"'.($intP == $intPage ? ' aria-current="page"' : '').'>'.$intP.'</a>';
            echo '</nav>';
        }
        ?>
    </div>
</form>
<?php
echo ui::card_close();
echo ui::script();
