<!DOCTYPE html>
<!-- Gabarit « corporate », page d'accueil : index.tpl + bandeau et cartes des rubriques. Reporter ici toute modification d'index.tpl. -->
<html lang="fr">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="description" content="{WORKSPACE_META_DESCRIPTION} {PAGE_META_DESCRIPTION}" />
    <meta name="keywords" content="{WORKSPACE_META_KEYWORDS}, {PAGE_META_KEYWORDS}" />
    <meta name="author" content="{WORKSPACE_META_AUTHOR}" />
    <meta name="copyright" content="{WORKSPACE_META_COPYRIGHT}" />
    <meta name="robots" content="{WORKSPACE_META_ROBOTS}" />
    <meta name="color-scheme" content="light dark" />

    <title>{PAGE_TITLE} · {WORKSPACE_TITLE}</title>

    <base href="{SITE_BASEPATH}" />
    <link rel="icon" type="image/svg+xml" href="{TEMPLATE_PATH}/img/favicon.svg" />

    <!-- BEGIN switch_atomfeed_site -->
    <link rel="alternate" type="application/atom+xml" href="{switch_atomfeed_site.URL}" title="Atom - {switch_atomfeed_site.TITLE}" />
    <!-- END switch_atomfeed_site -->
    <!-- BEGIN switch_atomfeed_heading -->
    <link rel="alternate" type="application/atom+xml" href="{switch_atomfeed_heading.URL}" title="Atom - {switch_atomfeed_heading.TITLE}" />
    <!-- END switch_atomfeed_heading -->
    <!-- BEGIN switch_rssfeed_site -->
    <link rel="alternate" type="application/rss+xml" href="{switch_rssfeed_site.URL}" title="RSS - {switch_rssfeed_site.TITLE}" />
    <!-- END switch_rssfeed_site -->
    <!-- BEGIN switch_rssfeed_heading -->
    <link rel="alternate" type="application/rss+xml" href="{switch_rssfeed_heading.URL}" title="RSS - {switch_rssfeed_heading.TITLE}" />
    <!-- END switch_rssfeed_heading -->
    <!-- BEGIN switch_docfeed -->
        <!-- BEGIN atom -->
    <link rel="alternate" type="application/atom+xml" href="{switch_docfeed.atom.URL}" title="Atom - {switch_docfeed.atom.TITLE}" />
        <!-- END atom -->
        <!-- BEGIN rss -->
    <link rel="alternate" type="application/rss+xml" href="{switch_docfeed.rss.URL}" title="RSS - {switch_docfeed.rss.TITLE}" />
        <!-- END rss -->
    <!-- END switch_docfeed -->

    <link rel="stylesheet" href="{TEMPLATE_PATH}/css/corporate.css?v={PLOOPI_VERSION}" />
    <link rel="stylesheet" href="{TEMPLATE_PATH}/css/ploopi.css?v={PLOOPI_VERSION}" />
    <!-- BEGIN module_css -->
    <link rel="stylesheet" href="{module_css.PATH}" />
    <!-- END module_css -->

    <!-- BEGIN ploopi_js -->
    <script src="{ploopi_js.PATH}"></script>
    <!-- END ploopi_js -->
    <!-- BEGIN module_js -->
    <script src="{module_js.PATH}"></script>
    <!-- END module_js -->
    <script src="{TEMPLATE_PATH}/js/corporate.js?v={PLOOPI_VERSION}" defer></script>
    <script>
    //<!--
    {ADDITIONAL_JAVASCRIPT}
    //-->
    </script>
    {ADDITIONAL_HEAD}
    <!-- BEGIN switch_content_page -->
    {PAGE_HEADCONTENT}
    <!-- END switch_content_page -->
</head>

<body class="c-home">
<a class="c-skip" href="#contenu">Aller au contenu</a>

