<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<!--
    // OVENSIA ~ Template pour PLOOPI
    // Réalisation: Ovensia 2007 ~ www.ovensia.fr
    // Testé avec IE 6, IE 7, Firefox 2, Safari 3, Opera 9.2, Konqueror 3.5.6
    // Valide XHTML 1.0 Strict, CSS 2, ADAE argent, WCAG 1.0 Niv3
    -->
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
<head>
    <meta http-equiv="content-type" content="text/html; charset=iso-8859-1" />
    <meta name="description" content="{WORKSPACE_META_DESCRIPTION} {PAGE_META_DESCRIPTION}" />
    <meta name="keywords" content="{WORKSPACE_META_KEYWORDS}, {PAGE_META_KEYWORDS}" />
    <meta name="author" content="{WORKSPACE_META_AUTHOR}" />
    <meta name="copyright" content="{WORKSPACE_META_COPYRIGHT}" />
    <meta name="robots" content="{WORKSPACE_META_ROBOTS}" />

    <title>{WORKSPACE_TITLE} - {PAGE_TITLE}</title>

    <!-- BEGIN switch_atomfeed_site -->
    <link rel="alternate" type="application/atom+xml" href="{switch_atomfeed_site.URL}" title="ATOM - {switch_atomfeed_site.TITLE}" />
    <!-- END switch_atomfeed_site -->
    <!-- BEGIN switch_atomfeed_heading -->
    <link rel="alternate" type="application/atom+xml" href="{switch_atomfeed_heading.URL}" title="ATOM - {switch_atomfeed_heading.TITLE}" />
    <!-- END switch_atomfeed_site -->

    <!-- BEGIN switch_rssfeed_site -->
    <link rel="alternate" type="application/rss+xml" href="{switch_rssfeed_site.URL}" title="RSS - {switch_rssfeed_site.TITLE}" />
    <!-- END switch_rssfeed_site -->
    <!-- BEGIN switch_rssfeed_heading -->
    <link rel="alternate" type="application/rss+xml" href="{switch_rssfeed_heading.URL}" title="RSS - {switch_rssfeed_heading.TITLE}" />
    <!-- END switch_rssfeed_site -->

    <link type="text/css" rel="stylesheet" href="{TEMPLATE_PATH}/css/styles.css" media="screen" />
    <link type="text/css" rel="stylesheet" href="{TEMPLATE_PATH}/css/skin.css" media="screen" />
    <link type="text/css" rel="stylesheet" href="{TEMPLATE_PATH}/css/calendar.css" media="screen" />
    <link type="text/css" rel="stylesheet" href="{TEMPLATE_PATH}/css/forms.css" media="screen" />
    <link type="text/css" rel="stylesheet" href="{TEMPLATE_PATH}/css/rss.css" media="screen" />
    <link type="text/css" rel="stylesheet" href="{TEMPLATE_PATH}/css/news.css" media="screen" />
    <link type="text/css" rel="stylesheet" href="{TEMPLATE_PATH}/css/search.css" media="screen" />
    <link type="text/css" rel="stylesheet" href="{TEMPLATE_PATH}/css/system_trombi.css" media="screen" />

    <!--[if lte IE 7]>
    <link type="text/css" rel="stylesheet" href="{TEMPLATE_PATH}/css/styles_ie.css" media="screen" />
    <link type="text/css" rel="stylesheet" href="{TEMPLATE_PATH}/css/skin_ie.css" media="screen" />
    <link type="text/css" rel="stylesheet" href="{TEMPLATE_PATH}/css/calendar_ie.css" media="screen" />
    <![endif]-->
    
    <!--[if lte IE 6]>
    <link type="text/css" rel="stylesheet" href="{TEMPLATE_PATH}/css/png.css" media="screen" />
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
    <!-- BEGIN switch_content_page -->
    {PAGE_HEADCONTENT}
    <!-- END switch_content_page -->
