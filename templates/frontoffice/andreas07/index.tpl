<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
    <meta http-equiv="content-type" content="text/html; charset=iso-8859-1" />
    <meta name="description" content="{WORKSPACE_META_DESCRIPTION} {PAGE_META_DESCRIPTION}" />
    <meta name="keywords" content="{WORKSPACE_META_KEYWORDS}, {PAGE_META_KEYWORDS}" />
    <meta name="author" content="{WORKSPACE_META_AUTHOR}" />
    <meta name="copyright" content="{WORKSPACE_META_COPYRIGHT}" />
    <meta name="robots" content="{WORKSPACE_META_ROBOTS}" />

    <title>{SITE_TITLE} - {PAGE_TITLE}</title>

    <base href="{SITE_BASEPATH}" />

    <link rel="stylesheet" type="text/css" href="{TEMPLATE_PATH}/css/styles.css" title="andreas07" media="screen,projection" />
    <link rel="stylesheet" type="text/css" href="{TEMPLATE_PATH}/css/skin.css" media="screen,projection" />
    <link rel="stylesheet" type="text/css" href="{TEMPLATE_PATH}/css/forms.css" media="screen,projection" />
    <link rel="stylesheet" type="text/css" href="{TEMPLATE_PATH}/css/calendar.css" media="screen,projection" />
    <link rel="stylesheet" type="text/css" href="{TEMPLATE_PATH}/css/search.css" media="screen,projection" />
    <link rel="stylesheet" type="text/css" href="{TEMPLATE_PATH}/css/system_trombi.css" media="screen,projection" />
    <!-- BEGIN module_css -->
    <link type="text/css" rel="stylesheet" href="{module_css.PATH}" media="screen" />
    <!-- END module_css -->

    <!--[if lte IE 6]>
    <link type="text/css" rel="stylesheet" href="{TEMPLATE_PATH}/css/skin_ie.css" media="screen" />
    <link type="text/css" rel="stylesheet" href="{TEMPLATE_PATH}/css/calendar_ie.css" media="screen" />
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
    <!-- BEGIN switch_content_page -->
    {PAGE_HEADCONTENT}
    <!-- END switch_content_page -->
</head>

<body>
<div id="wrap">

<div id="leftside">
    <h1>{SITE_TITLE}</h1>
    <h2>Slogan � modifier</h2>

    <div id="menu">
        <!-- BEGIN root1 -->
            <!-- BEGIN heading1 -->
            <a class="{root1.heading1.SEL}" href="{root1.heading1.LINK}" target="{root1.heading1.LINK_TARGET}" href="index.html">{root1.heading1.LABEL}</a>
            <!-- END heading1 -->
        <!-- END root1 -->
    </div>

    <h3>Derni�re mise � jour:</h3>
    <p>{LASTUPDATE_DATE} � {LASTUPDATE_TIME}</p>
</div>

