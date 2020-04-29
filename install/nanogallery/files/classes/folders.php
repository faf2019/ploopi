<?php
/**
 * NanoGallery : Classe pour l'affichage de la structure des dossiers 'doc'
 *
 * @author JPP
 * @copyright DSIC-EST
 */
namespace ploopi\nanogallery;
use ploopi;

abstract class folders {

	/**
	 * Retourne la liste des dossiers visibles pour le module 
	 *
	 * @param int $moduleid Id de l'instance de module nanogallery
	 * @return array tableau contenant les dossiers
	 */
	static public function getfolders($moduleid) {

		$arrFolders = array('list' => array(),'tree' => array());
		$arrWhere = array();
		$arrFolders['list'][0] = array(
		    'id' => 0,
		    'name' => 'DOSSIERS contenant des images',
		    'parents' => '-1',
		    'id_folder' => -1
		);

        $objQuery = new ploopi\query_select();
        $objQuery->add_select('f.id, f.name, f.parents, f.id_folder, f.nbelements, m.label as module_label');
        $objQuery->add_select("(SELECT count(*) FROM ploopi_mod_doc_file WHERE id_folder = f.id AND LCASE(extension) IN ('jpg','jpeg','gif','png')) as nbmedias");
        $objQuery->add_from('ploopi_mod_doc_folder f');
        $objQuery->add_leftjoin('ploopi_mod_doc_folder f_val ON (f_val.id = f.waiting_validation)');
        $objQuery->add_leftjoin('ploopi_module m ON m.id = f.id_module');
        $objQuery->add_where("(f.foldertype = 'public' AND f.id_workspace IN (".self::viewworkspaces($moduleid).")) AND f.published = 1");
        $objQuery->add_orderby("f.name");
		$result = $objQuery->execute();
		$arrFolders['tree'][-1][] = 0;
		while ($fields = $result->fetchrow()) {
			$fields['parents'] = '-1;'.str_replace(',', ';', $fields['parents']);
			$arrFolders['list'][$fields['id']] = $fields;
			$arrFolders['tree'][$fields['id_folder']][] = $fields['id'];
		}

		// Suppression des dossiers feuille sans image
		foreach($arrFolders['tree'] as $treeKey => $treeArray) {
			if (empty($treeArray)) unset($arrFolders['tree'][$treeKey]);
		}
		do {
			$toDelete = array();
			foreach($arrFolders['list'] as $folder) {
				if (($folder['nbmedias'] == 0) && !isset($arrFolders['tree'][$folder['id']])) {
					$toDelete[] = $folder['id'];		
				}
			}
			if (empty($toDelete)) continue;
			foreach($toDelete as $id) {
				unset($arrFolders['list'][$id]);
				foreach($arrFolders['tree'] as $treeKey => $treeArray) {
					$todel = array_search ( $id , $treeArray);
					if (!is_null($todel) && $todel !== false) unset($arrFolders['tree'][$treeKey][$todel]);
				}
			}
			foreach($arrFolders['tree'] as $treeKey => $treeArray) {
				if (empty($treeArray)) unset($arrFolders['tree'][$treeKey]);
			}
		} while (!empty($toDelete));

		return $arrFolders;
	}

	/**
	 * Retourne le treeview de navigation dans les dossiers
	 *
	 * @param array $arrFolder tableau contenant les dossiers
	 * @return array tableau contenant la description du treeview
	 */
	static public function gettreeview($arrFolders = array())
	{
		$arrTreeview = array('list' => array(),'tree' => array());

		foreach($arrFolders['list'] as $id => $fields) {
            $strId = $fields['id'];
            $strparents = preg_split('/;/', $fields['parents']);
			$label = "&nbsp;".$fields['name'];
            $strOnClick = "nano_updateFolder(this, {$fields['id']},'{$fields['name']}')";
	        $arrTreeview['list'][$strId] = array(
	            'id' => $strId,
	            'label' => $label,
	            'description' => $fields['name'],
	            'parents' => preg_split('/;/', str_replace(';', ";", $fields['parents'])),
	            'node_onclick' => "ploopi.skin.treeview_shownode(
					'{$strId}', 
					'".ploopi\crypt::queryencode("ploopi_op=doc_folder_detail&doc_folder_id={$fields['id']}")."',
					'admin-light.php'
				);",
	            'onclick' => $strOnClick,
	            'icon' => "./modules/nanogallery/img/folder_alpha.png",
				'status' => "<b>{$fields['nbmedias']}</b> images / {$fields['nbelements']} fichiers - (module {$fields['module_label']})"
	        );
	        $arrTreeview['tree'][$fields['id_folder']][] = $fields['id'];
		}

