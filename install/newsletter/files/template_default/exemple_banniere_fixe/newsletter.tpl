<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">

<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-15" />
<title>{TITLE}</title>
<style type="text/css">
body{
  width: 580px;
  margin: 0 auto;
  border: none;
  padding: 0;
  background-color: {BACKGROUND_COLOR};
  color: {TEXT_COLOR};
}

img{
  border: none;
  padding: 0;
  margin: 0;
}

a {
  border-width : 0;
  text-decoration: none;
  padding: 0;
  margin: 0;
}

a:visited {
  color: #666666;
}

a:hover {
  color: #000099;
}

div.top{
  clear: both;
  width: 580px;
  margin: 0;
  padding: 0;
  text-align: center;
  color: black;
}

div.banniere{
  clear: both;
  width: 580px;
  margin: 0;
  padding: 0;
}

div.content{
  clear: both;
  margin: 0;
  padding: 0;
  border-left: 1px solid black; 
  border-right: 1px solid black; 
  border-bottom: 1px solid black;
  background-color: {CONTENT_COLOR};
}

div.style1{
  clear: both; 
  margin: 0;
  padding: 2px 10px;
  text-align: right;
  font-size: 0.8em;
  background-color: #dddddd;  
}

div.style2{
  clear: both;
  margin: 0; 
  padding: 0 2px 5px 2px; 
}

div.bottom{
  clear: both;
  width: 580px;
  margin: 0;
  padding: 0;
  text-align: center;
  color: black;
}
</style>
</head>
<body>
  <div style="width: 580px; margin: 0; padding: 0;">
    <div class="top">
      <font face="Arial, Helvetica, sans-serif" size="1">Si le message ne s'affiche pas correctement, <a href="{LINK}">cliquez ici</a>.</font>
    </div>
    <div class="banniere"><a href="{HOST}"><img src="{HOST}modules/newsletter/template_default/exemple_banniere_fixe/img/banniere.jpg" /></a></div>
    <div class="content">
	    <div class="style1">{DATE_DAYTEXT}&nbsp;{DATE_DAY}&nbsp;{DATE_MONTHTEXT}&nbsp;{DATE_YEAR}</div>
	    <div class="style2">{PAGE_CONTENT}</div>
	  </div>
    <div class="bottom">
      <font face="Arial, Helvetica, sans-serif" size="1">Conform&eacute;ment &agrave; l'article 40 du 6 janvier 1978 de la loi Informatique et Libert&eacute; modifi&eacute;e, <br />
      vous disposez d'un droit d'acc&egrave;s, de modification, de rectification 
      et de suppression des donn&eacute;es vous concernant. <br />
      Si vous souhaitez vous désabonner, <a href="{LINK_UNSUBSCRIB}">cliquez ici</a>.</font>
    </div>
  </div>
</body>
</html>     