<div id="extras">
    <h2>Recherche</h2>
    <form method="post" action="{SITE_HOME}">
        <input type="text" alt="recherche" id="recherche_field" name="query_string" value="{PAGE_QUERYSTRING}" class="text" style="width:75%" />
        <input type="submit" value="go" class="button" style="width:20%" />
    </form>
                        <a href="./recherche.html">Recherche avanc�e</a>

    <!-- BEGIN switch_subscription -->
        <h2>Abonnement</h2>
           <!-- BEGIN switch_response -->
           <div style="padding-bottom:4px;"><strong>{switch_subscription.switch_response.CONTENT}</strong></div>
           <!-- END switch_response -->
            <form method="post" action="{switch_subscription.ACTION}">
                <div>
                    <input type="radio" class="pointer" name="subscription_headingid" id="subscription_site" value="{switch_subscription.ROOTID}" /><label class="pointer" for="subscription_site">Site</label>
                    <input type="radio" class="pointer" name="subscription_headingid" id="subscription_heading" value="{switch_subscription.HEADINGID}" checked /><label class="pointer" for="subscription_heading">Rubrique</label>
                </div>
                <div>
                    <input type="text" title="Entrez votre adresse email" alt="Entrez votre adresse email" class="text" name="subscription_email" value="Entrez votre adresse email" onfocus="javascript:this.value='';" style="width:75%" />
                    <input type="submit" title="Bouton pour valider l'abonnement" class="button" value="go" style="width:20%" />
                </div>
            </form>
    <!-- END switch_subscription -->
    <!-- BEGIN switch_newsletter_subscription -->
        <div class="minibloc">
            <h2>Abonnement NewsLetter</h2>
            <!-- BEGIN switch_response -->
            <div style="padding-bottom:4px;"><strong>{switch_newsletter_subscription.switch_response.CONTENT}</strong></div>
            <!-- END switch_response -->
            <form method="post" action="{switch_newsletter_subscription.ACTION}">
                <div>
                    <input type="text" title="Entrez votre adresse email" alt="Entrez votre adresse email" class="text" name="subscription_email" value="Entrez votre adresse email" onfocus="javascript:this.value='';" style="width:75%" />
                    <input type="submit" title="Bouton pour valider l'inscription" class="button" value="go" style="width:20%" />
                </div>
            </form>
        </div>
    <!-- END switch_newsletter_subscription -->

    <!-- BEGIN switch_pages -->
    <ul>
        <h2>Articles</h2>
        <!-- BEGIN page -->
        <li><a class="{switch_pages.page.SEL}" href="{switch_pages.page.LINK}">{switch_pages.page.LABEL}</a></li>
        <!-- END page -->
    </ul>
    <!-- END switch_pages -->

    <h2>Nuage de tags</h2>
    <div id="tagcloud">
        <!-- BEGIN tagcloud -->
            <a href="{tagcloud.LINK}" class="{tagcloud.SELECTED}" title="Afficher les articles contenant le tag &laquo; {tagcloud.TAG} &raquo;" style="font-size:{tagcloud.SIZE}%;">{tagcloud.TAG}<sup>{tagcloud.OCCURENCE}</sup></a>
        <!-- END tagcloud -->
    </div>
    
    <!-- BEGIN bloc1 -->
    <h2>Exemple de bloc1</h2>
    <div>
    {bloc1.CONTENT}
    </div>
    <!-- END bloc1 -->
    
    <!-- BEGIN bloc2 -->
    <h2>Exemple de bloc2</h2>
    <div>
    {bloc2.CONTENT}
    </div>
    <!-- END bloc2 -->

    <!-- BEGIN bloc3 -->
    <h2>Exemple de bloc3</h2>
    <div>
    {bloc3.CONTENT}
    </div>
    <!-- END bloc3 -->
                    
    <!-- BEGIN switch_blog -->
        <!-- BEGIN calendar -->
        {switch_blog.calendar.CONTENT}
        <!-- END calendar -->
        <br/>
        <!-- BEGIN archive -->
        <ul>
            <h2>{switch_blog.archive.YEAR}</h2>
            <!-- BEGIN month -->
            <li><a href="javascript:void(0);" onclick="javascript:window.location.href='{switch_blog.archive.month.URL}'; return false;" class="row_archives">{switch_blog.archive.month.MONTH_LETTER} ({switch_blog.archive.month.NBART})</a></li>
            <!-- END month -->
        </ul>
        <!-- END archive -->
    <!-- END switch_blog -->

</div>

