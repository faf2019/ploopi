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

<!-- BEGIN switch_user_logged_out -->
    <div id="top-menu">
    </div>
    <div id="header">
        <h1>{WORKSPACE_TITLE}</h1>
    </div>


    <div id="loginbox">
        <div class="loginbox_title">
            Identification
        </div>
        <div style="padding:10px;">
            <form id="login_form" action="admin.php" method="post">
                <div class="loginbox_line" style="text-align:right;">
                    <label for="ploopi_login">Identifiant:&nbsp;</label>
                    <input type="text" class="text" id="ploopi_login" name="ploopi_login" value="{USER_LOGIN}" style="width:150px;" title="Saisissez votre identifiant" tabindex="1" />
                </div>
                <div class="loginbox_line" style="text-align:right;">
                    <label for="ploopi_password">Mot de passe:&nbsp;</label>
                    <input type="password" class="text" id="ploopi_password" name="ploopi_password" value="{USER_PASSWORD}" style="width:150px;" title="Saisissez votre mot de passe" tabindex="2" />
                </div>

                <!-- BEGIN switch_passwordreset -->
                    <style>
                        #protopass {float:right;width:160px;}
                        #protopass * {font-size:10px;}
                        #protopass .password-strength-bar {border-radius:2px;}
                    </style>

                    <div style="color:#ff8800;text-align:center;padding:10px;"><strong>Votre mot de passe a expiré.</strong><br />Vous devez en saisir un nouveau ci-dessous:</div>

                    <div class="loginbox_line" style="text-align:right;">
                        <label for="ploopi_password_new">Nouveau mot de passe:&nbsp;</label>
                        <input type="password" class="text" id="ploopi_password_new" name="ploopi_password_new" style="width:150px;" title="Saisissez votre mot de passe" tabindex="3" />
                    </div>

                    <div>
                        <div id="protopass"></div>
                    </div>

                    <div class="loginbox_line" style="clear:both;text-align:right;">
                        <label for="ploopi_password_new_confirm">Confirmation mot de passe:&nbsp;</label>
                        <input type="password" class="text" id="ploopi_password_new_confirm" name="ploopi_password_new_confirm" style="width:150px;" title="Saisissez votre mot de passe" tabindex="4" />
                    </div>

                    <script type="text/javascript">
                        var backupColor = $('ploopi_password_new').style.backgroundColor;

                        function tpl_verif_pass() {
                            if ($('ploopi_password_new').value != '' && $('ploopi_password_new_confirm').value != '') {
                                if ($('ploopi_password_new').value == $('ploopi_password_new_confirm').value) {
                                    $('ploopi_password_new_confirm').style.backgroundColor = $('ploopi_password_new').style.backgroundColor = 'lightgreen';
                                } else {
                                    $('ploopi_password_new_confirm').style.backgroundColor = $('ploopi_password_new').style.backgroundColor = 'indianred';
                                }
                            }
                            else $('ploopi_password_new_confirm').style.backgroundColor = $('ploopi_password_new').style.backgroundColor = backupColor;
                        }

                        Event.observe(window, 'load', function() {

                            <!-- BEGIN switch_np -->
                                var options = {
                                    minchar: 6,
                                    scores: [5, 10, 20, 30]
                                };
                            <!-- END switch_np -->

                            <!-- BEGIN switch_cp -->
                                var options = {
                                    minchar: 8,
                                    scores: [5, 10, 20, 30]
                                };
                            <!-- END switch_cp -->


                            new Protopass('ploopi_password_new', 'protopass', options);

                            Event.observe($('ploopi_password_new'), 'change', function() { tpl_verif_pass(); });
                            Event.observe($('ploopi_password_new_confirm'), 'change', function() { tpl_verif_pass(); });

                            Event.observe($('login_form'), 'submit', function(e) {


                                if ($('ploopi_password_new').value == '' && $('ploopi_password_new_confirm').value == '') {
                                    alert('Votre mot de passe a expiré.\nVous devez redéfinir votre mot de passe.');
                                    e.stop();
                                    return;
                                }

                                if ($('ploopi_password_new').value != $('ploopi_password_new_confirm').value) {
                                    alert('Les deux saisies sont différentes.\nVous devez corriger votre saisie.');
                                    e.stop();
                                    return;
                                }
                            });

                        });
                    </script>
                <!-- END switch_passwordreset -->

                <div class="loginbox_line">
                    <input type="submit" value="Connexion" class="flatbutton" style="width:100%;" title="Cliquez pour vous connecter" tabindex="3" />
                </div>
            </form>
            <div class="loginbox_line" style="text-align:right;">
                <a href="javascript:void(0);" onclick="javascript:tpl_passwordlost();">Mot de passe perdu ?</a>
            </div>
            <form id="formpasswordlost" action="{PASSWORDLOST_URL}" method="post" style="display:none;" onsubmit="javascript:return tpl_passwordlost_submit();">
                <div class="loginbox_line" style="text-align:right;">
                    <label for="ploopi_lostpassword_login">Identifiant:&nbsp;</label>
                    <input type="text" class="text" id="ploopi_lostpassword_login" name="ploopi_lostpassword_login" style="width:150px;" title="Saisissez votre identifiant" tabindex="10" />
                </div>
                <div class="loginbox_line" style="text-align:right;">
                    <label for="ploopi_lostpassword_email">(ou) Mèl:&nbsp;</label>
                    <input type="text" class="text" id="ploopi_lostpassword_email" name="ploopi_lostpassword_email" style="width:150px;" title="Saisissez votre mèl" tabindex="11" />
                </div>
                <div class="loginbox_line">
                    <em><strong>ATTENTION</strong>, une demande de mot de passe génère un nouveau mot de passe automatique.</em>
                </div>
                <div class="loginbox_line">
                    <input type="submit" value="Envoyer" class="button" style="float:right;width:49%;" title="Cliquez pour envoyer votre demande de mot de passe" tabindex="13" />
                    <input type="button" value="Annuler" class="button" style="width:49%;" tabindex="12" title="Cliquez pour annuler votre demande de mot de passe" onclick="javascript:tpl_passwordlost_cancel();" />
                </div>
            </form>


            <!-- BEGIN switch_ploopimsg -->
            <div style="color:#a60000;overflow:auto;padding:10px;">
                <img style="display:block;float:left;" src="{TEMPLATE_PATH}/img/system/information.png" /><span style="display:block;margin-left:24px;">{PLOOPI_MSG}</span>
            </div>
            <!-- END switch_ploopimsg -->

           <!-- BEGIN switch_ploopierrormsg -->
            <div style="color:#a60000;overflow:auto;padding:10px;">
                <img style="display:block;float:left;" src="{TEMPLATE_PATH}/img/system/attention.png" /><span style="display:block;margin-left:24px;">{PLOOPI_ERROR}</span>
            </div>
            <!-- END switch_ploopierrormsg -->

        </div>

    </div>

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
    <div id="top-menu">
        <div id="user-menu">
            <ul>
                <li><a class="{MAINMENU_SHOWTICKETS_SEL}" href="{MAINMENU_SHOWTICKETS_URL}">{MAINMENU_TICKETS}<em id="tpl_ploopi_tickets_new">({NEWTICKETS})</em></a></li>
                <li><a class="{MAINMENU_SHOWANNOTATIONS_SEL}" href="{MAINMENU_SHOWANNOTATIONS_URL}">{MAINMENU_ANNOTATIONS}</a></li>
                <li><a class="{MAINMENU_SHOWPROFILE_SEL}" href="{MAINMENU_SHOWPROFILE_URL}">{MAINMENU_PROFILE}</a></li>
                <li><a href="{USER_DECONNECT}">{MAINMENU_DISCONNECTION}</a></li>
            </ul>
        </div>
        <span>Connecté en tant que &nbsp;<b>{USER_FIRSTNAME}&nbsp;{USER_LASTNAME}</b></span>
    </div>

    <div id="header">
        <div id="workspace-menu">
            <select class="select" onchange="javascript:if (this.value != '') window.location = this.value;">
                <option value="" selected="selected">Aller à un espace...</option>
                <option value="" disabled="disabled">---</option>
                <!-- BEGIN workspace -->
                    <option value="{switch_user_logged_in.workspace.URL}" {switch_user_logged_in.workspace.SELECTED}>{switch_user_logged_in.workspace.TITLE}</option>
                <!-- END workspace -->
                <option value="{USER_WORKSPACE_URL}" {USER_WORKSPACE_SEL}>({USER_WORKSPACE_LABEL})</option>
            </select>
        </div>

        <!-- BEGIN switch_blockmenu -->
            <!-- BEGIN switch_search -->
                <div id="quick-search">
                    <form method="post" id="form_recherche" action="{MAINMENU_SHOWSEARCH_URL}">
                        <label for="system_search_keywords" accesskey="4">Recherche:</label>
                        <input type="text" name="system_search_keywords" class="text" size="20" value="{SEARCH_KEYWORDS}" accesskey="f" />
                    </form>
                </div>
            <!-- END switch_search -->
        <!-- END switch_blockmenu -->


        <h1>{WORKSPACE_TITLE}</h1>
        <!-- h2>{WORKSPACE_META_DESCRIPTION}</h2 -->

        <!-- BEGIN switch_blockmenu -->
            <div id="main-menu">
                <ul>
                    <!-- BEGIN block -->
                        <li><a class="{switch_user_logged_in.switch_blockmenu.block.SELECTED}" href="{switch_user_logged_in.switch_blockmenu.block.URL}" title="Accéder au module &laquo; {switch_user_logged_in.switch_blockmenu.block.TITLE} &raquo;">{switch_user_logged_in.switch_blockmenu.block.TITLE}</a></li>
                    <!-- END block -->
                </ul>
            </div>
        <!-- END switch_blockmenu -->
    </div>

    <div id="main">

        <!-- BEGIN switch_blockmenu -->
            <!-- BEGIN switch_blocksel -->
                <div id="sidebar">
                    <h3>{switch_user_logged_in.switch_blockmenu.switch_blocksel.TITLE}</h3>
                    <!-- BEGIN switch_content -->
                        <div class="block_content">{switch_user_logged_in.switch_blockmenu.switch_blocksel.switch_content.CONTENT}</div>
                    <!-- END switch_content -->
                    <ul>
                    <!-- BEGIN menu -->
                        <li><a class="{switch_user_logged_in.switch_blockmenu.switch_blocksel.menu.SELECTED}" href="{switch_user_logged_in.switch_blockmenu.switch_blocksel.menu.URL}" target="{switch_user_logged_in.switch_blockmenu.switch_blocksel.menu.TARGET}" title="Accéder au menu &laquo; {switch_user_logged_in.switch_blockmenu.switch_blocksel.menu.CLEANED_LABEL} &raquo;">{switch_user_logged_in.switch_blockmenu.switch_blocksel.menu.LABEL}</a></li>
                    <!-- END menu -->
                    </ul>
                </div>
            <!-- END switch_blocksel -->
        <!-- END switch_blockmenu -->

        <div id="page_content">
            {PAGE_CONTENT}
        </div>

    </div>

    <script type="text/javascript">
    ploopi_window_onload_stock(function() { ploopi_tickets_refresh({LAST_NEWTICKET}, 30, '(', ')'); } );
    </script>
