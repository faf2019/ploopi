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
    Gabarit ploopi4. Contrat avec le socle et les modules, à préserver :
    - #pagecontent.hook : conteneur défilant où ploopi.popup accroche les fenêtres ;
    - #background : enveloppe de l'application ;
    - #tpl_ploopi_tickets_new : compteur de messages, tenu à jour par ploopi.tickets.refresh ;
    - formulaire de connexion : #login_form, #ploopi_login, #ploopi_password,
      #ploopi_password_new, #ploopi_password_new_confirm, #checkpass, #lostpassword_form ;
    - blocs et variables alimentés par include/backoffice.php.
    Aucun script ni gestionnaire d'évènement en ligne : le comportement est dans
    js/shell.js, qui lit les attributs data-p4-*. Le moteur ignore le reste d'une
    ligne portant BEGIN ou END et prend {mot} pour une variable.
    -->

<html lang="fr" data-theme="{USER_THEME}" data-density="{USER_DENSITY}">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />
    <meta name="description" content="{WORKSPACE_META_DESCRIPTION}" />
    <meta name="keywords" content="{WORKSPACE_META_KEYWORDS}" />
    <meta name="author" content="{WORKSPACE_META_AUTHOR}" />
    <meta name="copyright" content="{WORKSPACE_META_COPYRIGHT}" />
    <meta name="robots" content="{WORKSPACE_META_ROBOTS}" />
    <meta name="theme-color" content="#1e3d44" />
    <link rel="apple-touch-icon" href="{TEMPLATE_PATH}/img/template/logo_ios.png" />

    <title>{WORKSPACE_TITLE}</title>

    <link rel="icon" href="{TEMPLATE_PATH}/img/favicon.png" type="image/png" />
    <link rel="preload" href="{TEMPLATE_PATH}/fonts/Inter-latin.woff2" as="font" type="font/woff2" crossorigin="anonymous" />

    <link type="text/css" rel="stylesheet" href="{TEMPLATE_PATH}/css/styles.pack.css?v={PLOOPI_VERSION}" media="screen" />
    <!-- BEGIN template_palette -->
    <link type="text/css" rel="stylesheet" href="{template_palette.PATH}" media="screen" />
    <!-- END template_palette -->

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
    </script>

    <script type="text/javascript" src="{TEMPLATE_PATH}/js/shell.js?v={PLOOPI_VERSION}"></script>

    {ADDITIONAL_HEAD}
</head>

<!-- BEGIN switch_user_logged_out -->
<body class="p4-body p4-body-login" data-p4-sprite="{TEMPLATE_PATH}/img/icons.svg">

