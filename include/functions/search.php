<?php
/*
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

include_once './include/classes/class_index_element.php';
include_once './include/classes/class_index_keyword.php';
include_once './include/classes/class_index_keyword_element.php';
include_once './include/classes/class_index_stem.php';
include_once './include/classes/class_index_stem_element.php';

/*
 * Ajout d'un mot clé ($keyword) pour un enregistrement ($id_record) d'un objet ($id_object)
 * $relevance : pertinence du mot
 * $weight : nb d'occurences du mot dans l'enregistrement
 * $ratio : ratio d'occurences du mot dans l'enregitrement
 * */
/*
 *
function ploopi_search_keyword_add($id_object, $id_record, $keyword, $relevance = 100, $weight = 0, $ratio = 0)
{
	global $db;

	// on determine le lemme
	$kw_stem = stem_french($keyword);
	// on supprime les accents
	$kw = ploopi_convertaccents($keyword);
	// on calcule le hash md5
	$kw_md5 = md5($kw);

	$db->query("SELECT id FROM ploopi_keyword WHERE id = '{$kw_md5}'");
	if (!$db->numrows())
	{
		$objKw = new keyword();
		$objKw->fields['id'] = $kw_md5;
		$objKw->fields['keyword'] = $kw;
		$objKw->fields['length'] = strlen($kw);
		$objKw->fields['stem'] = $kw_stem;
		$objKw->save();
	}

	$objKwObject = new keyword_object();
	$objKwObject->fields['id_keyword'] = $kw_md5;
	$objKwObject->fields['id_object'] = $id_object;
	$objKwObject->fields['id_record'] = $id_record;
	$objKwObject->fields['weight'] = $weight;
	$objKwObject->fields['ratio'] = $ratio;
	$objKwObject->fields['relevance'] = $relevance;
	$objKwObject->setuwm();
	$objKwObject->save();
}
*/

function ploopi_search_generate_id($id_module, $id_object, $id_record)
{
	return(md5(sprintf("%04d%04d%s", $id_module, $id_object, $id_record)));
}

/*
 * Supprime les mots clés associés à un enregistrement d'un objet
 * */

function ploopi_search_remove_index($id_object, $id_record, $id_module = -1)
{
	global $db;

	if ($id_module == -1 && !empty($_SESSION['ploopi']['moduleid'])) $id_module= $_SESSION['ploopi']['moduleid'];
	$id_element = ploopi_search_generate_id($id_module,$id_object,$id_record);

	$db->query("DELETE FROM ploopi_index_element WHERE id = '{$id_element}'");
	$db->query("DELETE FROM ploopi_index_keyword_element WHERE id_element = '{$id_element}'");
	$db->query("DELETE FROM ploopi_index_stem_element WHERE id_element = '{$id_element}'");
}


