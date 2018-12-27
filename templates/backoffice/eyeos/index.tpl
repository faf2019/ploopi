<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

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

    <!-- BEGIN ploopi_js -->
    <script type="text/javascript" src="{ploopi_js.PATH}"></script>
    <!-- END ploopi_js -->

    <!-- BEGIN module_js -->
    <script type="text/javascript" src="{module_js.PATH}"></script>
    <!-- END module_js -->

    <script type="text/javascript">
    //<!--
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
        var height = jQuery(window).height() - jQuery('#statusbar').height() - jQuery('#dock').height();

        jQuery('#pagecontent')[0].style.height = height+'px';

        jQuery('.blockmenu').each(function(key, item) {
            jQuery(item).css([
                'max-height:'+height+'px',
                'overflow:auto'
            ]);
        });
    };

    ploopi.template.display_time = function() {
        var now = new Date();
        var hours = now.getHours();
        var minutes = now.getMinutes();
        var day = now.getDate();
        var month = now.getMonth()+1;
        var year = now.getFullYear();

        minutes = ((minutes < 10) ? "0" : "") + minutes;
        day = ((day < 10) ? "0" : "") + day;
        month = ((month < 10) ? "0" : "") + month;

        var clock = day + "/" + month + "/" + year + " " + hours + ":" + minutes;
        jQuery('#status_time').html(clock);
        timer = setTimeout(ploopi.template.display_time, 30000);
    };

    jQuery(function() {

        with(ploopi.template) {
            resize_content();
            display_time();
            jQuery(window).resize(resize_content);
        }

        ploopi.tickets.refresh({LAST_NEWTICKET}, 30, '(', ')');
    });

    <!-- END switch_user_logged_in -->

    //-->
    </script>

    {ADDITIONAL_HEAD}
</head>

<body>

