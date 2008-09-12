<?
ob_start();
session_start();
chdir('../..');
include_once './config/config.php'; // load config (mysql, path, etc.)
include_once './include/errors.php';

include_once './include/classes/class_timer.php' ;

// execution timer
$ploopi_timer = new timer();
$ploopi_timer->start();


// get vars from GET, POST, REQUEST
include_once './include/import_gpr.php';

// load DIMS global classes
include_once './include/classes/class_data_object.php';

// initialize DIMS
include_once './include/global.php';        // load ploopi global functions & constants

if (file_exists('./db/class_db_'._PLOOPI_SQL_LAYER.'.php')) include_once './db/class_db_'._PLOOPI_SQL_LAYER.'.php';


$db = new ploopi_db(_PLOOPI_DB_SERVER, _PLOOPI_DB_LOGIN, _PLOOPI_DB_PASSWORD, _PLOOPI_DB_DATABASE);
if(!$db->connection_id) trigger_error(_PLOOPI_MSG_DBERROR, E_USER_ERROR);



/*header("Cache-control: private");
header("Content-type: text/xml");
header("Content-Disposition: attachment; filename=mb.xml");
header("Pragma: public");*/

echo "<?xml version=\"1.0\" encoding=\"iso-8859-1\" standalone=\"no\"?>\n";

$array_tables = array();
$array_fields = array();

$sql = "SHOW tables from `"._PLOOPI_DB_DATABASE."`";
$result=$db->query($sql);

$filtre = 'ploopi_mod_forum_cat';
$filtre = 'ploopi_mod_forum_mess';

$xml_tables = '<ploopi_mb_table>';
$xml_fields = '<ploopi_mb_field>';
$xml_relations = '<ploopi_mb_relation>';
$xml_schema = '<ploopi_mb_schema>';

// ON PARCOURS TOUTES LES TABLES
while($table = $db->fetchrow($result, MYSQL_NUM))
{
    if (strstr($table[0], $filtre))
    {

        $libelle = substr($table[0],strlen($filtre));
        $visible=1;

        $xml_tables .=  "
                        <row>
                            <name>{$table[0]}</name>
                            <label>{$libelle}</label>
                            <visible>1</visible>
                        </row>
                        ";

        $array_tables[$table[0]] = $table[0];

        $sql ="SHOW columns from `$table[0]`";
        $result2=$db->query($sql);

        // ON PARCOURT TOUS LES CHAMPS DE LA TABLE COURANTE
        while($champ = $db->fetchrow($result2, MYSQL_NUM))
        {
            if ($champ[0] == 'id_module' || $champ[0] == 'id_workspace' || $champ[0] == 'id_user' || $champ[0] == 'id') $visible = 0;
            else $visible = 1;

            $xml_fields .=  "
                            <row>
                                <tablename>{$table[0]}</tablename>
                                <name>{$champ[0]}</name>
                                <label>{$champ[0]}</label>
                                <type>{$champ[1]}</type>
                                <visible>$visible</visible>
                            </row>
                            ";
            $array_fields[$table[0]][$champ[0]] = $champ;
        }
    }
}

foreach($array_fields as $tablename => $fields)
{
    foreach($fields as $fieldname => $detail)
    {
        switch($fieldname)
        {
            // cas particuliers, clés étrangères connues
            case 'id_user':
                $xml_relations .=   "
                                    <row>
                                        <tablesrc>{$tablename}</tablesrc>
                                        <fieldsrc>{$fieldname}</fieldsrc>
                                        <tabledest>ploopi_user</tabledest>
                                        <fielddest>id</fielddest>
                                    </row>
                                    ";

                $xml_schema .=  "
                                <row>
                                    <tablesrc>{$tablename}</tablesrc>
                                    <tabledest>ploopi_user</tabledest>
                                </row>
                                ";
            break;

            case 'id_module':
                $xml_relations .=   "
                                    <row>
                                        <tablesrc>{$tablename}</tablesrc>
                                        <fieldsrc>{$fieldname}</fieldsrc>
                                        <tabledest>ploopi_module</tabledest>
                                        <fielddest>id</fielddest>
                                    </row>
                                    ";

                $xml_schema .=  "
                                <row>
                                    <tablesrc>{$tablename}</tablesrc>
                                    <tabledest>ploopi_module</tabledest>
                                </row>
                                ";
            break;

            case 'id_workspace':
                $xml_relations .=   "
                                    <row>
                                        <tablesrc>{$tablename}</tablesrc>
                                        <fieldsrc>{$fieldname}</fieldsrc>
                                        <tabledest>ploopi_workspace</tabledest>
                                        <fielddest>id</fielddest>
                                    </row>
                                    ";

                $xml_schema .=  "
                                <row>
                                    <tablesrc>{$tablename}</tablesrc>
                                    <tabledest>ploopi_workspace</tabledest>
                                </row>
                                ";
            break;

            default:
                // autres clés

                if (substr($fieldname, -3, 3) == '_id')
                {
                    $tablename_dest = $filtre.substr($fieldname,0,strlen($fieldname)-3);

                    if ($tablename != $tablename_dest) // on évite les tables liées sur elles-mêmes : non géré dans dbreport
                    {
                        $xml_relations .=   "
                                            <row>
                                                <tablesrc>{$tablename}</tablesrc>
                                                <fieldsrc>{$fieldname}</fieldsrc>
                                                <tabledest>{$tablename_dest}</tabledest>
                                                <fielddest>id</fielddest>
                                            </row>
                                            ";

                        $xml_schema .=  "
                                        <row>
                                            <tablesrc>{$tablename}</tablesrc>
                                            <tabledest>{$tablename_dest}</tabledest>
                                        </row>
                                        ";
                    }
                }
                if (substr($fieldname, 0, 3) == 'id_') // champs commençant par "id_"
                {
                    $tablename_dest = $filtre.substr($fieldname,3,strlen($fieldname)-3);

                    if ($tablename != $tablename_dest) // on évite les tables liées sur elles-mêmes : non géré dans dbreport
                    {
                        $xml_relations .=   "
                                            <row>
                                                <tablesrc>{$tablename}</tablesrc>
                                                <fieldsrc>{$fieldname}</fieldsrc>
                                                <tabledest>{$tablename_dest}</tabledest>
                                                <fielddest>id</fielddest>
                                            </row>
                                            ";

                        $xml_schema .=  "
                                        <row>
                                            <tablesrc>{$tablename}</tablesrc>
                                            <tabledest>{$tablename_dest}</tabledest>
                                        </row>
                                        ";
                    }
                }
            break;
        }
    }
}

$xml_tables .= '</ploopi_mb_table>';
$xml_fields .= '</ploopi_mb_field>';
$xml_relations .= '</ploopi_mb_relation>';
$xml_schema .= '</ploopi_mb_schema>';


$xml =  "
        <ploopi>
            {$xml_tables}
            {$xml_fields}
            {$xml_schema}
            {$xml_relations}
        </ploopi>
        ";

require_once 'XML/Beautifier.php'; // new pear XML, XML_Util & XML_Beautifier packages
$fmt = new XML_Beautifier();
$fmt->setOption("indent", "\t");
echo $fmt->formatString($xml);


?>