<div id="background" class="p4-login p4-ui">
    <div class="p4-login-visual" aria-hidden="true">
        <div class="p4-login-tagline">
            <strong>{WORKSPACE_LABEL}</strong>
            <span>Espace de travail collaboratif</span>
        </div>
    </div>

    <section class="p4-login-panel" aria-labelledby="p4-login-title">
        <div class="p4-login-brand">
            <img class="p4-brand-mark" src="{TEMPLATE_PATH}/img/logo.svg" alt="" />
            <span><strong>ploopi</strong><small>{WORKSPACE_LABEL}</small></span>
        </div>

        <div id="login" class="p4-login-view">
            <form id="login_form" class="p4-login-form" action="{switch_user_logged_out.FORM_URL}" method="post">
                <h1 id="p4-login-title">Connexion</h1>
                <p class="p4-lead">Identifiez-vous pour accéder à votre espace de travail.</p>

                <div class="p4-field">
                    <label for="ploopi_login">Identifiant</label>
                    <div class="p4-input-icon">
                        <svg class="p4-icon" aria-hidden="true"><use href="{TEMPLATE_PATH}/img/icons.svg#i-user"/></svg>
                        <input type="text" class="p4-input" id="ploopi_login" name="ploopi_login" value="{USER_LOGIN}" placeholder="Votre identifiant" autocomplete="username" autocapitalize="none" spellcheck="false" tabindex="1" />
                    </div>
                </div>

                <div class="p4-field">
                    <div class="p4-label-row">
                        <label class="p4-label" for="ploopi_password">Mot de passe</label>
                        <button type="button" class="p4-link-btn" id="tpl_passwordlost" tabindex="6">Mot de passe perdu&nbsp;?</button>
                    </div>
                    <div class="p4-input-icon p4-pw">
                        <svg class="p4-icon" aria-hidden="true"><use href="{TEMPLATE_PATH}/img/icons.svg#i-key"/></svg>
                        <input type="password" class="p4-input" id="ploopi_password" name="ploopi_password" value="{USER_PASSWORD}" placeholder="Votre mot de passe" autocomplete="current-password" tabindex="2" />
                        <button type="button" class="p4-pw-toggle" data-p4-pw="ploopi_password" aria-label="Afficher le mot de passe" aria-pressed="false">
                            <svg class="p4-icon" aria-hidden="true"><use href="{TEMPLATE_PATH}/img/icons.svg#i-eye"/></svg>
                        </button>
                    </div>
                    <div class="p4-caps" data-p4-caps="ploopi_password"><svg class="p4-icon p4-icon-sm" aria-hidden="true"><use href="{TEMPLATE_PATH}/img/icons.svg#i-alert-triangle"/></svg>Verrouillage des majuscules activé</div>
                </div>

                <!-- BEGIN switch_passwordreset -->
                <div class="p4-login-message p4-warning" role="alert">
                    <svg class="p4-icon" aria-hidden="true"><use href="{TEMPLATE_PATH}/img/icons.svg#i-alert-triangle"/></svg>
                    <span><strong>Votre mot de passe a expiré.</strong><br />Choisissez-en un nouveau ci-dessous.</span>
                </div>
                <div class="p4-field">
                    <label for="ploopi_password_new">Nouveau mot de passe</label>
                    <input type="password" class="p4-input" id="ploopi_password_new" name="ploopi_password_new" placeholder="Nouveau mot de passe" autocomplete="new-password" tabindex="3" />
                    <div id="checkpass"></div>
                </div>
                <div class="p4-field">
                    <label for="ploopi_password_new_confirm">Confirmation du mot de passe</label>
                    <input type="password" class="p4-input" id="ploopi_password_new_confirm" name="ploopi_password_new_confirm" placeholder="Confirmation" autocomplete="new-password" tabindex="4" />
                </div>
                <!-- BEGIN switch_np -->
                <span hidden data-p4-checkpass="6"></span>
                <!-- END switch_np -->
                <!-- BEGIN switch_cp -->
                <span hidden data-p4-checkpass="{switch_user_logged_out.switch_passwordreset.switch_cp.MIN_SIZE}"></span>
                <!-- END switch_cp -->
                <!-- END switch_passwordreset -->

                <!-- BEGIN switch_ploopimsg -->
                <div class="p4-login-message" role="status">
                    <svg class="p4-icon" aria-hidden="true"><use href="{TEMPLATE_PATH}/img/icons.svg#i-info-circle"/></svg>
                    <span>{PLOOPI_MSG}</span>
                </div>
                <!-- END switch_ploopimsg -->

                <!-- BEGIN switch_ploopierrormsg -->
                <div class="p4-login-message p4-error" role="alert">
                    <svg class="p4-icon" aria-hidden="true"><use href="{TEMPLATE_PATH}/img/icons.svg#i-circle-x"/></svg>
                    <span>{PLOOPI_ERROR}</span>
                </div>
                <!-- END switch_ploopierrormsg -->

                <button type="submit" class="p4-btn p4-btn-primary p4-btn-block" tabindex="5">
                    <span>Se connecter</span>
                    <svg class="p4-icon" aria-hidden="true"><use href="{TEMPLATE_PATH}/img/icons.svg#i-chevron-right"/></svg>
                </button>
            </form>
        </div>

        <form id="lostpassword_form" class="p4-login-form p4-login-view" action="{PASSWORDLOST_URL}" method="post" hidden>
            <h1>Mot de passe perdu</h1>
            <p class="p4-lead">Indiquez votre identifiant ou votre adresse de courriel : un lien vous sera envoyé pour en choisir un nouveau.</p>

            <div class="p4-field">
                <label for="ploopi_lostpassword_login">Identifiant</label>
                <div class="p4-input-icon">
                    <svg class="p4-icon" aria-hidden="true"><use href="{TEMPLATE_PATH}/img/icons.svg#i-user"/></svg>
                    <input type="text" class="p4-input" id="ploopi_lostpassword_login" name="ploopi_lostpassword_login" autocomplete="username" tabindex="11" />
                </div>
            </div>
            <div class="p4-field">
                <label for="ploopi_lostpassword_email">ou adresse de courriel</label>
                <div class="p4-input-icon">
                    <svg class="p4-icon" aria-hidden="true"><use href="{TEMPLATE_PATH}/img/icons.svg#i-mail"/></svg>
                    <input type="text" class="p4-input" id="ploopi_lostpassword_email" name="ploopi_lostpassword_email" autocomplete="email" tabindex="12" />
                </div>
            </div>

            <button type="submit" class="p4-btn p4-btn-primary p4-btn-block" tabindex="13">
                <span>Envoyer</span>
                <svg class="p4-icon" aria-hidden="true"><use href="{TEMPLATE_PATH}/img/icons.svg#i-chevron-right"/></svg>
            </button>
            <button type="button" class="p4-back-link" id="tpl_passwordlost_cancel" tabindex="14">
                <svg class="p4-icon p4-icon-sm" aria-hidden="true"><use href="{TEMPLATE_PATH}/img/icons.svg#i-chevron-left"/></svg>Retour à la connexion
            </button>
        </form>

        <footer class="p4-login-foot">
            <span>Propulsé par <a href="https://www.ploopi.fr">Ploopi</a> · Une création <a href="https://www.ovensia.fr">Ovensia</a></span>
            <span class="p4-theme-switch" role="group" aria-label="Thème">
                <button type="button" data-p4-theme="light" aria-label="Thème clair" title="Thème clair"><svg class="p4-icon" aria-hidden="true"><use href="{TEMPLATE_PATH}/img/icons.svg#i-sun"/></svg></button>
                <button type="button" data-p4-theme="auto" aria-label="Thème du système" title="Thème du système"><svg class="p4-icon" aria-hidden="true"><use href="{TEMPLATE_PATH}/img/icons.svg#i-device-desktop"/></svg></button>
                <button type="button" data-p4-theme="dark" aria-label="Thème sombre" title="Thème sombre"><svg class="p4-icon" aria-hidden="true"><use href="{TEMPLATE_PATH}/img/icons.svg#i-moon"/></svg></button>
            </span>
        </footer>
    </section>
