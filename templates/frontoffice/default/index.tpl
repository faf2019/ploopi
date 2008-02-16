<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
<head>
    <meta http-equiv="content-type" content="text/html; charset=iso-8859-15" />
    <meta name="description" content="{WORKSPACE_META_DESCRIPTION}" />
    <meta name="keywords" content="{WORKSPACE_META_KEYWORDS}" />
    <meta name="author" content="{WORKSPACE_META_AUTHOR}" />
    <meta name="copyright" content="{WORKSPACE_META_COPYRIGHT}" />
    <meta name="robots" content="{WORKSPACE_META_ROBOTS}" />

    <link type="text/css" rel="stylesheet" href="{TEMPLATE_PATH}/styles.css" media="screen" title="styles" />
    <link type="text/css" rel="stylesheet" href="{TEMPLATE_PATH}/forms_application.css" media="screen" />

    <script type="text/javascript" src="./include/prototype.js"></script>
    <script type="text/javascript" src="./include/functions.js"></script>
    <script type="text/javascript">
    //<!--
    {ADDITIONAL_JAVASCRIPT}

    var lstmsg = new Array();
    lstmsg[0] = "L'adresse mèl n'est pas valide.\nIl n'y a pas de caractère @\nUne adresse mèl valide est du type \"adresse@domaine.com\"";
    lstmsg[1] = "L'adresse mèl n'est pas valide.\nIl ne peut pas y avoir un point (.) juste après @\nUne adresse mèl valide est du type \"adresse@domaine.com\"";
    lstmsg[2] = "L'adresse mèl n'est pas valide.\nL'adresse mèl ne peut pas finir par un point (.)\nUne adresse mèl valide est du type \"adresse@domaine.com\"";
    lstmsg[3] = "L'adresse mèl n'est pas valide.\nL'adresse mèl ne peut pas contenir 2 points (.) qui se suivent.\nUne adresse mèl valide est du type \"adresse@domaine.com\"";
    lstmsg[4] = "Le champ '<FIELD_LABEL>' ne doit pas être vide";
    lstmsg[5] = "Le champ '<FIELD_LABEL>' doit être un nombre entier valide";
    lstmsg[6] = "Le champ '<FIELD_LABEL>' doit être un nombre réel valide";
    lstmsg[7] = "Le champ '<FIELD_LABEL>' doit être une date valide";
    lstmsg[8] = "Le champ '<FIELD_LABEL>' doit être une heure valide";
    lstmsg[9] = "Vous devez sélectionner une valeur pour le champ '<FIELD_LABEL>'";
    lstmsg[10] = "Le champ '<FIELD_LABEL>' doit être une couleur valide (#ffff00 / jaune / yellow)";

    var error_bgcolor = "#FFAAAA";

    //-->
    </script>

</head>

<body>
<table cellpadding="0" cellspacing="0" style="width:100%;background-color:#ffffff;height:500px;">
<tr>
    <td class="heading_menu">
        <a class="article_module_title" href="{ROOT_LINK}">Default Template</a>
        
        <!-- BEGIN root1 -->
            <!-- BEGIN heading1 -->
            <a class="heading1{root1.heading1.SEL}" href="{root1.heading1.LINK}">&raquo;&nbsp;{root1.heading1.LABEL}</a>
                <!-- BEGIN heading2 -->
                <a class="heading2{root1.heading1.heading2.SEL}" href="{root1.heading1.heading2.LINK}">&raquo;&nbsp;{root1.heading1.heading2.LABEL}</a>
                    <!-- BEGIN heading3 -->
                    <a class="heading3{root1.heading1.heading2.heading3.SEL}" href="{root1.heading1.heading2.heading3.LINK}">&raquo;&nbsp;{root1.heading1.heading2.heading3.LABEL}</a>
                    <!-- END heading3 -->
                <!-- END heading2 -->
            <!-- END heading1 -->
        <!-- END root1 -->
    </td>
    <td style="vertical-align:top;">
        <table cellpadding="0" cellspacing="0" style="width:100%;">
        <tr>
            <td class="article_menu">
            <!-- BEGIN page -->
                <a class="article{page.SELECTED}" href="{page.LINK}">{page.LABEL}</a>
            <!-- END page -->
            &nbsp;
            </td>
        </tr>
        </table>

        <table cellpadding="0" cellspacing="0" style="width:100%;">
        <tr>
            <td class="article_content">
            <!-- BEGIN switch_content_page -->
            <table cellpadding="0" cellspacing="0" style="width:100%;">
            <tr>
                <td>
                    <div class="article_title">{PAGE_TITLE}</div>
                    <div class="article_desc"> Auteur: <b>{PAGE_AUTHOR}</b></div>
                </td>
                <td>
                    <div style="float:right;padding:2px;border:1px solid #c0c0c0;background-color:#f0f0f0;">
                        Ref: <b>{PAGE_REFERENCE}</b>
                        <br>Version: <b>{PAGE_VERSION}</b>
                        <br>Date: <b>{PAGE_DATE}</b>
                    </div>
                </td>
            </tr>
            </table>
            <table cellpadding="0" cellspacing="0" style="width:100%;">
            <tr>
                <td>
                {PAGE_CONTENT}
                </td>
            </tr>
            </table>
            <!-- END switch_content_page -->
            </td>
        </tr>
        </table>
    </td>
</tr>
</table>
</body>
</html>
