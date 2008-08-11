<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<!--
    Ploopi_Menus ~ Template pour Ploopi
    Copyright (c) 2007 Ovensia

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
    <link type="text/css" rel="alternate stylesheet" title="large" href="{TEMPLATE_PATH}/css/styles_large.css" media="screen" />

    <!-- BEGIN module_css -->
    <link type="text/css" rel="stylesheet" href="{module_css.PATH}" media="screen" />
    <!-- END module_css -->

    <!--[if lte IE 6]>
    <link type="text/css" rel="stylesheet" href="{TEMPLATE_PATH}/css/png.css" media="screen"/>
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

    <script type="text/javascript" src="{TEMPLATE_PATH}/js/styleswitcher.js"></script>

 <script type="text/javascript">
    {ADDITIONAL_JAVASCRIPT}

    <!-- BEGIN switch_user_logged_in -->
    function findPosX(obj)
    {
        var curleft = 0;
        if (obj.offsetParent)
        {
            while (obj.offsetParent)
            {
                curleft += obj.offsetLeft;
                obj = obj.offsetParent;
            }
        }
        else if (obj.x) curleft += obj.x;
        return curleft;
    }

    function findPosY(obj)
    {
        var curtop = 0;
        if (obj.offsetParent)
        {
            while (obj.offsetParent)
            {
                curtop += obj.offsetTop;
                obj = obj.offsetParent;
            }
        }
        else if (obj.y) curtop += obj.y;
        return curtop;
    }

    function activer(action, blockid, src)
    {
        cible = $(blockid);

        if (!action) cible.style.visibility = 'hidden'; 
        else
        {
            x = 0;
            y = 0;
            if (action && src)
            {
                x = findPosX(src);
                y = findPosY(src);
            }
            if (cible.innerHTML.length > 60)
            {
                if (x!=0) cible.style.left = (x-2)+'px';
                if (y!=0) cible.style.top = (y+22)+'px';
                cible.style.visibility = 'visible';
            }
        }

    }

    function cacher()
    {
        <!-- BEGIN switch_blockmenu -->
            <!-- BEGIN block -->
            activer(false,'menu_deroulant{switch_user_logged_in.switch_blockmenu.block.ID}', this);
            <!-- END block -->
        <!-- END switch_blockmenu -->
    }
    <!-- END switch_user_logged_in -->
    </script>

    {ADDITIONAL_HEAD}
</head>

<body class="index">

<!-- BEGIN switch_user_logged_out -->
<div id="container_out">
    <div id="loginbox">
    <form name="formlogin" action="admin.php" method="post" {PLOOPI_TEST_MAC}>
        <div class="loginbox_title">
            <p>Identification</p>
        </div>
        <div style="padding:10px;">
            <div class="loginbox_line" style="text-align:right;">
                <label for="ploopi_login">utilisateur:&nbsp;</label>
                <input type="text" class="text" id="ploopi_login" name="ploopi_login" style="width:100px;" tabindex="1" />
            </div>
            <div class="loginbox_line" style="text-align:right;">
                <label for="ploopi_password">mot de passe:&nbsp;</label>
                <input class="text" type="password" id="ploopi_password" name="ploopi_password" style="width:100px;" tabindex="2" />
            </div>
            <div class="loginbox_line">
                <div style="float:left;width:100%;">
                    <input type="submit" value="connexion" class="flatbutton" style="width:100%;" tabindex="3"/>
                </div>
            </div>
            <div class="loginbox_line" style="text-align:right;">
                <a href="">Mot de passe perdu ?</a>
            </div>
            <!-- BEGIN switch_ploopierrormsg -->
            <div class="loginbox_line" style="text-align:right;">
                <img src="{TEMPLATE_PATH}/img/system/attention.png" style="display:block;float:left;"><span class="error">&nbsp;{PLOOPI_ERROR}</span>
            </div>
            <!-- END switch_ploopierrormsg -->
        </div>
    </form>
        <div class="loginbox_footer"></div>
    </div>
</div>
<!-- END switch_user_logged_out -->