<!-- END switch_user_logged_in -->

<div id="footer">
    Template:&nbsp;<a href="http://www.ovensia.fr">{TEMPLATE_NAME}</a> |&nbsp;Propulsé par&nbsp;<a href="http://www.ploopi.fr">Ploopi {PLOOPI_VERSION} ({PLOOPI_REVISION})</a>&nbsp;&#169;&nbsp;2008&nbsp;<a href="http://www.ovensia.fr">Ovensia</a>&nbsp;|&nbsp;<a href="http://www.ploopi.org/#Utilisation">Documentation utilisateur</a>&nbsp;|&nbsp;<a href="http://www.mozilla-europe.org/fr/products/firefox/">Préférez Firefox</a>&nbsp;
    <br />[ page: <PLOOPI_PAGE_SIZE> ko | exec: <PLOOPI_EXEC_TIME> ms | sql: <PLOOPI_NUMQUERIES> req (<PLOOPI_SQL_P100> %) | session: <PLOOPI_SESSION_SIZE> ko | mem: <PLOOPI_PHP_MEMORY> ko | {SITE_CONNECTEDUSERS} connecté(s) - {SITE_ANONYMOUSUSERS} anonyme(s) ]&nbsp;
</div>

<!-- BEGIN switch_mod_message -->
<div id="{switch_mod_message.MSG_ID}" class="{switch_mod_message.MSG_CLASS}">{switch_mod_message.MSG}</div>
<script type="text/javascript">ploopi_window_onload_stock(function() { $('{switch_mod_message.MSG_ID}').fade({ duration: 3.0}); });</script>
<!-- END switch_mod_message -->

</body>
</html>
