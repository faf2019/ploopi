<?php

ploopi\module::init('dbreport',false, false, false);

include_once './modules/dbreport/classes/class_dbreport_query.php';

$objDbrQuery = new dbreport_query();
if (!isset($_GET['dbreport_query_id']) || !is_numeric($_GET['dbreport_query_id']) || !$objDbrQuery->open($_GET['dbreport_query_id'])) ploopi\system::kill();

header('Content-type: text/html; charset=utf-8');
ploopi\buffer::clean();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
    <head>
        <meta http-equiv="content-type" content="text/html; charset=utf-8" />
        <title>Graphique: <?php echo ploopi\str::htmlentities($objDbrQuery->fields['label']); ?></title>
        <script src="./lib/protoaculous/protoaculous.min.js"></script>
        <script src="./modules/dbreport/lib/highcharts/adapters/prototype-adapter.js"></script>
        <script src="./modules/dbreport/lib/highcharts/highcharts.js"></script>
        <script src="./modules/dbreport/lib/highcharts/modules/exporting.js"></script>
    </head>
    <body>
        <?php
        $objDbrQuery->generate();
        $objDbrQuery->exec(0, true);
        $objDbrQuery->displayChart();
        ?>
    </body>
</html>
<?php

ploopi\system::kill();