<header class="c-header" data-c-header>
    <div class="c-container c-header__inner">
        <a class="c-brand" href="{SITE_HOME}" title="Accueil">
            <span class="c-brand__mark" aria-hidden="true"></span>
            <span class="c-brand__name">{SITE_TITLE}</span>
        </a>

        <button class="c-burger" type="button" aria-controls="c-nav" aria-expanded="false" data-c-burger>
            <span></span><span></span><span></span>
            <span class="c-sr">Menu</span>
        </button>

        <nav class="c-nav" id="c-nav" aria-label="Navigation principale">
            <ul class="c-nav__list">
                <!-- BEGIN root1 -->
                    <!-- BEGIN heading1 -->
                    <li class="c-nav__item {root1.heading1.SEL}">
                        <a class="c-nav__link" href="{root1.heading1.LINK}" {root1.heading1.LINK_TARGET}>{root1.heading1.LABEL}</a>
                        <!-- BEGIN switch_submenu -->
                        <button class="c-nav__toggle" type="button" aria-expanded="false" data-c-submenu><span class="c-sr">Sous-menu de {root1.heading1.LABEL}</span></button>
                        <!-- END switch_submenu -->
                        <ul class="c-nav__sub">
                            <!-- BEGIN heading2 -->
                            <li class="{root1.heading1.heading2.SEL}"><a href="{root1.heading1.heading2.LINK}" {root1.heading1.heading2.LINK_TARGET}>{root1.heading1.heading2.LABEL}</a></li>
                            <!-- END heading2 -->
                        </ul>
                    </li>
                    <!-- END heading1 -->
                <!-- END root1 -->
            </ul>

            <form class="c-search" method="post" action="{SITE_HOME}" role="search">
                <label class="c-sr" for="c-search-field">Rechercher</label>
                <input type="search" id="c-search-field" name="query_string" value="{PAGE_QUERYSTRING}" placeholder="Rechercher…" />
                <button type="submit" aria-label="Lancer la recherche"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg></button>
            </form>

            <div class="c-account">
                <!-- BEGIN switch_user_logged_out -->
                <details class="c-login">
                    <summary class="c-btn c-btn--ghost">Connexion</summary>
                    <form class="c-login__form" action="" method="post">
                        <label for="c-login">Identifiant</label>
                        <input type="text" id="c-login" name="ploopi_login" autocomplete="username" />
                        <label for="c-password">Mot de passe</label>
                        <input type="password" id="c-password" name="ploopi_password" autocomplete="current-password" />
                        <button type="submit" class="c-btn c-btn--primary">Se connecter</button>
                    </form>
                </details>
                <!-- END switch_user_logged_out -->
                <!-- BEGIN switch_user_logged_in -->
                <span class="c-account__name">{USER_FIRSTNAME} {USER_LASTNAME}</span>
                <a class="c-btn c-btn--ghost" href="{USER_DECONNECT}">Déconnexion</a>
                <!-- END switch_user_logged_in -->
            </div>
        </nav>
    </div>
</header>

<section class="c-hero" aria-labelledby="c-hero-title">
    <div class="c-container c-hero__inner">
        <span class="c-hero__badge">{SITE_TITLE}</span>
        <h1 class="c-hero__title" id="c-hero-title">{PAGE_TITLE}</h1>
        <p class="c-hero__lead">{WORKSPACE_META_DESCRIPTION}</p>
        <div class="c-hero__actions">
            <a class="c-btn c-btn--light" href="#contenu">Découvrir</a>
            <a class="c-btn c-btn--outline-light" href="./recherche.html">Rechercher sur le site</a>
        </div>
    </div>
</section>

<section class="c-features" aria-label="Rubriques">
    <div class="c-container c-features__grid">
        <!-- BEGIN root1 -->
            <!-- BEGIN heading1 -->
            <a class="c-feature" href="{root1.heading1.LINK}" {root1.heading1.LINK_TARGET}>
                <span class="c-feature__icon" aria-hidden="true">{root1.heading1.POSITION}</span>
                <span class="c-feature__title">{root1.heading1.LABEL}</span>
                <span class="c-feature__text">{root1.heading1.DESCRIPTION}</span>
                <span class="c-feature__more">En savoir plus →</span>
            </a>
            <!-- END heading1 -->
        <!-- END root1 -->
    </div>
</section>

