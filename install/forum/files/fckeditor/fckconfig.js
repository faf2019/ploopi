/*
 * FCKeditor - The text editor for Internet - http://www.fckeditor.net
 * Copyright (C) 2003-2007 Frederico Caldeira Knabben
 *
 * == BEGIN LICENSE ==
 *
 * Licensed under the terms of any of the following licenses at your
 * choice:
 *
 *  - GNU General Public License Version 2 or later (the "GPL")
 *    http://www.gnu.org/licenses/gpl.html
 *
 *  - GNU Lesser General Public License Version 2.1 or later (the "LGPL")
 *    http://www.gnu.org/licenses/lgpl.html
 *
 *  - Mozilla Public License Version 1.1 or later (the "MPL")
 *    http://www.mozilla.org/MPL/MPL-1.1.html
 *
 * == END LICENSE ==
 *
 * Editor configuration settings.
 *
 * Follow this link for more information:
 * http://wiki.fckeditor.net/Developer%27s_Guide/Configuration/Configurations_Settings
 */

FCKConfig.ToolbarSets["Default"] = [
    /*['Source'],*/
    ['Cut','Copy','Paste'],
    ['Undo','Redo'],
    ['JustifyLeft','JustifyCenter','JustifyRight','JustifyFull'],
    ['OrderedList','UnorderedList','-','Outdent','Indent'],
    ['Link','Unlink'],
    '/',
    ['Bold','Italic','Underline','StrikeThrough','-','Subscript','Superscript'],
    ['TextColor','BGColor'],
    ['FontSize'],
    ['Image','Smiley']
] ;

/*
FCKConfig.EnterMode = 'br' ;            // p | div | br
FCKConfig.ShiftEnterMode = 'div' ;    // p | div | br
*/

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