		unset($arrTreeview['list'][0]['onclick']);
		unset($arrTreeview['list'][0]['status']);
		return($arrTreeview);
	}

	/**
	 * Retourne le nom du dossier avec l'id en paramètre
	 *
	 * @param int $id id du dossier
	 * @return string le nom du dossier
	 */
	static public function getFolderName($id) {
        $objQuery = new ploopi\query_select();
        $objQuery->add_select('name');
        $objQuery->add_from('ploopi_mod_doc_folder');
        $objQuery->add_where("id = $id");
		return $objQuery->execute()->getarray()[0]['name'];
	}

    /**
     * Affiche un treeview
     *
     * @param array $nodes tableau associatif contenant les noeuds
     * @param array $treeview tableau contenant la hiérarchie des noeuds
     * @param string $node_id_sel identifiant du noeud sélectionné
     * @param string $node_id_from identifiant du noeud de départ (permet de n'afficher qu'un sous-ensemble)
     * @param boolean $viewall true tous les noeuds de l'arbre doivent être ouvert (false par défaut)
     * @return string code html du treeview
     */
    static public function display_treeview(&$nodes, &$treeview, $node_id_sel = null ,$node_id_from = null, $viewall = false) {
        // recherche du premier noeud
        if (is_null($node_id_from)) 
		$node_id_from = key($treeview);
		$node_id_sel = null;
        if (!is_null($node_id_sel) && isset($nodes[$node_id_sel])) $nodesel = $nodes[$node_id_sel];

        // code html généré par ce niveau de boucle
        $html = '';

        if (isset($treeview[$node_id_from])) {
            $c = 0;
            foreach($treeview[$node_id_from] as $node_id) {
                // noeud courant
                $node = $nodes[$node_id];
                // true si le noeud courant est sélectionné
                $is_node_sel = (!is_null($node_id_sel) && ($node_id_sel == $node['id']));
                // parents du noeud sélectionné
                $nodesel_parents = (isset($nodesel)) ? $nodesel['parents'] : array();
                // parents du noeud courant
                $node_parents = array_merge($node['parents'], array($node['id']));
                // true si le noeud est ouvert : le noeud est ouvert si les parents du noeud courant et du noeud sélectionné se superposent
                $is_node_opened = ($viewall || sizeof(array_intersect_assoc($nodesel_parents, $node_parents)) == sizeof($node_parents));
                // true si le noeud est le dernier fils de son père
                $is_node_last = ($c == sizeof($treeview[$node_id_from])-1);
                // profondeur du noeud ( = nombre de noeuds parents)
                $node_depth = sizeof($node['parents']);

                $node_link = '';
                $bg = '';

                if ($node_depth == 1) {
                    // au premier niveau de profondeur, on ne crée pas de décalage
                    $marginleft = 0;
                } else {
                    $type_node = 'join';
                    if (isset($treeview[$node_id])) $type_node = ($is_node_sel || $is_node_opened) ? 'minus' : 'plus';
                    $n_link = (empty($node['node_link'])) ? 'javascript:void(0);' : $node['node_link'];
                    $n_onclick = (empty($node['node_onclick'])) ? '' : 'onclick="javascript:'.$node['node_onclick'].';"';
                    $node_link = "<a href=\"{$n_link}\" {$n_onclick}><img id=\"t{$node['id']}\" 
						style=\"display:block;float:left;margin:0;\" src=\"./modules/nanogallery/img/treeview/{$type_node}3.png\" /></a>";
                    $marginleft = 20;
                }

                // récupération du code html des noeuds fils par un appel récursif
                $html_children = ($is_node_sel || $is_node_opened || $node_depth == 1) ? self::display_treeview($nodes, $treeview, $node_id_sel, $node['id'], $viewall) : '';
                // si du contenu à afficher, display = 'block'
                $display = ($html_children == '') ? 'none' : 'block';
                // lien sur le libellé
                $link = (empty($node['link'])) ? 'javascript:void(0);' : $node['link'];
                // onclick sur le libellé
                $onclick = (empty($node['onclick'])) ? '' : 'onclick="'.$node['onclick'].';"';
                // label supplémentaire
                $status = (empty($node['status'])) ? '' : $node['status'];
				// Largeur
				$width = 380 - ($node_depth * 20);
                // génération du code html du noeud courant
                $html .= "
                    <div class=\"treeview_node\" id=\"treeview_node{$node['id']}\">
                        <div>
                            {$node_link}<img src=\"{$node['icon']}\" style=\"height:18px;\"/>
                            <div style=\"display:block;margin-left:".($marginleft+20)."px;line-height:20px;font-size:90%;border-bottom:1px dashed #000;\">
                                <a href=\"{$link}\" {$onclick} style=\"display:inline-block;min-width:{$width}px;margin:0 0 0 5px;\">".$node['label']."</a>
                                {$status}
                            </div>
                        </div>
                        <div style=\"margin-left:{$marginleft}px;display:{$display};\" id=\"n{$node['id']}\">{$html_children}</div>
                    </div>
                ";
                $c++;
            }
        }
        return $html;

    }

	/**
	 * Retourne la liste du workspace où est instancié le module avec ses sous-espaces 
	 *
	 * @param int $id id du module
	 * @return string la liste des id de workspace séparés par des virgules
	 */
	static private function viewworkspaces($moduleid) {
		$objModule = new ploopi\module();
		$objModule->open($moduleid);
		$objWorkspace = new ploopi\workspace();
        $objWorkspace->open($objModule->fields['id_workspace']);
        $workspaces = array_keys($objWorkspace->getChildren());
        $workspaces[] = $objWorkspace->fields['id'];
        $workspaces = implode(',', $workspaces);
		return $workspaces;
	}

}
