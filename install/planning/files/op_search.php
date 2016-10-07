<?php
switch($ploopi_op) {
    case 'planning_search':

        ploopi\module::init('planning');


        $arrSearchPattern = planning_getcookie();

        if (empty($arrSearchPattern['planning_resources']['user']) && empty($arrSearchPattern['planning_resources']['group']))
        {
            ?>
            <div style="padding:4px;">Vous devez sélectionner au moins un utilisateur ou un groupe.</div>
            <?php
        }
        else
        {

            $strQueryString = isset($_POST['query_string']) ? $_POST['query_string'] : '';

            list($arrWords) = ploopi\str::getwords($strQueryString, false);


            $objQuery = new ploopi\query_select();

            $objQuery->add_select('event.object');
            $objQuery->add_select('detail.timestp_begin');
            $objQuery->add_select('detail.timestp_end');
            $objQuery->add_select('detail.id');
            $objQuery->add_select('resource.id_resource');
            $objQuery->add_select('resource.type_resource');

            $objQuery->add_from('ploopi_mod_planning_event event');
            $objQuery->add_from('ploopi_mod_planning_event_detail detail');
            $objQuery->add_from('ploopi_mod_planning_event_detail_resource resource');

            $objQuery->add_where('event.id = detail.id_event');
            $objQuery->add_where('detail.id = resource.id_event_detail');

            foreach(array_keys($arrWords) as $strWord) $objQuery->add_where('event.object like %s', '%'.$strWord.'%');


            $objRecordSet = $objQuery->execute();

            $i = 0;
            $columns = array();
            $values = array();

            $columns['auto']['event'] =
                array(
                    'label'     => 'Evénement',
                    'style'     => 'text-align:center;',
                    'options'   => array('sort' => true)
                );

            $arrSearchResult = array();

            while($row = $objRecordSet->fetchrow())
            {
                // Nouvel événement
                if(!isset($arrSearchResult[$row['id']]))
                {
                    $arrSearchResult[$row['id']] = array(
                        'object' => $row['object'],
                        'timestp_begin' => $row['timestp_begin'],
                        'timestp_end' => $row['timestp_end'],
                        'resources' => array(
                            'user' => array(),
                            'group' => array()
                        ),
                    );
                }

                // Affectation de la ressource à l'événement
                $arrSearchResult[$row['id']]['resources'][$row['type_resource']][$row['id_resource']] = $row['id_resource'];
            }

            // Pour chaque événement
            foreach($arrSearchResult as $id => $row)
            {
                // On vérifie l'intersection entre les utilisateurs de l'événement et les utilisateurs à afficher (idem pour les groupes)
                if (array_intersect($arrSearchResult[$id]['resources']['group'], $arrSearchPattern['planning_resources']['group']) || array_intersect($arrSearchResult[$id]['resources']['user'], $arrSearchPattern['planning_resources']['user']))
                {
                    $arrEventBegin = ploopi\date::timestamp2local($row['timestp_begin']);
                    $arrEventEnd = ploopi\date::timestamp2local($row['timestp_end']);

                    // Construction de la liste des utilisateurs/groupes à afficher
                    $arrWho = array();

                    // Pour chaque groupe associé à l'événement
                    foreach($arrSearchResult[$id]['resources']['group'] as $intIdGroup)
                    {
                        $objGroup = new ploopi\group();
                        if($objGroup->open($intIdGroup))
                        {
                            $arrWho[] = "<img src='./modules/planning/img/ico_group.png'>";
                            $arrWho[] = ploopi\str::htmlentities($objGroup->fields['label']);
                        }
                    }

                    // Pour chaque utilisateur associé à l'événement
                    foreach($arrSearchResult[$id]['resources']['user'] as $intIdUser)
                    {
                        $objUser = new ploopi\user();
                        if($objUser->open($intIdUser))
                        {
                            $strColor = !empty($objUser->fields['color']) ? "background:{$objUser->fields['color']}" : '';
                            $arrWho[] = '<img src="./modules/planning/img/ico_user.png" style="'.$strColor.';">';
                            $arrWho[] = ploopi\str::htmlentities(sprintf("%s %s",$objUser->fields['firstname'],$objUser->fields['lastname']));
                        }
                    }

                    // Conversion de la date de début au format Unix (pour utiliser la fonction date)
                    $intUnixEventBegin = ploopi\date::timestamp2unixtimestamp($row['timestp_begin']);

                    $values[$i]['values']['event'] =
                        array(
                            'label'        => '<strong>'.ploopi\str::htmlentities($arrEventBegin['date']).'</strong>&nbsp;->&nbsp;'.substr($arrEventBegin['time'], 0, 2).'h'.substr($arrEventBegin['time'], 3, 2).'&nbsp;&agrave;&nbsp;'.substr($arrEventEnd['time'], 0, 2).'h'.substr($arrEventEnd['time'], 3, 2).'<br />'.ploopi\str::htmlentities($row['object']).'<br />'.implode(' ', $arrWho),
                            'style'        => 'text-align:left;',
                            'sort_label'   => $row['timestp_begin']
                        );

                    $values[$i]['description'] = 'Aller sur l\'événement';
                    $values[$i]['link'] = 'javascript:void(0);';
                    $values[$i]['onclick'] = "ploopi_xmlhttprequest_todiv('admin-light.php', '".ploopi\crypt::queryencode('ploopi_op=planning_refresh&planning_display_type=day&planning_day='.date('j',$intUnixEventBegin).'&planning_month='.date('n',$intUnixEventBegin).'&planning_year='.date('Y',$intUnixEventBegin))."', 'planning_main');";


                    $i += 1;
                }
            }

            $skin->display_array($columns, $values, 'event_list', array('sortable' => true, 'orderby_default' => 'event', 'sort_default' => 'DESC', 'limit' => 5));
        }

        ploopi\system::kill();
        break;
}
