<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<!--
    Ploopi ~ Template pour Ploopi
    Copyright (c) 2007-2018 Ovensia

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
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <meta name="description" content="{WORKSPACE_META_DESCRIPTION}" />
    <meta name="keywords" content="{WORKSPACE_META_KEYWORDS}" />
    <meta name="author" content="{WORKSPACE_META_AUTHOR}" />
    <meta name="copyright" content="{WORKSPACE_META_COPYRIGHT}" />
    <meta name="robots" content="{WORKSPACE_META_ROBOTS}" />
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent" />
    <!--meta name="viewport" content="user-scalable=no, width=device-width" /-->
    <link rel="apple-touch-icon" href="{TEMPLATE_PATH}/img/template/logo_ios.png" />

    <title>{WORKSPACE_TITLE}</title>

    <link rel="icon" href="{TEMPLATE_PATH}/img/favicon.png" type="image/png" />

    <link type="text/css" rel="stylesheet" href="{TEMPLATE_PATH}/css/styles.pack.css" media="screen" />

    <!-- BEGIN module_css -->
    <link type="text/css" rel="stylesheet" href="{module_css.PATH}" media="screen" />
    <!-- END module_css -->

    <!-- BEGIN ploopi_js -->
    <script type="text/javascript" src="{ploopi_js.PATH}"></script>
    <!-- END ploopi_js -->

    <!-- BEGIN module_js -->
    <script type="text/javascript" src="{module_js.PATH}"></script>
    <!-- END module_js -->

    <script type="text/javascript">
    {ADDITIONAL_JAVASCRIPT}


    ploopi.template = {
    };

    <!-- BEGIN switch_user_logged_out -->
    ploopi.template.passwordlost = function() {
        jQuery('#lostpassword_form').eq(0).fadeIn(
            'slow'
        );
    };

    ploopi.template.passwordlost_cancel = function() {
        jQuery('#lostpassword_form').eq(0).fadeOut(
            'slow'
        );
    }

    jQuery(function() {
        if (jQuery('#ploopi_password_new').length) jQuery('#ploopi_password_new')[0].focus();
        else if (jQuery('#ploopi_login').length) jQuery('#ploopi_login')[0].focus();
    });

    <!-- END switch_user_logged_out -->

    <!-- BEGIN switch_user_logged_in -->


    ploopi.template.resize_content = function() {

        var top = jQuery('#dock').height()+2;
        var bottom = 0;
        var height = jQuery(window).height() - top - bottom;
        jQuery('#pagecontent').css({
            top: top,
            height: height
        });


        /*
        var height = jQuery(window).height() - jQuery('#dock').height();
            alert('coucou');

        jQuery('#pagecontent').css({
            height: height
        });
        */

        jQuery('.blockmenu').each(function(key, item) {
            jQuery(item).css([
                'max-height:'+height+'px',
                'overflow:auto'
            ]);
        });
    };

    jQuery(function() {

        with(ploopi.template) {
            resize_content();
            jQuery( window ).resize(function() {
                resize_content();
            });
        }

        ploopi.tickets.refresh({LAST_NEWTICKET}, 30, '(', ')');
    });




    <!-- END switch_user_logged_in -->


    </script>

    {ADDITIONAL_HEAD}
</head>

<body>

<div id="background" style="min-width:1180px;">

