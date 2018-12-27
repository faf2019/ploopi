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

    ploopi.template = {
    };
    //-->
    </script>

    {ADDITIONAL_HEAD}
</head>

<body class="index">

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
            <form id="login_form" action="admin.php" method="post">
                <div class="loginbox_line">
                    <label for="ploopi_login">Utilisateur:&nbsp;</label>
                    <input type="text" class="text" id="ploopi_login" name="ploopi_login" value="{USER_LOGIN}" tabindex="1" />
                </div>
                <div class="loginbox_line">
                    <label for="ploopi_password">Mot de passe:&nbsp;</label>
                    <input type="password" class="text" id="ploopi_password" name="ploopi_password" value="{USER_PASSWORD}" tabindex="2" />
                </div>

                <!-- BEGIN switch_passwordreset -->
                    <style>
                        #checkpass {padding:0 2px;}
                        #checkpass * {font-size:10px;}
                        #checkpass .password-strength-bar {border-radius:2px;}
                    </style>

                    <div style="color:#ff8800;padding:2px;"><strong>Votre mot de passe a expiré.</strong><br />Vous devez en saisir un nouveau ci-dessous:</div>

                    <div class="loginbox_line">
                        <label for="ploopi_password_new">Nouveau mot de passe:&nbsp;</label>
                        <input type="password" class="text" id="ploopi_password_new" name="ploopi_password_new" tabindex="3" />
                    </div>

                    <div id="checkpass"></div>

                    <div class="loginbox_line">
                        <label for="ploopi_password_new_confirm">Confirmation mot de passe:&nbsp;</label>
                        <input type="password" class="text" id="ploopi_password_new_confirm" name="ploopi_password_new_confirm" tabindex="4" />
                    </div>

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


                <div class="loginbox_line">
                    <input type="submit" value="Connexion" class="flatbutton" style="width:100%;" tabindex="6"/>
                </div>
            </form>
            <div class="loginbox_line" style="text-align:right;">
                <a href="javascript:void(0);" onclick="javascript:tpl_passwordlost();">Mot de passe perdu ?</a>
            </div>
            <form id="formpasswordlost" action="{PASSWORDLOST_URL}" method="post" style="display:none;" onsubmit="javascript:return ploopi.template.passwordlost_submit();">
                <div class="loginbox_line">
                    <label for="ploopi_lostpassword_login">utilisateur:&nbsp;</label>
                    <input type="text" class="text" id="ploopi_lostpassword_login" name="ploopi_lostpassword_login" tabindex="10" />
                </div>
                <div class="loginbox_line">
                    <label for="ploopi_lostpassword_email">(ou) email:&nbsp;</label>
                    <input type="text" class="text" id="ploopi_lostpassword_email" name="ploopi_lostpassword_email" tabindex="11" />
                </div>
                <div class="loginbox_line">
                    <em><strong>ATTENTION</strong>, une demande de mot de passe génère un nouveau mot de passe automatique.</em>
                </div>
                <div class="loginbox_line">
                    <input type="submit" value="Envoyer" class="button" style="width:49%;float:right;" tabindex="13" />
                    <input type="button" value="Annuler" class="button" style="width:49%;float:left;" tabindex="12" onclick="javascript:tpl_passwordlost_cancel();" />
                </div>
            </form>

            <!-- BEGIN switch_ploopimsg -->
            <div style="color:#a60000;overflow:auto;padding:2px;">
                <img style="display:block;float:left;" src="{TEMPLATE_PATH}/img/system/information.png" /><span style="display:block;margin-left:24px;">{PLOOPI_MSG}</span>
            </div>
            <!-- END switch_ploopimsg -->
            <!-- BEGIN switch_ploopierrormsg -->
            <div style="color:#a60000;overflow:auto;padding:2px;">
                <img style="display:block;float:left;" src="{TEMPLATE_PATH}/img/system/attention.png" /><span style="display:block;margin-left:24px;">{PLOOPI_ERROR}</span>
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
                            <img src="{TEMPLATE_PATH}/img/template/search.png" value="Recherche" style="cursor:pointer;" onclick="jQuery('#form_recherche')[0].submit();">
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
Template:&nbsp;<a href="http://www.ovensia.fr">{TEMPLATE_NAME}</a> |&nbsp;Propulsé par&nbsp;<a href="http://www.ploopi.fr">Ploopi</a>&nbsp;&#169;&nbsp;2016&nbsp;<a href="http://www.ovensia.fr">Ovensia</a>&nbsp;|&nbsp;<a href="http://www.ploopi.org/#Utilisation">Documentation utilisateur</a>&nbsp;|&nbsp;<a href="http://www.mozilla-europe.org/fr/products/firefox/">Préférez Firefox</a>
</p>

<!-- BEGIN switch_user_logged_out -->
<script type="text/javascript">
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

ploopi.template.passwordlost_submit() {
    if (jQuery('#ploopi_lostpassword_login')[0].value != '' || jQuery('#ploopi_lostpassword_email')[0].value != '') return true;
    else alert('Vous devez remplir un des deux champs');

    return false;
}

jQuery(function() {
    if (jQuery('#ploopi_password_new').length) jQuery('#ploopi_password_new')[0].focus();
    else if (jQuery('#ploopi_login').length) jQuery('#ploopi_login')[0].focus();
});

</script>
<!-- END switch_user_logged_out -->

<!-- BEGIN switch_user_logged_in -->
<script type="text/javascript">
jQuery(function() { ploopi.tickets.refresh({LAST_NEWTICKET}, 30, '(', ')'); });
</script>
<!-- END switch_user_logged_in -->

</body>
</html>
