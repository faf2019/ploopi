<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<!--
 * FCKeditor - The text editor for internet
 * Copyright (C) 2003-2006 Frederico Caldeira Knabben
 * 
 * Licensed under the terms of the GNU Lesser General Public License:
 * 		http://www.opensource.org/licenses/lgpl-license.php
 * 
 * For further information visit:
 * 		http://www.fckeditor.net/
 * 
 * "Support Open Source software. What about a donation today?"
 * 
 * File Name: find.html
 * 	This is the sample "Find" plugin window.
 * 
 * File Authors:
 * 		Frederico Caldeira Knabben (fredck@fckeditor.net)
-->
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<meta content="noindex, nofollow" name="robots">
		<script type="text/javascript">

var oEditor = window.parent.InnerDialogLoaded() ;

function OnLoad()
{
	// Whole word is available on IE only.
	if ( oEditor.FCKBrowserInfo.IsIE )
		document.getElementById('divWord').style.display = '' ;
	
	// First of all, translate the dialog box texts.
	oEditor.FCKLanguageManager.TranslatePage( document ) ;

	window.parent.SetAutoSize( true ) ;
}

function btnStat(frm)
{
	document.getElementById('btnFind').disabled = 
		( document.getElementById('txtFind').value.length == 0 ) ;
}

function ReplaceTextNodes( parentNode, regex, replaceValue, replaceAll )
{
	for ( var i = 0 ; i < parentNode.childNodes.length ; i++ )
	{
		var oNode = parentNode.childNodes[i] ;
		if ( oNode.nodeType == 3 )
		{
			var sReplaced = oNode.nodeValue.replace( regex, replaceValue ) ;
			if ( oNode.nodeValue != sReplaced )
			{
				oNode.nodeValue = sReplaced ;
				if ( ! replaceAll )
					return true ;
			}
		}
		else
		{
			if ( ReplaceTextNodes( oNode, regex, replaceValue ) )
				return true ;
		}
	}
	return false ;
}

function GetRegexExpr()
{
	if ( document.getElementById('chkWord').checked )
		var sExpr = '\\b' + document.getElementById('txtFind').value + '\\b' ;
	else
		var sExpr = document.getElementById('txtFind').value ;
		
	return sExpr ;
}

function GetCase() 
{
	return ( document.getElementById('chkCase').checked ? '' : 'i' ) ;
}

function Ok()
{
	if ( document.getElementById('txtFind').value.length == 0 )
		return ;
	
	if ( oEditor.FCKBrowserInfo.IsIE )
		FindIE() ;
	else
		FindGecko() ;
}

var oRange = null ;

function FindIE()
{
	if ( oRange == null )
		oRange = oEditor.FCK.EditorDocument.body.createTextRange() ;

	var iFlags = 0 ;
	
	if ( chkCase.checked )
		iFlags = iFlags | 4 ;
	
	if ( chkWord.checked )
		iFlags = iFlags | 2 ;
	
	var bFound = oRange.findText( document.getElementById('txtFind').value, 1, iFlags ) ;
	
	if ( bFound )
	{
		oRange.scrollIntoView() ;
		oRange.select() ;
		oRange.collapse(false) ;
		oLastRangeFound = oRange ;
	}
	else
	{
		oRange = null ;
		alert( oEditor.FCKLang.DlgFindNotFoundMsg ) ;
	}
}

function FindGecko()
{
	var bCase = document.getElementById('chkCase').checked ;
	var bWord = document.getElementById('chkWord').checked ;
	
	// window.find( searchString, caseSensitive, backwards, wrapAround, wholeWord, searchInFrames, showDialog ) ;
	oEditor.FCK.EditorWindow.find( document.getElementById('txtFind').value, bCase, false, false, bWord, false, false ) ;
	
}
		</script>
	</head>
	<body onload="OnLoad()" scroll="no" style="OVERFLOW: hidden">
		<div align="center">
			Choisissez une objet PLOOPI à insérer dans la page
		</div>
		<table cellSpacing="3" cellPadding="2" width="100%" border="0">
			<tr>
				<td nowrap>
					<label for="ploopi_objects">Objets:</label>&nbsp;
				</td>
				<td width="100%">
					<select id="ploopi_objects" style="WIDTH: 100%" tabIndex="1" >
						<option>xxx</option>
					</select>
				</td>
				<td>
					<input id="btnFind" style="WIDTH: 100%; PADDING-RIGHT: 5px; PADDING-LEFT: 5px" disabled
						onclick="Ok();" type="button" value="Find" fckLang="DlgMyFindFindBtn">
				</td>
			</tr>
		</table>
	</body>
</html>
