<?php
/*
    Copyright (c) 2007-2020 Ovensia
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

namespace ploopi\news2;
use ploopi;
use ploopi\news2;

class controller extends ploopi\controller {

    public function setBlock() {

        $this->addBlockMenu('Toutes les News', 'public','all');
        $this->addBlockMenu('Administration', 'admin','default',  news2\tools::ACTION_ANY);

        $result = news2\tools::getNews($this->getModuleId(),true, $this->getParam('nbnewsdisplay'));
        while ($news_fields = $result->fetchrow()) {
            $localdate = ploopi\date::timestamp2local($news_fields['date_publish']);
            // $titre = '<div style="font-size:80%;margin:-6px 0 -8px 0;line-height:1;vertical-align:bottom;">'
            $titre = '<div style="font-size:80%;">'
                .($news_fields['hot'] ? '<strong>' : '')
                .ploopi\str::htmlentities($news_fields['title'])
                .($news_fields['hot'] ? '</strong>' : '')
                ." - le {$localdate['date']} à {$localdate['time']}</div>";
            $this->addBlockMenu($titre,'public',"one&id={$news_fields['id']}");
        }
    }

}
