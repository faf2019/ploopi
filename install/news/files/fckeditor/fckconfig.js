FCKConfig.ToolbarSets["Default"] = [
    ['Source','DocProps'],
    ['Cut','Copy','Paste','PasteText','PasteWord','-','Print','SpellCheck'],
    ['Undo','Redo','-','Find','Replace','-','SelectAll','RemoveFormat'],
    '/',
    ['Bold','Italic','Underline','StrikeThrough','-','Subscript','Superscript'],
    ['OrderedList','UnorderedList','-','Outdent','Indent'],
    ['JustifyLeft','JustifyCenter','JustifyRight','JustifyFull'],
    ['Link','Unlink','Anchor'],
    ['Image','Flash','Table','Rule','Smiley','SpecialChar','PageBreak'],
    '/',
    ['FontFormat','FontSize'],
    ['TextColor','BGColor'],
] ;

FCKConfig.EnterMode = 'br' ;            // p | div | br
FCKConfig.ShiftEnterMode = 'p' ;    // p | div | br

FCKConfig.LinkArticle = true;

FCKConfig.LinkBrowser = true ;
FCKConfig.LinkBrowserURL = FCKConfig.BaseHref + '/admin-light.php?ploopi_op=doc_selectfile';
FCKConfig.LinkBrowserWindowWidth  = 600 ;   // 70% ;
FCKConfig.LinkBrowserWindowHeight = 350 ;   // 70% ;

FCKConfig.ImageBrowser = true ;
FCKConfig.ImageBrowserURL = FCKConfig.BaseHref + '/admin-light.php?ploopi_op=doc_selectimage';
FCKConfig.ImageBrowserWindowWidth  = 600 ;  // 70% ;
FCKConfig.ImageBrowserWindowHeight = 350 ;  // 70% ;

FCKConfig.FlashBrowser = true ;
FCKConfig.FlashBrowserURL = FCKConfig.BaseHref + '/admin-light.php?ploopi_op=doc_selectflash';
FCKConfig.FlashBrowserWindowWidth  = 600 ;  // 70% ;
FCKConfig.FlashBrowserWindowHeight = 350 ;  // 70% ;