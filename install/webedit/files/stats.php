<?php
/*
    Copyright (c) 2008 Ovensia
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
 * Affichage des statistiques de fréquentation du site
 *
 * @package webedit
 * @subpackage public
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

echo ploopi\skin::get()->create_pagetitle(ploopi\str::htmlentities($_SESSION['ploopi']['modulelabel']));
echo ploopi\skin::get()->open_simplebloc('Statistiques');

$intYearSel = (empty($_GET['webedit_yearsel']) || !is_numeric($_GET['webedit_yearsel'])) ? date('Y') : $_GET['webedit_yearsel'];
$intMonthSel = (empty($_GET['webedit_monthsel']) || empty($_GET['webedit_yearsel'])  || !is_numeric($_GET['webedit_monthsel']) || !is_numeric($_GET['webedit_yearsel'])) ? '' : $_GET['webedit_monthsel'];

$rs = ploopi\db::get()->query("SELECT distinct(year) FROM ploopi_mod_webedit_counter WHERE id_module = {$_SESSION['ploopi']['moduleid']} ORDER BY year");
$arrSelectYear = ploopi\db::get()->getarray($rs, true);

$rs = ploopi\db::get()->query("SELECT distinct(month) FROM ploopi_mod_webedit_counter WHERE id_module = {$_SESSION['ploopi']['moduleid']} AND year = {$intYearSel} ORDER BY month");
$arrSelectMonth = ploopi\db::get()->getarray($rs, true);

// aucun mois sélectionné
if (empty($intMonthSel))
{
    // année en cours
    if ($intYearSel == date('Y')) $intMonthSel = date('n');
    else $intMonthSel = current($arrSelectMonth);
}

// récupération des rubriques
$arrHeadings = webedit_getheadings();

?>
<div id="webedit_stats">
    <div id="webedit_stats_select">
        <p>
            <strong>Année:</strong>
            <?php
            foreach($arrSelectYear as $year)
            {
                ?>
                <a href="<?php echo ploopi\crypt::urlencode("admin.php?webedit_menu=stats&webedit_yearsel={$year}"); ?>" <?php if ($year == $intYearSel) echo 'class="selected"'; ?>><?php echo $year; ?></a>
                <?php
            }
            ?>
        </p>
        <p>
            <strong>Mois:</strong>
            <?php
            foreach($arrSelectMonth as $month)
            {
                ?>
                <a href="<?php echo ploopi\crypt::urlencode("admin.php?webedit_menu=stats&webedit_yearsel={$intYearSel}&webedit_monthsel={$month}"); ?>" <?php if ($month == $intMonthSel) echo 'class="selected"'; ?>><?php echo $ploopi_months[$month]; ?></a>
                <?php
            }
            ?>
        </p>
    </div>
    <?php
    // 1er Diagramme : année par mois

    $dataset = array();
    $legend = array();

    foreach($ploopi_months as $key => $value)
    {
        $dataset[$key] = 0;
        $dataset2[$key] = 0;

        $legend[$key] = $value;
    }

    ploopi\db::get()->query(
        "
        SELECT  month,
                sum(hits) as c
        FROM    ploopi_mod_webedit_counter
        WHERE   id_module = {$_SESSION['ploopi']['moduleid']}
        AND     year = {$intYearSel}
        GROUP BY month
        ORDER BY month
        ");

    while ($row = ploopi\db::get()->fetchrow())
    {
        $dataset[$row['month']] = $row['c'];
        $dataset2[$row['month']] = $row['c']-10;
    }

    $objBarChartYear = new ploopi\barchart(700, 150, array('padding' => 1));
    $objBarChartYear->setvalues($dataset, 'Fréquentation mensuelle', '#1E64A1', '#f0f0f0');
    $objBarChartYear->setlegend($legend);

    // 2eme Diagramme : mois par jours

    $dataset = array();
    $legend = array();

    $nbdays = date('t', mktime(0, 0, 0, $intMonthSel, 1, $intYearSel));

    for ($d=1;$d<=$nbdays;$d++)
    {
        $weekday = date('N', mktime(0, 0, 0, $intMonthSel, $d, $intYearSel));
        $dataset[$d] = 0;
        $legend[$d] = substr($ploopi_days[$weekday],0,2).'<br />'.$d;
    }

    ploopi\db::get()->query(
        "
        SELECT  day,
                sum(hits) as c
        FROM    ploopi_mod_webedit_counter
        WHERE   id_module = {$_SESSION['ploopi']['moduleid']}
        AND     year = {$intYearSel}
        AND     month = {$intMonthSel}
        GROUP BY day
        ORDER BY day
        ");

    while ($row = ploopi\db::get()->fetchrow()) $dataset[$row['day']] = $row['c'];

    $objBarChartMonth = new ploopi\barchart(700, 150, array('padding' => 1));
    $objBarChartMonth->setvalues($dataset, 'Fréquentation quotidienne', '#4FA11E', '#f0f0f0');
    $objBarChartMonth->setlegend($legend);

    // Affichage
    ?>
    <div class="webedit_stats_graph">
        <h1>Statistiques globales de fréquentation pour <em><?php echo $intYearSel ?></em> (nombre d'articles vus)</h1>
        <div><?php $objBarChartYear->draw(); ?></div>
    </div>
    <div class="webedit_stats_graph">
        <h1>Statistiques globales de fréquentation pour <em><?php echo $ploopi_months[$intMonthSel] ?> <?php echo $intYearSel ?></em> (nombre d'articles vus)</h1>
        <div><?php $objBarChartMonth->draw(); ?></div>
    </div>

    <?php
    // Recherche des articles les plus consultés

    ploopi\db::get()->query(
        "
        SELECT      c.id_article,
                    sum(c.hits) as counter,
                    a.title,
                    a.metatitle,
                    a.id_heading,
                    h.label

        FROM        ploopi_mod_webedit_counter as c

        INNER JOIN  ploopi_mod_webedit_article a
        ON          a.id = c.id_article

        INNER JOIN  ploopi_mod_webedit_heading h
        ON          h.id = a.id_heading

        WHERE       c.id_module = {$_SESSION['ploopi']['moduleid']}
        AND         c.year = {$intYearSel}
        AND         c.month = {$intMonthSel}

        GROUP BY    c.id_article

        ORDER BY    counter DESC

        LIMIT 0,50
        ");

    // initialisation du contenu du tableau
    $columns = array();
    $values = array();

    $columns['auto']['article'] =
        array(
            'label' => 'Article',
            'options' => array('sort' => true)
        );

    $columns['right']['counter'] =
        array(
            'label' => 'visites',
            'width' => '80',
            'options' => array('sort' => true)
        );

    $columns['right']['heading'] =
        array(
            'label' => 'Rubrique',
            'width' => '200',
            'options' => array('sort' => true)
        );

    $c = 0;

    while ($row = ploopi\db::get()->fetchrow())
    {
        $arrParents = array();
        if (isset($arrHeadings['list'][$row['id_heading']])) foreach(preg_split('/;/', $arrHeadings['list'][$row['id_heading']]['parents']) as $hid_parent) if (isset($arrHeadings['list'][$hid_parent])) $arrParents[] = $arrHeadings['list'][$hid_parent]['label'];

        $values[$c]['values']['article'] =
            array(
                'label' => ploopi\str::htmlentities($row['title'])
            );

        $values[$c]['values']['heading'] =
            array(
                'label' => ploopi\str::htmlentities($row['label'])
            );

        $values[$c]['values']['counter'] =
            array(
                'label' => ploopi\str::htmlentities($row['counter'])
            );

        $values[$c]['description'] = ploopi\str::htmlentities($row['title']);
        $values[$c]['link'] = ploopi\str::urlrewrite("index.php?headingid={$row['id_heading']}&articleid={$row['id_article']}", webedit_getrewriterules(), $row['metatitle'], $arrParents);

        $c++;
    }
    ?>

    <div class="webedit_stats_array">
        <h1>Articles les plus visités pour <em><?php echo $ploopi_months[$intMonthSel] ?> <?php echo $intYearSel ?></em> (nombre de visites)</h1>
        <div style="border-top:1px solid #c0c0c0;">
        <?php
        ploopi\skin::get()->display_array(
            $columns,
            $values,
            'webedit_array_stats_articles',
            array(
                'sortable' => true,
                'orderby_default' => 'counter',
                'sort_default' => 'DESC'
            )
        );
        ?>
        </div>
    </div>

    <?php
    // Recherche des rubriques les plus consultées

    ploopi\db::get()->query(
        "
        SELECT      h.id,
                    h.label,
                    h.parents,
                    sum(c.hits) as counter

        FROM        ploopi_mod_webedit_counter as c

        INNER JOIN  ploopi_mod_webedit_article a
        ON          a.id = c.id_article

        INNER JOIN  ploopi_mod_webedit_heading h
        ON          h.id = a.id_heading

        WHERE       c.id_module = {$_SESSION['ploopi']['moduleid']}
        AND         c.year = {$intYearSel}
        AND         c.month = {$intMonthSel}

        GROUP BY    h.id

        ORDER BY    counter DESC

        LIMIT 0,50
        ");

    // initialisation du contenu du tableau
    $columns = array();
    $values = array();

    $columns['auto']['heading'] =
        array(
            'label' => 'Rubrique',
            'options' => array('sort' => true)
        );

    $columns['right']['counter'] =
        array(
            'label' => 'visites',
            'width' => '80',
            'options' => array('sort' => true)
        );

    $c = 0;

    while ($row = ploopi\db::get()->fetchrow())
    {
        $arrParents = array();
        foreach(preg_split('/;/', $row['parents']) as $hid_parent) if (isset($arrHeadings['list'][$hid_parent])) $arrParents[] = $arrHeadings['list'][$hid_parent]['label'];

        $values[$c]['values']['heading'] =
            array(
                'label' => ploopi\str::htmlentities($row['label'])
            );

        $values[$c]['values']['counter'] =
            array(
                'label' => ploopi\str::htmlentities($row['counter'])
            );

        $values[$c]['description'] = ploopi\str::htmlentities($row['label']);
        $values[$c]['link'] =  ploopi\str::urlrewrite($script = "index.php?headingid={$row['id']}", webedit_getrewriterules(),$row['label'], $arrParents);

        $c++;
    }
    ?>

    <div class="webedit_stats_array">
        <h1>Rubriques les plus visités pour <em><?php echo $ploopi_months[$intMonthSel] ?> <?php echo $intYearSel ?></em> (nombre de visites)</h1>
        <div style="border-top:1px solid #c0c0c0;">
        <?php
        ploopi\skin::get()->display_array(
            $columns,
            $values,
            'webedit_array_stats_headings',
            array(
                'sortable' => true,
                'orderby_default' => 'counter',
                'sort_default' => 'DESC'
            )
        );
        ?>
        </div>
    </div>
</div>

<?php
echo ploopi\skin::get()->close_simplebloc();
?>