function ploopi_search_create_index($id_object, $id_record, $label, $content, $meta = '', $usecommonwords = true, $timestp_create = 0, $timestp_modify = 0, $id_user = -1, $id_workspace = -1, $id_module = -1, $debug = false)
{
	// TESTS :
	// http://ploopidev/admin-light.php?op=doc_fileindex&currentfolder=9&docfile_md5id=a9a7aba316abf25e65f01a320698baa3
	// http://ploopidev/admin-light.php?op=doc_fileindex&currentfolder=9&docfile_md5id=e2472480adc4de03ea45862b9e487930

	global $db;
	global $ploopi_timer;

	if ($id_user == -1 && !empty($_SESSION['ploopi']['userid'])) $id_user = $_SESSION['ploopi']['userid'];
	if ($id_workspace == -1 && !empty($_SESSION['ploopi']['workspaceid'])) $id_workspace = $_SESSION['ploopi']['workspaceid'];
	if ($id_module == -1 && !empty($_SESSION['ploopi']['moduleid'])) $id_module= $_SESSION['ploopi']['moduleid'];

	$id_element = ploopi_search_generate_id($id_module,$id_object,$id_record);

	$words = array();
	$words_indexed = $words_overall = 0;

	if ($usecommonwords && !isset($_SESSION['ploopi']['commonwords']) && file_exists(_PLOOPI_INDEXATION_COMMONWORDS_FR) )
	{
		$filecontent = '';
		$handle = @fopen(_PLOOPI_INDEXATION_COMMONWORDS_FR, 'r');
		if ($handle)
		{
			while (!feof($handle)) $filecontent .= fgets($handle);
			fclose($handle);
		}

		$_SESSION['ploopi']['commonwords'] = array_flip(split("[\n]", str_replace("\r",'',$filecontent)));
	}

	if (empty($_SESSION['ploopi']['commonwords'])) $_SESSION['ploopi']['commonwords'] = array();


	// TRAITEMENT DU CONTENT
	for ($kw = strtok($content, _PLOOPI_INDEXATION_WORDSEPARATORS); $kw !== false; $kw = strtok(_PLOOPI_INDEXATION_WORDSEPARATORS))
	{
		// mot en minuscule avec accents
		$kw = trim(mb_strtolower($kw),"\x0..\x20\xa0");

		// mot en minuscule sans accent et sans caractère parasite
		$kw_clean = preg_replace("/[^a-zA-Z0-9]/","",ploopi_convertaccents($kw));

		// on vérifie qu'il n'est pas dans la liste des mots à exclure
		if (!isset($_SESSION['ploopi']['commonwords'][$kw_clean]))
		{
			// on vérifie sa taille
			$len = strlen($kw);
			if ($len >= _PLOOPI_INDEXATION_WORDMINLENGHT && $len <= _PLOOPI_INDEXATION_WORDMAXLENGHT)
			{
				// détermination du lemme (racine)
				$kw_stem = stem_french($kw);

				if (!isset($words[$kw_stem])) $words[$kw_stem]['weight'] = 1;
				else $words[$kw_stem]['weight']++;

				if (!isset($words[$kw_stem]['words'][$kw_clean])) $words[$kw_stem]['words'][$kw_clean]['weight'] = 1;
				else $words[$kw_stem]['words'][$kw_clean]['weight']++;

				$words_indexed++;
			}
		}
		$words_overall++;
	}

	// tri des lemmes par poids
	arsort($words);

	$stem = current($words);
	$max_weight = $stem['weight'];

	// TRAITEMENT DES METAS
	for ($kw = strtok($meta, _PLOOPI_INDEXATION_WORDSEPARATORS); $kw !== false; $kw = strtok(_PLOOPI_INDEXATION_WORDSEPARATORS))
	{
		// mot en minuscule avec accents
		$kw = trim(mb_strtolower($kw),"\x0..\x20\xa0");

		// mot en minuscule sans accent et sans caractère parasite
		$kw_clean = preg_replace("/[^a-zA-Z0-9]/","",ploopi_convertaccents($kw));

		// on vérifie qu'il n'est pas dans la liste des mots à exclure
		if (!isset($_SESSION['ploopi']['commonwords'][$kw_clean]))
		{
			// on vérifie sa taille
			$len = strlen($kw);
			if ($len >= _PLOOPI_INDEXATION_WORDMINLENGHT && $len <= _PLOOPI_INDEXATION_WORDMAXLENGHT)
			{
				// détermination du lemme (racine)
				$kw_stem = stem_french($kw);

				$words[$kw_stem]['weight'] = _PLOOPI_INDEXATION_METAWEIGHT;
				$words[$kw_stem]['meta'] = 1;

				$words[$kw_stem]['words'][$kw_clean]['weight'] = _PLOOPI_INDEXATION_METAWEIGHT;
				$words[$kw_stem]['words'][$kw_clean]['meta'] = 1;
			}
		}
	}

	// tri des lemmes par poids
	arsort($words);

	if ($debug) printf("<br />GETWORDS: %0.2f",$ploopi_timer->getexectime()*1000);

	// nettoyage index
	ploopi_search_remove_index($id_object, $id_record);
	if ($debug) printf("<br />REMOVE INDEX: %0.2f",$ploopi_timer->getexectime()*1000);


	$stem = current($words);
	$stem_ratio = (empty($words_overall)) ? 0 : ($stem['weight']*100 / $words_overall);

	$max_stem = (_PLOOPI_INDEXATION_KEYWORDSMAXPCENT) ? (sizeof($words)*_PLOOPI_INDEXATION_KEYWORDSMAXPCENT)/100 : sizeof($words);

	$objElement = new index_element();
	$objElement->fields['id'] = $id_element;
	$objElement->fields['id_object'] = $id_object;
	$objElement->fields['id_record'] = $id_record;
	$objElement->fields['label'] = $label;
	$objElement->fields['id_user'] = $id_user;
	$objElement->fields['id_workspace'] = $id_workspace;
	$objElement->fields['id_module'] = $id_module;
	$objElement->fields['timestp_create'] = $timestp_create;
	$objElement->fields['timestp_modify'] = $timestp_modify;
	$objElement->fields['timestp_lastindex'] = ploopi_createtimestamp();
	$objElement->save();

	for ($i = 1; $i <= $max_stem && $stem_ratio >= _PLOOPI_INDEXATION_RATIOMIN; $i++)
	{
		// on calcule le hash md5
		$stem_value = key($words);
		$stem_md5 = md5($stem_value);

		// enregistrement de la racine si n'existe pas déjà
		$db->query("SELECT id FROM ploopi_index_stem WHERE id = '{$stem_md5}'");
		if (!$db->numrows())
		{
			$objStem = new index_stem();
			$objStem->fields['id'] = $stem_md5;
			$objStem->fields['stem'] = $stem_value;
			$objStem->save();
		}

		// enregistrement du lien racine <-> enregistrement
		$objStemElement = new index_stem_element();
		$objStemElement->fields['id_stem'] = $stem_md5;
		$objStemElement->fields['id_element'] = $id_element;
		$objStemElement->fields['weight'] = $stem['weight'];
		$objStemElement->fields['ratio'] = (empty($stem['meta'])) ? $stem_ratio : 1;
		$objStemElement->fields['relevance'] = (empty($stem['meta'])) ? ($stem['weight']*100)/$max_weight : 100;
		$objStemElement->save();


		foreach($stem['words'] as $kw_value => $kw)
		{
			$kw_weight = $kw['weight'];

			// on calcule le hash md5
			$kw_md5 = md5($kw_value);

			// enregistrement du mot clé si n'existe pas déjà
			$db->query("SELECT id FROM ploopi_index_keyword WHERE id = '{$kw_md5}'");
			if (!$db->numrows())
			{
				$objKw = new index_keyword();
				$objKw->fields['id'] = $kw_md5;
				$objKw->fields['keyword'] = $kw_value;
				$objKw->fields['id_stem'] = $stem_md5;
				$objKw->save();
			}

			// enregistrement du lien mot clé <-> enregistrement
			$objKwElement = new index_keyword_element();
			$objKwElement->fields['id_keyword'] = $kw_md5;
			$objKwElement->fields['id_element'] = $id_element;
			$objKwElement->fields['weight'] = $kw_weight;
			$objKwElement->fields['ratio'] = (empty($kw['meta'])) ? ((empty($words_overall)) ? 0 : ($kw_weight / $words_overall)*100) : 1;
			$objKwElement->fields['relevance'] = (empty($kw['meta'])) ? ($kw_weight*100)/$max_weight : 100;
			$objKwElement->save();

		}

		$stem = next($words);
		$stem_ratio = (empty($words_overall)) ? 0 : ($stem['weight'] / $words_overall)*100;
	}

	if ($debug) printf("<br />KEYWORDS: %0.2f",$ploopi_timer->getexectime()*1000);
}


