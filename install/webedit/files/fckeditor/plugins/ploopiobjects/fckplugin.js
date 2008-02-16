/*
 * FCKeditor - The text editor for internet
 * Copyright (C) 2003-2006 Frederico Caldeira Knabben
 *
 * Licensed under the terms of the GNU Lesser General Public License:
 *      http://www.opensource.org/licenses/lgpl-license.php
 *
 * For further information visit:
 *      http://www.fckeditor.net/
 *
 * "Support Open Source software. What about a donation today?"
 *
 * File Name: fckplugin.js
 *  This is the sample plugin definition file.
 *
 * File Authors:
 *      Netlor
 */

// Register the related commands.
FCKCommands.RegisterCommand( 'PloopiObjects'    , new FCKDialogCommand( FCKLang['DlgPloopiObjectsTitle']    , FCKLang['DlgPloopiObjectsTitle']      , FCKConfig.BaseHref + 'admin-light.php?ploopi_op=webedit_getploopiobjects' , 340, 170 ) ) ;
// Create the "Find" toolbar button.
var oPloopiObjects      = new FCKToolbarButton( 'PloopiObjects', FCKLang['DlgPloopiObjectsTitle'] ) ;
oPloopiObjects.IconPath = FCKConfig.PluginsPath + 'ploopiobjects/ploopiobjects.gif' ;

FCKToolbarItems.RegisterItem( 'PloopiObjects', oPloopiObjects ) ;           // 'PloopiObjects' is the name used in the Toolbar config.



// The object used for all Placeholder operations.
var FCKPlaceholders = new Object() ;

// Add a new placeholder at the actual selection.
FCKPlaceholders.Add = function( name )
{
    var oSpan = FCK.CreateElement( 'SPAN' ) ;
    this.SetupSpan( oSpan, name ) ;
}

FCKPlaceholders.SetupSpan = function( span, name )
{
    span.innerHTML = '[[ ' + name + ' ]]' ;

    span.style.backgroundColor = '#ffff00' ;
    span.style.color = '#000000' ;

    if ( FCKBrowserInfo.IsGecko )
        span.style.cursor = 'default' ;

    span._fckplaceholder = name ;
    span.contentEditable = false ;

    // To avoid it to be resized.
    span.onresizestart = function()
    {
        FCK.EditorWindow.event.returnValue = false ;
        return false ;
    }
}

// On Gecko we must do this trick so the user select all the SPAN when clicking on it.
FCKPlaceholders._SetupClickListener = function()
{
    FCKPlaceholders._ClickListener = function( e )
    {
        if ( e.target.tagName == 'SPAN' && e.target._fckplaceholder )
            FCKSelection.SelectNode( e.target ) ;
    }

    FCK.EditorDocument.addEventListener( 'click', FCKPlaceholders._ClickListener, true ) ;
}

// Open the Placeholder dialog on double click.
FCKPlaceholders.OnDoubleClick = function( span )
{
    if ( span.tagName == 'SPAN' && span._fckplaceholder )
        FCKCommands.GetCommand( 'PloopiObjects' ).Execute() ;
}

FCK.RegisterDoubleClickHandler( FCKPlaceholders.OnDoubleClick, 'SPAN' ) ;

// Check if a Placholder name is already in use.
FCKPlaceholders.Exist = function( name )
{
    var aSpans = FCK.EditorDocument.getElementsByTagName( 'SPAN' )

    for ( var i = 0 ; i < aSpans.length ; i++ )
    {
        if ( aSpans[i]._fckplaceholder == name )
            return true ;
    }
}

if ( FCKBrowserInfo.IsIE )
{
    FCKPlaceholders.Redraw = function()
    {
        var aPlaholders = FCK.EditorDocument.body.innerText.match( /\[\[[^\[\]]+\]\]/g ) ;
        if ( !aPlaholders )
            return ;

        var oRange = FCK.EditorDocument.body.createTextRange() ;

        for ( var i = 0 ; i < aPlaholders.length ; i++ )
        {
            if ( oRange.findText( aPlaholders[i] ) )
            {
                var sName = aPlaholders[i].match( /\[\[\s*([^\]]*?)\s*\]\]/ )[1] ;
                oRange.pasteHTML( '<span style="color: #000000; background-color: #ffff00" contenteditable="false" _fckplaceholder="' + sName + '">' + aPlaholders[i] + '</span>' ) ;
            }
        }
    }
}
else
{
    FCKPlaceholders.Redraw = function()
    {
        var oInteractor = FCK.EditorDocument.createTreeWalker( FCK.EditorDocument.body, NodeFilter.SHOW_TEXT, FCKPlaceholders._AcceptNode, true ) ;

        var aNodes = new Array() ;

        while ( oNode = oInteractor.nextNode() )
        {
            aNodes[ aNodes.length ] = oNode ;
        }

        for ( var n = 0 ; n < aNodes.length ; n++ )
        {
            var aPieces = aNodes[n].nodeValue.split( /(\[\[[^\[\]]+\]\])/g ) ;

            for ( var i = 0 ; i < aPieces.length ; i++ )
            {
                if ( aPieces[i].length > 0 )
                {
                    if ( aPieces[i].indexOf( '[[' ) == 0 )
                    {
                        var sName = aPieces[i].match( /\[\[\s*([^\]]*?)\s*\]\]/ )[1] ;

                        var oSpan = FCK.EditorDocument.createElement( 'span' ) ;
                        FCKPlaceholders.SetupSpan( oSpan, sName ) ;

                        aNodes[n].parentNode.insertBefore( oSpan, aNodes[n] ) ;
                    }
                    else
                        aNodes[n].parentNode.insertBefore( FCK.EditorDocument.createTextNode( aPieces[i] ) , aNodes[n] ) ;
                }
            }

            aNodes[n].parentNode.removeChild( aNodes[n] ) ;
        }

        FCKPlaceholders._SetupClickListener() ;
    }

    FCKPlaceholders._AcceptNode = function( node )
    {
        if ( /\[\[[^\[\]]+\]\]/.test( node.nodeValue ) )
            return NodeFilter.FILTER_ACCEPT ;
        else
            return NodeFilter.FILTER_SKIP ;
    }
}

FCK.Events.AttachEvent( 'OnAfterSetHTML', FCKPlaceholders.Redraw ) ;

// We must process the SPAN tags to replace then with the real resulting value of the placeholder.
FCKXHtml.TagProcessors['span'] = function( node, htmlNode )
{
    if ( htmlNode._fckplaceholder )
        node = FCKXHtml.XML.createTextNode( '[[' + htmlNode._fckplaceholder + ']]' ) ;
    else
        FCKXHtml._AppendChildNodes( node, htmlNode, false ) ;

    return node ;
}