<!-- BEGIN switch_user_logged_out -->
    <div id="login_window">
        <div style="position:relative;">
            <div id="login">
                <form id="login_form" action="{switch_user_logged_out.FORM_URL}" method="post">
                <div class="login_form_box">
                    <p>
                        <label for="ploopi_login">Identifiant:&nbsp;</label>
                        <input type="text" class="text" id="ploopi_login" name="ploopi_login" value="{USER_LOGIN}" title="Saisir votre identifiant" placeholder="Saisir votre identifiant" tabindex="1" />
                    </p>
                    <p>
                        <label for="ploopi_password">Mot de passe:&nbsp;</label>
                        <input type="password" class="text" id="ploopi_password" name="ploopi_password" value="{USER_PASSWORD}" title="Saisir votre mot de passe" placeholder="Saisir votre mot de passe" tabindex="2" />
                    </p>
                    <div style="min-height:30px;">
                        <!-- BEGIN switch_passwordreset -->
                            <style>
                                #checkpass * {font-size:10px; }
                                #checkpass .password-strength-bar {border-radius:2px;}
                            </style>

                            <span style="color:#ffff00;"><strong>Votre mot de passe a expiré.</strong><br />Vous devez en saisir un nouveau ci-dessous:</span>
                            <p>
                                <label for="ploopi_password_new">Nouveau mot de passe:&nbsp;</label>
                                <input type="password" class="text" id="ploopi_password_new" name="ploopi_password_new" title="Saisir votre mot de passe" placeholder="Saisir votre mot de passe" tabindex="2" />
                            </p>
                            <div id="checkpass"></div>
                            <p>
                                <label for="ploopi_password_new_confirm">Confirmation mot de passe:&nbsp;</label>
                                <input type="password" class="text" id="ploopi_password_new_confirm" name="ploopi_password_new_confirm" title="Saisir votre mot de passe" placeholder="Saisir votre mot de passe" tabindex="2" />
                            </p>

                            <script type="text/javascript">
                                var backupColor = jQuery('#ploopi_password_new')[0].style.backgroundColor;

                                ploopi.template.verif_pass = function() {
                                    if (jQuery('#ploopi_password_new')[0].value != '' && jQuery('#ploopi_password_new_confirm')[0].value != '') {
                                        if (jQuery('#ploopi_password_new')[0].value == jQuery('#ploopi_password_new_confirm')[0].value) {
                                            jQuery('#ploopi_password_new_confirm')[0].style.backgroundColor = jQuery('#ploopi_password_new')[0].style.backgroundColor = 'lightgreen';
                                        } else {
                                            jQuery('#ploopi_password_new_confirm')[0].style.backgroundColor = jQuery('#ploopi_password_new')[0].style.backgroundColor = 'indianred';
                                        }
                                    }
                                    else jQuery('#ploopi_password_new_confirm')[0].style.backgroundColor = jQuery('#ploopi_password_new')[0].style.backgroundColor = backupColor;
                                }

                                jQuery(function() {
                                    <!-- BEGIN switch_np -->
                                        var options = {
                                            minchar: 6,
                                            scores: [5, 10, 20, 30]
                                        };
                                    <!-- END switch_np -->

                                    <!-- BEGIN switch_cp -->
                                        var options = {
                                            minchar: {switch_user_logged_out.switch_passwordreset.switch_cp.MIN_SIZE},
                                            scores: [5, 10, 20, 30]
                                        };
                                    <!-- END switch_cp -->


                                    new ploopi.checkpass('ploopi_password_new', 'checkpass', options);

                                    jQuery('#ploopi_password_new').on('change', function() { ploopi.template.verif_pass(); });
                                    jQuery('#ploopi_password_new_confirm').on('change', function() { ploopi.template.verif_pass(); });

                                    jQuery('#login_form').on('submit', function(e) {

                                        if (jQuery('#ploopi_password_new')[0].value == '' && jQuery('#ploopi_password_new_confirm')[0].value == '') {
                                            alert('Votre mot de passe a expiré.\nVous devez redéfinir votre mot de passe.');
                                            e.stopPropagation();
                                            return;
                                        }

                                        if (jQuery('#ploopi_password_new')[0].value != jQuery('#ploopi_password_new_confirm')[0].value) {
                                            alert('Les deux saisies sont différentes.\nVous devez corriger votre saisie.');
                                            e.stopPropagation();
                                            return;
                                        }
                                    });

                                });
                            </script>
                        <!-- END switch_passwordreset -->


                        <!-- BEGIN switch_ploopimsg -->
                        <div style="color:#ff8800;overflow:auto;">
                            <img style="display:block;float:left;" src="{TEMPLATE_PATH}/img/system/information.png" /><span style="display:block;margin-left:24px;">{PLOOPI_MSG}</span>
                        </div>
                        <!-- END switch_ploopimsg -->

                       <!-- BEGIN switch_ploopierrormsg -->
                        <div style="color:#ff8800;overflow:auto;">
                            <img style="display:block;float:left;" src="{TEMPLATE_PATH}/img/system/attention.png" /><span style="display:block;margin-left:24px;">{PLOOPI_ERROR}</span>
                        </div>
                        <!-- END switch_ploopierrormsg -->
                    </div>
                </div>

                <div class="login_btn_right">
                    <button type="submit">
                        <span style="margin-right:4px;">Valider</span>
                        <img src="{TEMPLATE_PATH}/img/template/enter.png">
                    </button>
                </div>
                <div class="login_btn_left">
                    <a href="javascript:void(0);" onclick="javascript:ploopi.template.passwordlost();"><img src="{TEMPLATE_PATH}/img/template/lost.png" /><span style="margin-left:6px;">Mot de passe perdu</span></a>
                </div>
                </form>
            </div>
            <form style="display:none;" id="lostpassword_form" action="{PASSWORDLOST_URL}" method="post" onsubmit="javascript:return tpl_passwordlost_submit();">
                <div class="login_form_box">
                    <p>
                        <label for="ploopi_lostpassword_login">Identifiant:&nbsp;</label>
                        <input type="text" class="text" id="ploopi_lostpassword_login" name="ploopi_lostpassword_login" size="20" title="Saisissez votre identifiant" tabindex="11" />
                    </p>
                    <p>
                        <label for="ploopi_lostpassword_email">(ou) Adresse Email:&nbsp;</label>
                        <input type="text" class="text" id="ploopi_lostpassword_email" name="ploopi_lostpassword_email" size="20" title="Saisissez votre adresse email" tabindex="12" />
                    </p>
                    <div style="color:#ffff00;margin-top:10px;">
                        <em><strong>ATTENTION</strong>, une demande de mot de passe génère un nouveau mot de passe automatique.</em>
                    </div>
                </div>
                <div class="login_btn_right">
                    <button type="submit">
                        <span style="margin-right:4px;">Envoyer</span>
                        <img src="{TEMPLATE_PATH}/img/template/enter.png">
                    </button>
                </div>
                <div class="login_btn_left">
                    <a href="javascript:void(0);" onclick="javascript:ploopi.template.passwordlost_cancel();"><img src="{TEMPLATE_PATH}/img/template/cancel.png" /><span style="margin-left:6px;">Annuler</span></a>
                </div>
            </form>
        </div>

    </div>
    <div id="login_statusbar">
        Propulsé par&nbsp;<a href="http://www.ploopi.fr">Ploopi</a>&nbsp;|&nbsp;Une création&nbsp;<a href="http://www.ovensia.fr">Ovensia</a>
    </div>
