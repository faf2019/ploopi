<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
<head>
<meta http-equiv="content-type" content="text/html; charset=iso-8859-1" />
<meta name="description" content="_your description goes here_" />
<meta name="keywords" content="_your,keywords,goes,here_" />
<meta name="author" content="_your name goes here_  / Original design: Andreas Viklund - http://andreasviklund.com/" />
<link rel="icon" href="{TEMPLATE_PATH}icons/16/web.png" type="image/png" />
<link type="text/css" rel="stylesheet" href="{TEMPLATE_PATH}pda.css" media="screen" title="styles" />
<title>{HEADINGS_TITLE}</title>
<script type="text/javascript">
//<!--

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
error_bgcolor = "#FCE6D6";

//-->
</script>

<script type="text/javascript" src="./include/functions.js"></script>

</head>

<body>
<div id="header">PLOOPI Online Mobility Business</div>


<!-- BEGIN switch_user_logged_out -->
<div id="login">
    <form name="formlogin" action="admin.php" method="post" {PLOOPI_TEST_MAC}>
    <table>
        <tr>
            <td align="right"><label for="ploopi_login">login</label>:</td>
            <td><input type="text" id="ploopi_login" name="ploopi_login" size="10" /></td>
        </tr>
        <tr>
            <td align="right"><label for="ploopi_password">password</label>:</td>
            <td><input type="password" id="ploopi_password" name="ploopi_password" size="10" /></td>
        </tr>
        <tr>
            <td colspan="2" align="right">
                <input type="submit" value="connexion"  class="button" />
            </td>
        </tr>
    </table>
    </form>
</div>
<!-- END switch_user_logged_out -->

<!-- BEGIN switch_user_logged_in -->
<div id="mainmenu">
    Bonjour <b>{USER_FIRSTNAME} {USER_LASTNAME}</b>
    <!-- BEGIN workgroup -->
        &#149;&nbsp;<a class="{switch_user_logged_in.workgroup.SELECTED}" href="{switch_user_logged_in.workgroup.URL}">{switch_user_logged_in.workgroup.TITLE}</a>
    <!-- END workgroup -->
    &#149;&nbsp;<a class="{MAINMENU_SHOWTICKETS_SEL}" href="{MAINMENU_SHOWTICKETS_URL}">Mes Tickets</a>
    &#149;&nbsp;<a class="{MAINMENU_SHOWANNOTATIONS_SEL}" href="{MAINMENU_SHOWANNOTATIONS_URL}">Mes Annotations</a>
    &#149;&nbsp;<a class="{MAINMENU_SHOWPROFILE_SEL}" href="{MAINMENU_SHOWPROFILE_URL}">Mon Profil</a>
    &#149;&nbsp;<a href="{USER_DECONNECT}">Déconnexion</a>
</div>
<!-- END switch_user_logged_in -->

<!-- BEGIN switch_blockmenu -->
<div id="block_modules">
<!-- BEGIN blocktype -->
    <!-- BEGIN block -->
        <tr>
            &#149;&nbsp;<a CLASS="{switch_blockmenu.blocktype.block.CLASS}" href="{switch_blockmenu.blocktype.block.URL}">{switch_blockmenu.blocktype.block.TITLE}</a>
        </tr>
    <!-- END block -->
<!-- END blocktype -->
</div>
<!-- END switch_blockmenu -->
<div>{PAGE_CONTENT}</div>



<div id="footer">{PLOOPI_COPYRIGHT} | <PLOOPI_PAGE_SIZE> | <PLOOPI_EXEC_TIME> ms | <PLOOPI_NUMQUERIES> qry</div>
</body>
</html>