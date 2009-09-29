<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<!--
    Ploopi ~ Template pour Ploopi
    Copyright (c) 2007-2008 Ovensia

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
    -->

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
<head>
    <meta http-equiv="content-type" content="text/html; charset=iso-8859-15" />
    <meta name="description" content="{WORKSPACE_META_DESCRIPTION}" />
    <meta name="keywords" content="{WORKSPACE_META_KEYWORDS}" />
    <meta name="author" content="{WORKSPACE_META_AUTHOR}" />
    <meta name="copyright" content="{WORKSPACE_META_COPYRIGHT}" />
    <meta name="robots" content="{WORKSPACE_META_ROBOTS}" />

    <title>{WORKSPACE_TITLE}</title>

    <link rel="icon" href="{TEMPLATE_PATH}/img/favicon.png" type="image/png" />

    <link type="text/css" rel="stylesheet" href="{TEMPLATE_PATH}/css/styles.pack.css" media="screen" />

    <!-- BEGIN module_css -->
    <link type="text/css" rel="stylesheet" href="{module_css.PATH}" media="screen" />
    <!-- END module_css -->

    <!--[if lte IE 6]>
    <link type="text/css" rel="stylesheet" href="{TEMPLATE_PATH}/css/styles_ie.pack.css" media="screen" />
    <!-- BEGIN module_css_ie -->
    <link type="text/css" rel="stylesheet" href="{module_css_ie.PATH}" media="screen" />
    <!-- END module_css_ie -->
    <![endif]-->

    <!-- BEGIN ploopi_js -->
    <script type="text/javascript" src="{ploopi_js.PATH}"></script>
    <!-- END ploopi_js -->

    <!-- BEGIN module_js -->
    <script type="text/javascript" src="{module_js.PATH}"></script>
    <!-- END module_js -->

    <script type="text/javascript">
    //<!--
    {ADDITIONAL_JAVASCRIPT}
    //-->
    </script>

    {ADDITIONAL_HEAD}
</head>

<body class="index">

<!-- BEGIN switch_mod_message -->
<div id="{switch_mod_message.MSG_ID}" class="{switch_mod_message.MSG_CLASS}">{switch_mod_message.MSG}</div>
<script type="text/javascript">ploopi_window_onload_stock(function() { $('{switch_mod_message.MSG_ID}').fade({ duration: 3.0}); });</script>
<!-- END switch_mod_message -->

<!-- BEGIN switch_user_logged_out -->
<div id="container_out">
<!-- END switch_user_logged_out -->

