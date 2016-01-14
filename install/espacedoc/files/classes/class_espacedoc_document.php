<?
/**
 * Gestion des documents
 *
 * @package espacedoc
 * @subpackage document
 * @author Stéphane Escaich
 * @copyright SZSIC Metz / OVENSIA
 */

/**
 * Inclusion de la classe parent
 */
include_once './include/classes/data_object.php';

/**
 * Classe d'accès à la table 'ploopi_mod_espacedoc_document'
 *
 * @package espacedoc
 * @subpackage document
 * @author Stéphane Escaich
 * @copyright SZSIC Metz / OVENSIA
 */

class espacedoc_document extends data_object
{
    /**
     * Constructeur de la classe
     *
     * @return espacedoc_document
     */

    function espacedoc_document()
    {
        parent::data_object('ploopi_mod_espacedoc_document', 'id');
    }

    /**
     * Retourne le chemin vers le fichier joint
     *
     * @return string chemin du fichier
     */

    public function getfilepath()
    {
        $filepath = _PLOOPI_PATHDATA._PLOOPI_SEP.'espacedoc'._PLOOPI_SEP.'documents';
        ploopi_makedir($filepath);
        return($filepath._PLOOPI_SEP."document{$this->fields['id']}");
    }

    /**
     * Enregistre le document
     */

    public function save($booParse = false)
    {
        $this->fields['timestp_modify'] = ploopi_createtimestamp();

        if ($this->new)
        {
            $this->setuwm();
            $this->fields['timestp_create'] = $this->fields['timestp_modify'];
        }

        parent::save();

        if ($booParse) $this->parse();

        return $this->fields['id'];
    }

    /**
     * Supprime le document et le fichier associé
     */

    public function delete()
    {
        $filepath = $this->getfilepath();

        // Suppression du fichier
        if (file_exists($filepath) && is_writable($filepath)) unlink($filepath);

        // Suppression de l'index de recherche
        ploopi_search_remove_index(_ESPACEDOC_OBJECT_DOCUMENT, $this->fields['id']);

        return parent::delete();
    }

    /**
     * Indexe le document
     *
     * @param boolean $debug true si on veut afficher des informations de debug
     * @return unknown
     *
     * @see docmeta
     * @see _ESPACEDOC_OBJECT_DOCUMENT
     * @see ploopi_search_create_index
     */

    function parse($debug = false)
    {
        if (!file_exists('./modules/doc')) return false;

        ploopi_init_module('espacedoc', false, false, false);

        include_once './modules/doc/class_docmeta.php';

        global $db;

        global $ploopi_timer;
        if ($debug) printf("<br />START: %0.2f",$ploopi_timer->getexectime()*1000);

        if (!ini_get('safe_mode')) @set_time_limit(0);

        $metakeywords_str = '';

        $allowedmeta_list =
            array(
                'Camera Make',      //jpg
                'Camera Model',     //jpg
                'Comment',          //jpg
                'Producer',         //png
                'Creator',          //pdf,png
                'Author',           //doc
                'Title'             //pdf
            );

        $res_txt = '';

        $ext = ploopi_file_getextension($this->fields['fichier']);

        // on recherche les parsers adaptés au format du fichier
        $sql =  "
                SELECT      p.path
                FROM        ploopi_mod_doc_parser p
                WHERE       lcase(p.extension) = '".$db->addslashes($ext)."'
                ";

        $res = $db->query($sql);
        $fields = $db->fetchrow($res);

        $path = $this->getfilepath();

        if (file_exists($path))
        {
            /* GESTION/EXTRACTION DES METADONNEES */

            switch($ext)
            {
                case 'pdf':
                    $exec = "pdfinfo \"{$path}\"";
                break;

                case 'jpg':
                case 'jpeg':
                    $exec = "jhead \"{$path}\"";
                break;

                default:
                    $exec = "hachoir-metadata --quiet --raw \"{$path}\"";
                break;
            }

            $res_txt = "<div style=\"background-color:#e0e0e0;border-bottom:1px solid #c0c0c0;padding:1px;margin-top:2px;\"><b>{$this->fields['fichier']}</b> : {$exec}</div>\n";

            exec($exec,$array_result);
            if ($debug) printf("<br />META: %0.2f",$ploopi_timer->getexectime()*1000);

            // parse doc metadata
            foreach($array_result as $value)
            {
                if ($value!="")
                {
                    foreach(explode("\n",$value) as $line)
                    {
                        unset($meta_information);

                        switch($ext)
                        {
                            case 'pdf':
                                preg_match("/([A-Za-z0-9_. ]*):(.*)/", $value, $meta_information);
                            break;

                            case 'jpg':
                            case 'jpeg':
                                preg_match("/([A-Za-z0-9_. ]*) : (.*)/", $value, $meta_information);
                            break;

                            default:
                                if ($value != 'Metadata:') preg_match("/- ([A-Za-z0-9_. ]*): (.*)/", $value, $meta_information);
                            break;
                        }

                    }
                    //$this->fields['metadata'] .= strtolower($value)."\n";
                    $metakeywords_str .= $value." ";
                }

            }
            unset($array_result);

            if ($debug) printf("<br />META 2: %0.2f",$ploopi_timer->getexectime()*1000);

            /* EXTRACTION DES CONTENUS */

            $content = '';

            if (!is_null($fields['path']))
            {
                // parse doc content
                $exec = str_replace('%f',"\"{$path}\"",$fields['path']);

                $res_txt .= "<div style=\"background-color:#ffe0e0;border-bottom:1px solid #c0c0c0;padding:1px;margin-top:2px;\">{$exec}</div>\n";

                exec($exec,$array_result);
                if ($debug) printf("<br />CONTENT: %0.2f",$ploopi_timer->getexectime()*1000);

                foreach($array_result as $key => $value)
                {
                    if ($value!="")
                    {
                        switch($ext)
                        {
                            case 'odg':
                            case 'odt':
                            case 'ods':
                            case 'odp':
                            case 'sxw':
                                $value = iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $value);
                            break;
                        }
                        $content .= $value.' ';
                    }
                }

                unset($array_result);
            }

            $res_txt .= "<div style=\"background-color:#e0e0f0;border-bottom:1px solid #c0c0c0;padding:1px;\">".ploopi_strcut($content,200)."</div>\n";

            $metakeywords_str .= "{$this->fields['intitule']} {$this->fields['fichier']} ";

            ploopi_search_remove_index(_ESPACEDOC_OBJECT_DOCUMENT, $this->fields['id'], $this->fields['id_module']);
            ploopi_search_create_index(
                _ESPACEDOC_OBJECT_DOCUMENT,
                $this->fields['id'],
                $this->fields['fichier'],
                $content,
                $metakeywords_str,
                true,
                $this->fields['timestp_create'],
                $this->fields['timestp_modify'],
                $this->fields['id_user'],
                $this->fields['id_workspace'],
                $this->fields['id_module']
            );

        }
        else $res_txt .= "<div><strong>erreur de fichier sur {$path}</strong></div>\n";

        unset($content);
        unset($metakeywords_str);

        return($res_txt);
    }


}
?>