</div>

</body>
<!-- END switch_user_logged_out -->

<!-- BEGIN switch_user_logged_in -->
<body class="p4-body" data-p4-module="{MODULE_TYPE}" data-p4-sprite="{TEMPLATE_PATH}/img/icons.svg" data-p4-lastticket="{LAST_NEWTICKET}">

<a class="p4-skiplink" href="#p4-main">Aller au contenu</a>

<div id="background" class="p4-app">

    <header class="p4-topbar p4-ui">
        <button type="button" class="p4-btn p4-btn-ghost p4-btn-icon" id="p4-sidebar-toggle" aria-label="Afficher ou masquer le menu" aria-controls="p4-sidebar">
            <svg class="p4-icon" aria-hidden="true"><use href="{TEMPLATE_PATH}/img/icons.svg#i-menu-2"/></svg>
        </button>

        <a class="p4-brand" href="{USER_WORKSPACE_URL}" title="Ploopi {PLOOPI_VERSION}">
            <img class="p4-brand-mark" src="{TEMPLATE_PATH}/img/logo.svg" alt="" />
            <span class="p4-brand-name">ploopi</span>
            <span class="p4-brand-ws">{WORKSPACE_TITLE}</span>
        </a>

        <form class="p4-search" id="p4-search" action="{MAINMENU_SHOWSEARCH_URL}" method="post" role="search" data-p4-suggest="{MAINMENU_SEARCHSUGGEST_URL}">
            <svg class="p4-icon" aria-hidden="true"><use href="{TEMPLATE_PATH}/img/icons.svg#i-search"/></svg>
            <input type="search" class="p4-input" id="p4-search-input" name="system_search_keywords" value="{SEARCH_KEYWORDS}" placeholder="Rechercher dans les contenus…" autocomplete="off" aria-label="Recherche intégrale" role="combobox" aria-autocomplete="list" aria-expanded="false" aria-controls="p4-search-suggest" />
            <span class="p4-kbd">Ctrl K</span>
            <div class="p4-suggest" id="p4-search-suggest" role="listbox" aria-label="Suggestions" hidden></div>
        </form>

        <div class="p4-topbar-actions">
            <button type="button" class="p4-btn p4-btn-ghost p4-btn-icon p4-show-sm" id="p4-search-open" aria-label="Rechercher">
                <svg class="p4-icon" aria-hidden="true"><use href="{TEMPLATE_PATH}/img/icons.svg#i-search"/></svg>
            </button>

            <a id="tpl_messages" class="p4-btn p4-btn-ghost p4-btn-icon p4-notif" href="{MAINMENU_SHOWTICKETS_URL}" title="{MAINMENU_TICKETS}" aria-label="{MAINMENU_TICKETS}">
                <svg class="p4-icon" aria-hidden="true"><use href="{TEMPLATE_PATH}/img/icons.svg#i-mail"/></svg>
                <em id="tpl_ploopi_tickets_new" class="p4-count">{NEWTICKETS}</em>
            </a>

            <div class="p4-menu-wrap">
                <button type="button" class="p4-user-btn" data-p4-menu="p4-user-menu" aria-expanded="false" aria-haspopup="true" aria-label="Menu utilisateur">
                    <span class="p4-avatar">{USER_INITIALS}</span>
                    <span class="p4-user-name">{USER_FIRSTNAME} {USER_LASTNAME}<small>{USER_LEVEL}</small></span>
                    <svg class="p4-icon p4-icon-sm" aria-hidden="true"><use href="{TEMPLATE_PATH}/img/icons.svg#i-chevron-down"/></svg>
                </button>
                <div class="p4-menu p4-user-menu" id="p4-user-menu">
                    <div class="p4-user-head">
                        <span class="p4-avatar">{USER_INITIALS}</span>
                        <span class="p4-user-ident"><strong>{USER_FIRSTNAME} {USER_LASTNAME}</strong><small>{WORKSPACE_LABEL} · {USER_LEVEL}</small></span>
                    </div>
                    <div class="p4-menu-sep"></div>
                    <a class="p4-menu-item" id="tpl_profile" href="{MAINMENU_SHOWPROFILE_URL}" data-p4-profile-required="{MAINMENU_PROFILE_REQUIRED}">
                        <svg class="p4-icon" aria-hidden="true"><use href="{TEMPLATE_PATH}/img/icons.svg#i-user-circle"/></svg>{MAINMENU_PROFILE}
                    </a>
                    <a class="p4-menu-item" href="{USER_WORKSPACE_URL}">
                        <svg class="p4-icon" aria-hidden="true"><use href="{TEMPLATE_PATH}/img/icons.svg#i-home"/></svg>{USER_WORKSPACE_LABEL}
                    </a>
                    <div class="p4-menu-title">Apparence</div>
                    <div class="p4-menu-pad" data-p4-keep-open>
                        <div class="p4-seg p4-seg-block" role="group" aria-label="Thème">
                            <button type="button" data-p4-theme="light"><svg class="p4-icon p4-icon-sm" aria-hidden="true"><use href="{TEMPLATE_PATH}/img/icons.svg#i-sun"/></svg>Clair</button>
                            <button type="button" data-p4-theme="auto"><svg class="p4-icon p4-icon-sm" aria-hidden="true"><use href="{TEMPLATE_PATH}/img/icons.svg#i-device-desktop"/></svg>Auto</button>
                            <button type="button" data-p4-theme="dark"><svg class="p4-icon p4-icon-sm" aria-hidden="true"><use href="{TEMPLATE_PATH}/img/icons.svg#i-moon"/></svg>Sombre</button>
                        </div>
                    </div>
                    <div class="p4-menu-title">Densité d'affichage</div>
                    <div class="p4-menu-pad" data-p4-keep-open>
                        <div class="p4-seg p4-seg-block" role="group" aria-label="Densité">
                            <button type="button" data-p4-density="comfortable"><svg class="p4-icon p4-icon-sm" aria-hidden="true"><use href="{TEMPLATE_PATH}/img/icons.svg#i-baseline-density-medium"/></svg>Confortable</button>
                            <button type="button" data-p4-density="compact"><svg class="p4-icon p4-icon-sm" aria-hidden="true"><use href="{TEMPLATE_PATH}/img/icons.svg#i-baseline-density-small"/></svg>Compacte</button>
                        </div>
                    </div>
                    <div class="p4-menu-sep"></div>
                    <button type="button" class="p4-menu-item" data-p4-open="p4-shortcuts">
                        <svg class="p4-icon" aria-hidden="true"><use href="{TEMPLATE_PATH}/img/icons.svg#i-keyboard"/></svg>Raccourcis clavier
                    </button>
                    <a class="p4-menu-item p4-danger" href="{USER_DECONNECT}">
                        <svg class="p4-icon" aria-hidden="true"><use href="{TEMPLATE_PATH}/img/icons.svg#i-logout"/></svg>{MAINMENU_DISCONNECTION}
                    </a>
                </div>
            </div>
        </div>
    </header>

    <aside class="p4-sidebar p4-ui" id="p4-sidebar" aria-label="Navigation principale">
        <div class="p4-sidebar-head">
            <img class="p4-brand-mark" src="{TEMPLATE_PATH}/img/logo.svg" alt="" />
            <span class="p4-brand-name">ploopi</span>
            <button type="button" class="p4-btn p4-btn-ghost p4-btn-icon" id="p4-sidebar-close" aria-label="Fermer le menu">
                <svg class="p4-icon" aria-hidden="true"><use href="{TEMPLATE_PATH}/img/icons.svg#i-x"/></svg>
            </button>
        </div>

        <div class="p4-menu-wrap">
            <button type="button" class="p4-ws-switch" data-p4-menu="p4-ws-menu" aria-expanded="false" aria-haspopup="true" title="Changer d'espace de travail">
                <span class="p4-ws-mark">{WORKSPACE_INITIAL}</span>
                <span class="p4-ws-text"><small>Espace de travail</small><strong>{WORKSPACE_LABEL}</strong></span>
                <svg class="p4-icon p4-icon-sm" aria-hidden="true"><use href="{TEMPLATE_PATH}/img/icons.svg#i-selector"/></svg>
            </button>
            <div class="p4-menu p4-menu-left p4-ws-menu" id="p4-ws-menu">
                <div class="p4-menu-title">Espaces</div>
                <!-- BEGIN workspace -->
                <a class="p4-menu-item {switch_user_logged_in.workspace.SELECTED}" href="{switch_user_logged_in.workspace.URL}">
                    <span class="p4-ws-mark">{switch_user_logged_in.workspace.INITIAL}</span>
                    <span class="p4-grow">{switch_user_logged_in.workspace.TITLE}</span>
                    <svg class="p4-icon p4-check" aria-hidden="true"><use href="{TEMPLATE_PATH}/img/icons.svg#i-check"/></svg>
                </a>
                <!-- END workspace -->
                <div class="p4-menu-sep"></div>
                <a class="p4-menu-item {USER_WORKSPACE_SEL}" href="{USER_WORKSPACE_URL}">
                    <svg class="p4-icon" aria-hidden="true"><use href="{TEMPLATE_PATH}/img/icons.svg#i-home"/></svg>
                    <span class="p4-grow">{USER_WORKSPACE_LABEL}</span>
                    <svg class="p4-icon p4-check" aria-hidden="true"><use href="{TEMPLATE_PATH}/img/icons.svg#i-check"/></svg>
                </a>
            </div>
        </div>

        <nav class="p4-nav" aria-label="Modules">
            <!-- BEGIN switch_blockmenu -->
            <div class="p4-nav-title">Modules</div>
            <!-- BEGIN block -->
            <div class="p4-nav-module {switch_user_logged_in.switch_blockmenu.block.SELECTED}">
                <a class="{switch_user_logged_in.switch_blockmenu.block.SELECTED}" href="{switch_user_logged_in.switch_blockmenu.block.URL}" title="{switch_user_logged_in.switch_blockmenu.block.TITLE}">
                    <svg class="p4-icon" aria-hidden="true"><use href="{TEMPLATE_PATH}/img/icons.svg#i-{switch_user_logged_in.switch_blockmenu.block.ICON}"/></svg>
                    <span>{switch_user_logged_in.switch_blockmenu.block.TITLE}</span>
                </a>
                <div class="p4-nav-sub">
                    <!-- BEGIN switch_content -->
                    <div class="p4-nav-content">{switch_user_logged_in.switch_blockmenu.block.switch_content.CONTENT}</div>
                    <!-- END switch_content -->
                    <!-- BEGIN menu -->
                    <!-- BEGIN switch_section -->
                    <div class="p4-nav-title">{switch_user_logged_in.switch_blockmenu.block.menu.switch_section.TITLE}</div>
                    <!-- END switch_section -->
                    <a class="{switch_user_logged_in.switch_blockmenu.block.menu.SELECTED}" href="{switch_user_logged_in.switch_blockmenu.block.menu.URL}" target="{switch_user_logged_in.switch_blockmenu.block.menu.TARGET}" title="{switch_user_logged_in.switch_blockmenu.block.menu.CLEANED_LABEL}">
                        <!-- BEGIN switch_icon -->
                        <svg class="p4-icon" aria-hidden="true"><use href="{TEMPLATE_PATH}/img/icons.svg#i-{switch_user_logged_in.switch_blockmenu.block.menu.switch_icon.ICON}"/></svg>
                        <!-- END switch_icon -->
                        <span>{switch_user_logged_in.switch_blockmenu.block.menu.LABEL}</span>
                    </a>
                    <!-- END menu -->
                </div>
            </div>
            <!-- END block -->
            <!-- END switch_blockmenu -->
        </nav>

        <div class="p4-sidebar-foot">
            <button type="button" class="p4-btn p4-btn-ghost p4-btn-icon p4-btn-sm" id="p4-sidebar-collapse" aria-label="Réduire ou déplier le menu" title="Réduire le menu">
                <svg class="p4-icon p4-icon-sm" aria-hidden="true"><use href="{TEMPLATE_PATH}/img/icons.svg#i-layout-sidebar-left-collapse"/></svg>
            </button>
            <span>Ploopi {PLOOPI_VERSION} · Ovensia</span>
        </div>
    </aside>
    <div class="p4-backdrop" id="p4-backdrop"></div>

    <main class="p4-main" id="p4-main" tabindex="-1">
        <div id="pagecontent" class="hook">
            <nav class="p4-breadcrumb p4-ui" aria-label="Fil d'Ariane">
                <!-- BEGIN crumb -->
                <a href="{switch_user_logged_in.crumb.URL}" aria-current="{switch_user_logged_in.crumb.CURRENT}">{switch_user_logged_in.crumb.LABEL}</a>
                <svg class="p4-icon p4-sep" aria-hidden="true"><use href="{TEMPLATE_PATH}/img/icons.svg#i-chevron-right"/></svg>
                <!-- END crumb -->
            </nav>
            <div id="ploopi_mod_mess"></div>
            {PAGE_CONTENT}
        </div>
    </main>

    <div class="p4-drawer-backdrop" data-p4-close></div>
    <div class="p4-modal p4-ui" id="p4-shortcuts" role="dialog" aria-modal="true" aria-labelledby="p4-shortcuts-title">
        <div class="p4-drawer-header">
            <svg class="p4-icon" aria-hidden="true"><use href="{TEMPLATE_PATH}/img/icons.svg#i-keyboard"/></svg>
            <h2 id="p4-shortcuts-title">Raccourcis clavier</h2>
            <button type="button" class="p4-btn p4-btn-ghost p4-btn-icon" data-p4-close aria-label="Fermer">
                <svg class="p4-icon" aria-hidden="true"><use href="{TEMPLATE_PATH}/img/icons.svg#i-x"/></svg>
            </button>
        </div>
        <div class="p4-drawer-body">
            <ul class="p4-shortcuts">
                <li><span>Rechercher</span><span><span class="p4-kbd">Ctrl</span> <span class="p4-kbd">K</span> ou <span class="p4-kbd">/</span></span></li>
                <li><span>Afficher ou masquer le menu</span><span><span class="p4-kbd">Alt</span> <span class="p4-kbd">M</span></span></li>
                <li><span>Fermer un menu, un tiroir, une fenêtre</span><span class="p4-kbd">Échap</span></li>
                <li><span>Cette aide</span><span><span class="p4-kbd">?</span></span></li>
            </ul>
        </div>
    </div>
</div>

</body>
<!-- END switch_user_logged_in -->

</html>