<!-- BEGIN switch_user_logged_in -->
<div id="container_in">
<!-- END switch_user_logged_in -->

    <!-- BEGIN switch_user_logged_out -->
    <div id="loginbox">
        <div class="loginbox_title">
            <p>Identification</p>
        </div>
        <div style="padding:10px;">
            <form id="formlogin" action="admin.php" method="post">
                <div class="loginbox_line" style="text-align:right;">
                    <label for="ploopi_login">utilisateur:&nbsp;</label>
                    <input type="text" class="text" id="ploopi_login" name="ploopi_login" style="width:100px;" tabindex="1" />
                </div>
                <div class="loginbox_line" style="text-align:right;">
                    <label for="ploopi_password">mot de passe:&nbsp;</label>
                    <input type="password" class="text" id="ploopi_password" name="ploopi_password" style="width:100px;" tabindex="2" />
                </div>
                <div class="loginbox_line">
                    <input type="submit" value="Connexion" class="flatbutton" style="width:100%;" tabindex="3"/>
                </div>
            </form>
            <div class="loginbox_line" style="text-align:right;">
                <a href="javascript:void(0);" onclick="javascript:tpl_passwordlost();">Mot de passe perdu ?</a>
            </div>
            <form id="formpasswordlost" action="{PASSWORDLOST_URL}" method="post" style="display:none;" onsubmit="javascript:return tpl_passwordlost_submit();">
                <div class="loginbox_line" style="text-align:right;">
                    <label for="ploopi_lostpassword_login">utilisateur:&nbsp;</label>
                    <input type="text" class="text" id="ploopi_lostpassword_login" name="ploopi_lostpassword_login" style="width:100px;" tabindex="10" />
                </div>
                <div class="loginbox_line" style="text-align:right;">
                    <label for="ploopi_lostpassword_email">(ou) email:&nbsp;</label>
                    <input type="text" class="text" id="ploopi_lostpassword_email" name="ploopi_lostpassword_email" style="width:100px;" tabindex="11" />
                </div>
                <div class="loginbox_line">
                    <em><strong>ATTENTION</strong>, une demande de mot de passe génère un nouveau mot de passe automatique.</em>
                </div>
                <div class="loginbox_line">
                    <input type="button" value="Annuler" class="button" style="width:49%;" tabindex="12" onclick="javascript:tpl_passwordlost_cancel();" />
                    <input type="submit" value="Envoyer" class="button" style="width:49%;" tabindex="13" />
                </div>
            </form>

            <!-- BEGIN switch_ploopimsg -->
            <div class="loginbox_line" style="text-align:right;">
                <img src="{TEMPLATE_PATH}/img/system/information.png" style="display:block;float:left;"><span class="information">&nbsp;{PLOOPI_MSG}</span>
            </div>
            <!-- END switch_ploopimsg -->
            <!-- BEGIN switch_ploopierrormsg -->
            <div class="loginbox_line" style="text-align:right;">
                <img src="{TEMPLATE_PATH}/img/system/attention.png" style="display:block;float:left;"><span class="error">&nbsp;{PLOOPI_ERROR}</span>
            </div>
            <!-- END switch_ploopierrormsg -->
        </div>
    <div class="loginbox_footer"></div>
    </div>
    <!-- END switch_user_logged_out -->

    <!-- BEGIN switch_user_logged_in -->
    <div id="header">
        <p>Bienvenue&nbsp;<b>{USER_FIRSTNAME}&nbsp;{USER_LASTNAME}</b></p>
        <!-- BEGIN workspace -->
            <a class="{switch_user_logged_in.workspace.SELECTED}" href="{switch_user_logged_in.workspace.URL}">{switch_user_logged_in.workspace.TITLE}</a>
        <!-- END workspace -->
        <a class="{MAINMENU_SHOWTICKETS_SEL}" href="{MAINMENU_SHOWTICKETS_URL}">{MAINMENU_TICKETS}
        <em id="tpl_ploopi_tickets_new">
        ({NEWTICKETS})
        </em>
        </a>
        <a class="{MAINMENU_SHOWANNOTATIONS_SEL}" href="{MAINMENU_SHOWANNOTATIONS_URL}">{MAINMENU_ANNOTATIONS}</a>
        <a class="{MAINMENU_SHOWPROFILE_SEL}" href="{MAINMENU_SHOWPROFILE_URL}">{MAINMENU_PROFILE}</a>
        <a href="{USER_DECONNECT}">{MAINMENU_DISCONNECTION}</a>
    </div>

    <div id="container2">
        <div style="clear:both;">
            <!-- BEGIN switch_blockmenu -->
            <div id="block_modules" style="display:{SHOW_BLOCKMENU}">

                <!-- BEGIN switch_search -->
                <div class="module_block">
                    <div class="module_title">
                        <p>{MAINMENU_SEARCH}</p>
                    </div>
                    <form method="post" id="form_recherche" action="{MAINMENU_SHOWSEARCH_URL}">
                    <div class="module_content">
                        <p class="ploopi_va" style="padding:2px;">
                            <input type="text" name="system_search_keywords" class="text" style="width:120px;" value="{SEARCH_KEYWORDS}">
                            <img src="{TEMPLATE_PATH}/img/template/search.png" value="Recherche" style="cursor:pointer;" onclick="$('form_recherche').submit();">
                        </p>
                    </div>
                    </form>
                    <div class="module_footer"></div>
                </div>
                <!-- END switch_search -->

                <!-- BEGIN block -->
                <div class="module_block">
                    <div class="module_title">
                        <p>{switch_user_logged_in.switch_blockmenu.block.TITLE}</p>
                    </div>
                    <!-- BEGIN switch_content -->
                        <div class="module_content">{switch_user_logged_in.switch_blockmenu.block.switch_content.CONTENT}</div>
                    <!-- END switch_content -->
                    <!-- BEGIN menu -->
                        <a class="module_menu{switch_user_logged_in.switch_blockmenu.block.menu.SELECTED}" href="{switch_user_logged_in.switch_blockmenu.block.menu.URL}" target="{switch_user_logged_in.switch_blockmenu.block.menu.TARGET}">{switch_user_logged_in.switch_blockmenu.block.menu.LABEL}</a>
                    <!-- END menu -->
                    <div class="module_footer"></div>
                </div>
                <!-- END block -->
            </div>
            <!-- END switch_blockmenu -->

            <div id="page_content">
                {PAGE_CONTENT}
            </div>
        </div>
    </div>
    <!-- END switch_user_logged_in -->

</div>

<p id="footer">
Template:&nbsp;<a href="http://www.ovensia.fr">{TEMPLATE_NAME}</a> |&nbsp;Propulsé par&nbsp;<a href="http://www.ploopi.fr">Ploopi {PLOOPI_VERSION} ({PLOOPI_REVISION})</a>&nbsp;&#169;&nbsp;2008&nbsp;<a href="http://www.ovensia.fr">Ovensia</a>&nbsp;|&nbsp;<a href="http://www.ploopi.org/#Utilisation">Documentation utilisateur</a>&nbsp;|&nbsp;<a href="http://www.mozilla-europe.org/fr/products/firefox/">Préférez Firefox</a>
<br />[ page: <PLOOPI_PAGE_SIZE> ko | exec: <PLOOPI_EXEC_TIME> ms | sql: <PLOOPI_NUMQUERIES> req (<PLOOPI_SQL_P100> %) | session: <PLOOPI_SESSION_SIZE> ko | mem: <PLOOPI_PHP_MEMORY> ko ]
</p>

<!-- BEGIN switch_user_logged_out -->
<script type="text/javascript">
ploopi_window_onload_stock(function() { if ($('ploopi_login')) $('ploopi_login').focus(); } );

var effect = false;

function tpl_passwordlost() {
    if (effect) return false;
    effect = true;
    new Effect.BlindDown(
        'formpasswordlost',
        {
            duration: 0.3,
            afterFinish:function() {
                $('ploopi_lostpassword_login').focus();
                effect = false;
            }
        }
    );
}

function tpl_passwordlost_cancel() {
    if (effect) return false;
    effect = true;
    new Effect.BlindUp(
        'formpasswordlost',
        {
            duration: 0.3,
            afterFinish:function() {
                $('ploopi_login').focus();
                effect = false;
            }
        }
    );
}

function tpl_passwordlost_submit() {
    if ($('ploopi_lostpassword_login').value != '' || $('ploopi_lostpassword_email').value != '') return true;
    else alert('Vous devez remplir un des deux champs');

    return false;
}
</script>
<!-- END switch_user_logged_out -->

<!-- BEGIN switch_user_logged_in -->
<script type="text/javascript">
ploopi_window_onload_stock(function() { ploopi_tickets_refresh({LAST_NEWTICKET}, 30, '(', ')'); } );
</script>
<!-- END switch_user_logged_in -->

</body>
</html>