<!-- BEGIN switch_user_logged_in -->
<div id="container_in">

    <div id="bandeau">
        <img src="{TEMPLATE_PATH}/img/template/bandeau_gauche.png" style="position:absolute;top:0;right:297px;"/>
        <img src="{TEMPLATE_PATH}/img/template/bandeau_droit.png" style="position:absolute;top:0;right:0px;" usemap="#map" border="0" />

        <map name="map">
        <area shape="rect" coords="5,15,102,45" alt="{MAINMENU_TICKETS}" href="{MAINMENU_SHOWTICKETS_URL}" />
        <area shape="rect" coords="6,54,121,79" alt="{MAINMENU_ANNOTATIONS}" href="{MAINMENU_SHOWANNOTATIONS_URL}" />
        <area shape="rect" coords="75,84,151,105" alt="{MAINMENU_PROFILE" href="{MAINMENU_SHOWPROFILE_URL}" />
        <area shape="rect" coords="205,80,292,99" alt="{MAINMENU_DISCONNECTION}" href="{USER_DECONNECT}" />
        </map>

        <div id="menu_horizontal">
        <!-- BEGIN switch_blockmenu -->
            <!-- BEGIN block -->
            <a class="{switch_user_logged_in.switch_blockmenu.block.SELECTED}" href="javascript:void(0);" onmouseover="javascript:cacher();activer(true,'menu_deroulant{switch_user_logged_in.switch_blockmenu.block.ID}', this)">{switch_user_logged_in.switch_blockmenu.block.TITLE}</a>
            <!-- END block -->
        <!-- END switch_blockmenu -->
        &nbsp;
        </div>

        <div id="choix_espace">
            <p class="ploopi_va">
                <img id="choix_style_large" alt="Agrandir" title="Agrandir" src="{TEMPLATE_PATH}/img/template/view-fullscreen.png" onclick="cacher();setActiveStyleSheet('large');" />
                <img id="choix_style_short" alt="Réduire" title="Réduire" style="cursor:pointer;" src="{TEMPLATE_PATH}/img/template/view-restore.png" onclick="cacher();setActiveStyleSheet('short');" />
                <span>Bienvenue <b>{USER_FIRSTNAME} {USER_LASTNAME}</b>, choisissez votre espace :</span>
                <select class="select" onchange="javascript:if (this.value!='') document.location.href = this.value;" style="width:150px;">
                <!-- BEGIN workspace -->
                    <option value="{switch_user_logged_in.workspace.URL}" {switch_user_logged_in.workspace.SELECTED}>{switch_user_logged_in.workspace.TITLE}</option>
                <!-- END workspace -->
                <option value="{USER_WORKSPACE}" {USER_WORKSPACE_SEL}>(Mon Espace)</option>
                </select>
            </p>
        </div>

        <div id="workspace_title">
            <h1>{WORKSPACE_TITLE}</h1>
            <h2>{WORKSPACE_META_DESCRIPTION}</h2>
        </div>

        <!-- BEGIN switch_search -->
        <div id="recherche">
            <form method="post" id="form_recherche" action="{MAINMENU_SHOWSEARCH_URL}">
                {MAINMENU_SEARCH}:
                <p class="ploopi_va">
                    <input type="text" name="system_search_keywords" class="text" value="{SEARCH_KEYWORDS}">&nbsp;<a href="javascript:void(0);" onclick="$('form_recherche').submit();"><img src="{TEMPLATE_PATH}/img/template/search.png" value="Recherche"></a>
                </p>
            </form>
        </div>
        <!-- END switch_search -->
    </div>

    <!-- BEGIN switch_blockmenu -->
        <!-- BEGIN block -->
            <div class="module_block_deroulant" id="menu_deroulant{switch_user_logged_in.switch_blockmenu.block.ID}" onmouseout="javascript:cacher();" onmouseover="javascript:activer(true,'menu_deroulant{switch_user_logged_in.switch_blockmenu.block.ID}');" >
                <!-- BEGIN switch_content -->
                    <div class="module_content">{switch_user_logged_in.switch_blockmenu.block.switch_content.CONTENT}</div>
                <!-- END switch_content -->
                <!-- BEGIN menu -->
                    <a class="module_menu{switch_user_logged_in.switch_blockmenu.block.menu.SELECTED}" href="{switch_user_logged_in.switch_blockmenu.block.menu.URL}" target="{switch_user_logged_in.switch_blockmenu.block.menu.TARGET}">{switch_user_logged_in.switch_blockmenu.block.menu.LABEL}</a>
                <!-- END menu -->
                <div class="module_footer"></div>
            </div>
        <!-- END block -->
    <!-- END switch_blockmenu -->

    <div id="container2">
        <div id="module_haut"><div></div></div>
        <div id="module_fond">
            <div id="module_fond_droite">
                <div id="module_fond_vague">
                    <div id="page_content">{PAGE_CONTENT}</div>
                </div>
            </div>
        </div>
        <div id="module_bas"><div></div></div>
    </div>
</div>
<!-- END switch_user_logged_in -->




<p id="footer">
Template:&nbsp;<a href="http://ovensia.fr">{TEMPLATE_NAME}</a> |&nbsp;Propulsé par&nbsp;<a href="http://www.ploopi.fr">Ploopi {PLOOPI_VERSION}</a>&nbsp;&#169;&nbsp;2008&nbsp;<a href="http://ovensia.fr">Ovensia</a>&nbsp;|&nbsp;<a href="http://www.mozilla-europe.org/fr/products/firefox/">Préférez Firefox</a>
<br />[ page: <PLOOPI_PAGE_SIZE> ko | exec: <PLOOPI_EXEC_TIME> ms | sql: <PLOOPI_NUMQUERIES> req (<PLOOPI_SQL_P100> %) | session: <PLOOPI_SESSION_SIZE> ko ] 
</p>

<!-- BEGIN switch_user_logged_out -->
<script type="text/javascript">
ploopi_window_onload_stock(function() { if ($('ploopi_login')) $('ploopi_login').focus(); } );
</script>
<!-- END switch_user_logged_out -->
</body>
</html>
