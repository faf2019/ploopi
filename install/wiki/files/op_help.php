<?php
/*
 Copyright (c) 2007-2016 Ovensia
 Contributors hold Copyright (c) to their code submissions.

 This file is part of Ploopi.

 Ploopi is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Ploopi is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Ploopi; if not, write to the Free Software
 Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

/**
 * Aide en ligne
 *
 * @package wiki
 * @subpackage op
 * @copyright Ovensia
 * @author Ovensia
 * @version  $Revision$
 * @modifiedby $LastChangedBy$
 * @lastmodified $Date$
 */
?>
<div id="wiki_help">
<table width="100%">
	<tr>
		<th colspan="3">Styles de texte</th>
	</tr>

	<tr>
		<th><img src="./lib/jstoolbar/images/bt_strong.png"
			style="border: 1px solid #bbb;" alt="Strong" /></th>
		<td width="50%">*Strong*</td>
		<td width="50%"><strong>Strong</strong></td>
	</tr>
	<tr>
		<th><img src="./lib/jstoolbar/images/bt_em.png"
			style="border: 1px solid #bbb;" alt="Italic" /></th>
		<td>_Italic_</td>
		<td><em>Italic</em></td>
	</tr>
	<tr>
		<th><img src="./lib/jstoolbar/images/bt_ins.png"
			style="border: 1px solid #bbb;" alt="Underline" /></th>
		<td>+Underline+</td>
		<td><ins>Underline</ins></td>
	</tr>
	<tr>
		<th><img src="./lib/jstoolbar/images/bt_del.png"
			style="border: 1px solid #bbb;" alt="Deleted" /></th>
		<td>-Deleted-</td>
		<td><del>Deleted</del></td>
	</tr>
	<tr>
		<th></th>
		<td>??Quote??</td>
		<td><cite>Quote</cite></td>
	</tr>
	<tr>
		<th><img src="./lib/jstoolbar/images/bt_code.png"
			style="border: 1px solid #bbb;" alt="Inline Code" /></th>
		<td>@Inline Code@</td>
		<td><code>Inline Code</code></td>
	</tr>

	<tr>
		<th><img src="./lib/jstoolbar/images/bt_pre.png"
			style="border: 1px solid #bbb;" alt="Preformatted text" /></th>
		<td>&lt;pre><br />
		&nbsp;lines<br />
		&nbsp;of code<br />
		&lt;/pre></td>
		<td><pre>
     lines
     of code
    </pre></td>
	</tr>

	<tr>
		<th colspan="3">Listes</th>
	</tr>
	<tr>
		<th><img src="./lib/jstoolbar/images/bt_ul.png"
			style="border: 1px solid #bbb;" alt="Unordered list" /></th>
		<td>* Item 1<br />
		* Item 2</td>
		<td>
		<ul>
			<li>Item 1</li>
			<li>Item 2</li>
		</ul>
		</td>
	</tr>

	<tr>
		<th><img src="./lib/jstoolbar/images/bt_ol.png"
			style="border: 1px solid #bbb;" alt="Ordered list" /></th>
		<td># Item 1<br />
		# Item 2</td>
		<td>
		<ol>
			<li>Item 1</li>
			<li>Item 2</li>
		</ol>
		</td>
	</tr>

	<tr>
		<th colspan="3">Titres</th>
	</tr>
	<tr>
		<th><img src="./lib/jstoolbar/images/bt_h1.png"
			style="border: 1px solid #bbb;" alt="Heading 1" /></th>
		<td>h1. Title 1</td>
		<td>
		<h1>Title 1</h1>
		</td>
	</tr>
	<tr>
		<th><img src="./lib/jstoolbar/images/bt_h2.png"
			style="border: 1px solid #bbb;" alt="Heading 2" /></th>
		<td>h2. Title 2</td>
		<td>
		<h2>Title 2</h2>
		</td>
	</tr>
	<tr>
		<th><img src="./lib/jstoolbar/images/bt_h3.png"
			style="border: 1px solid #bbb;" alt="Heading 3" /></th>
		<td>h3. Title 3</td>
		<td>
		<h3>Title 3</h3>
		</td>
	</tr>

	<tr>
		<th colspan="3">Liens</th>
	</tr>
	<tr>
		<th></th>
		<td>http://foo.bar</td>
		<td><a href="#">http://foo.bar</a></td>
	</tr>
	<tr>
		<th></th>
		<td>"Foo":http://foo.bar</td>
		<td><a href="#">Foo</a></td>
	</tr>

	<tr>
		<th colspan="3">Liens Wiki</th>
	</tr>
	<tr>
		<th><img src="./lib/jstoolbar/images/bt_link.png"
			style="border: 1px solid #bbb;" alt="Link to a Wiki page" /></th>
		<td>[[Wiki page]]</td>
		<td><a href="#">Wiki page</a></td>
	</tr>

	<tr>
		<th colspan="3">Images</th>
	</tr>
	<tr>
		<th><img src="./lib/jstoolbar/images/bt_img.png"
			style="border: 1px solid #bbb;" alt="Image" /></th>
		<td>!<em>image_url</em>!</td>
		<td></td>
	</tr>

	<tr>
		<th></th>
		<td>!<em>attached_image</em>!</td>
		<td></td>
	</tr>
    <tr>
        <th colspan="3">Pour aller plus loin...</th>
    </tr>
    <tr>
        <td colspan="3"><a href="http://redcloth.org/hobix.com/textile/" target=_"new"><strong>Guide complet</strong> : Syntaxe avancée</a></td>
    </tr>
    <tr>
        <td colspan="3"><a href="http://redcloth.org/hobix.com/textile/quick.html" target=_"new"><strong>Guide Complet</strong> : Version simplifiée</a></td>
    </tr>
    <tr>
        <td colspan="3"><a href="http://fr.wikipedia.org/wiki/Textile_(langage)" target=_"new"><strong>Wikipedia</strong> : Présentation de Textile</a></td>
    </tr>
    
</table>
</div>
