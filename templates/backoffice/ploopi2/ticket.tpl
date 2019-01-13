<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
<head>
<style type="text/css">
body, form, table, td, input, select, textarea {
font: 12px tahoma, verdana, sans-serif;
color: #303030;
}

body, form  {
margin: 0;
}

body {
background: #f0f0f0;
padding:10px;
}

a {
text-decoration: underline;
color: #304066;
}

a:hover {
text-decoration: underline;
}

#mailcontent {
border:1px solid #c0c0c0;
padding:4px;
margin:12px 0;
background-color:#fff;
}

#linkedobject {
border:1px solid #c0c0c0;
padding:4px;
margin:12px 0;
background-color:#fff0f0;
}

</style>
</head>
<body>
Bonjour,
<br /><br />
Vous avez reçu un nouveau message envoyé par <strong><a href="mailto:{USER_FROM_EMAIL}">{USER_FROM_NAME}</a></strong> depuis le site <a href="{HTTP_HOST}">{HTTP_HOST}</a> :
<div id="mailcontent">
    {MAIL_CONTENT}
    <!-- BEGIN sw_linkedobject -->
    <div id="linkedobject">
        <span><strong>Objet lié</strong>: </span><a href="{OBJECT_URL}">{MODULE_LABEL} / {OBJECT_TYPE} <b>"{OBJECT_LABEL}"</b></a>
    </div>
    <!-- END sw_linkedobject -->
</div>
</body>
</html>
