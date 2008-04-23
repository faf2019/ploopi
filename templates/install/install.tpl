<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<!-- Réalisation: Ovensia 2007 // Template pour PLOOPI -->
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
	<meta http-equiv="content-style-type" content="text/css" />
	<meta http-equiv="content-language" content="fr" />
	
	<title>{WORKSPACE_TITLE} - {PAGE_TITLE}</title>
	
	<script type="text/javascript" src="../js/functions.pack.js"></script>
	<script type="text/javascript"><!--
	    //
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
	
	    {ADDITIONAL_JAVASCRIPT_CTRL}
	
	    function formSubmit(idForm,testOnsubmit,idName,stage,nb)
	    {
	      var myForm = document.getElementById(idForm);
	      var myStage = document.getElementById(idName);
	
	      stage = stage+nb;
	      if(stage<0) stage=0;
	
	      if(testOnsubmit==true)
	      {
	        if(myForm.onsubmit())
	        {
	          myStage.value = stage;
	          myForm.submit();
	        }
	      }
	      else
	      {
	        myStage.value = stage;
	        myForm.submit();
	      }
	    }
	
	    function duplicSelectToField(nameSelectFrom,idFieldTo)
	    {
	      var fieldTo = document.getElementById(idFieldTo);
	      if(nameSelectFrom.selectedIndex>0)
	      {
	         fieldTo.value = nameSelectFrom[nameSelectFrom.selectedIndex].value;
	      }
	      else
	      {
	        fieldTo.value = '';
	        fieldTo.focus();
	      }
	    }
	
	    function changeHideView(idForm)
	    {
	        var myForm = document.getElementById(idForm);
	
	        if(myForm.style.display == "none" || myForm.style.display == "")
	        {
	          myForm.style.display = "block";
	        }
	        else
	        {
	          myForm.style.display = "none";
	        }
	    }
	
	    //
	    --></script>
	
	<style>
	#page {display:none;}
	#error {
	    display:block;
	    font-family: Tahoma, Helvetica, Verdana, Arial, sans-serif;
	    font-size: 11px;
	    width:600px;
	    margin:20px auto 0 auto;
	    padding:10px;
	    border:2px solid #a60000;
	    color:#000;
	    background:#f0f0f0;
	}
	#error pre {
	    border:1px dotted #c0c0c0;
	    padding:2px 4px;
	    background:#fff;
	}
	</style>
	
	<link type="text/css" rel="stylesheet" href="{TEMPLATE_PATH}/css/styles.css" media="screen" />
	
	<!--[if lte IE 7]>
	    <link type="text/css" rel="stylesheet" href="{TEMPLATE_PATH}/css/styles_ie.css" media="screen" />
    <![endif]-->
	<!--[if lte IE 6]>
	    <link type="text/css" rel="stylesheet" href="{TEMPLATE_PATH}/css/png.css" media="screen" />
    <![endif]-->
	
