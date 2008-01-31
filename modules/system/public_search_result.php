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

if (isset($_GET['system_search_keywords'])) 	$_SESSION['ploopi'][_PLOOPI_MODULE_SYSTEM]['search_keywords'] = $_GET['system_search_keywords'];
if (isset($_GET['system_search_workspace'])) 	$_SESSION['ploopi'][_PLOOPI_MODULE_SYSTEM]['search_workspace'] = $_GET['system_search_workspace'];
if (isset($_GET['system_search_date1'])) 	$_SESSION['ploopi'][_PLOOPI_MODULE_SYSTEM]['search_date1'] = $_GET['system_search_date1'];
if (isset($_GET['system_search_date2'])) 	$_SESSION['ploopi'][_PLOOPI_MODULE_SYSTEM]['search_date2'] = $_GET['system_search_date2'];

if (!isset($_SESSION['ploopi'][_PLOOPI_MODULE_SYSTEM]['search_keywords'])) $_SESSION['ploopi'][_PLOOPI_MODULE_SYSTEM]['search_keywords'] = '';
if (!isset($_SESSION['ploopi'][_PLOOPI_MODULE_SYSTEM]['search_workspace'])) $_SESSION['ploopi'][_PLOOPI_MODULE_SYSTEM]['search_workspace'] = '';
if (!isset($_SESSION['ploopi'][_PLOOPI_MODULE_SYSTEM]['search_date1'])) $_SESSION['ploopi'][_PLOOPI_MODULE_SYSTEM]['search_date1'] = '';
if (!isset($_SESSION['ploopi'][_PLOOPI_MODULE_SYSTEM]['search_date2'])) $_SESSION['ploopi'][_PLOOPI_MODULE_SYSTEM]['search_date2'] = '';