<div id="background">

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
        Template:&nbsp;<a href="http://www.ovensia.fr">{TEMPLATE_NAME}</a> |&nbsp;Propulsé par&nbsp;<a href="http://www.ploopi.fr">Ploopi</a>&nbsp;&#169;&nbsp;2016&nbsp;<a href="http://www.ovensia.fr">Ovensia</a>&nbsp;|&nbsp;<a href="http://www.ploopi.org/#Utilisation">Documentation utilisateur</a>&nbsp;|&nbsp;<a href="http://www.mozilla-europe.org/fr/products/firefox/">Préférez Firefox</a>
    </div>
    <!-- END switch_user_logged_out -->

    <!-- BEGIN switch_user_logged_in -->
    <div id="dock" style="z-index:2;">

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
                <img src="{TEMPLATE_PATH}/img/template/dock_sep.png" />
            <!-- END block -->
        </ul>
        <!-- END switch_blockmenu -->

    </div>

    <div id="statusbar">

        <a style="position:relative;padding-left:25px;margin-right:1px;" class="menu_right" href="{USER_DECONNECT}" title="Fermer la session en cours"><img style="position:absolute;left:10px;top:8px;" src="{TEMPLATE_PATH}/img/template/icons/logout.png" /></a>
        <img class="menu_right" src="{TEMPLATE_PATH}/img/template/status_sep.png" />

        <div class="menu_right" id="status_time"></div>
        <img class="menu_right" src="{TEMPLATE_PATH}/img/template/status_sep.png" />

        <ul id="aboutmenu" class="statusmenu">
            <li>
                <a class="menu" href="javascript:void(0);" title="A propos de Ploopi"><img style="margin-top:8px;" src="{TEMPLATE_PATH}/img/template/icons/about.png" /></a>
                <div>
                    <a href="http://www.ploopi.fr">Propulsé par <strong>Ploopi</strong></a>
                    <a href="http://www.mozilla-europe.org/fr/products/firefox/">Ce template ne fonctionne pas avec IE6. <strong>Préférez Firefox</strong></a>
                    <a href="http://www.ploopi.org/#Utilisation"><strong>Documentation utilisateur</strong></a>
                    <p><strong>Informations Utilisateur :</strong>
                        <label>Identifiant</label><span>{USER_LOGIN}</span>
                        <label>Nom</label><span>{USER_FIRSTNAME} {USER_LASTNAME}</span>
                    </p>
                    <p><strong>Informations Système :</strong>
                        <label>Gabarit</label><span>{TEMPLATE_NAME}</span>
                        <label>Exécution</label><span><PLOOPI_EXEC_TIME> ms</span>
                        <label>SQL</label><span><PLOOPI_NUMQUERIES> req (<PLOOPI_SQL_P100> %)</span>
                        <label>Session</label><span><PLOOPI_SESSION_SIZE> KB</span>
                        <label>Mémoire</label><span><PLOOPI_PHP_MEMORY> KB</span>
                        <label>Page</label><span><PLOOPI_PAGE_SIZE> KB </span>
                        <label>Connectés</label><span>{SITE_CONNECTEDUSERS}</span>
                        <label>Anonymes</label><span>{SITE_ANONYMOUSUSERS}</span>
                    </p>
                </div>
            </li>
            <img class="menu_right" src="{TEMPLATE_PATH}/img/template/status_sep.png" />
            <!-- BEGIN switch_search -->
            <li>
                <a class="menu" href="javascript:void(0);" title="A propos de Ploopi"><img style="margin-top:8px;" src="{TEMPLATE_PATH}/img/template/icons/search.png" /></a>
                <div>
                    <form method="post" id="form_recherche" action="{MAINMENU_SHOWSEARCH_URL}">
                    <p style="margin:0;padding:10px;overflow:auto;">
                        <input type="text" name="system_search_keywords" class="text" style="width:180px;float:left;" value="{SEARCH_KEYWORDS}">
                        <img src="{TEMPLATE_PATH}/img/template/icons/search.png" value="Recherche" style="cursor:pointer;float:left;margin-left:4px;margin-top:4px;" onclick="jQuery('#form_recherche')[0].submit();">
                    </p>
                    </form>
                </div>
            </li>
            <img class="menu_right" src="{TEMPLATE_PATH}/img/template/status_sep.png" />
            <!-- END switch_search -->
        </ul>

        <a style="position:relative;padding-left:30px;" class="menu_right {MAINMENU_SHOWTICKETS_SEL}" href="{MAINMENU_SHOWTICKETS_URL}" title="Accéder à &laquo; {MAINMENU_TICKETS} &raquo;"><img style="position:absolute;left:10px;top:8px;" src="{TEMPLATE_PATH}/img/template/icons/tickets.png" />{MAINMENU_TICKETS}<em id="tpl_ploopi_tickets_new">({NEWTICKETS})</em></a>
        <img class="menu_right" src="{TEMPLATE_PATH}/img/template/status_sep.png" />

        <a style="position:relative;padding-left:30px;" class="menu_right {MAINMENU_SHOWANNOTATIONS_SEL}" href="{MAINMENU_SHOWANNOTATIONS_URL}" title="Accéder à &laquo; {MAINMENU_ANNOTATIONS} &raquo;"><img style="position:absolute;left:10px;top:8px;" src="{TEMPLATE_PATH}/img/system/annotation.png" />{MAINMENU_ANNOTATIONS}</a>
        <img class="menu_right" src="{TEMPLATE_PATH}/img/template/status_sep.png" />

        <a style="position:relative;padding-left:30px;" class="menu_right {MAINMENU_SHOWPROFILE_SEL}" href="{MAINMENU_SHOWPROFILE_URL}" title="Accéder à &laquo; {MAINMENU_PROFILE} &raquo;"><img style="position:absolute;left:10px;top:8px;" src="{TEMPLATE_PATH}/img/template/icons/user.png" />{MAINMENU_PROFILE}</a>
        <img class="menu_right" src="{TEMPLATE_PATH}/img/template/status_sep.png" />

        <div id="workspace" style="float:left;line-height:30px;height:30px;padding:0 10px;">Espace de travail sélectionné :</div>
        <img src="{TEMPLATE_PATH}/img/template/status_sep.png" style="display:block;float:left;" />
        <ul id="workspacemenu" class="statusmenu">
            <li>
                <a class="menu" href="javascript:void(0);" title="Sélectionner un autre espace de travail">{WORKSPACE_LABEL}</a>
                <div>
                <!-- BEGIN workspace -->
                    <a href="{switch_user_logged_in.workspace.URL}" class="{switch_user_logged_in.workspace.SELECTED}" title="Sélectionner l'espace de travail &laquo; {switch_user_logged_in.workspace.TITLE} &raquo;">{switch_user_logged_in.workspace.TITLE}</a>
                <!-- END workspace -->
                <a href="{USER_WORKSPACE_URL}" class="{USER_WORKSPACE_SEL}" title="Sélectionner l'espace de travail &laquo; {USER_WORKSPACE_LABEL} &raquo;"><em>{USER_WORKSPACE_LABEL}</em></a>
                </div>
            </li>
        </ul>
        <img src="{TEMPLATE_PATH}/img/template/status_sep.png" style="display:block;float:left;" />

    </div>

    <div id="ploopi_mod_mess" class=""></div>

    <div id="pagecontent" class="hook">
        <div style="padding:10px;">
        {PAGE_CONTENT}
        </div>
    </div>
    <!-- END switch_user_logged_in -->

</div>
</body>
</html>