<!-- END switch_user_logged_out -->

<!-- BEGIN switch_user_logged_in -->

    <div id="dock">
        <!-- BEGIN switch_blockmenu -->
        <ul id="mainmenu">
            <!-- BEGIN block -->
                <li>
                    <a class="mainmenu {switch_user_logged_in.switch_blockmenu.block.SELECTED}" href="{switch_user_logged_in.switch_blockmenu.block.URL}" title="Accéder au module &laquo; {switch_user_logged_in.switch_blockmenu.block.TITLE} &raquo;">{switch_user_logged_in.switch_blockmenu.block.TITLE}</a>
                    <div class="blockmenu">
                    <!-- BEGIN switch_content -->
                        <div class="blockcontent" style="overflow:auto;">{switch_user_logged_in.switch_blockmenu.block.switch_content.CONTENT}</div>
                    <!-- END switch_content -->
                    <!-- BEGIN menu -->
                        <a class="blockmenu {switch_user_logged_in.switch_blockmenu.block.menu.SELECTED}" href="{switch_user_logged_in.switch_blockmenu.block.menu.URL}" target="{switch_user_logged_in.switch_blockmenu.block.menu.TARGET}" title="Accéder au menu &laquo; {switch_user_logged_in.switch_blockmenu.block.menu.CLEANED_LABEL} &raquo;">{switch_user_logged_in.switch_blockmenu.block.menu.LABEL}</a>
                    <!-- END menu -->
                    </div>
                </li>
            <!-- END block -->
        </ul>
        <!-- END switch_blockmenu -->





        <div id="userbox">
            <div><img src="{TEMPLATE_PATH}/img/template/user.png" /><span>{USER_FIRSTNAME} {USER_LASTNAME}</span></div>
            <a class="{MAINMENU_SHOWTICKETS_SEL}" href="{MAINMENU_SHOWTICKETS_URL}">{MAINMENU_TICKETS}
            <em id="tpl_ploopi_tickets_new">
            ({NEWTICKETS})
            </em>
            </a>


            <a href="javascript:void(0);" title="{MAINMENU_PROFILE}" onclick="tpl_open_profile();"><img src="{TEMPLATE_PATH}/img/template/profile.png" /></a>
            <a href="{USER_DECONNECT}" title="{MAINMENU_DISCONNECTION}" ><img src="{TEMPLATE_PATH}/img/template/door.png" /></a>
        </div>

        <form id="searchbox" action="{MAINMENU_SHOWSEARCH_URL}" method="post">
            <input type="text" name="system_search_keywords" class="text" value="{SEARCH_KEYWORDS}" placeholder="Recherche" title="Recherche intégrale">
            <button type="submit"><img src="{TEMPLATE_PATH}/img/template/search.png" /></button>
        </form>

        <div id="workspace">
            <select class="dropdown" onchange="javascript:if (this.value!='') document.location.href = this.value;" title="Choix d'un espace">
                <!-- BEGIN workspace -->
                <option value="{switch_user_logged_in.workspace.URL}" {switch_user_logged_in.workspace.SELECTED}>{switch_user_logged_in.workspace.TITLE}</option>
                <!-- END workspace -->
                <option value="" {USER_WORKSPACE_SEL}>(Choisir un espace)</option>
            </select>
        </div>

    </div>

    <div id="ploopi_mod_mess" class=""></div>

    <div id="pagecontent" class="hook">
        {PAGE_CONTENT}
    </div>