if (!empty($_SESSION['ploopi'][_PLOOPI_MODULE_SYSTEM]['search_keywords']))
{
	/* Mini Algo
	 *
	 * 1. On extrait les racines (stem) et mots clés (keyword) de la requête de recherche
	 * 2. On effectue deux recherches. Une sur les racines, l'autre sur les mots clés.
	 * 3. On traite les résultats de recherche en indiquant pour chaque élément de réponse la pertinence (relevance) totale, le nombre de liens (mot clé  ou stem / élément)
	 * 4. On calcule ensuite le ratio mots clés trouvés / mots clés cherchés
	 * 5. On calcule ensuite la pertinence moyenne avec cette formule : ((pertinence totale)/(mots clés trouvés))*(ratio mots clés trouvés)
	 *
	 * */


	$arrObjectTypes = array();
	$arrRelevance = array();
	$arrStems = array();
	$arrSearch = array();


	// on construit $arrObjectTypes, la liste des objets ploopi
	$db->query(	'
				SELECT 		mbo.*, m.id as module_id, m.label as module_label
				FROM 		ploopi_mb_object mbo
				INNER JOIN	ploopi_module m
				ON			m.id_module_type = mbo.id_module_type
				');

	while ($row = $db->fetchrow())
	{
		if (empty($arrObjectTypes[$row['module_id']]))
		{
			$arrObjectTypes[$row['module_id']]['label'] = $row['module_label'];
			$arrObjectTypes[$row['module_id']]['objects'] = array();
		}
		$arrObjectTypes[$row['module_id']]['objects'][$row['id']] = array('label' => $row['label'], 'script' => $row['script']);
	}

	// contruction du filtre de recherche
	if (!empty($_SESSION['ploopi'][_PLOOPI_MODULE_SYSTEM]['search_workspace']) && is_numeric($_SESSION['ploopi'][_PLOOPI_MODULE_SYSTEM]['search_workspace'])) $arrSearch[] = "e.id_workspace = {$_SESSION['ploopi'][_PLOOPI_MODULE_SYSTEM]['search_workspace']}";

	$strSearch = (empty($arrSearch)) ? '' : ' AND '.implode(' AND ',$arrSearch);

	// on récupère la liste des racines contenues dans la liste des mots clés
	list($arrStems) = ploopi_getwords($_SESSION['ploopi'][_PLOOPI_MODULE_SYSTEM]['search_keywords'], true, true);

	// on récupère la liste des mots contenus dans la liste des mots clés
	list($arrKeywords) = ploopi_getwords($_SESSION['ploopi'][_PLOOPI_MODULE_SYSTEM]['search_keywords'], true, false);

	// pour chaque racine (stem), on cherche les occurences d'éléments correspondants
	foreach($arrStems as $stem => $occ)
	{
		$sql = 	"
				SELECT		se.relevance,
							e.*

				FROM		ploopi_index_stem s,
							ploopi_index_stem_element se,
							ploopi_index_element e

				WHERE		e.id = se.id_element
				AND			s.id = se.id_stem
				AND			s.stem = '".$db->addslashes($stem)."'
				{$strSearch}
				";


		$db->query($sql);

		while ($row = $db->fetchrow())
		{
			if (!isset($arrRelevance[$row['id']]))
			{
				$arrRelevance[$row['id']] = $row;
				$arrRelevance[$row['id']]['count'] = 1;
				$arrRelevance[$row['id']]['kw'] = array();
				$arrRelevance[$row['id']]['stem'] = array();
			}
			else
			{
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
				SELECT		ke.relevance,
							e.*,
							k.keyword

				FROM		ploopi_index_keyword k,
							ploopi_index_keyword_element ke,
							ploopi_index_element e

				WHERE		e.id = ke.id_element
				AND			k.id = ke.id_keyword
				AND			k.keyword like '".$db->addslashes($kw)."%'
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
				$arrRelevance[$row['id']] = $row;
				$arrRelevance[$row['id']]['count'] = 1;
				$arrRelevance[$row['id']]['kw'] = array();
				$arrRelevance[$row['id']]['stem'] = array();
			}
			else
			{
				$arrRelevance[$row['id']]['relevance'] += $row['relevance'];
				$arrRelevance[$row['id']]['count'] ++;
			}

			$arrRelevance[$row['id']]['kw'][$kw] = 1;
		}
	}

	// on trie les éléments par pertinence (relevance)
	arsort($arrRelevance);

	foreach($arrRelevance as $key => $element)
	{
		$arrRelevance[$key]['kw_ratio'] = (sizeof($arrRelevance[$key]['kw'])+sizeof($arrRelevance[$key]['stem'])) / (sizeof($arrKeywords)+sizeof($arrStems));
		$arrRelevance[$key]['relevance'] = ($arrRelevance[$key]['relevance']/$arrRelevance[$key]['count']) * $arrRelevance[$key]['kw_ratio'];
	}

	arsort($arrRelevance);

	if (empty($arrRelevance))
	{
		?>
		<div style="padding:4px;font-weight:bold;background-color:#f0f0f0;border-top:2px solid #c0c0c0;">
		Saisissez un mot clé puis cliquez sur "Rechercher" ou appuyez sur "Entrée"
		</div>
		<?
	}
	else
	{
		$columns = array();
		$values = array();
		$c = 0;

		$columns['left']['relevance'] 		= array('label' => 'Pertinence', 'width' => 100, 'options' => array('sort' => true));
		$columns['auto']['label'] 			= array('label' => 'Libellé', 'options' => array('sort' => true));
		$columns['right']['timestp'] 		= array('label' => 'Indexé le', 'width' => '90', 'options' => array('sort' => true));
		$columns['right']['workspace'] 		= array('label' => 'Espace', 'width' => '120', 'options' => array('sort' => true));
		$columns['right']['module'] 		= array('label' => 'Module', 'width' => '120', 'options' => array('sort' => true));
		$columns['right']['object_type'] 	= array('label' => 'Type d\'Objet', 'width' => '120', 'options' => array('sort' => true));

		// DISPLAY FILES
		foreach ($arrRelevance as $row)
		{
			if (!empty($arrObjectTypes[$row['id_module']]))
			{
				$l_timestp_lastindex = ploopi_timestamp2local($row['timestp_lastindex']);
				$l_timestp_create = ploopi_timestamp2local($row['timestp_create']);

				$object_script = str_replace(
												array(
													'<IDRECORD>',
													'<IDMODULE>',
													'<IDWORKSPACE>'
												),
												array(
													$row['id_record'],
													$row['id_module'],
													$row['id_workspace']
												),
												$arrObjectTypes[$row['id_module']]['objects'][$row['id_object']]['script']
											);

				$rel = $row['relevance'];

				$values[$c]['values']['relevance'] = array('label' => sprintf("%d %%", $rel), 'sort_label' => $rel);
				$values[$c]['values']['label'] = array('label' => $row['label']);
				$values[$c]['values']['timestp'] = array('label' => $l_timestp_lastindex['date'], 'sort_label' => $row['timestp_lastindex']);
				$values[$c]['values']['workspace'] = array('label' => $_SESSION['ploopi']['workspaces'][$row['id_workspace']]['label']);
				$values[$c]['values']['module'] = array('label' => $arrObjectTypes[$row['id_module']]['label']);
				$values[$c]['values']['object_type'] = array('label' => $arrObjectTypes[$row['id_module']]['objects'][$row['id_object']]['label']);

				$values[$c]['description'] = $row['label'];
				$values[$c]['link'] = ploopi_urlencode("admin.php?ploopi_mainmenu=1&{$object_script}");
				$values[$c]['style'] = '';
			}

			$c++;
		}
		?>
		<div style="background-color:#f0f0f0;border-top:2px solid #c0c0c0;">
		<? $skin->display_array($columns, $values, 'system_search', array('sortable' => true, 'orderby_default' => 'relevance', 'sort_default' => 'DESC')); ?>
		</div>
		<?
	}
}
?>
</div>