<div id="content">
    <!-- BEGIN switch_content_page -->
        <h1>{PAGE_TITLE}</h1>
        <h2>{PAGE_DESCRIPTION}</h2>
        <p>{PAGE_CONTENT}</p>
        <!-- BEGIN sw_comment -->
            <div id="bloc_comment_{switch_content_page.PAGE_ID}">
                <!-- BEGIN sw_comment_response -->
                    <h3 style="text-align: center; background-color: #ffffaa;">{switch_content_page.sw_comment.sw_comment_response.RESPONSE}</h3>
                <!-- END sw_comment_response -->
                <!-- BEGIN comment -->
                    <div class="block_comment">
                    <p>{switch_content_page.sw_comment.comment.POSTBY}</p>
                    {switch_content_page.sw_comment.comment.COMMENT}
                    </div>
                <!-- END comment -->
                <!-- BEGIN sw_showall -->
                <div class="block_comment_showall">
                    <a href="javascript:void(0);" onclick="javascript:window.location.href='{switch_content_page.sw_comment.sw_showall.URL_ARTICLE}'; return false;">{switch_content_page.sw_comment.sw_showall.LIBELLE}</a>
                </div>
                <!-- END sw_showall -->
                <div>
                    <a name="form_comment">
                    <form action="{switch_content_page.sw_comment.ACTION}" method="post" onsubmit="javascript:return comment_validate(this);">
                        <div class="form" style="width: 65%; float: left;">
                            <p>
                                <label>Nom(*) :</label><input type="text" class="text" id="comment_nickname" name="comment_nickname"  maxlength="50"/>
                            </p>
                            <p>
                                <label>Email (ne sera pas affich�) :</label><input type="text" class="text" id="comment_email" name="comment_email" maxlength="255"/>
                            </p>
                            <p>
                                <label>Commentaire(*) :</label><textarea class="textarea" id="comment_comment" name="comment_comment"></textarea>
                            </p>
                        </div>
                        <div class="form" style="padding: 50px 0 0 30px; float: left;">
                            <p>
                                <div style="margin: 0 5px 0 0; float: left; width: 130px; height: 45px; text-align: center;">
                                    <img id="img_captcha_{switch_content_page.sw_comment.IDCAPTCHA}" align="center" src="./img/ajax-loader.gif"/>
                                </div>
                                <div style="float: left; padding: 0; margin: 0;">
                                    <div style="padding: 2px 0 4px 0;">
                                        <object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=9,0,0,0" width="19" height="19" id="SecurImage_{switch_content_page.PAGE_ID}" align="top">
                                            <param name="allowScriptAccess" value="sameDomain" />
                                            <param name="allowFullScreen" value="false" />
                                            <param name="movie" value="./img/captcha/securimage_play.swf?audio={switch_content_page.sw_comment.URLTOCAPTCHASOUND}&bgColor1=#286EA0&bgColor2=#fff&iconColor=#000&roundedCorner=5" />
                                            <param name="quality" value="high" />
                                            <param name="bgcolor" value="#ffffff" />
                                            <embed src="./img/captcha/securimage_play.swf?audio={switch_content_page.sw_comment.URLTOCAPTCHASOUND}&bgColor1=#286EA0&bgColor2=#fff&iconColor=#000&roundedCorner=5" quality="high" bgcolor="#ffffff" width="19" height="19" name="SecurImage_{switch_content_page.PAGE_ID}" align="top" allowScriptAccess="sameDomain" allowFullScreen="false" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" />
                                        </object>
                                    </div>
                                    <div style="cursor: pointer;" onclick="javascript: $('img_captcha_{switch_content_page.sw_comment.IDCAPTCHA}').src = '{switch_content_page.sw_comment.URLTOCAPTCHA}&random='+Math.random(); return false;"><img src="{TEMPLATE_PATH}/img/refresh.png" alt="Reload Image" border="0" align="bottom" /></div>
                                </div>
                            </p>
                            <p>
                                <label>Code(*) :</label><input type="text" class="text" id="captcha_code_{switch_content_page.sw_comment.IDCAPTCHA}" name="captcha_code" maxlength="8" style="width: 140px;" />
                            </p>
                        </div>
                        <div class="form_validate">(*) Champs requis</div>
                        <div class="form_validate">
                            <input type="submit" class="" value="Envoyer" />
                        </div>
                    </form>
                    </a>
                    <script type="text/javascript">
                    function comment_validate(form)
                    {
                        if (ploopi_validatefield('Nom', form.comment_nickname, 'string'))
                        if (ploopi_validatefield('Email', form.comment_email, 'emptyemail'))
                        if (ploopi_validatefield('Commentaire', form.comment_comment, 'string'))
                        if (ploopi_validatefield('Code', form.captcha_code_{switch_content_page.sw_comment.IDCAPTCHA}, 'captcha', '{PAGE_URL_CONTROLCAPTCHA}', 'img_captcha_{switch_content_page.sw_comment.IDCAPTCHA}', '{PAGE_URL_UPDATECAPTCHA}'))
                          return(true);

                        return(false);
                    }

                    Event.observe(window, 'load', function() { $('img_captcha_{switch_content_page.sw_comment.IDCAPTCHA}').src = '{switch_content_page.sw_comment.URLTOCAPTCHA}&random='+Math.random(); } );
                    </script>
                </div>
            </div>
        <!-- END sw_comment -->
    <!-- END switch_content_page -->

    <!-- BEGIN switch_content_blog -->
        <!-- BEGIN article -->
            <h1>{switch_content_blog.article.PAGE_TITLE}</h1>
            <h2>{switch_content_blog.article.PAGE_DESCRIPTION}</h2>
            <p>{switch_content_blog.article.PAGE_CONTENT}</p>
            <div style="clear: both; font-size: 9px; padding: 5px 20px 0 0; text-align: center;">
                <!-- BEGIN sw_modify -->
                <div style="float: right;">modifi� le : {switch_content_blog.article.PAGE_LASTUPDATE_DATE}</div>
                <!-- END sw_modify -->
                <div style="float: left;">{switch_content_blog.article.PAGE_AUTHOR} - {switch_content_blog.article.PAGE_DATE}</div>
                <!-- BEGIN sw_comment -->
                    <!-- BEGIN info -->
                        <a href="javascript:void(0);" onclick="javascript:window.location.href='{switch_content_blog.article.PAGE_URL_ARTICLE}'; return false;">{switch_content_blog.article.sw_comment.info.NB_COMMENT} {switch_content_blog.article.sw_comment.info.LIBELLE}</a>
                    <!-- END info -->
                <!-- END sw_comment -->
            </div>
            <!-- BEGIN sw_comment -->
                <div id="bloc_comment_{switch_content_blog.article.PAGE_ID}">
                    <!-- BEGIN comment -->
                        <div class="block_comment">
                        <p>{switch_content_blog.article.sw_comment.comment.POSTBY}</p>
                        {switch_content_blog.article.sw_comment.comment.COMMENT}
                        </div>
                    <!-- END comment -->
                    <div style="overflow: auto;">
                      <div class="block_comment_show_or_post" style="float: right; text-align:right;">
                          <a href="javascript:void(0);" onclick="javascript:window.location.href='{switch_content_blog.article.PAGE_URL_ARTICLE}#form_comment'; return false;">{switch_content_blog.article.sw_comment.LIBELLE_POST}</a>
                      </div>
                      <!-- BEGIN sw_showall -->
                      <div class="block_comment_show_or_post" style="float: left;">
                          <a href="javascript:void(0);" onclick="javascript:window.location.href='{switch_content_blog.article.PAGE_URL_ARTICLE}'; return false;">{switch_content_blog.article.sw_comment.sw_showall.LIBELLE_SHOW}</a>
                      </div>
                      <!-- END sw_showall -->
                    </div>
                </div>
            <!-- END sw_comment -->
            <!-- BEGIN sw_separator -->
                <div style="clear: both;"><hr/></div>
            <!-- END sw_separator -->
        <!-- END article -->

        <div style="overflow: hidden; clear: both;">
            <!-- BEGIN page_after -->
            <a href="javascript:void(0);" onclick="javascript:window.location.href='{switch_content_blog.page_after.URL}'; return false;" style="float: right; padding: 10px 10px 0 0;">pages suivantes&nbsp;&gt;&gt;</a>
            <!-- END page_after -->
            <!-- BEGIN page_before -->
            <a href="javascript:void(0);" onclick="javascript:window.location.href='{switch_content_blog.page_before.URL}'; return false;" style="float: left; padding: 10px 0 0 0;">&lt;&lt;&nbsp;pages pr�c�dentes</a>
            <!-- END page_before -->
        </div>
    <!-- END switch_content_blog -->

    <!-- BEGIN switch_content_message -->
        <h1>{MESSAGE_TITLE}</h1>
        <h2>{MESSAGE_CONTENT}</h2>
    <!-- END switch_content_message -->

    <!-- BEGIN switch_content_error -->
        <h1>Erreur {PAGE_ERROR_CODE}</h1>
        <h2>Cette page n'existe pas</h2>
    <!-- END switch_content_error -->


    <!-- BEGIN switch_advanced_search -->
        <h2>Recherche avanc�e</h2>

        <form id="form_advanced_search" method="post" action="./recherche.html">

            <div>
                <label for="query_string">Mot(s) recherch�(s):</label>
                <br /><input type="text" alt="recherche" id="search_query_string" name="query_string" value="{PAGE_QUERYSTRING}" />
            </div>

            <div>
                <label for="query_content_type">Type de contenu:</label>
                <br />
                <select id="query_content_type" name="query_content_type">
                    <option value="all" {PAGE_QUERYCT_ALL_SELECTED}>Tout</option>
                    <option value="art" {PAGE_QUERYCT_ART_SELECTED}>Articles</option>
                    <option value="doc" {PAGE_QUERYCT_DOC_SELECTED}>Documents</option>
                </select>
            </div>

            <div id="query_mime_type_div">
                <label for="query_mime_type">Type de document (extension):</label>
                <br /><input style="width:50px;" type="text" id="query_mime_type" name="query_mime_type" value="{PAGE_QUERYMT}" />
            </div>

            <script type="text/javascript">
                Event.observe(window, 'load', function() { $('query_mime_type_div').style.display = $('query_content_type').value == 'art' ? 'none' : 'block'; });
                Event.observe($('query_content_type'), 'change', function() { $('query_mime_type_div').style.display = this.value == 'art' ? 'none' : 'block'; });
            </script>

            <div>
                <label for="query_date_b">Date de publication:</label>
                <br /><input style="width:80px;" type="text" class="date" name="query_date_b" id="query_date_b" value="{PAGE_QUERYDATE_B}"/><a onclick="javascript:ploopi_xmlhttprequest_topopup(192, event, 'ploopi_popup_calendar', 'index-light.php?ploopi_op=calendar_open', 'selected_date='+$('query_date_b').value+'&amp;inputfield_id=query_date_b', 'POST', true);" href="javascript:void(0);"><img src="./img/calendar/calendar.gif"></a>
                <br /><input style="width:80px;" type="text" class="date" name="query_date_e" id="query_date_e" value="{PAGE_QUERYDATE_E}"/><a onclick="javascript:ploopi_xmlhttprequest_topopup(192, event, 'ploopi_popup_calendar', 'index-light.php?ploopi_op=calendar_open', 'selected_date='+$('query_date_e').value+'&amp;inputfield_id=query_date_e', 'POST', true);" href="javascript:void(0);"><img src="./img/calendar/calendar.gif"></a>
            </div>

            <div>
                <label for="query_heading_id">Rubrique:</label>
                <br />
                <select id="query_heading_id" name="query_heading_id">
                    <option value="">Toutes</option>
                    <!-- BEGIN headings -->
                    <option value="{switch_advanced_search.headings.ID}" class="search_headings_{switch_advanced_search.headings.DEPTH}" {switch_advanced_search.headings.SELECTED}>{switch_advanced_search.headings.LABEL}</option>
                    <!-- END headings -->
                </select>
            </div>

            <div>
                <button type="submit">Rechercher</button>
            </div>
        </form>
    <!-- END switch_advanced_search -->

    <!-- BEGIN switch_search -->
        <h1>R�sultat de la recherche</h1>
        <h2>Mot cl�: {PAGE_QUERYSTRING}</h2>
        <!-- BEGIN switch_notfound -->
            <p>Aucun r�sultat pour cette recherche</p>
        <!-- END switch_notfound -->
        <div id="search_result">
            <!-- BEGIN result -->
                <a href="{switch_search.result.LINK}" title="Lien vers {switch_search.result.TITLE}">
                    <h3>{switch_search.result.TITLE}<span class="relevance">{switch_search.result.RELEVANCE} %</span></h3>
                    {switch_search.result.EXTRACT}
                    <div class="link">&raquo; {switch_search.result.SHORT_LINK} ({switch_search.result.SIZE} ko)</div>
                </a>
            <!-- END result -->
        </div>
    <!-- END switch_search -->

    <!-- BEGIN switch_tagsearch -->
        <h1>Articles tagu�s</h1>
        <h2>{PAGE_QUERYTAG}</h2>
        <div id="search_result">
            <!-- BEGIN result -->
                <a href="{switch_tagsearch.result.LINK}" title="Lien vers {switch_tagsearch.result.TITLE}">
                    <h3>{switch_tagsearch.result.TITLE}</h3>
                    <div class="link">&raquo; {switch_tagsearch.result.SHORT_LINK} ({switch_tagsearch.result.SIZE} ko)</div>
                </a>
            <!-- END result -->
        </div>
    <!-- END switch_tagsearch -->

    <!-- BEGIN switch_content_heading -->
        <h1>{HEADING_LABEL}</h1>
        <!-- BEGIN page -->
            <a class="headings_page" href="{switch_content_heading.page.LINK}">
                <img src="{TEMPLATE_PATH}/img/page.png" />
                <span><b>{switch_content_heading.page.LABEL}</b><br />{switch_content_heading.page.DATE}</span>
            </a>
        <!-- END page -->

        <!-- BEGIN subheading1 -->
            <a class="subheading1" href="{switch_content_heading.subheading1.LINK}">
                <img src="{TEMPLATE_PATH}/img/folder.png" />
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
        <h1>Plan du site</h1>
        <div id="sitemap">
            <!-- BEGIN heading0 -->
            <div>
            <a href="{switch_content_sitemap.heading0.LINK}">{switch_content_sitemap.heading0.LABEL}</a>
            <!-- BEGIN heading1 -->
            <div>
            <a href="{switch_content_sitemap.heading0.heading1.LINK}">{switch_content_sitemap.heading0.heading1.LABEL}</a>
            <!-- BEGIN heading2 -->
            <div>
            <a href="{switch_content_sitemap.heading0.heading1.heading2.LINK}">{switch_content_sitemap.heading0.heading1.heading2.LABEL}</a>
            <!-- BEGIN heading3 -->
            <div>
            <a href="{switch_content_sitemap.heading0.heading1.heading2.heading3.LINK}">{switch_content_sitemap.heading0.heading1.heading2.heading3.LABEL}</a>
            <!-- BEGIN heading4 -->
            <div>
            <a href="{switch_content_sitemap.heading0.heading1.heading2.heading3.heading4.LINK}">{switch_content_sitemap.heading0.heading1.heading2.heading3.heading4.LABEL}</a>
            </div>
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


    <h3>Informations sur le site</h3>
    <p>&copy; 2008 <a href="#">{WORKSPACE_META_COPYRIGHT}</a> | Original design by <a href="http://andreasviklund.com/">Andreas Viklund</a> | Propuls� par <a href="http://www.ploopi.org" target="_blank">Ploopi</a></p>
</div>

</div>
</body>
</html>