<!-- END switch_user_logged_in -->

</div>


<script type="text/javascript">

<!-- BEGIN switch_user_logged_out -->
var effect = false;

function tpl_passwordlost() {
    if (effect) return false;
    effect = true;
    new Effect.Appear(
        'lostpassword_form', {
            from: 0.0,
            to: 1.0,
            duraction: 0.2,
            fps: 25,
            afterFinish:function() {
                jQuery('#ploopi_lostpassword_login')[0].focus();
                effect = false;
            }
        }
    );
}

function tpl_passwordlost_cancel() {
    if (effect) return false;
    effect = true;
    new Effect.Appear(
        'lostpassword_form', {
            from: 1.0,
            to: 0.0,
            duraction: 0.2,
            fps: 25,
            afterFinish:function() {
                jQuery('#ploopi_login')[0].focus();
                effect = false;
            }
        }
    );
}


<!-- END switch_user_logged_out -->

<!-- BEGIN switch_user_logged_in -->

function tpl_open_profile() {
    ploopi.popup.show('', 950, null, true, 'system_popup_update_profile')
    ploopi.xhr.todiv('admin-light.php', '{POPUP_PROFLE}', 'system_popup_update_profile');
}

console.log('page: <PLOOPI_PAGE_SIZE> ko | exec: <PLOOPI_EXEC_TIME> ms | sql: <PLOOPI_NUMQUERIES> req (<PLOOPI_SQL_P100> %) | session: <PLOOPI_SESSION_SIZE> ko | mem: <PLOOPI_PHP_MEMORY> ko');
<!-- END switch_user_logged_in -->
</script>

</body>
</html>
