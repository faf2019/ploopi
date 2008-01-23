<?php
/*
	Copyright (c) 2002-2007 Netlor
	Copyright (c) 2007-2008 Ovensia
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

define ('_RSS_ACTION_FEEDADD', 			1);
define ('_RSS_ACTION_FEEDMODIFY', 		2);
define ('_RSS_ACTION_FEEDDELETE', 		3);
define ('_RSS_ACTION_FEEDPUBLISH',		4);
define ('_RSS_ACTION_CATADD', 			5);
define ('_RSS_ACTION_CATMODIFY', 		6);
define ('_RSS_ACTION_CATDELETE', 		7);

define ('_RSS_OBJECT_NEWS_FEED', 		1);
define ('_RSS_OBJECT_NEWS_ENTRY', 		2);

global $rss_revisit_values;

$rss_revisit_values = array (	900		=>	'15mn',
								1800	=>	'30mn',
								3600	=>	'1h',
								7200	=>	'2h',
								14400	=>	'4h',
								21600	=>	'6h',
								43200	=>	'12h',
								86400	=>	'24h',
							);

?>