function ploopi_search_get_index($id_object, $id_record, $limit = 100, $id_module = -1)
{
	global $db;

	$index = array();

	if (is_numeric($limit))
	{
		if ($id_module == -1 && !empty($_SESSION['ploopi']['moduleid'])) $id_module= $_SESSION['ploopi']['moduleid'];

		$id_element = ploopi_search_generate_id($id_module,$id_object,$id_record);

		$sql = 	"
				SELECT 		k.keyword, ke.weight, ke.ratio, s.stem, se.relevance

				FROM 		ploopi_index_keyword_element ke

				INNER JOIN	ploopi_index_keyword k
				ON 			ke.id_keyword = k.id

				INNER JOIN	ploopi_index_stem s
				ON 			s.id = k.id_stem

				INNER JOIN	ploopi_index_stem_element se
				ON 			se.id_stem = s.id
				AND			se.id_element = '{$id_element}'

				WHERE		ke.id_element = '{$id_element}'

				ORDER BY	se.relevance DESC, ke.weight DESC, k.keyword

				LIMIT 		0,{$limit}
			";

		$db->query($sql);
		$index = $db->getarray();


	}

	return($index);
}


function ploopi_search($id_object, $keywords, $id_record = '', $options = null, $id_module = -1)
{
	global $db;

	if (!is_numeric($id_object) || !is_numeric($id_module)) return(false);

	if ($id_module == -1 && !empty($_SESSION['ploopi']['moduleid'])) $id_module= $_SESSION['ploopi']['moduleid'];

	// on récupère la liste des racines contenues dans la liste des mots clés
	list($arrStems) = ploopi_getwords($keywords, true, true);

	// on récupère la liste des mots contenus dans la liste des mots clés
	list($arrKeywords) = ploopi_getwords($keywords, true, false);

	$arrSearch = array();
	$arrRelevance = array();

	if ($id_record != '') $arrSearch[] = "e.id_record LIKE '".$db->addslashes($id_record)."%'";

	$strSearch = (empty($arrSearch)) ? '' : ' AND '.implode(' AND ',$arrSearch);

	$orderby = (isset($options['orderby'])) ? $options['orderby'] : '';
	$sort = (isset($options['sort'])) ? $options['sort'] : 'DESC';

	// pour chaque racine (stem), on cherche les occurences d'éléments correspondants
	foreach($arrStems as $stem => $occ)
	{
		$sql = 	"
				SELECT		0 as sort_id,
							se.relevance,
							e.*

				FROM		ploopi_index_stem s,
							ploopi_index_stem_element se,
							ploopi_index_element e

				WHERE		e.id = se.id_element
				AND			s.id = se.id_stem
				AND			s.stem = '".$db->addslashes($stem)."'
				AND			e.id_object = {$id_object}
				AND			e.id_module = {$id_module}
				{$strSearch}
				";


		$db->query($sql);

		while ($row = $db->fetchrow())
		{
			if (!isset($arrRelevance[$row['id']]))
			{
				switch($orderby)
				{
					case 'timestp_create':
						$row['sort_id'] = $row['timestp_create'];
					break;

					default:
					case 'relevance':
						$row['sort_id'] = $row['relevance'];
					break;
				}

				$arrRelevance[$row['id']] = $row;
				$arrRelevance[$row['id']]['count'] = 1;
				$arrRelevance[$row['id']]['kw'] = array();
				$arrRelevance[$row['id']]['stem'] = array();
			}
			else
			{
				switch($orderby)
				{
					case 'timestp_create':
					break;

					default:
					case 'relevance':
						$row['sort_id'] += $row['relevance'];
					break;
				}

				$arrRelevance[$row['id']]['relevance'] += $row['relevance'];
				$arrRelevance[$row['id']]['count'] ++;
			}
			$arrRelevance[$row['id']]['stem'][$stem] = 1;
		}
	}

	// pour chaque mot, on cherche les occurences d'éléments correspondants
	foreach($arrKeywords as $kw => $occ)
	{
		$sql = 	"
				SELECT		0 as sort_id,
							ke.relevance,
							e.*,
							k.keyword

				FROM		ploopi_index_keyword k,
							ploopi_index_keyword_element ke,
							ploopi_index_element e

				WHERE		e.id = ke.id_element
				AND			k.id = ke.id_keyword
				AND			k.keyword like '".$db->addslashes($kw)."%'
				AND			e.id_object = {$id_object}
				AND			e.id_module = {$id_module}
				{$strSearch}
				";


		$db->query($sql);

		while ($row = $db->fetchrow())
		{
			// relevance = relevance * ratio de similarité entre les 2 chaines
			$row['relevance'] *= (strlen($kw)/strlen($row['keyword']));
			unset($row['keyword']);

			if (!isset($arrRelevance[$row['id']]))
			{
				switch($orderby)
				{
					case 'timestp_create':
						$row['sort_id'] = $row['timestp_create'];
					break;

					default:
					case 'relevance':
						$row['sort_id'] = $row['relevance'];
					break;
				}

				$arrRelevance[$row['id']] = $row;
				$arrRelevance[$row['id']]['count'] = 1;
				$arrRelevance[$row['id']]['kw'] = array();
				$arrRelevance[$row['id']]['stem'] = array();
			}
			else
			{
				switch($orderby)
				{
					case 'timestp_create':
					break;

					default:
					case 'relevance':
						$row['sort_id'] += $row['relevance'];
					break;
				}

				$arrRelevance[$row['id']]['relevance'] += $row['relevance'];
				$arrRelevance[$row['id']]['count'] ++;
			}

			$arrRelevance[$row['id']]['kw'][$kw] = 1;
		}
	}

	foreach($arrRelevance as $key => $element)
	{
		$arrRelevance[$key]['kw_ratio'] = (sizeof($arrRelevance[$key]['kw'])+sizeof($arrRelevance[$key]['stem'])) / (sizeof($arrKeywords)+sizeof($arrStems));
		$arrRelevance[$key]['relevance'] = ($arrRelevance[$key]['relevance']/$arrRelevance[$key]['count']) * $arrRelevance[$key]['kw_ratio'];
	}

	if ($sort == 'DESC') arsort($arrRelevance);
	else asort($arrRelevance);

	return($arrRelevance);
}




