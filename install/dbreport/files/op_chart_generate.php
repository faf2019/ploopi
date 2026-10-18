<?php

ploopi\module::init('dbreport',false, false, false);

include_once './modules/dbreport/classes/class_dbreport_query.php';

$objDbrQuery = new dbreport_query();
if (!isset($_GET['dbreport_query_id']) || !is_numeric($_GET['dbreport_query_id']) || !$objDbrQuery->open($_GET['dbreport_query_id'])) ploopi\system::kill();

header('Content-type: text/html; charset=utf-8');
ploopi\buffer::clean();
?>
<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="utf-8" />
        <title>Graphique: <?php echo ploopi\str::htmlentities($objDbrQuery->fields['label']); ?></title>
        <?php /* Chart.js (MIT) : remplace Highcharts, dont la licence n'est pas libre */ ?>
        <script src="./modules/dbreport/lib/chartjs/chart.umd.js"></script>
        <script src="./modules/dbreport/include/chart.js"></script>
        <style>
            body { margin: 8px; font-family: sans-serif; }
            .dbreport_chart_actions { font-size: 12px; }
            @media print { .dbreport_chart_actions { display: none; } }
        </style>
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
