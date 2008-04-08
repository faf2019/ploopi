<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<!--
    // OVENSIA ~ Template pour PLOOPI
    // R�alisation: Ovensia 2007 ~ www.ovensia.fr
    // Test� avec IE 6, IE 7, Firefox 2, Safari 3, Opera 9.2, Konqueror 3.5.6
    // Valide XHTML 1.0 Strict, CSS 2, ADAE argent, WCAG 1.0 Niv3
    -->
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
<head>
    <meta http-equiv="content-type" content="text/html; charset=iso-8859-1" />
    <meta name="description" content="{WORKSPACE_META_DESCRIPTION} {PAGE_DESCRIPTION}" />
    <meta name="keywords" content="{PAGE_ALLKEYWORDS}" />
    <meta name="author" content="{WORKSPACE_META_AUTHOR}" />
    <meta name="copyright" content="{WORKSPACE_META_COPYRIGHT}" />
    <meta name="robots" content="{WORKSPACE_META_ROBOTS}" />

    <title>{WORKSPACE_TITLE} - {PAGE_TITLE}</title>

    <link rel="alternate" type="application/rss+xml" href="{SITE_RSSFEED_URL}" title="{SITE_RSSFEED_TITLE}">

    <link type="text/css" rel="stylesheet" href="{TEMPLATE_PATH}/css/styles.css" media="screen" />
    <link type="text/css" rel="stylesheet" href="{TEMPLATE_PATH}/css/calendar.css" media="screen" />
    <link type="text/css" rel="stylesheet" href="{TEMPLATE_PATH}/css/forms.css" media="screen" />
    <link type="text/css" rel="stylesheet" href="{TEMPLATE_PATH}/css/rss.css" media="screen" />
    <link type="text/css" rel="stylesheet" href="{TEMPLATE_PATH}/css/news.css" media="screen" />

    <!--[if lte IE 7]>
    <link type="text/css" rel="stylesheet" href="{TEMPLATE_PATH}/css/styles_ie.css" media="screen" />
    <link type="text/css" rel="stylesheet" href="{TEMPLATE_PATH}/css/calendar_ie.css" media="screen" />
    <![endif]-->

    <!--[if lte IE 6]>
    <link type="text/css" rel="stylesheet" href="{TEMPLATE_PATH}/css/png.css" media="screen" />
    <![endif]-->

    <!-- BEGIN ploopi_js -->
        <script type="text/javascript" src="{ploopi_js.PATH}"></script>
    <!-- END module_js -->
    <script type="text/javascript">
    //<!--
    {ADDITIONAL_JAVASCRIPT}
    //-->
    </script>

</head>