<main class="c-container c-layout" id="contenu">
    <article class="c-main">

        <!-- BEGIN switch_pages -->
        <nav class="c-pages" aria-label="Pages de la rubrique">
            <!-- BEGIN page -->
            <a class="c-pages__item {switch_pages.page.SEL}" href="{switch_pages.page.LINK}">{switch_pages.page.LABEL}</a>
            <!-- END page -->
        </nav>
        <!-- END switch_pages -->

        <!-- BEGIN switch_private -->
        <div class="c-notice">
            <h2>Cette rubrique est privée</h2>
            <!-- BEGIN switch_notconnected -->
            <p>Identifiez-vous (bouton « Connexion » en haut de page) pour accéder à son contenu.</p>
            <!-- END switch_notconnected -->
            <!-- BEGIN switch_notallowed -->
            <p>Votre compte n'a pas accès à cette rubrique.</p>
            <!-- END switch_notallowed -->
        </div>
        <!-- END switch_private -->

        <!-- BEGIN switch_advanced_search -->
        <form class="c-form c-card" id="form_advanced_search" method="post" action="./recherche.html">
            <h2>Recherche avancée</h2>
            <div class="c-form__grid">
                <p>
                    <label for="search_query_string">Mot(s) recherché(s)</label>
                    <input type="text" id="search_query_string" name="query_string" value="{PAGE_QUERYSTRING}" />
                </p>
                <p>
                    <label for="query_content_type">Type de contenu</label>
                    <select id="query_content_type" name="query_content_type" data-c-toggle="query_mime_type_div" data-c-toggle-hide="art">
                        <option value="all" {PAGE_QUERYCT_ALL_SELECTED}>Tout</option>
                        <option value="art" {PAGE_QUERYCT_ART_SELECTED}>Articles</option>
                        <option value="doc" {PAGE_QUERYCT_DOC_SELECTED}>Documents</option>
                    </select>
                </p>
                <p id="query_mime_type_div">
                    <label for="query_mime_type">Extension de document</label>
                    <input type="text" id="query_mime_type" name="query_mime_type" value="{PAGE_QUERYMT}" placeholder="pdf, odt…" />
                </p>
                <p>
                    <label for="query_heading_id">Rubrique</label>
                    <select id="query_heading_id" name="query_heading_id">
                        <option value="">Toutes</option>
                        <!-- BEGIN headings -->
                        <option value="{switch_advanced_search.headings.ID}" class="search_headings_{switch_advanced_search.headings.DEPTH}" {switch_advanced_search.headings.SELECTED}>{switch_advanced_search.headings.LABEL}</option>
                        <!-- END headings -->
                    </select>
                </p>
                <p>
                    <label for="query_date_b">Publié entre le</label>
                    <input type="text" class="date" id="query_date_b" name="query_date_b" value="{PAGE_QUERYDATE_B}" placeholder="jj/mm/aaaa" />
                </p>
                <p>
                    <label for="query_date_e">et le</label>
                    <input type="text" class="date" id="query_date_e" name="query_date_e" value="{PAGE_QUERYDATE_E}" placeholder="jj/mm/aaaa" />
                </p>
            </div>
            <button type="submit" class="c-btn c-btn--primary">Rechercher</button>
        </form>
        <!-- END switch_advanced_search -->

        <!-- BEGIN switch_search -->
        <p class="c-lead">Résultats pour « {PAGE_QUERYSTRING} » · <a href="./recherche.html">recherche avancée</a></p>
        <div class="c-results">
            <!-- BEGIN result -->
            <a class="c-result" href="{switch_search.result.LINK}">
                <span class="c-result__title">{switch_search.result.TITLE}</span>
                <span class="c-result__score">{switch_search.result.RELEVANCE} %</span>
                <span class="c-result__extract">{switch_search.result.EXTRACT}</span>
                <span class="c-result__link">{switch_search.result.SHORT_LINK} · {switch_search.result.SIZE} ko</span>
            </a>
            <!-- END result -->
            <!-- BEGIN switch_notfound -->
            <p class="c-empty">Aucun résultat pour cette recherche.</p>
            <!-- END switch_notfound -->
        </div>
        <!-- END switch_search -->

        <!-- BEGIN switch_tagsearch -->
        <p class="c-lead">Articles portant l'étiquette « {PAGE_QUERYTAG} »</p>
        <div class="c-results">
            <!-- BEGIN result -->
            <a class="c-result" href="{switch_tagsearch.result.LINK}">
                <span class="c-result__title">{switch_tagsearch.result.TITLE}</span>
                <span class="c-result__link">{switch_tagsearch.result.SHORT_LINK} · {switch_tagsearch.result.SIZE} ko</span>
            </a>
            <!-- END result -->
        </div>
        <!-- END switch_tagsearch -->

        <!-- BEGIN switch_content_page -->
            <!-- BEGIN switch_tags -->
            <p class="c-tags">
                <!-- BEGIN tag -->
                <a href="{switch_content_page.switch_tags.tag.LINK}">#{switch_content_page.switch_tags.tag.TAG}</a>
                <!-- END tag -->
            </p>
            <!-- END switch_tags -->
            <div class="c-prose">{PAGE_CONTENT}</div>
            <p class="c-meta">Publié le {PAGE_DATE} · mis à jour le {PAGE_LASTUPDATE_DATE}</p>
        <!-- END switch_content_page -->

        <!-- BEGIN switch_content_blog -->
            <div class="c-blog">
            <!-- BEGIN article -->
                <section class="c-post">
                    <h2 class="c-post__title">{switch_content_blog.article.PAGE_TITLE}</h2>
                    <p class="c-meta">{switch_content_blog.article.PAGE_AUTHOR} · {switch_content_blog.article.PAGE_DATE}<!-- BEGIN sw_modify --> · modifié le {switch_content_blog.article.PAGE_LASTUPDATE_DATE}<!-- END sw_modify --></p>
                    <!-- BEGIN switch_tags -->
                    <p class="c-tags">
                        <!-- BEGIN tag -->
                        <a href="{switch_content_blog.article.switch_tags.tag.LINK}">#{switch_content_blog.article.switch_tags.tag.TAG}</a>
                        <!-- END tag -->
                    </p>
                    <!-- END switch_tags -->
                    <div class="c-prose">{switch_content_blog.article.PAGE_DESCRIPTION}</div>
                    {switch_content_blog.article.PAGE_URL_ARTICLE}
                </section>
            <!-- END article -->
            </div>
            <nav class="c-pager">
                <!-- BEGIN page_before -->
                <a class="c-btn c-btn--ghost" href="{switch_content_blog.page_before.URL}">← Articles précédents</a>
                <!-- END page_before -->
                <!-- BEGIN page_after -->
                <a class="c-btn c-btn--ghost" href="{switch_content_blog.page_after.URL}">Articles suivants →</a>
                <!-- END page_after -->
            </nav>
        <!-- END switch_content_blog -->

        <!-- BEGIN switch_content_message -->
        <div class="c-notice">
            <h2>{MESSAGE_TITLE}</h2>
            {MESSAGE_CONTENT}
        </div>
        <!-- END switch_content_message -->

        <!-- BEGIN switch_content_error -->
        <div class="c-notice c-notice--error">
            <h2>Erreur {PAGE_ERROR_CODE}</h2>
            <p>Cette page n'existe pas ou n'est plus publiée.</p>
            <p><a class="c-btn c-btn--primary" href="{SITE_HOME}">Revenir à l'accueil</a></p>
        </div>
        <!-- END switch_content_error -->

        <!-- BEGIN switch_content_heading -->
        <div class="c-cards">
            <!-- BEGIN subheading1 -->
            <a class="c-card c-card--link" href="{switch_content_heading.subheading1.LINK}" {switch_content_heading.subheading1.LINK_TARGET}>
                <span class="c-card__kicker">Rubrique</span>
                <span class="c-card__title">{switch_content_heading.subheading1.LABEL}</span>
                <span class="c-card__text">{switch_content_heading.subheading1.DESCRIPTION}</span>
            </a>
            <!-- END subheading1 -->
            <!-- BEGIN page -->
            <a class="c-card c-card--link" href="{switch_content_heading.page.LINK}">
                <span class="c-card__kicker">{switch_content_heading.page.DATE}</span>
                <span class="c-card__title">{switch_content_heading.page.LABEL}</span>
            </a>
            <!-- END page -->
        </div>
        <!-- END switch_content_heading -->

        <!-- BEGIN switch_content_sitemap -->
        <div class="c-sitemap">
            <!-- BEGIN heading0 -->
            <section>
                <h2><a href="{switch_content_sitemap.heading0.LINK}">{switch_content_sitemap.heading0.LABEL}</a></h2>
                <ul>
                    <!-- BEGIN page -->
                    <li class="c-sitemap__page"><a href="{switch_content_sitemap.heading0.page.LINK}">{switch_content_sitemap.heading0.page.LABEL}</a></li>
                    <!-- END page -->
                    <!-- BEGIN heading1 -->
                    <li><a href="{switch_content_sitemap.heading0.heading1.LINK}">{switch_content_sitemap.heading0.heading1.LABEL}</a>
                        <ul>
                            <!-- BEGIN page -->
                            <li class="c-sitemap__page"><a href="{switch_content_sitemap.heading0.heading1.page.LINK}">{switch_content_sitemap.heading0.heading1.page.LABEL}</a></li>
                            <!-- END page -->
                            <!-- BEGIN heading2 -->
                            <li><a href="{switch_content_sitemap.heading0.heading1.heading2.LINK}">{switch_content_sitemap.heading0.heading1.heading2.LABEL}</a>
                                <ul>
                                    <!-- BEGIN page -->
                                    <li class="c-sitemap__page"><a href="{switch_content_sitemap.heading0.heading1.heading2.page.LINK}">{switch_content_sitemap.heading0.heading1.heading2.page.LABEL}</a></li>
                                    <!-- END page -->
                                    <!-- BEGIN heading3 -->
                                    <li><a href="{switch_content_sitemap.heading0.heading1.heading2.heading3.LINK}">{switch_content_sitemap.heading0.heading1.heading2.heading3.LABEL}</a>
                                        <ul>
                                            <!-- BEGIN page -->
                                            <li class="c-sitemap__page"><a href="{switch_content_sitemap.heading0.heading1.heading2.heading3.page.LINK}">{switch_content_sitemap.heading0.heading1.heading2.heading3.page.LABEL}</a></li>
                                            <!-- END page -->
                                            <!-- BEGIN heading4 -->
                                            <li><a href="{switch_content_sitemap.heading0.heading1.heading2.heading3.heading4.LINK}">{switch_content_sitemap.heading0.heading1.heading2.heading3.heading4.LABEL}</a>
                                                <ul>
                                                    <!-- BEGIN page -->
                                                    <li class="c-sitemap__page"><a href="{switch_content_sitemap.heading0.heading1.heading2.heading3.heading4.page.LINK}">{switch_content_sitemap.heading0.heading1.heading2.heading3.heading4.page.LABEL}</a></li>
                                                    <!-- END page -->
                                                </ul>
                                            </li>
                                            <!-- END heading4 -->
                                        </ul>
                                    </li>
                                    <!-- END heading3 -->
                                </ul>
                            </li>
                            <!-- END heading2 -->
                        </ul>
                    </li>
                    <!-- END heading1 -->
                </ul>
            </section>
            <!-- END heading0 -->
        </div>
        <!-- END switch_content_sitemap -->

        <!-- BEGIN switch_content_module_directory -->
        <form class="c-form c-card" action="{DIRECTORY_FORMACTION}" method="post">
            <h2>Rechercher dans l'annuaire</h2>
            <div class="c-form__grid">
                <p><label for="directory_lastname">Nom</label><input type="text" id="directory_lastname" name="directory_lastname" value="{DIRECTORY_SEARCH_LASTNAME}" /></p>
                <p><label for="directory_firstname">Prénom</label><input type="text" id="directory_firstname" name="directory_firstname" value="{DIRECTORY_SEARCH_FIRSTNAME}" /></p>
                <p><label for="directory_function">Fonction</label><input type="text" id="directory_function" name="directory_function" value="{DIRECTORY_SEARCH_FUNCTION}" /></p>
                <p><label for="directory_heading">Rattachement</label><input type="text" id="directory_heading" name="directory_heading" value="{DIRECTORY_SEARCH_HEADING}" /></p>
            </div>
            <div class="c-form__actions">
                <button type="submit" class="c-btn c-btn--primary">Chercher</button>
                <a class="c-btn c-btn--ghost" href="{DIRECTORY_LINK_FULL}">Annuaire complet</a>
                <a class="c-btn c-btn--ghost" href="{DIRECTORY_LINK_ORGANIGRAM}">Organigramme</a>
                <a class="c-btn c-btn--ghost" href="{DIRECTORY_LINK_SPEEDDIALING}">Numéros abrégés</a>
            </div>
        </form>
        <!-- END switch_content_module_directory -->

        <!-- BEGIN directory_switch_result -->
        <section class="c-directory">
            <h2>Résultat de la recherche</h2>
            <div class="c-table">
                <table>
                    <thead><tr><th>Nom</th><th>Fonction</th><th>Grade</th><th>Poste</th><th>Téléphone</th></tr></thead>
                    <tbody>
                    <!-- BEGIN contact -->
                    <tr class="c-row{directory_switch_result.contact.ALTERNATE_STYLE}">
                        <td><a href="{directory_switch_result.contact.LINK}">{directory_switch_result.contact.LASTNAME} {directory_switch_result.contact.FIRSTNAME}</a></td>
                        <td>{directory_switch_result.contact.FUNCTION}</td>
                        <td>{directory_switch_result.contact.RANK}</td>
                        <td>{directory_switch_result.contact.NUMBER}</td>
                        <td>{directory_switch_result.contact.PHONE}</td>
                    </tr>
                    <!-- END contact -->
                    </tbody>
                </table>
            </div>
            <!-- BEGIN switch_message -->
            <p class="c-empty">{directory_switch_result.switch_message.CONTENT}</p>
            <!-- END switch_message -->
        </section>
        <!-- END directory_switch_result -->

        <!-- BEGIN directory_switch_contact -->
        <section class="c-directory">
            <div class="c-profile c-card">
                <img title="Photo de {directory_switch_contact.LASTNAME} {directory_switch_contact.FIRSTNAME}" src="{directory_switch_contact.PHOTOPATH}" class="c-profile__photo" alt="" />
                <div>
                    <h2>{directory_switch_contact.CIVILITY} {directory_switch_contact.FIRSTNAME} {directory_switch_contact.LASTNAME}</h2>
                    <dl class="c-profile__facts">
                        <dt>Fonction</dt><dd>{directory_switch_contact.FUNCTION}</dd>
                        <dt>Grade</dt><dd>{directory_switch_contact.RANK}</dd>
                        <dt>Téléphone</dt><dd>{directory_switch_contact.PHONE}</dd>
                        <dt>Poste</dt><dd>{directory_switch_contact.NUMBER}</dd>
                        <dt>Bâtiment</dt><dd>{directory_switch_contact.BUILDING}</dd>
                        <dt>Étage</dt><dd>{directory_switch_contact.FLOOR}</dd>
                        <dt>Bureau</dt><dd>{directory_switch_contact.OFFICE}</dd>
                        <dt>Adresse</dt><dd>{directory_switch_contact.HEADING_ADDRESS_FULL}</dd>
                        <dt>Rattaché à</dt><dd>{directory_switch_contact.HEADINGS}</dd>
                    </dl>
                </div>
            </div>
            <!-- BEGIN switch_files -->
            <h3>Documents</h3>
            <ul class="c-files">
                <!-- BEGIN file -->
                <li>{directory_switch_contact.switch_files.file.PATH} · <a href="{directory_switch_contact.switch_files.file.URL}">{directory_switch_contact.switch_files.file.FILENAME}</a></li>
                <!-- END file -->
            </ul>
            <!-- END switch_files -->
            <h3>Dans la même rubrique</h3>
            <div class="c-table">
                <table>
                    <thead><tr><th>Nom</th><th>Fonction</th><th>Grade</th><th>Poste</th><th>Téléphone</th></tr></thead>
                    <tbody>
                    <!-- BEGIN contact -->
                    <tr class="c-row{directory_switch_contact.contact.ALTERNATE_STYLE}">
                        <td><a href="{directory_switch_contact.contact.LINK}">{directory_switch_contact.contact.CIVILITY} {directory_switch_contact.contact.LASTNAME} {directory_switch_contact.contact.FIRSTNAME}</a></td>
                        <td>{directory_switch_contact.contact.FUNCTION}</td>
                        <td>{directory_switch_contact.contact.RANK}</td>
                        <td>{directory_switch_contact.contact.NUMBER}</td>
                        <td>{directory_switch_contact.contact.PHONE}</td>
                    </tr>
                    <!-- END contact -->
                    </tbody>
                </table>
            </div>
        </section>
        <!-- END directory_switch_contact -->

        <!-- BEGIN directory_switch_full -->
        <section class="c-directory">
            <h2>Annuaire complet</h2>
            <!-- BEGIN switch_selected_heading -->
            <nav class="c-crumbs c-crumbs--inline">
                <a href="{DIRECTORY_LINK_FULL}">Tout</a>
                <!-- BEGIN heading -->
                <a href="{directory_switch_full.switch_selected_heading.heading.LINK}">{directory_switch_full.switch_selected_heading.heading.LABEL}</a>
                <!-- END heading -->
            </nav>
            <!-- END switch_selected_heading -->
            <div class="c-table">
                <table>
                    <thead><tr><th>Nom</th><th>Fonction</th><th>Grade</th><th>Poste</th><th>Téléphone</th></tr></thead>
                    <tbody>
                    <!-- BEGIN line -->
                        <!-- BEGIN heading -->
                        <tr class="c-row-heading"><td colspan="5" class="c-depth{directory_switch_full.line.heading.DEPTH}"><a href="{directory_switch_full.line.heading.LINK}">{directory_switch_full.line.heading.LABEL}</a></td></tr>
                        <!-- END heading -->
                        <!-- BEGIN contact -->
                        <tr class="c-row{directory_switch_full.line.contact.ALTERNATE_STYLE}">
                            <td><a href="{directory_switch_full.line.contact.LINK}">{directory_switch_full.line.contact.CIVILITY} {directory_switch_full.line.contact.LASTNAME} {directory_switch_full.line.contact.FIRSTNAME}</a></td>
                            <td>{directory_switch_full.line.contact.FUNCTION}</td>
                            <td>{directory_switch_full.line.contact.RANK}</td>
                            <td>{directory_switch_full.line.contact.NUMBER}</td>
                            <td>{directory_switch_full.line.contact.PHONE}</td>
                        </tr>
                        <!-- END contact -->
                    <!-- END line -->
                    </tbody>
                </table>
            </div>
        </section>
        <!-- END directory_switch_full -->

        <!-- BEGIN directory_switch_organigram -->
        <section class="c-directory">
            <h2>Organigramme</h2>
            <div class="c-organigram">
                <!-- BEGIN heading -->
                <a class="c-depth{directory_switch_organigram.heading.DEPTH}" href="{directory_switch_organigram.heading.LINK}">{directory_switch_organigram.heading.LABEL}</a>
                <!-- END heading -->
            </div>
        </section>
        <!-- END directory_switch_organigram -->

        <!-- BEGIN directory_switch_speeddialing -->
        <section class="c-directory">
            <h2>Numéros abrégés</h2>
            <!-- BEGIN heading -->
            <details class="c-accordion">
                <summary>{directory_switch_speeddialing.heading.LABEL}</summary>
                <div class="c-table">
                    <table>
                        <thead><tr><th>Intitulé</th><th>Numéro</th><th>Abrégé</th></tr></thead>
                        <tbody>
                        <!-- BEGIN number -->
                        <tr><td>{directory_switch_speeddialing.heading.number.LABEL}</td><td>{directory_switch_speeddialing.heading.number.NUMBER}</td><td>{directory_switch_speeddialing.heading.number.SHORTNUMBER}</td></tr>
                        <!-- END number -->
                        </tbody>
                    </table>
                </div>
            </details>
            <!-- END heading -->
        </section>
        <!-- END directory_switch_speeddialing -->

    </article>

    <aside class="c-aside">
        <!-- BEGIN switch_subscription -->
        <section class="c-card c-widget">
            <h2 class="c-widget__title">Être averti des nouveautés</h2>
            <!-- BEGIN switch_response -->
            <p class="c-widget__response">{switch_subscription.switch_response.CONTENT}</p>
            <!-- END switch_response -->
            <form method="post" action="{switch_subscription.ACTION}">
                <p class="c-choice">
                    <label><input type="radio" name="subscription_headingid" value="{switch_subscription.ROOTID}" /> Tout le site</label>
                    <label><input type="radio" name="subscription_headingid" value="{switch_subscription.HEADINGID}" checked="checked" /> Cette rubrique</label>
                </p>
                <label class="c-sr" for="subscription_email">Adresse électronique</label>
                <div class="c-inline">
                    <input type="email" id="subscription_email" name="subscription_email" placeholder="vous@exemple.fr" required="required" />
                    <button type="submit" class="c-btn c-btn--primary">S'abonner</button>
                </div>
            </form>
        </section>
        <!-- END switch_subscription -->

        <!-- BEGIN bloc1 -->
        <section class="c-card c-widget c-prose">{bloc1.CONTENT}</section>
        <!-- END bloc1 -->
        <!-- BEGIN bloc2 -->
        <section class="c-card c-widget c-prose">{bloc2.CONTENT}</section>
        <!-- END bloc2 -->
        <!-- BEGIN bloc3 -->
        <section class="c-card c-widget c-prose">{bloc3.CONTENT}</section>
        <!-- END bloc3 -->

        <!-- BEGIN switch_blog -->
        <section class="c-card c-widget">
            <h2 class="c-widget__title">Archives</h2>
            <!-- BEGIN calendar -->
            {switch_blog.calendar.CONTENT}
            <!-- END calendar -->
            <!-- BEGIN archive -->
            <details class="c-accordion">
                <summary>{switch_blog.archive.YEAR}</summary>
                <ul class="c-list">
                    <!-- BEGIN month -->
                    <li><a href="{switch_blog.archive.month.URL}">{switch_blog.archive.month.MONTH_LETTER}</a> <span class="c-count">{switch_blog.archive.month.NBART}</span></li>
                    <!-- END month -->
                </ul>
            </details>
            <!-- END archive -->
        </section>
        <!-- END switch_blog -->

        <section class="c-card c-widget">
            <h2 class="c-widget__title">Étiquettes</h2>
            <div class="c-tagcloud">
                <!-- BEGIN tagcloud -->
                <a href="{tagcloud.LINK}" class="{tagcloud.SELECTED}" title="Articles portant l'étiquette « {tagcloud.TAG} »" style="font-size:{tagcloud.SIZE}%;">{tagcloud.TAG}</a>
                <!-- END tagcloud -->
            </div>
        </section>

        <!-- BEGIN rssfeed -->
        <section class="c-card c-widget">
            <h2 class="c-widget__title"><a href="{rssfeed.LINK}">{rssfeed.TITLE}</a></h2>
            <ul class="c-list">
                <!-- BEGIN rssentry -->
                <li><a href="{rssfeed.rssentry.LINK}" target="_blank" rel="noopener">{rssfeed.rssentry.TITLE}</a> <span class="c-count">{rssfeed.rssentry.PUBLISHED_DATE}</span></li>
                <!-- END rssentry -->
            </ul>
        </section>
        <!-- END rssfeed -->
    </aside>
</main>

<footer class="c-footer">
    <div class="c-container c-footer__inner">
        <div>
            <p class="c-footer__brand">{SITE_TITLE}</p>
            <p class="c-footer__text">{WORKSPACE_META_DESCRIPTION}</p>
        </div>
        <nav class="c-footer__links" aria-label="Liens de pied de page">
            <!-- BEGIN root2 -->
                <!-- BEGIN heading1 -->
                <a href="{root2.heading1.LINK}" {root2.heading1.LINK_TARGET}>{root2.heading1.LABEL}</a>
                <!-- END heading1 -->
            <!-- END root2 -->
            <a href="./recherche.html">Recherche avancée</a>
            <!-- BEGIN switch_rssfeed_site -->
            <a href="{switch_rssfeed_site.URL}">Flux RSS</a>
            <!-- END switch_rssfeed_site -->
        </nav>
    </div>
    <div class="c-container c-footer__legal">
        <span>© {DATE_YEAR} {WORKSPACE_META_COPYRIGHT}</span>
        <span>Propulsé par <a href="https://www.ploopi.fr">Ploopi</a></span>
    </div>
</footer>
</body>
</html>