/*
 * Extrait les mots clés d'un texte
 * $usecommonwords : oui si on veut exclure la liste des mots communs
 * */

function ploopi_getwords($content, $usecommonwords = true, $getstem = false)
{
	$words = array();
	$words_indexed = $words_overall = 0;

	if ($usecommonwords && !isset($_SESSION['ploopi']['commonwords']) && file_exists(_PLOOPI_INDEXATION_COMMONWORDS_FR) )
	{
		$filecontent = '';
		$handle = @fopen(_PLOOPI_INDEXATION_COMMONWORDS_FR, 'r');
		if ($handle)
		{
			while (!feof($handle)) $filecontent .= fgets($handle);
			fclose($handle);
		}

		$_SESSION['ploopi']['commonwords'] = array_flip(split("[\n]", str_replace("\r",'',$filecontent)));
	}

	if (empty($_SESSION['ploopi']['commonwords'])) $_SESSION['ploopi']['commonwords'] = array();

	for ($kw = strtok($content, _PLOOPI_INDEXATION_WORDSEPARATORS); $kw !== false; $kw = strtok(_PLOOPI_INDEXATION_WORDSEPARATORS))
	{
		// remove empty characters
		$kw = trim(mb_strtolower($kw),"\x0..\x20\xa0");

		// only keep "normal" characters
		$kw_clean = preg_replace("/[^a-zA-Z0-9]/","",ploopi_convertaccents($kw));

		if (!isset($_SESSION['ploopi']['commonwords'][$kw_clean]))
		{
			$len = strlen($kw_clean);
			if ($len >= _PLOOPI_INDEXATION_WORDMINLENGHT && $len <= _PLOOPI_INDEXATION_WORDMAXLENGHT)
			{
				$kw = ($getstem) ? stem_french($kw) : $kw_clean;

				if (!isset($words[$kw])) $words[$kw] = 1;
				else $words[$kw]++;

				$words_indexed++;
			}
		}
		$words_overall++;
	}

	arsort($words);

	return(array($words, $words_indexed, $words_overall));
}
?>