</head>

<body>
    <div id="entete">
        <a href="./" title="Lien vers Accueil" accesskey="1" tabindex="1">Accueil</a>&nbsp;&#149;
        <!-- BEGIN root2 -->
            <a href="{root2.LINK}" title="Lien vers Plan de site" accesskey="2" tabindex="2">Plan de site</a>&nbsp;&#149;
        <!-- END root2 -->
        <a href="#page_content" title="Lien vers le Contenu" accesskey="3" tabindex="3">Aller au Contenu</a>&nbsp;&#149;
        <a href="#recherche" title="Lien vers le Moteur de Recherche" accesskey="C" tabindex="4">Aller au Moteur de recherche</a>&nbsp;&nbsp;
    </div>
    <div id="page">
        <div id="page_haut"></div>
        <div id="page_inner">
            <div id="page_menu">
                <!-- BEGIN root1 -->
                    <!-- BEGIN heading1 -->
                        <img src="{TEMPLATE_PATH}/gfx/root1_sep.png" alt="séparateur" />
                        <a href="{root1.heading1.LINK}" title="{root1.heading1.LABEL}"><img src="{TEMPLATE_PATH}/gfx/root1_{root1.heading1.POSITION}.png" alt="{root1.heading1.LABEL}" title="{root1.heading1.LABEL}" /></a>
                    <!-- END heading1 -->
                <!-- END root1 -->
                <img src="{TEMPLATE_PATH}/gfx/root1_sep.png" alt="séparateur" />
            </div>
            <div id="title">
                <div id="slogan">Architecte de vos solutions informatiques libres</div>
                <img src="{TEMPLATE_PATH}/gfx/logo.png" alt="logo OVENSIA" title="logo OVENSIA" />
            </div>

            <div id="path">
                <div id="lastupdate">
                    Mis à jour le {LASTUPDATE_DATE} à {LASTUPDATE_TIME}
                </div>
                Vous êtes ici :
                <!-- BEGIN path -->
                    &raquo;&nbsp;<a href="{path.LINK}">{path.LABEL}</a>
                <!-- END path -->
                <!-- BEGIN switch_content_page -->
                    &raquo;&nbsp;<a href="#">{PAGE_TITLE}</a>
                <!-- END switch_content_page -->
            </div>
            <div id="page_main">

                <div id="menu_vertical">
                    <div class="mini_form">
                        <form method="post" action="index.php">
                            <fieldset>
                                <label for="recherche_field">Recherche:</label>
                                <div>
                                    <input type="text" title="Champ de recherche" alt="Champ de recherche" class="text" name="query_string" value="{PAGE_QUERYSTRING}" onfocus="javascript:this.value='';" />
                                    <input type="submit" title="Bouton pour valider la recherche" class="button" value="go" />
                                </div>
                            </fieldset>
                        </form>
                    </div>
                    
                    <!-- BEGIN switch_newsletter_subscription -->
                    <div class="mini_form">
                        <form method="post" action="{switch_newsletter_subscription.ACTION}">
                            <fieldset>
                              <label for="subscription_email">Inscription NewsLetter:</label>
                              <!-- BEGIN switch_response -->
                              <div><strong>{switch_newsletter_subscription.switch_response.CONTENT}</strong></div>
                              <!-- END switch_response -->
                              <div>
                                <input type="text" title="Entrez votre adresse email" alt="Entrez votre adresse email" class="text" name="subscription_email" value="Entrez votre adresse email" onfocus="javascript:this.value='';" />
                                <input type="submit" title="Bouton pour valider l'inscription" class="button" value="go" />
                              </div>
                            </fieldset>
                        </form>
                    </div>
                    <!-- END switch_newsletter_subscription -->
                                                            
                    <!-- BEGIN switch_subscription -->
                    <div class="mini_form">
                        <form method="post" action="{switch_subscription.ACTION}">
                            <fieldset>
                                <label for="subscription_email">Abonnement:</label>
                                <!-- BEGIN switch_response -->
                                <div><strong>{switch_subscription.switch_response.CONTENT}</strong></div>
                                <!-- END switch_response -->
                                <p class="va">
                                    <input type="radio" class="pointer" name="subscription_headingid" id="subscription_site" value="{switch_subscription.ROOTID}" /><label class="pointer" for="subscription_site">Site</label>
                                    <input type="radio" class="pointer" name="subscription_headingid" id="subscription_heading" value="{switch_subscription.HEADINGID}" checked="checked" /><label class="pointer" for="subscription_heading">Rubrique</label>
                                </p>
                                <div>
                                    <input type="text" title="Entrez votre adresse email" alt="Entrez votre adresse email" class="text" name="subscription_email" value="Entrez votre adresse email" onfocus="javascript:this.value='';" />
                                    <input type="submit" title="Bouton pour valider l'abonnement" class="button" value="go" />
                                </div>
                            </fieldset>
                        </form>
                    </div>
                    <!-- END switch_subscription -->
                    
                    <!-- BEGIN switch_pages -->
                    <img class="box_bg" src="{TEMPLATE_PATH}/gfx/box.png" alt="fond menu vertical" title="fond menu vertical" />
                    <div class="box">
                        <h1>Articles</h1>
                        <div id="articles">
                        <!-- BEGIN page -->
                            <a class="{switch_pages.page.SEL}" href="{switch_pages.page.LINK}" title="Lien vers {switch_pages.page.LABEL}">
                                <strong>{switch_pages.page.LABEL}</strong>
                            </a>
                        <!-- END page -->
                        </div>
                    </div>
                    <!-- END switch_pages -->

                    <img class="box_bg" src="{TEMPLATE_PATH}/gfx/box.png" alt="fond menu vertical" title="fond menu vertical" />
                    <div class="box">
                        <h1>Nuage de tags</h1>
                        <div id="tagcloud">
                            <!-- BEGIN tagcloud -->
                                <a href="{tagcloud.LINK}" class="{tagcloud.SELECTED}" title="Afficher les articles contenant le tag &laquo; {tagcloud.TAG} &raquo;" style="font-size:{tagcloud.SIZE}%;">{tagcloud.TAG}<sup>{tagcloud.OCCURENCE}</sup></a>
                            <!-- END tagcloud -->
                        </div>
                    </div>
                    
                    <img class="box_bg" src="{TEMPLATE_PATH}/gfx/box.png" alt="fond menu vertical" title="fond menu vertical" />
                    <div class="box">
                        <h1>Actualités</h1>
                        <!-- BEGIN news -->
                            <div class="news">
                                <div class="title">{news.TITLE}</div>
                                <div class="date">le {news.DATE} à {news.TIME}</div>
                                <div class="content">{news.CONTENT}</div>
                                <!--div style="padding-bottom:4px;"><a href="{news.URL}">{news.URLTITLE}</a></div-->
                            </div>
                        <!-- END news -->
                    </div>
                </div>

                <div id="page_content">
                    <!-- BEGIN switch_search -->
                    <h1>Résultat de la recherche pour "{PAGE_QUERYSTRING}"</h1>
                    <div id="search_result">
                        <!-- BEGIN result -->
                            <a href="{switch_search.result.LINK}" title="Lien vers {switch_search.result.TITLE}">
                                <h2>{switch_search.result.TITLE}<span class="relevance">{switch_search.result.RELEVANCE} %</span></h2>
                                <div class="extract">{switch_search.result.EXTRACT}</div>
                                <div class="link">&raquo; {switch_search.result.SHORT_LINK} ({switch_search.result.SIZE} ko)</div>
                            </a>
                        <!-- END result -->
                    </div>
                    <!-- END switch_search -->

                    <!-- BEGIN switch_tagsearch -->
                    <h1>Articles contenant le tag "{PAGE_QUERYTAG}"</h1>
                    <div id="search_result">
                        <!-- BEGIN result -->
                            <a href="{switch_tagsearch.result.LINK}" title="Lien vers {switch_tagsearch.result.TITLE}">
                                <h2>{switch_tagsearch.result.TITLE}</h2>
                                <div class="link">&raquo; {switch_tagsearch.result.SHORT_LINK} ({switch_tagsearch.result.SIZE} ko)</div>
                            </a>
                        <!-- END result -->
                    </div>
                    <!-- END switch_tagsearch -->

                    <!-- BEGIN switch_content_heading -->
                        <!-- BEGIN page -->
                            <a class="headings_page" href="{switch_content_heading.page.LINK}">
                                <img src="{TEMPLATE_PATH}/gfx/page.png" />
                                <span><b>{switch_content_heading.page.LABEL}</b><br />{switch_content_heading.page.DATE}</span>
                            </a>
                        <!-- END page -->

                        <!-- BEGIN subheading1 -->
                            <a class="subheading1" href="{switch_content_heading.subheading1.LINK}">
                                <img src="{TEMPLATE_PATH}/gfx/folder.png" />
                                <span>{switch_content_heading.subheading1.LABEL}</span>
                            </a>
                            <div id="subheading2">
                            <!-- BEGIN subheading2 -->
                                &raquo;&nbsp;<a href="{switch_content_heading.subheading1.subheading2.LINK}">{switch_content_heading.subheading1.subheading2.LABEL}</a>&nbsp;&nbsp; 
                            <!-- END subheading2 -->
                            </div>
                        <!-- END subheading1 -->
                    <!-- END switch_content_heading -->

                    <!-- BEGIN switch_content_sitemap -->
                        <h2>Plan du site</h2>
                        <div id="sitemap">
                            <!-- BEGIN heading0 -->
                                <a title="Lien vers {switch_content_sitemap.heading0.LABEL}" href="{switch_content_sitemap.heading0.LINK}">{switch_content_sitemap.heading0.LABEL}</a>
                                <div class="sitemap_heading1">
                                    <!-- BEGIN heading1 -->
                                        <a title="Lien vers {switch_content_sitemap.heading0.heading1.LABEL}" href="{switch_content_sitemap.heading0.heading1.LINK}">{switch_content_sitemap.heading0.heading1.LABEL}</a>
                                        <div class="sitemap_heading2">
                                            <!-- BEGIN heading2 -->
                                                <a title="Lien vers {switch_content_sitemap.heading0.heading1.heading2.LABEL}" href="{switch_content_sitemap.heading0.heading1.heading2.LINK}">{switch_content_sitemap.heading0.heading1.heading2.LABEL}</a>
                                                <div class="sitemap_heading3">
                                                <!-- BEGIN heading3 -->
                                                    <a title="Lien vers {switch_content_sitemap.heading0.heading1.heading2.heading3.LABEL}" href="{switch_content_sitemap.heading0.heading1.heading2.heading3.LINK}">{switch_content_sitemap.heading0.heading1.heading2.heading3.LABEL}</a>
                                                    <div class="sitemap_heading4">
                                                    <!-- BEGIN heading4 -->
                                                        <a title="Lien vers {switch_content_sitemap.heading0.heading1.heading2.heading3.heading4.LABEL}" href="{switch_content_sitemap.heading0.heading1.heading2.heading3.heading4.LINK}">{switch_content_sitemap.heading0.heading1.heading2.heading3.heading4.LABEL}</a>
                                                    <!-- END heading4 -->
                                                    </div>
                                                <!-- END heading3 -->
                                                </div>
                                            <!-- END heading2 -->
                                        </div>
                                    <!-- END heading1 -->
                                </div>
                            <!-- END heading0 -->
                        </div>
                    <!-- END switch_content_sitemap -->


                    <!-- BEGIN switch_content_page -->
                        <h1>{PAGE_TITLE}</h1>
                        <!-- BEGIN switch_tags -->
                            <p id="page_tags"><span>tags :</span>
                                <!-- BEGIN tag -->
                                    <a title="Afficher les articles contenant le tag &laquo; {switch_content_page.switch_tags.tag.TAG} &raquo;" href="{switch_content_page.switch_tags.tag.LINK}">{switch_content_page.switch_tags.tag.TAG}</a>
                                <!-- END tag -->
                            </p>
                        <!-- END switch_tags -->
                        <div>{PAGE_CONTENT}</div>
                        <div id="page_lastupdate">Auteur: {PAGE_LASTUPDATE_USER_FIRSTNAME} {PAGE_LASTUPDATE_USER_LASTNAME} - Modifié le: {PAGE_LASTUPDATE_DATE} à {PAGE_LASTUPDATE_TIME}</div>
                    <!-- END switch_content_page -->
                    
                    <!-- BEGIN switch_content_error -->
                        <div style="text-align:center;padding:10px;">
	                        <strong>Erreur {PAGE_ERROR_CODE}</strong>
	                        <br />Cette page n'existe pas
                        </div>
                    <!-- END switch_content_error -->
                    
                    <!-- BEGIN switch_content_message -->
                        <h1>{MESSAGE_TITLE}</h1>
                        {MESSAGE_CONTENT}
                    <!-- END switch_content_message -->  
                    
                    <!-- BEGIN switch_newsletter_unsubscrib -->
                      <div style="text-align: center;">
                        <h2>Desinscription à la NewsLetter:</h2>
                        <form method="post" action="{switch_newsletter_unsubscrib.ACTION}">
                            <div>
                                <input type="text" title="Entrez votre adresse email" alt="Entrez votre adresse email" class="text" name="unsubcrib_email" value="Entrez votre adresse email" onfocus="javascript:this.value='';" />
                                <input type="submit" title="Bouton pour valider la désinscription" class="button" value="désinscrire" />
                            </div>
                        </form>
                      </div>
                    <!-- END switch_newsletter_unsubscrib -->
                    
                    <!-- BEGIN switch_newsletter_unsubscrib_response -->
                      <div style="text-align: center;">
                        <h2>{switch_newsletter_unsubscrib_response.RESPONSE}</h2>
                      </div>
                    <!-- END switch_newsletter_unsubscrib_response -->
                                      
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
    Réalisation <a href="http://www.ovensia.fr" title="Aller sur le site ovensia.fr"><strong>Ovensia</strong></a> &#149; Template <a href="http://www.ovensia.fr" title="Aller sur le site ovensia.fr"><strong>Ovensia</strong></a> &#149; Propulsé par <strong><a title="Accéder au site de PLOOPI" href="http://www.ploopi.org">PLOOPI</a></strong>&nbsp;&nbsp;
    <div id="execinfo">
        Ce site est valide <a href="http://validator.w3.org/check?uri=referer" title="Vérifier la validité XHTML 1.0 strict"><strong>XHTML 1.0 strict</strong></a>, <a href="http://jigsaw.w3.org/css-validator/check/referer" title="Vérifier la validité CSS"><strong>CSS 2</strong></a> (sauf overflow-x / CSS3), <a href="http://www.ocawa.com/autotest/validate.php" title="Page testée par Ocawa, testez cette page sur le site ocawa"><strong>ADAE argent</strong> / <strong>WCAG 1.0 Niv3</strong></a>&nbsp;&nbsp;
        <br />[ exectime: <PLOOPI_EXEC_TIME> ms | php: <PLOOPI_PHP_P100>% | sql: <PLOOPI_NUMQUERIES>q | size: <PLOOPI_PAGE_SIZE>kB ]&nbsp;&nbsp;
    </div>
    </div>

</body>
</html>