<body>
    <div id="entete">
        <a href="./" title="Lien vers Accueil" accesskey="1" tabindex="1">Accueil</a>&nbsp;&#149;
        <a href="#page_content" title="Lien vers le Contenu" accesskey="2" tabindex="2">Aller au Contenu</a>&nbsp;&#149;
        <a href="#recherche" title="Lien vers le Moteur de Recherche" accesskey="C" tabindex="3">Aller au Moteur de recherche</a>&nbsp;&nbsp;
    </div>
    <div id="page">
        <div id="page_haut"></div>
        <div id="page_inner">
            <div id="page_menu">
                <!-- BEGIN root1 -->
                    <!-- BEGIN heading1 -->
                        <img src="{TEMPLATE_PATH}/gfx/root1_sep.png" alt="s�parateur" />
                        <a href="{root1.heading1.LINK}" title="{root1.heading1.LABEL}"><img src="{TEMPLATE_PATH}/gfx/root1_{root1.heading1.POSITION}.png" alt="{root1.heading1.LABEL}" title="{root1.heading1.LABEL}" /></a>
                    <!-- END heading1 -->
                <!-- END root1 -->
                <img src="{TEMPLATE_PATH}/gfx/root1_sep.png" alt="s�parateur" />
            </div>
            <div id="title">
                <div id="slogan">Architecte de vos solutions informatiques libres</div>
                <img src="{TEMPLATE_PATH}/gfx/logo.png" alt="logo OVENSIA" title="logo OVENSIA" />
            </div>

            <!--div id="pages">
                <!-- BEGIN page -->
                    <a class="{page.SEL}" title="Lien vers la page {page.LABEL}" href="{page.LINK}">{page.LABEL}</a>
                    <!-- BEGIN sw_separator -->
                    &nbsp;&#149;&nbsp;
                    <!-- END sw_separator -->
                <!-- END page -->
                &nbsp;
            </div-->

            <div id="path">
                <div id="lastupdate">
                    Mis � jour le {LASTUPDATE_DATE} � {LASTUPDATE_TIME}
                </div>
                Vous �tes ici :
                <!-- BEGIN path -->
                    &raquo;&nbsp;<a href="{path.LINK}">{path.LABEL}</a>
                <!-- END path -->
                <!-- BEGIN switch_content_page -->
                    &raquo;&nbsp;<a href="#">{PAGE_TITLE}</a>
                <!-- END switch_content_page -->
            </div>
            <div id="page_main">

                <div id="menu_vertical">
                    <div id="recherche">
                        <form method="post" action="index.php">
                            <fieldset>
                                <label for="recherche_field">Recherche:</label>
                                <div>
                                    <input type="text" title="Champ de recherche" alt="Champ de recherche" id="recherche_field" name="query_string" value="{PAGE_QUERYSTRING}" onfocus="javascript:this.value='';" />
                                    <input type="submit" title="Bouton pour valider la recherche" id="recherche_button" value="go" />
                                </div>
                            </fieldset>
                        </form>
                    </div>

                    <img class="actubox_bg" src="{TEMPLATE_PATH}/gfx/actubox.png" alt="fond menu vertical" title="fond menu vertical" />
                    <div class="actubox">
                        <h1>Actualit�s du Site</h1>
                        <!-- BEGIN news -->
                            <div class="news">
                                <div class="title">{news.TITLE}</div>
                                <div class="date">le {news.DATE} � {news.TIME}</div>
                                <div class="content">{news.CONTENT}</div>
                                <!--div style="padding-bottom:4px;"><a href="{news.URL}" target="_blank">{news.URLTITLE}</a></div-->
                            </div>
                        <!-- END news -->
                    </div>

                    <img class="actubox_bg" src="{TEMPLATE_PATH}/gfx/actubox.png" alt="fond menu vertical" title="fond menu vertical" />
                    <div class="actubox">
                        <h1>Actualit�s du Web</h1>
                        <!-- BEGIN rssfeed -->
                        <div class="rssfeed">
                            <a class="rssfeedtitle" href="{rssfeed.LINK}">{rssfeed.TITLE}<br /><i>{rssfeed.SUBTITLE}</i></a>
                            <!-- BEGIN rssentry -->
                                <a title="{rssfeed.rssentry.TITLE}" class="rsscache" href="{rssfeed.rssentry.LINK}" target="_blank">
                                    <div><b>{rssfeed.rssentry.TITLE}</b></div>
                                    <div><i>{rssfeed.rssentry.SUBTITLE}</i></div>
                                    <div style="font-size:0.8em;margin-top:2px;">{rssfeed.rssentry.PUBLISHED_DATE} {rssfeed.rssentry.PUBLISHED_TIME}</div>
                                </a>
                            <!-- END rssentry -->
                        </div>
                        <!-- END rssfeed -->
                    </div>

                </div>

                <div id="page_content">
                    <!-- BEGIN switch_content_page -->
                        <h1>{PAGE_TITLE}</h1>
                        {PAGE_CONTENT}
                        <div id="page_lastupdate">Auteur: {PAGE_LASTUPDATE_USER_FIRSTNAME} {PAGE_LASTUPDATE_USER_LASTNAME} - Modifi� le: {PAGE_LASTUPDATE_DATE} � {PAGE_LASTUPDATE_TIME}</div>
                    <!-- END switch_content_page -->
                </div>
            </div>
            <div id="menubas">
                <!-- BEGIN root2 -->
                    <!-- BEGIN heading1 -->
                        <span>|</span>
                        <a href="{root2.heading1.LINK}" title="Lien vers {root2.heading1.LABEL}">{root2.heading1.LABEL}</a>
                    <!-- END heading1 -->
                <!-- END root2 -->
                <span>|</span><a href="#entete" title="Lien vers Haut de page">Haut de page</a><span>|</span>
            </div>
        </div>
        <div id="page_bas"></div>
    </div>
    <div id="pied">
    R�alisation <a href="http://www.ovensia.fr" title="Aller sur le site ovensia.fr"><strong>Ovensia</strong></a> &#149; Template <a href="http://www.ovensia.fr" title="Aller sur le site ovensia.fr"><strong>Ovensia</strong></a> &#149; Propuls� par <strong>PLOOPI</strong>&nbsp;&nbsp;
    <div id="execinfo">
        Ce site est valide <a href="http://validator.w3.org/check?uri=referer" title="V�rifier la validit� XHTML 1.0 strict"><strong>XHTML 1.0 strict</strong></a>, <a href="http://jigsaw.w3.org/css-validator/check/referer" title="V�rifier la validit� CSS"><strong>CSS 2</strong></a> (sauf overflow-x / CSS3), <a href="http://www.ocawa.com/autotest/validate.php" title="Page test�e par Ocawa, testez cette page sur le site ocawa"><strong>ADAE argent</strong> / <strong>WCAG 1.0 Niv3</strong></a>&nbsp;&nbsp;
        <br />[ exectime: <PLOOPI_EXEC_TIME> ms | php: <PLOOPI_PHP_P100>% | sql: <PLOOPI_NUMQUERIES>q | size: <PLOOPI_PAGE_SIZE>kB ]&nbsp;&nbsp;
    </div>
    </div>

</body>
</html>
