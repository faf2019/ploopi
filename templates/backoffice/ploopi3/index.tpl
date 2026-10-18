<!DOCTYPE html>

<!--
    Ploopi ~ Template pour Ploopi
    Copyright (c) 2007-2026 Ovensia

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

<!--
    Gabarit ploopi3. Contrat avec le socle, à préserver :
    - #pagecontent.hook : conteneur défilant où ploopi.popup accroche les fenêtres ;
      sa position est calculée d'après la hauteur de #dock ;
    - #tpl_ploopi_tickets_new : compteur de messages ;
    - formulaire de connexion : #login_form, #ploopi_login, #ploopi_password,
      #ploopi_password_new, #ploopi_password_new_confirm, #checkpass, #lostpassword_form ;
    - blocs et variables alimentés par include/backoffice.php.
    Le moteur ignore le reste d'une ligne portant BEGIN ou END, et prend {mot} pour
    une variable : dans le script ci-dessous, jamais d'accolades collées.
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
    <!-- BEGIN switch_user_logged_out -->
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <!-- END switch_user_logged_out -->
    <link rel="apple-touch-icon" href="{TEMPLATE_PATH}/img/template/logo_ios.png" />

    <title>{WORKSPACE_TITLE}</title>

    <link rel="icon" href="{TEMPLATE_PATH}/img/favicon.png" type="image/png" />

    <link type="text/css" rel="stylesheet" href="{TEMPLATE_PATH}/css/styles.pack.css?v={PLOOPI_VERSION}" media="screen" />

    <!-- BEGIN module_css -->
    <link type="text/css" rel="stylesheet" href="{module_css.PATH}" media="screen" />
    <!-- END module_css -->

    <!-- BEGIN ploopi_js -->
    <script type="text/javascript" src="{ploopi_js.PATH}"></script>
    <!-- END ploopi_js -->

    <!-- BEGIN module_js -->
    <script type="text/javascript" src="{module_js.PATH}"></script>
    <!-- END module_js -->

    <script type="text/javascript" nonce="{CSP_NONCE}">
    {ADDITIONAL_JAVASCRIPT}

    ploopi.template = new Object();

    <!-- BEGIN switch_user_logged_out -->
    // Mot de passe perdu : le second formulaire prend la place du premier
    ploopi.template.passwordlost = function() {
        jQuery('#login').hide();
        jQuery('#lostpassword_form').fadeIn('fast', function() {
            jQuery('#ploopi_lostpassword_login').trigger('focus');
        });
    };

    // Identifiant ou adresse : l'un des deux suffit
    ploopi.template.passwordlost_submit = function(form) {
        if (jQuery.trim(form.ploopi_lostpassword_login.value) != '' || jQuery.trim(form.ploopi_lostpassword_email.value) != '') return true;
        form.ploopi_lostpassword_login.focus();
        return false;
    };

    ploopi.template.passwordlost_cancel = function() {
        jQuery('#lostpassword_form').hide();
        jQuery('#login').fadeIn('fast', function() {
            jQuery('#ploopi_login').trigger('focus');
        });
    };

    jQuery(function() {
        jQuery('#tpl_passwordlost').on('click', function(e) {
            e.preventDefault();
            ploopi.template.passwordlost();
        });
        jQuery('#tpl_passwordlost_cancel').on('click', function(e) {
            e.preventDefault();
            ploopi.template.passwordlost_cancel();
        });
        jQuery('#lostpassword_form').on('submit', function() {
            return ploopi.template.passwordlost_submit(this);
        });

        if (jQuery('#ploopi_password_new').length) jQuery('#ploopi_password_new')[0].focus();
        else if (jQuery('#ploopi_login').length) jQuery('#ploopi_login')[0].focus();
    });
    <!-- END switch_user_logged_out -->

    <!-- BEGIN switch_user_logged_in -->
    // #pagecontent occupe la fenêtre sous #dock, dont la hauteur varie
    // (menu du module courant présent ou non)
    ploopi.template.resize_content = function() {
        var top = jQuery('#dock').outerHeight();
        jQuery('#pagecontent').css({
            top: top,
            height: jQuery(window).height() - top
        });
        jQuery('#mainmenu div.blockmenu').css('max-height', (jQuery(window).height() - top - 20) + 'px');
    };

    ploopi.template.open_profile = function() {
        ploopi.popup.show('', 950, null, true, 'system_popup_update_profile');
        ploopi.xhr.todiv('admin-light.php', '{POPUP_PROFLE}', 'system_popup_update_profile');
    };

    ploopi.template.tickets_badge = function(nb) {
        jQuery('#tpl_messages').toggleClass('new_tickets', nb > 0).toggleClass('no_tickets', !(nb > 0));
    };

    // Conservée pour les appels existants
    function tpl_open_profile() {
        ploopi.template.open_profile();
    }

    jQuery(function() {
        ploopi.template.resize_content();
        jQuery(window).on('resize', ploopi.template.resize_content);

        jQuery('#tpl_workspace_select').on('change', function() {
            if (this.value != '') document.location.href = this.value;
        });

        jQuery('#tpl_profile').on('click', function(e) {
            e.preventDefault();
            ploopi.template.open_profile();
        });

        // Messages non lus : pastille mise en évidence, masquée à zéro ; tenue à jour
        // par ploopi.tickets.refresh (événement ploopi_tickets_count)
        ploopi.template.tickets_badge(parseInt(jQuery('#tpl_ploopi_tickets_new').text().replace(/[^0-9]/g, ''), 10));
        jQuery(document).on('ploopi_tickets_count', function(e, nb) {
            ploopi.template.tickets_badge(nb);
        });

        ploopi.tickets.refresh({LAST_NEWTICKET}, 30, '', '');
    });
    <!-- END switch_user_logged_in -->
    </script>

    {ADDITIONAL_HEAD}