</head>
<body>
    <div id="page">
      <form id="form_install" method="post" action="" enctype="multipart/form-data" onsubmit="{ADD_VALIDATEFIELD}">
      <input type="hidden" id="stage" name="stage" value="{STAGE}" />
      <!-- div id="entete"></div-->
      <div id="inner">
        <div id="menu">
          <!-- BEGIN menu -->
            <div class="{menu.CLASS}">{menu.LEVEL}&nbsp;.&nbsp;{menu.NAME}</div>
          <!-- END menu -->
        </div>
        <div id="pages">{PAGE_TITLE}<br /><br />
          <!-- BEGIN stage1 -->
            {stage1.TEXT}<br /><br />
            {stage1.CHOOSE_LANGUAGE} : <select name="language" onchange="javascript:formSubmit('form_install',true,'stage',{STAGE},0);">
            <!-- BEGIN languages -->
              <option value="{stage1.languages.LANGUAGE}" {stage1.languages.SELECTED}>{stage1.languages.LANGUAGE}</option>
            <!-- END languages -->
            </select>
          <!-- END stage1 -->
          <!-- BEGIN stage2 -->
          <!-- END stage2 -->
          <!-- BEGIN stage3 -->
          <!-- END stage3 -->
          <!-- BEGIN stage4 -->
          {stage4.TEXT}
          <!-- END stage4 -->
          <!-- INFORMATIONS -->
          <div class="infos">
            <noscript>
              <div class="info_block">
                <input type="hidden" id="nojs" name="nojs" value="nojs" />
                <div class="info_error">
                  <div class="info_icon"><img src="{TEMPLATE_PATH}{ICON_ERROR}" /></div>
                  <div class="info_mess">{JS_MESS}</div>
                </div>
                <div class="info_warning">{JS_ERROR}</div>
              </div>
            </noscript>
            <!-- BEGIN infos -->
              <div class="info_block">
                <div id="{infos.ID}">
                  <div class="{infos.CLASS_TITLE}">
                    <!-- BEGIN url_info -->
                      <div class="info_url"><a href="{infos.url_info.URL}" target="_blank"><img src="{TEMPLATE_PATH}{infos.url_info.URL_ICON}" /></a></div>
                    <!-- END url_info -->
                    <!-- BEGIN state_icon -->
                      <div class="info_icon"><img src="{TEMPLATE_PATH}{infos.state_icon.ICON}" /></div>
                    <!-- END state_icon -->
                    <div class="info_title">{infos.TITLE}</div>
                  </div>
                  <div class="{infos.CLASS_MESS}">{infos.MESS}</div>
                  <div class="{infos.CLASS_WARNING}">{infos.WARNING}</div>
                </div>
              </div>
              <div id="{infos.ID_FORM}" class="{infos.CLASS_FORM}">
              <!-- BEGIN form_install -->
                <div class="form_install"><div class="{infos.form_install.CLASS_LABEL}">{infos.form_install.LABEL}</div><div class="{infos.form_install.CLASS_FIELD}">{infos.form_install.FIELD}</div></div>
              <!-- END form_install -->
              </div>
            <!-- END infos -->
          </div>
          <div class="mess_field_must">{ADD_MESS_FIELD_MUST}</div>
          <div class="buttons">
            <!-- BEGIN next_button -->
              <div class="next_button"><input type="button" tabindex="{next_button.TABINDEX}" class="button" {next_button.NEXT_BUTTON_DISABLE} value="{next_button.NEXT_BUTTON}" onclick="javascript:formSubmit('form_install',true,'stage',{STAGE},1);" /></div>
            <!-- END next_button -->
            <!-- BEGIN prec_button -->
              <div class="prec_button"><input type="button" tabindex="{prec_button.TABINDEX}" class="button" {prec_button.NEXT_BUTTON_DISABLE} value="{prec_button.PREC_BUTTON}" onclick="javascript:formSubmit('form_install',false,'stage',{STAGE},-1);"  /></div>
            <!-- END prec_button -->
            <!-- BEGIN refresh_button -->
              <div class="refresh_button"><input type="button" tabindex="{refresh_button.TABINDEX}" class="button" {refresh_button.NEXT_BUTTON_DISABLE} value="{refresh_button.REFRESH_BUTTON}" onclick="javascript:formSubmit('form_install',true,'stage',{STAGE},0);" /></div>
            <!-- END refresh_button -->
          </div>
        </div>
      </div>
      <!-- BEGIN debug -->
        {debug.INFO}
      <!-- END debug -->
      </form>
    </div>
    <div id="error">
        La feuille de style n'a pas pu être chargée correctement.
        <br />Vous devriez vérifier le paramétrage de la directive <em>RewriteBase</em> pour qu'elle pointe sur la racine de votre site.
        <br /><br />Par exemple, si votre URL d'accès à Ploopi est de la forme http://mondomaine/ploopi/ , vous devez paramétrer <em>RewriteBase</em> de la manière suivante :
        <br />
        <pre>RewriteBase /</pre>
    </div>
</body>
</html>
