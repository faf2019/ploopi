FCKConfig.Plugins.Add( 'flvPlayer','en') ;

FCKConfig.ToolbarSets["Default"] = [
    ['Source','DocProps','-','Save','NewPage','Preview','-','Templates'],
    ['Cut','Copy','Paste','PasteText','PasteWord','-','Print','SpellCheck'],
    ['Undo','Redo','-','Find','Replace','-','SelectAll','RemoveFormat'],
    '/',
    ['Bold','Italic','Underline','StrikeThrough','-','Subscript','Superscript'],
    ['OrderedList','UnorderedList','-','Outdent','Indent','Blockquote'],
    ['JustifyLeft','JustifyCenter','JustifyRight','JustifyFull'],
    ['Link','Unlink','Anchor'],
    ['Image','Flash', 'flvPlayer', 'Table','Rule','Smiley','SpecialChar'],
    '/',
    ['Style','FontFormat','FontName','FontSize'],
    ['TextColor','BGColor'],
    ['ShowBlocks']      // No comma for the last row.
] ;

FCKConfig.ToolbarSets["Beginner"] = [
    ['Cut','Copy','Paste','PasteText','PasteWord'],
    ['Undo','Redo','-','SelectAll','RemoveFormat'],
    ['Style', 'Bold','Italic','Underline'],
    ['JustifyLeft','JustifyCenter','JustifyRight','JustifyFull'],
    ['OrderedList','UnorderedList','-','Outdent','Indent'],
    ['Link','Unlink','Image'],
] ;

FCKConfig.EnterMode = 'br' ;            // p | div | br
FCKConfig.ShiftEnterMode = 'p' ;    // p | div | br

FCKConfig.LinkArticle = true;

FCKConfig.LinkBrowser = true ;
FCKConfig.LinkBrowserURL = '../../../../admin-light.php?ploopi_op=doc_selectfile';
FCKConfig.LinkBrowserWindowWidth  = 600 ;   // 70% ;
FCKConfig.LinkBrowserWindowHeight = 350 ;   // 70% ;

FCKConfig.ImageBrowser = true ;
FCKConfig.ImageBrowserURL = '../../../../admin-light.php?ploopi_op=doc_selectimage';
FCKConfig.ImageBrowserWindowWidth  = 600 ;  // 70% ;
FCKConfig.ImageBrowserWindowHeight = 350 ;  // 70% ;

FCKConfig.FlashBrowser = true ;
FCKConfig.FlashBrowserURL = '../../../../admin-light.php?ploopi_op=doc_selectflash';
FCKConfig.FlashBrowserWindowWidth  = 600 ;  // 70% ;
FCKConfig.FlashBrowserWindowHeight = 350 ;  // 70% ;

FCKConfig.CustomStyles = { };

FCKConfig.AutoDetectLanguage    = false ;
FCKConfig.DefaultLanguage       = 'fr' ;