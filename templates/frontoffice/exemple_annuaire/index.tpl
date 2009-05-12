<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
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
    <link type="text/css" rel="stylesheet" href="{TEMPLATE_PATH}/css/directory.css" media="screen" />

    <!--[if lte IE 6]>
    <link type="text/css" rel="stylesheet" href="{TEMPLATE_PATH}/css/skin_ie.css" media="screen" />
    <link type="text/css" rel="stylesheet" href="{TEMPLATE_PATH}/css/styles_ie.css" media="screen" />
    <link type="text/css" rel="stylesheet" href="{TEMPLATE_PATH}/css/calendar_ie.css" media="screen" />
    <style type="text/css">
    </style>
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
    <div id="wrap">
        <div id="header">
            <div id="title">
                {SITE_TITLE}
            </div>
            <div id="path">
                <a href="{SITE_HOME}">&raquo;&nbsp;Accueil</a>
                <!-- BEGIN switch_heading1 -->
                <a href="{switch_heading1.LINK}">&raquo;&nbsp;{switch_heading1.LABEL}</a>
                <!-- END switch_heading1 -->
                <!-- BEGIN switch_heading2 -->
                <a href="{switch_heading2.LINK}">&raquo;&nbsp;{switch_heading2.LABEL}</a>
                <!-- END switch_heading2 -->
                <!-- BEGIN switch_heading3 -->
                <a href="{switch_heading3.LINK}">&raquo;&nbsp;{switch_heading3.LABEL}</a>
                <!-- END switch_heading3 -->
                <!-- BEGIN switch_heading4 -->
                <a href="{switch_heading4.LINK}">&raquo;&nbsp;{switch_heading4.LABEL}</a>
                <!-- END switch_heading4 -->
                <!-- BEGIN switch_heading5 -->
                <a href="{switch_heading5.LINK}">&raquo;&nbsp;{switch_heading5.LABEL}</a>
                <!-- END switch_heading5 -->

                <!-- BEGIN switch_content_page -->
                    <a href="#">&raquo;&nbsp;{PAGE_TITLE}</a>
                <!-- END switch_content_page -->
            </div>
            <div id="pathtrans">
            </div>
        </div>

        <div id="main">
            <div id="mainmenu">
                <div id="hmenu">
                    <div class="minibloc">
                        <form action="{DIRECTORY_FORMACTION}" method="post">

                        <div>
                            <label for="directory_name" class="title">Nom:</label>
                            <br /><input type="text" class="text" value="{DIRECTORY_SEARCH_LASTNAME}" name="directory_lastname"  id="directory_lastname" style="width:95%;" />
                        </div>
                        <div>
                            <label for="directory_name" class="title">Prénom:</label>
                            <br /><input type="text" class="text" value="{DIRECTORY_SEARCH_FIRSTNAME}" name="directory_firstname"  id="directory_firstname" style="width:95%;" />
                        </div>
                        <div>
                            <label for="directory_function" class="title">Fonction:</label>
                            <br /><input type="text" class="text" value="{DIRECTORY_SEARCH_FUNCTION}" name="directory_function" id="directory_function" style="width:95%;" />
                        </div>
                        <div>
                            <label for="directory_function" class="title">Rattachement:</label>
                            <br /><input type="text" class="text" value="{DIRECTORY_SEARCH_HEADING}" name="directory_heading" id="directory_heading" style="width:95%;" />
                        </div>
                        <div style="margin-top:2px;"><input type="submit" value="Chercher" class="button" style="width:98%;" /></div>
                        </form>
                        <div style="padding-top:5px;"><strong><a href="{DIRECTORY_LINK_FULL}">Annuaire complet</a></strong></div>
                        <div style="padding-top:5px;"><strong><a href="{DIRECTORY_LINK_ORGANIGRAM}">Organigramme</a></strong></div>
                        <div style="padding-top:5px;"><strong><a href="{DIRECTORY_LINK_SPEEDDIALING}">Numéros abrégés</a></strong></div>
                    </div>

                    <div class="minibloc">
                    <!-- BEGIN root1 -->
                        <!-- BEGIN heading1 -->
                        <a class="r1h1{root1.heading1.SEL}" href="{root1.heading1.LINK}" {root1.heading1.LINK_TARGET}>{root1.heading1.LABEL}</a>
                        <!-- END heading1 -->
                    <!-- END root1 -->
                    </div>

                </div>
            </div>

            <div id="content">

                <!-- BEGIN switch_pages -->
                <div id="pagemenu">
                    <!-- BEGIN page -->
                    <a class="page{switch_pages.page.SEL}" href="{switch_pages.page.LINK}">{switch_pages.page.LABEL}</a>
                    <!-- END page -->
                </div>
                <!-- END switch_pages -->

                <div id="pagecontent">

                    <!-- ***** DEBUT ANNUAIRE ***** -->

                    <!-- BEGIN directory_switch_result -->
                    <div class="directory">
                        <div class="directory_result">
                        <div class="directory_title1">Résultat de recherche dans l'annuaire</div>
                            <table cellspacing="0" cellpadding="0" border="1">
                                <tr class="directory_title">
                                    <th>Nom/Prénom</th>
                                    <th>Fonction</th>
                                    <th>Rattachements</th>
                                    <th>Grade</th>
                                    <th>Poste</th>
                                    <th>Téléphone</th>
                                </tr>
                                <!-- BEGIN contact -->
                                    <tr class="directory_contact{directory_switch_result.contact.ALTERNATE_STYLE}" onclick="javascript:document.location.href='{directory_switch_result.contact.LINK}';return false;" title="Ouvrir la fiche détaillée de {directory_switch_result.contact.LASTNAME} {directory_switch_result.contact.FIRSTNAME}">
                                        <td>{directory_switch_result.contact.LASTNAME} {directory_switch_result.contact.FIRSTNAME}</td>
                                        <td>{directory_switch_result.contact.FUNCTION}</td>
                                        <td>{directory_switch_result.contact.HEADINGS}</td>
                                        <td>{directory_switch_result.contact.RANK}</td>
                                        <td>{directory_switch_result.contact.NUMBER}</td>
                                        <td>{directory_switch_result.contact.PHONE}</td>
                                    </tr>
                                <!-- END  contact -->
                            </table>
                            <!-- BEGIN switch_message -->
                               <p style="padding:4px 0;"><em>{directory_switch_result.switch_message.CONTENT}</em></p>
                            <!-- END  switch_message -->
                        </div>
                    </div>
                    <!-- END directory_switch_result -->

                    <!-- BEGIN directory_switch_contact -->
                    <div class="directory">
                        <div class="directory_form">
                            <div>
                                <div style="float:left;width:120px;">
                                    <img title="Photo de {directory_switch_contact.LASTNAME} {directory_switch_contact.FIRSTNAME}" src="{directory_switch_contact.PHOTOPATH}" style="border:4px solid #d0d0d0;display:block;margin:0 auto;" />
                                </div>
                                <div style="margin-left:120px;">
                                    <div class="directory_title1">{directory_switch_contact.CIVILITY} {directory_switch_contact.LASTNAME} {directory_switch_contact.FIRSTNAME}</div>
                                    <div>
                                    <table class="directory_contact_form">
                                        <tr>
                                            <th>N° de Poste:</th>
                                            <td>{directory_switch_contact.NUMBER}</td>
                                            <th>Bâtiment:</th>
                                            <td>{directory_switch_contact.BUILDING}</td>
                                        </tr>
                                        <tr>
                                            <th>Téléphone:</th>
                                            <td>{directory_switch_contact.PHONE}</td>
                                            <th>Etage:</th>
                                            <td>{directory_switch_contact.FLOOR}</td>
                                        </tr>
                                        <tr>
                                            <th>Fonctions</th>
                                            <td>{directory_switch_contact.FUNCTION}</td>
                                            <th>Bureau:</th>
                                            <td>{directory_switch_contact.OFFICE}</td>
                                        </tr>
                                        <tr>
                                            <th>Grade:</th>
                                            <td>{directory_switch_contact.RANK}</td>
                                            <th>Adresse:</th>
                                            <td>{directory_switch_contact.HEADING_ADDRESS_FULL}</td>
                                        </tr>
                                        <tr>
                                            <th>Rattaché à:</th>
                                            <td colspan="3">{directory_switch_contact.HEADINGS}</td>
                                        </tr>
                                    </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div>
                            <div class="directory_title2">Autres personnes de la même rubrique:</div>
                            <table cellspacing="0" cellpadding="0" border="1">
                                <tr class="directory_title">
                                    <th>Nom/Prénom</th>
                                    <th>Fonction</th>
                                    <th>Grade</th>
                                    <th>Poste</th>
                                    <th>Téléphone</th>
                                </tr>
                                <!-- BEGIN contact -->
                                    <tr class="directory_contact{directory_switch_contact.contact.ALTERNATE_STYLE}" onclick="javascript:document.location.href='{directory_switch_contact.contact.LINK}';return false;" title="Ouvrir la fiche détaillée de {directory_switch_contact.contact.LASTNAME} {directory_switch_contact.contact.FIRSTNAME}">
                                        <td>{directory_switch_contact.contact.CIVILITY} {directory_switch_contact.contact.LASTNAME} {directory_switch_contact.contact.FIRSTNAME}</td>
                                        <td>{directory_switch_contact.contact.FUNCTION}</td>
                                        <td>{directory_switch_contact.contact.RANK}</td>
                                        <td>{directory_switch_contact.contact.NUMBER}</td>
                                        <td>{directory_switch_contact.contact.PHONE}</td>
                                    </tr>
                                <!-- END  contact -->
                            </table>
                        </div>

                    </div>
                    <!-- END directory_switch_contact -->

                    <!-- BEGIN directory_switch_full -->
                    <div class="directory">
                        <div class="directory_title1">Annuaire complet</div>
                        <!-- BEGIN switch_selected_heading -->
                            <div class="directory_title2">Rubrique
                                &nbsp;&raquo;&nbsp;<a href="{DIRECTORY_LINK_FULL}" title="Ouvrir l'annuaire complet">Tout</a>
                                <!-- BEGIN heading -->
                                &nbsp;&raquo;&nbsp;<a href="{directory_switch_full.switch_selected_heading.heading.LINK}" title="Ouvrir l'annuaire détaillé de {directory_switch_full.switch_selected_heading.heading.LABEL}">{directory_switch_full.switch_selected_heading.heading.LABEL}</a>
                                <!-- END heading -->
                            </div>
                        <!-- END switch_selected_heading -->
                        <table cellspacing="0" cellpadding="0" border="1">
                            <tr class="directory_title">
                                <th>Nom/Prénom</th>
                                <th>Fonction</th>
                                <th>Grade</th>
                                <th>Poste</th>
                                <th>Téléphone</th>
                            </tr>
                            <!-- BEGIN line -->
                                <!-- BEGIN heading -->
                                    <tr class="directory_heading" onclick="javascript:document.location.href='{directory_switch_full.line.heading.LINK}';return false;" title="Ouvrir l'annuaire détaillé de {directory_switch_full.line.heading.LABEL}">
                                        <td colspan="5" class="heading{directory_switch_full.line.heading.DEPTH}">&#149;&nbsp;{directory_switch_full.line.heading.LABEL}</td>
                                    </tr>
                                <!-- END heading -->

                                <!-- BEGIN contact -->
                                    <tr class="directory_contact{directory_switch_full.line.contact.ALTERNATE_STYLE}" onclick="javascript:document.location.href='{directory_switch_full.line.contact.LINK}';return false;" title="Ouvrir la fiche détaillée de {directory_switch_full.line.contact.LASTNAME} {directory_switch_full.line.contact.FIRSTNAME}">
                                        <td><div>{directory_switch_full.line.contact.CIVILITY} {directory_switch_full.line.contact.LASTNAME} {directory_switch_full.line.contact.FIRSTNAME}</div><div>{directory_switch_full.line.contact.COMMENTS}</div></td>
                                        <td>{directory_switch_full.line.contact.FUNCTION}</td>
                                        <td>{directory_switch_full.line.contact.RANK}</td>
                                        <td>{directory_switch_full.line.contact.NUMBER}</td>
                                        <td>{directory_switch_full.line.contact.PHONE}</td>
                                    </tr>
                                <!-- END  contact -->
                            <!-- END line -->
                        </table>
                    <!-- END directory_switch_full -->

                    <!-- BEGIN directory_switch_organigram -->
                    <div class="directory">
                        <div class="directory_title1">Organigramme</div>
                        <div class="directory_organigram">
                        <!-- BEGIN heading -->
			                 <a class="heading{directory_switch_organigram.heading.DEPTH}" href="{directory_switch_organigram.heading.LINK}">{directory_switch_organigram.heading.LABEL}</a>
                        <!-- END heading -->
                        </div>
                    <!-- END directory_switch_organigram -->

                    <!-- BEGIN directory_switch_speeddialing -->
                    <div class="directory">
                        <div class="directory_title1">Numéros abrégés</div>
                    </div>

                    <div class="directory_speeddialing">
                    <!-- BEGIN heading -->
                        <h1>{directory_switch_speeddialing.heading.LABEL}</h1>
	                    <!-- BEGIN number -->
                            <div>{directory_switch_speeddialing.heading.number.LABEL} : {directory_switch_speeddialing.heading.number.NUMBER}</div>
	                    <!-- END number -->
                    <!-- END heading -->
                    </div>
                    
                    <!-- END directory_switch_speeddialing -->

                    <!-- ***** FIN ANNUAIRE ***** -->

                    <!-- BEGIN switch_search -->
                    <h2>Résultat de la recherche pour "{PAGE_QUERYSTRING}"</h2>
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
                    <h2>Articles contenant le tag "{PAGE_QUERYTAG}"</h2>
                    <div id="search_result">
                        <!-- BEGIN result -->
                            <a href="{switch_tagsearch.result.LINK}" title="Lien vers {switch_tagsearch.result.TITLE}">
                                <h2>{switch_tagsearch.result.TITLE}</h2>
                                <div class="link">&raquo; {switch_tagsearch.result.SHORT_LINK} ({switch_tagsearch.result.SIZE} ko)</div>
                            </a>
                        <!-- END result -->
                    </div>
                    <!-- END switch_tagsearch -->

                    <!-- BEGIN switch_content_page -->
                        <h2>{PAGE_TITLE}</h2>
                        <!-- BEGIN switch_tags -->
                            <p id="page_tags"><span>tags :</span>
                                <!-- BEGIN tag -->
                                    <a title="Afficher les articles contenant le tag &laquo; {switch_content_page.switch_tags.tag.TAG} &raquo;" href="{switch_content_page.switch_tags.tag.LINK}">{switch_content_page.switch_tags.tag.TAG}</a>
                                <!-- END tag -->
                            </p>
                        <!-- END switch_tags -->
                        {PAGE_CONTENT}
                    <!-- END switch_content_page -->

                    <!-- BEGIN switch_content_message -->
                        <h2>{MESSAGE_TITLE}</h2>
                        {MESSAGE_CONTENT}
                    <!-- END switch_content_message -->

                    <!-- BEGIN switch_content_error -->
                        <div style="text-align:center;padding:10px;">
                            <strong>Erreur {PAGE_ERROR_CODE}</strong>
                            <br />Cette page n'existe pas
                        </div>
                    <!-- END switch_content_error -->

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
                                <a href="{switch_content_sitemap.heading0.LINK}">{switch_content_sitemap.heading0.LABEL}</a>
                                <div class="sitemap_heading1">
                                    <!-- BEGIN heading1 -->
                                        <a href="{switch_content_sitemap.heading0.heading1.LINK}">{switch_content_sitemap.heading0.heading1.LABEL}</a>
                                        <div class="sitemap_heading2">
                                            <!-- BEGIN heading2 -->
                                                <a href="{switch_content_sitemap.heading0.heading1.heading2.LINK}">{switch_content_sitemap.heading0.heading1.heading2.LABEL}</a>
                                                <div class="sitemap_heading3">
                                                <!-- BEGIN heading3 -->
                                                    <a href="{switch_content_sitemap.heading0.heading1.heading2.heading3.LINK}">{switch_content_sitemap.heading0.heading1.heading2.heading3.LABEL}</a>
                                                    <div class="sitemap_heading4">
                                                    <!-- BEGIN heading4 -->
                                                        <a href="{switch_content_sitemap.heading0.heading1.heading2.heading3.heading4.LINK}">{switch_content_sitemap.heading0.heading1.heading2.heading3.heading4.LABEL}</a>
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
                </div>
            </div>
        </div>

        <div id="footer">
            Designed by <a href="http://andreasviklund.com">Andreas Viklund</a> & <a href="http://netlor.fr">netlor</a> // Powered by <a href="http://www.ploopi.fr">PLOOPI</a> // <a href="admin.php">Online Management</a>
            <br />[ time: <PLOOPI_EXEC_TIME> ms | php: <PLOOPI_PHP_P100>% | sql: <PLOOPI_NUMQUERIES>q | size: <PLOOPI_PAGE_SIZE>kB ]
        </div>

    </div>
</body>
</html>