</head>

<body>

<!-- BEGIN switch_user_logged_out -->
<div id="background" class="login">
    <div id="login_window">
        <div id="login_brand">
            <strong><span>P</span>Ploopi</strong>
            <em>{WORKSPACE_TITLE}</em>
        </div>

        <div id="login">
            <form id="login_form" action="{switch_user_logged_out.FORM_URL}" method="post">
                <h1>Connexion</h1>
                <p class="login_intro">Identifiez-vous pour accéder à votre espace de travail.</p>
                <div class="login_form_box">
                    <p>
                        <label for="ploopi_login">Identifiant</label>
                        <input type="text" class="text" id="ploopi_login" name="ploopi_login" value="{USER_LOGIN}" title="Saisir votre identifiant" placeholder="Votre identifiant" autocomplete="username" tabindex="1" />
                    </p>
                    <p>
                        <label for="ploopi_password">Mot de passe</label>
                        <input type="password" class="text" id="ploopi_password" name="ploopi_password" value="{USER_PASSWORD}" title="Saisir votre mot de passe" placeholder="Votre mot de passe" autocomplete="current-password" tabindex="2" />
                    </p>

                    <!-- BEGIN switch_passwordreset -->
                    <div class="login_message warning" role="alert">
                        <span><strong>Votre mot de passe a expiré.</strong><br />Vous devez en saisir un nouveau ci-dessous.</span>
                    </div>
                    <p>
                        <label for="ploopi_password_new">Nouveau mot de passe</label>
                        <input type="password" class="text" id="ploopi_password_new" name="ploopi_password_new" title="Saisir votre mot de passe" placeholder="Nouveau mot de passe" autocomplete="new-password" tabindex="3" />
                    </p>
                    <div id="checkpass"></div>
                    <p>
                        <label for="ploopi_password_new_confirm">Confirmation du mot de passe</label>
                        <input type="password" class="text" id="ploopi_password_new_confirm" name="ploopi_password_new_confirm" title="Saisir votre mot de passe" placeholder="Confirmation" autocomplete="new-password" tabindex="4" />
                    </p>

                    <script type="text/javascript" nonce="{CSP_NONCE}">
                        var backupColor = jQuery('#ploopi_password_new')[0].style.backgroundColor;

                        ploopi.template.verif_pass = function() {
                            if (jQuery('#ploopi_password_new')[0].value != '' && jQuery('#ploopi_password_new_confirm')[0].value != '') {
                                if (jQuery('#ploopi_password_new')[0].value == jQuery('#ploopi_password_new_confirm')[0].value) {
                                    jQuery('#ploopi_password_new_confirm')[0].style.backgroundColor = jQuery('#ploopi_password_new')[0].style.backgroundColor = '#e6f4ec';
                                } else {
                                    jQuery('#ploopi_password_new_confirm')[0].style.backgroundColor = jQuery('#ploopi_password_new')[0].style.backgroundColor = '#fdeceb';
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
                    <div class="login_message" role="status">
                        <span>{PLOOPI_MSG}</span>
                    </div>
                    <!-- END switch_ploopimsg -->

                    <!-- BEGIN switch_ploopierrormsg -->
                    <div class="login_message error" role="alert">
                        <span>{PLOOPI_ERROR}</span>
                    </div>
                    <!-- END switch_ploopierrormsg -->
                </div>

                <div class="login_actions">
                    <button type="submit" tabindex="5">Se connecter</button>
                    <a href="#" id="tpl_passwordlost" tabindex="6">Mot de passe perdu ?</a>
                </div>
            </form>
        </div>

        <form style="display:none;" id="lostpassword_form" action="{PASSWORDLOST_URL}" method="post">
            <h1>Mot de passe perdu</h1>
            <p class="login_intro">Indiquez votre identifiant ou votre adresse de courriel : un nouveau mot de passe vous sera envoyé.</p>
            <div class="login_form_box">
                <p>
                    <label for="ploopi_lostpassword_login">Identifiant</label>
                    <input type="text" class="text" id="ploopi_lostpassword_login" name="ploopi_lostpassword_login" size="20" title="Saisissez votre identifiant" autocomplete="username" tabindex="11" />
                </p>
                <p>
                    <label for="ploopi_lostpassword_email">ou adresse de courriel</label>
                    <input type="text" class="text" id="ploopi_lostpassword_email" name="ploopi_lostpassword_email" size="20" title="Saisissez votre adresse email" autocomplete="email" tabindex="12" />
                </p>
                <div class="login_message warning">
                    <span><strong>Attention</strong> : la demande génère un nouveau mot de passe, l'actuel ne fonctionnera plus.</span>
                </div>
            </div>
            <div class="login_actions">
                <button type="submit" tabindex="13">Envoyer</button>
                <a href="#" id="tpl_passwordlost_cancel" tabindex="14">Retour à la connexion</a>
            </div>
        </form>
    </div>

    <div id="login_statusbar">
        Propulsé par&nbsp;<a href="http://www.ploopi.fr">Ploopi</a>&nbsp;·&nbsp;Une création&nbsp;<a href="http://www.ovensia.fr">Ovensia</a>
    </div>
</div>
<!-- END switch_user_logged_out -->

<!-- BEGIN switch_user_logged_in -->
<div id="background" class="connected">

    <div id="dock">
        <div id="topbar">
            <span id="brand" title="Ploopi {PLOOPI_VERSION}"><span>P</span>Ploopi</span>

            <div id="workspace">
                <select class="dropdown" id="tpl_workspace_select" title="Choix d'un espace">
                    <!-- BEGIN workspace -->
                    <option value="{switch_user_logged_in.workspace.URL}" {switch_user_logged_in.workspace.SELECTED}>{switch_user_logged_in.workspace.TITLE}</option>
                    <!-- END workspace -->
                    <option value="" {USER_WORKSPACE_SEL}>(Choisir un espace)</option>
                </select>
            </div>

            <div id="userbox">
                <div title="Utilisateur connecté">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="8" r="4"></circle><path d="M4 21c0-4.4 3.6-8 8-8s8 3.6 8 8"></path></svg>
                    <span>{USER_FIRSTNAME} {USER_LASTNAME}</span>
                </div>
                <a id="tpl_messages" class="{MAINMENU_SHOWTICKETS_SEL}" href="{MAINMENU_SHOWTICKETS_URL}" title="{MAINMENU_TICKETS}">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"></rect><path d="M3 7l9 6 9-6"></path></svg>
                    <span>{MAINMENU_TICKETS}</span>
                    <em id="tpl_ploopi_tickets_new">{NEWTICKETS}</em>
                </a>
                <a id="tpl_profile" href="#" title="{MAINMENU_PROFILE}">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="16" rx="2"></rect><circle cx="9" cy="11" r="2.5"></circle><path d="M5.5 17c.7-1.8 2-2.6 3.5-2.6s2.8.8 3.5 2.6"></path><path d="M15 9h3"></path><path d="M15 13h3"></path></svg>
                </a>
                <a href="{USER_DECONNECT}" title="{MAINMENU_DISCONNECTION}">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 4h3a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2h-3"></path><path d="M10 17l-5-5 5-5"></path><path d="M5 12h11"></path></svg>
                </a>
            </div>

            <form id="searchbox" action="{MAINMENU_SHOWSEARCH_URL}" method="post" role="search">
                <input type="text" name="system_search_keywords" class="text" value="{SEARCH_KEYWORDS}" placeholder="Rechercher" title="Recherche intégrale" />
                <button type="submit" title="Rechercher">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="7"></circle><path d="M20 20l-3.5-3.5"></path></svg>
                </button>
            </form>
        </div>

        <!-- BEGIN switch_blockmenu -->
        <ul id="mainmenu">
            <!-- BEGIN block -->
            <li>
                <a class="mainmenu {switch_user_logged_in.switch_blockmenu.block.SELECTED}" href="{switch_user_logged_in.switch_blockmenu.block.URL}" title="Accéder au module &laquo; {switch_user_logged_in.switch_blockmenu.block.TITLE} &raquo;">{switch_user_logged_in.switch_blockmenu.block.TITLE}</a>
                <div class="blockmenu">
                <!-- BEGIN switch_content -->
                    <div class="blockcontent">{switch_user_logged_in.switch_blockmenu.block.switch_content.CONTENT}</div>
                <!-- END switch_content -->
                <!-- BEGIN menu -->
                    <a class="blockmenu {switch_user_logged_in.switch_blockmenu.block.menu.SELECTED}" href="{switch_user_logged_in.switch_blockmenu.block.menu.URL}" target="{switch_user_logged_in.switch_blockmenu.block.menu.TARGET}" title="Accéder au menu &laquo; {switch_user_logged_in.switch_blockmenu.block.menu.CLEANED_LABEL} &raquo;">{switch_user_logged_in.switch_blockmenu.block.menu.LABEL}</a>
                <!-- END menu -->
                </div>
            </li>
            <!-- END block -->
        </ul>

        <!-- BEGIN switch_blocksel -->
        <div id="modulemenu" role="navigation" aria-label="Menu du module {switch_user_logged_in.switch_blockmenu.switch_blocksel.TITLE}">
            <!-- BEGIN menu -->
            <a class="{switch_user_logged_in.switch_blockmenu.switch_blocksel.menu.SELECTED}" href="{switch_user_logged_in.switch_blockmenu.switch_blocksel.menu.URL}" target="{switch_user_logged_in.switch_blockmenu.switch_blocksel.menu.TARGET}">{switch_user_logged_in.switch_blockmenu.switch_blocksel.menu.LABEL}</a>
            <!-- END menu -->
        </div>
        <!-- END switch_blocksel -->
        <!-- END switch_blockmenu -->
    </div>

    <div id="ploopi_mod_mess" class=""></div>

    <div id="pagecontent" class="hook">
        {PAGE_CONTENT}
    </div>
</div>

<script type="text/javascript" nonce="{CSP_NONCE}">
console.log('page: <PLOOPI_PAGE_SIZE> ko | exec: <PLOOPI_EXEC_TIME> ms | sql: <PLOOPI_NUMQUERIES> req (<PLOOPI_SQL_P100> %) | session: <PLOOPI_SESSION_SIZE> ko | mem: <PLOOPI_PHP_MEMORY> ko');
</script>
<!-- END switch_user_logged_in -->

</body>
</html>
