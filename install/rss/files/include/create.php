<?php
/*
	Copyright (c) 2002-2007 Netlor
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
?>
<?
// get current module id
//$create_id_module = $this->fields['id'];

$catid = 0;
$db->query("INSERT INTO `ploopi_mod_rssfeed` VALUES (0, 'http://www.clubic.com/c/xml.php?type=newsmateriellogiciel', 'Clubic.com - Actualité Logiciels et Matériel PC', 'http://www.clubic.com/', '', 1, 20041105095433, 43200, '', '', 0, $catid, {$_SESSION['ploopi']['userid']}, {$_SESSION['ploopi']['groupid']}, {$this->fields['id']})");
$db->query("INSERT INTO `ploopi_mod_rssfeed` VALUES (0, 'http://www.zdnet.fr/feeds/rss/actualites/', 'Actualités ZDNet.fr', 'http://www.zdnet.fr/actualites/?rss', '', 0, 20040920190725, 43200, '', '', 0, $catid, {$_SESSION['ploopi']['userid']}, {$_SESSION['ploopi']['groupid']}, {$this->fields['id']})");
$db->query("INSERT INTO `ploopi_mod_rssfeed` VALUES (0, 'http://www.infos-du-net.com/backend.php', 'Infos-du-Net.com', 'http://www.infos-du-net.com/', '', 0, 20040920190736, 43200, '', '', 0, $catid, {$_SESSION['ploopi']['userid']}, {$_SESSION['ploopi']['groupid']}, {$this->fields['id']})");
$db->query("INSERT INTO `ploopi_mod_rssfeed` VALUES (0, 'http://www.pcinpact.com/include/news.xml', 'PC INpact', 'http://www.pcinpact.com', '', 0, 20040920190737, 43200, '', '', 0, $catid, {$_SESSION['ploopi']['userid']}, {$_SESSION['ploopi']['groupid']}, {$this->fields['id']})");
$db->query("INSERT INTO `ploopi_mod_rssfeed` VALUES (0, 'http://www.wpc-fr.net/rss/', 'WPC News', 'http://www.wpc-fr.net/', '', 0, 20040920190738, 43200, '', '', 0, $catid, {$_SESSION['ploopi']['userid']}, {$_SESSION['ploopi']['groupid']}, {$this->fields['id']})");
$db->query("INSERT INTO `ploopi_mod_rssfeed` VALUES (0, 'http://www.zonehd.net/syndicate/rss.xml', 'ZoneHD.net', 'http://www.zonehd.net', '', 0, 20040920190743, 43200, '', '', 0, $catid, {$_SESSION['ploopi']['userid']}, {$_SESSION['ploopi']['groupid']}, {$this->fields['id']})");
$db->query("INSERT INTO `ploopi_mod_rssfeed` VALUES (0, 'http://xml.newsisfree.com/feeds/99/899.xml', 'HoaxBuster', 'http://www.hoaxbuster.com/', '', 0, 20040920190805, 43200, '', '', 0, $catid, {$_SESSION['ploopi']['userid']}, {$_SESSION['ploopi']['groupid']}, {$this->fields['id']})");


$db->query("INSERT INTO `ploopi_mod_rsscat` VALUES (0, '', 20040920190148, 'Logiciels', {$_SESSION['ploopi']['userid']}, {$_SESSION['ploopi']['groupid']}, {$this->fields['id']})");
$catid = $db->insertid();
$db->query("INSERT INTO `ploopi_mod_rssfeed` VALUES (0, 'http://www.logiciels-fr.com/backend.php', 'Logiciels-fr.com', 'http://www.logiciels-fr.com', '', 0, 20040920190728, 21600, '', '', 0, $catid, {$_SESSION['ploopi']['userid']}, {$_SESSION['ploopi']['groupid']}, {$this->fields['id']})");

$db->query("INSERT INTO `ploopi_mod_rsscat` VALUES (0, '', 20040920190148, 'Internet', {$_SESSION['ploopi']['userid']}, {$_SESSION['ploopi']['groupid']}, {$this->fields['id']})");
$catid = $db->insertid();
$db->query("INSERT INTO `ploopi_mod_rssfeed` VALUES (0, 'http://www.progforum.net/rss.xml', 'ProgForum.Net', 'http://www.progforum.net/', '', 0, 20040920190747, 43200, '', '', 0, $catid, {$_SESSION['ploopi']['userid']}, {$_SESSION['ploopi']['groupid']}, {$this->fields['id']})");

$db->query("INSERT INTO `ploopi_mod_rsscat` VALUES (0, '', 20040920190148, 'Libre', {$_SESSION['ploopi']['userid']}, {$_SESSION['ploopi']['groupid']}, {$this->fields['id']})");
$catid = $db->insertid();
$db->query("INSERT INTO `ploopi_mod_rssfeed` VALUES (0, 'http://www.framasoft.net/backend.php3', 'Framasoft', 'http://www.framasoft.net/', '', 0, 20040920190724, 43200, '', '', 0, $catid, {$_SESSION['ploopi']['userid']}, {$_SESSION['ploopi']['groupid']}, {$this->fields['id']})");
$db->query("INSERT INTO `ploopi_mod_rssfeed` VALUES (0, 'http://www.logiciellibre.net/backend/llnetshortnews.rss', 'LogicielLibre.Net', 'http://www.logiciellibre.net/', '', 0, 20040920190727, 43200, '', '', 0, $catid, {$_SESSION['ploopi']['userid']}, {$_SESSION['ploopi']['groupid']}, {$this->fields['id']})");
$db->query("INSERT INTO `ploopi_mod_rssfeed` VALUES (0, 'http://www.linuxfrench.net/backend.php', 'LinuxFrench.Net, Le WebMagazine du Libre', 'http://www.linuxfrench.net', '', 0, 20040920190734, 43200, '', '', 0, $catid, {$_SESSION['ploopi']['userid']}, {$_SESSION['ploopi']['groupid']}, {$this->fields['id']})");
$db->query("INSERT INTO `ploopi_mod_rssfeed` VALUES (0, 'http://linuxfr.org/backend.rss', 'Da Linux French Page', 'http://linuxfr.org/', '', 0, 20041105101028, 43200, '', '', 0, $catid, {$_SESSION['ploopi']['userid']}, {$_SESSION['ploopi']['groupid']}, {$this->fields['id']})");

$db->query("INSERT INTO `ploopi_mod_rsscat` VALUES (0, '', 20040920190148, 'Référencement', {$_SESSION['ploopi']['userid']}, {$_SESSION['ploopi']['groupid']}, {$this->fields['id']})");
$catid = $db->insertid();
$db->query("INSERT INTO `ploopi_mod_rssfeed` VALUES (0, 'http://www.abondance.com/rss/rss.xml', 'Abondance', 'http://www.abondance.com/', '', 0, 20041021183114, 43200, '', '', 0, $catid, {$_SESSION['ploopi']['userid']}, {$_SESSION['ploopi']['groupid']}, {$this->fields['id']})");
$db->query("INSERT INTO `ploopi_mod_rssfeed` VALUES (0, 'http://www.webrankinfo.com/rss.php', 'WebRankInfo', 'http://www.webrankinfo.com/', '', 0, 20040920190742, 43200, '', '', 0, $catid, {$_SESSION['ploopi']['userid']}, {$_SESSION['ploopi']['groupid']}, {$this->fields['id']})");
$db->query("INSERT INTO `ploopi_mod_rssfeed` VALUES (0, 'http://www.realposition.com/content/fr/rss/queries.php', 'Real Position - Archives des requêtes', 'http://www.realposition.com/usecase/audit/search/query/in.php', '', 0, 20040920190745, 43200, '', '', 0, $catid, {$_SESSION['ploopi']['userid']}, {$_SESSION['ploopi']['groupid']}, {$this->fields['id']})");
$db->query("INSERT INTO `ploopi_mod_rssfeed` VALUES (0, 'http://www.sumhit-referencement.com/logs/rss.xml', 'Weblog Sumhit. Actualité du référencement et des moteurs de recherche.', 'http://www.sumhit-referencement.com/', '', 0, 20040920190746, 43200, '', '', 0, $catid, {$_SESSION['ploopi']['userid']}, {$_SESSION['ploopi']['groupid']}, {$this->fields['id']})");

$db->query("INSERT INTO `ploopi_mod_rsscat` VALUES (0, '', 20040920190148, 'Programmation', {$_SESSION['ploopi']['userid']}, {$_SESSION['ploopi']['groupid']}, {$this->fields['id']})");
$catid = $db->insertid();
$db->query("INSERT INTO `ploopi_mod_rssfeed` VALUES (0, 'http://www.allhtml.com/news/news.xml', 'ALL HTML - Le Portail dédié aux Webmasters', 'http://www.allhtml.com/', '', 0, 20040920190724, 43200, '', '', 0, $catid, {$_SESSION['ploopi']['userid']}, {$_SESSION['ploopi']['groupid']}, {$this->fields['id']})");
$db->query("INSERT INTO `ploopi_mod_rssfeed` VALUES (0, 'http://www.nexen.net/news/backend.2.rss', 'PHP news', 'http://www.nexen.net/news/', '', 1, 20040920190728, 21600, '', '', 0, $catid, {$_SESSION['ploopi']['userid']}, {$_SESSION['ploopi']['groupid']}, {$this->fields['id']})");
$db->query("INSERT INTO `ploopi_mod_rssfeed` VALUES (0, 'http://www.php.net/news.rss', 'PHP: Hypertext Preprocessor', 'http://www.php.net/', '', 0, 20040920190729, 43200, '', '', 0, $catid, {$_SESSION['ploopi']['userid']}, {$_SESSION['ploopi']['groupid']}, {$this->fields['id']})");
$db->query("INSERT INTO `ploopi_mod_rssfeed` VALUES (0, 'http://developpeur.journaldunet.com/jdnetdev.xml', 'Journal du Net Développeurs', 'http://developpeur.journaldunet.com/', '', 0, 20040920190730, 43200, '', '', 0, $catid, {$_SESSION['ploopi']['userid']}, {$_SESSION['ploopi']['groupid']}, {$this->fields['id']})");
$db->query("INSERT INTO `ploopi_mod_rssfeed` VALUES (0, 'http://www.cybercodeur.net/site/utils/rss.php', '[C²] CYBERcodeur.net', 'http://www.cybercodeur.net', '', 0, 20040920190734, 43200, '', '', 0, $catid, {$_SESSION['ploopi']['userid']}, {$_SESSION['ploopi']['groupid']}, {$this->fields['id']})");
$db->query("INSERT INTO `ploopi_mod_rssfeed` VALUES (0, 'http://www.zdnet.fr/feeds/rss/builder/', 'Builder.fr', 'http://www.builder.fr/?rss', '', 0, 20040920190746, 43200, '', '', 0, $catid, {$_SESSION['ploopi']['userid']}, {$_SESSION['ploopi']['groupid']}, {$this->fields['id']})");
$db->query("INSERT INTO `ploopi_mod_rssfeed` VALUES (0, 'http://classes.scriptsphp.net/filrss', 'Classes.ScriptsPHP.org -- Fil RSS', 'http://classes.scriptsphp.org', '', 0, 20040920190824, 43200, '', '', 0, $catid, {$_SESSION['ploopi']['userid']}, {$_SESSION['ploopi']['groupid']}, {$this->fields['id']})");
$db->query("INSERT INTO `ploopi_mod_rssfeed` VALUES (0, 'http://xml.newsisfree.com//feeds/72/372.xml', 'PHPIndex - News Récents', 'http://www.phpindex.com/', '', 0, 20040920190824, 43200, '', '', 0, $catid, {$_SESSION['ploopi']['userid']}, {$_SESSION['ploopi']['groupid']}, {$this->fields['id']})");

$db->query("INSERT INTO `ploopi_mod_rsscat` VALUES (0, '', 20040920190148, 'Produits Informatiques', {$_SESSION['ploopi']['userid']}, {$_SESSION['ploopi']['groupid']}, {$this->fields['id']})");
$catid = $db->insertid();
$db->query("INSERT INTO `ploopi_mod_rssfeed` VALUES (0, 'http://www.zdnet.fr/feeds/rss/produits/guide/', 'Guides produits par ZDNet.fr', 'http://www.zdnet.fr/produits/?rss', '', 0, 20040920190737, 43200, '', '', 0, $catid, {$_SESSION['ploopi']['userid']}, {$_SESSION['ploopi']['groupid']}, {$this->fields['id']})");

$db->query("INSERT INTO `ploopi_mod_rsscat` VALUES (0, '', 20040920190148, 'Economie', {$_SESSION['ploopi']['userid']}, {$_SESSION['ploopi']['groupid']}, {$this->fields['id']})");
$catid = $db->insertid();
$db->query("INSERT INTO `ploopi_mod_rssfeed` VALUES (0, 'http://manchettes.branchez-vous.com/benefice-net.xml', 'bénéfice.net - L\'économie de la technologie', 'http://benefice-net.branchez-vous.com/', '', 0, 20041009011027, 43200, '', '', 0, $catid, {$_SESSION['ploopi']['userid']}, {$_SESSION['ploopi']['groupid']}, {$this->fields['id']})");
$db->query("INSERT INTO `ploopi_mod_rssfeed` VALUES (0, 'http://xml.newsisfree.com/feeds/19/619.xml', 'EuroNews: Business (Français)', 'http://www.euronews.net/create_html.php?page=accueil_eco&langue=fr', '', 0, 20040920190805, 43200, '', '', 0, $catid, {$_SESSION['ploopi']['userid']}, {$_SESSION['ploopi']['groupid']}, {$this->fields['id']})");
$db->query("INSERT INTO `ploopi_mod_rssfeed` VALUES (0, 'http://blogue.benefice.net/xml/rss.xml', 'benefice.net - les tendances', 'http://blogue.benefice.net/', '', 0, 20040920190830, 43200, '', '', 0, $catid, {$_SESSION['ploopi']['userid']}, {$_SESSION['ploopi']['groupid']}, {$this->fields['id']})");

$db->query("INSERT INTO `ploopi_mod_rsscat` VALUES (0, '', 20040920190148, 'NTIC', {$_SESSION['ploopi']['userid']}, {$_SESSION['ploopi']['groupid']}, {$this->fields['id']})");
$catid = $db->insertid();
$db->query("INSERT INTO `ploopi_mod_rssfeed` VALUES (0, 'http://manchettes.branchez-vous.com/branchez-vous.xml', 'BRANCHEZ-VOUS.com - Nouvelles Technologies', 'http://www.branchez-vous.com/', '', 0, 20040920190736, 43200, '', '', 0, $catid, {$_SESSION['ploopi']['userid']}, {$_SESSION['ploopi']['groupid']}, {$this->fields['id']})");
$db->query("INSERT INTO `ploopi_mod_rssfeed` VALUES (0, 'http://ntic.org/nouvelles/nouvelles_rss.php', 'NTIC.ORG', 'http://ntic.org/', '', 0, 20040920190741, 43200, '', '', 0, $catid, {$_SESSION['ploopi']['userid']}, {$_SESSION['ploopi']['groupid']}, {$this->fields['id']})");

$db->query("INSERT INTO `ploopi_mod_rsscat` VALUES (0, '', 20040920190148, 'Matériel', {$_SESSION['ploopi']['userid']}, {$_SESSION['ploopi']['groupid']}, {$this->fields['id']})");
$catid = $db->insertid();
$db->query("INSERT INTO `ploopi_mod_rssfeed` VALUES (0, 'http://www.configspc.com/backend.php', 'Tout sur le PC : monter son ordinateur PC, comparatif, acheter et choisir un PC portable...', 'http://www.configspc.com', '', 0, 20040920190748, 43200, '', '', 0, $catid, {$_SESSION['ploopi']['userid']}, {$_SESSION['ploopi']['groupid']}, {$this->fields['id']})");
$db->query("INSERT INTO `ploopi_mod_rssfeed` VALUES (0, 'http://xml.newsisfree.com/feeds/32/1032.xml', 'Hardware.fr', 'http://www.hardware.fr/html/news/', '', 0, 20040920190802, 43200, '', '', 0, $catid, {$_SESSION['ploopi']['userid']}, {$_SESSION['ploopi']['groupid']}, {$this->fields['id']})");
$db->query("INSERT INTO `ploopi_mod_rssfeed` VALUES (0, 'http://xml.newsisfree.com/feeds/35/535.xml', 'VTR-Hardware', 'http://www.vtr-hardware.com/', '', 0, 20040920190804, 43200, '', '', 0, $catid, {$_SESSION['ploopi']['userid']}, {$_SESSION['ploopi']['groupid']}, {$this->fields['id']})");
$db->query("INSERT INTO `ploopi_mod_rssfeed` VALUES (0, 'http://xml.newsisfree.com/feeds/33/1033.xml', 'Présence PC', 'http://www.presence-pc.com/', '', 0, 20040920190804, 43200, '', '', 0, $catid, {$_SESSION['ploopi']['userid']}, {$_SESSION['ploopi']['groupid']}, {$this->fields['id']})");

$db->query("INSERT INTO `ploopi_mod_rsscat` VALUES (0, '', 20040920190148, 'Sport', {$_SESSION['ploopi']['userid']}, {$_SESSION['ploopi']['groupid']}, {$this->fields['id']})");
$catid = $db->insertid();
$db->query("INSERT INTO `ploopi_mod_rssfeed` VALUES (0, 'http://www.rtl.fr/referencement/rtlsport.asp', 'RTL Sport', 'http://www.rtl.fr/rtlsport', '', 0, 20040920190749, 43200, '', '', 0, $catid, {$_SESSION['ploopi']['userid']}, {$_SESSION['ploopi']['groupid']}, {$this->fields['id']})");
$db->query("INSERT INTO `ploopi_mod_rssfeed` VALUES (0, 'http://www.sportstrategies.com/backend.php', 'Sportstrategies', 'http://www.sportstrategies.com/', '', 0, 20040920190749, 43200, '', '', 0, $catid, {$_SESSION['ploopi']['userid']}, {$_SESSION['ploopi']['groupid']}, {$this->fields['id']})");
$db->query("INSERT INTO `ploopi_mod_rssfeed` VALUES (0, 'http://www.cahiersdufootball.com/breves_rss/', 'Les brèves des Cahiers du football', 'http://www.cahiersdufootball.net', '', 0, 20040920190750, 43200, '', '', 0, $catid, {$_SESSION['ploopi']['userid']}, {$_SESSION['ploopi']['groupid']}, {$this->fields['id']})");
$db->query("INSERT INTO `ploopi_mod_rssfeed` VALUES (0, 'http://xml.newsisfree.com//feeds/21/8521.xml', 'Eurosport.fr: AthlÃƒÂ©tisme', 'http://www.eurosport.fr/home/pages/V4/L3/S6/sport_Lng3_Spo6.shtml', '', 0, 20040920190806, 43200, '', '', 0, $catid, {$_SESSION['ploopi']['userid']}, {$_SESSION['ploopi']['groupid']}, {$this->fields['id']})");
$db->query("INSERT INTO `ploopi_mod_rssfeed` VALUES (0, 'http://xml.newsisfree.com//feeds/23/8523.xml', 'Eurosport.fr: Autres sports', 'http://www.eurosport.fr/home/pages/V4/L3/othersport_Lng3.shtml', '', 0, 20040920190807, 43200, '', '', 0, $catid, {$_SESSION['ploopi']['userid']}, {$_SESSION['ploopi']['groupid']}, {$this->fields['id']})");
$db->query("INSERT INTO `ploopi_mod_rssfeed` VALUES (0, 'http://xml.newsisfree.com//feeds/25/8525.xml', 'Eurosport.fr: Alternative Sports', 'http://www.eurosport.fr/home/pages/V3/L3/F14/multimedia_Lng3_Fml14.shtml', '', 0, 20040920190807, 43200, '', '', 0, $catid, {$_SESSION['ploopi']['userid']}, {$_SESSION['ploopi']['groupid']}, {$this->fields['id']})");
$db->query("INSERT INTO `ploopi_mod_rssfeed` VALUES (0, 'http://xml.newsisfree.com//feeds/62/11762.xml', 'Actu DNA: Sport', 'http://actu.dna.fr/', '', 0, 20040920190808, 43200, '', '', 0, $catid, {$_SESSION['ploopi']['userid']}, {$_SESSION['ploopi']['groupid']}, {$this->fields['id']})");
$db->query("INSERT INTO `ploopi_mod_rssfeed` VALUES (0, 'http://xml.newsisfree.com//feeds/28/2828.xml', 'Edicom: Sport', 'http://www.edicom.ch', '', 0, 20040920190808, 43200, '', '', 0, $catid, {$_SESSION['ploopi']['userid']}, {$_SESSION['ploopi']['groupid']}, {$this->fields['id']})");
$db->query("INSERT INTO `ploopi_mod_rssfeed` VALUES (0, 'http://xml.newsisfree.com//feeds/30/8230.xml', 'La Libre Belgique: Sports', 'http://www.lalibre.be/les_titres.phtml?id=2', '', 0, 20040920190809, 43200, '', '', 0, $catid, {$_SESSION['ploopi']['userid']}, {$_SESSION['ploopi']['groupid']}, {$this->fields['id']})");
$db->query("INSERT INTO `ploopi_mod_rssfeed` VALUES (0, 'http://www.lalibre.be/rss/?section=2', 'Lalibre.be - Sports', 'http://www.lalibre.be/index.phtml?id=2', '', 0, 20040920190810, 43200, '', '', 0, $catid, {$_SESSION['ploopi']['userid']}, {$_SESSION['ploopi']['groupid']}, {$this->fields['id']})");
$db->query("INSERT INTO `ploopi_mod_rssfeed` VALUES (0, 'http://xml.newsisfree.com//feeds/03/803.xml', 'Sport24.com', 'http://www.sport24.com/', '', 0, 20040920190810, 43200, '', '', 0, $catid, {$_SESSION['ploopi']['userid']}, {$_SESSION['ploopi']['groupid']}, {$this->fields['id']})");

$db->query("INSERT INTO `ploopi_mod_rsscat` VALUES (0, '', 20040920190148, 'Sciences', {$_SESSION['ploopi']['userid']}, {$_SESSION['ploopi']['groupid']}, {$this->fields['id']})");
$catid = $db->insertid();
$db->query("INSERT INTO `ploopi_mod_rssfeed` VALUES (0, 'http://www.cite-sciences.fr/rss/sciences_actu_fr_20.xml', 'Cité des Sciences : Science Actualités', 'http://actualites.cite-sciences.fr', '', 1, 20040920190751, 43200, '', '', 0, $catid, {$_SESSION['ploopi']['userid']}, {$_SESSION['ploopi']['groupid']}, {$this->fields['id']})");
$db->query("INSERT INTO `ploopi_mod_rssfeed` VALUES (0, 'http://www.futura-sciences.com/services/rss/actu10.xml', 'Futura-Sciences.com - Actualités quotidiennes sur les Sciences et les Technologies', 'http://www.futura-sciences.com', '', 0, 20040920190752, 43200, '', '', 0, $catid, {$_SESSION['ploopi']['userid']}, {$_SESSION['ploopi']['groupid']}, {$this->fields['id']})");
$db->query("INSERT INTO `ploopi_mod_rssfeed` VALUES (0, 'http://www.maison-des-sciences.org/infos.rss', 'Maison des sciences', 'http://www.maison-des-sciences.org/', '', 0, 20040920190753, 43200, '', '', 0, $catid, {$_SESSION['ploopi']['userid']}, {$_SESSION['ploopi']['groupid']}, {$this->fields['id']})");
$db->query("INSERT INTO `ploopi_mod_rssfeed` VALUES (0, 'http://xml.newsisfree.com/feeds/98/898.xml', 'CyberSciences', 'http://www.cybersciences.com/cyber/3.0/3_0.asp', '', 0, 20041019021637, 43200, '', '', 0, $catid, {$_SESSION['ploopi']['userid']}, {$_SESSION['ploopi']['groupid']}, {$this->fields['id']})");

$db->query("INSERT INTO `ploopi_mod_rsscat` VALUES (0, '', 20040920190148, 'Jeux Vidéos', {$_SESSION['ploopi']['userid']}, {$_SESSION['ploopi']['groupid']}, {$this->fields['id']})");
$catid = $db->insertid();
$db->query("INSERT INTO `ploopi_mod_rssfeed` VALUES (0, 'http://www.nofrag.com/nofrag.rss', 'NoFrag', 'http://www.nofrag.com/', '', 0, 20040920190727, 21600, '', '', 0, $catid, {$_SESSION['ploopi']['userid']}, {$_SESSION['ploopi']['groupid']}, {$this->fields['id']})");
$db->query("INSERT INTO `ploopi_mod_rssfeed` VALUES (0, 'http://actupcjv.webdynamit.net/PHP-Nuke-6.9.1/html/backend.php', 'Actu PC Jeux video', 'http://actupcjv.webdynamit.net/PHP-Nuke-6.9.1/html', '', 0, 20040920190754, 43200, '', '', 0, $catid, {$_SESSION['ploopi']['userid']}, {$_SESSION['ploopi']['groupid']}, {$this->fields['id']})");
$db->query("INSERT INTO `ploopi_mod_rssfeed` VALUES (0, 'http://www.clubic.com/c/xml.php?type=demo', 'Clubic.com - Demos de Jeux', 'http://www.clubic.com/', '', 0, 20040920190754, 43200, '', '', 0, $catid, {$_SESSION['ploopi']['userid']}, {$_SESSION['ploopi']['groupid']}, {$this->fields['id']})");
$db->query("INSERT INTO `ploopi_mod_rssfeed` VALUES (0, 'http://www.clubic.com/c/xml.php?type=patch', 'Clubic.com - Patch de jeux', 'http://www.clubic.com/', '', 0, 20040920190755, 43200, '', '', 0, $catid, {$_SESSION['ploopi']['userid']}, {$_SESSION['ploopi']['groupid']}, {$this->fields['id']})");
$db->query("INSERT INTO `ploopi_mod_rssfeed` VALUES (0, 'http://www.clubic.com/c/xml.php?type=newsjeuxvideo', 'Clubic.com - Actualité Jeux Vidéo', 'http://www.clubic.com/', '', 0, 20041021183127, 43200, '', '', 0, $catid, {$_SESSION['ploopi']['userid']}, {$_SESSION['ploopi']['groupid']}, {$this->fields['id']})");
$db->query("INSERT INTO `ploopi_mod_rssfeed` VALUES (0, 'http://www0.gamekult.com/cobranding/rss/news.xml', 'Gamekult.com', 'http://www.gamekult.com/', '', 0, 20040920190756, 43200, '', '', 0, $catid, {$_SESSION['ploopi']['userid']}, {$_SESSION['ploopi']['groupid']}, {$this->fields['id']})");
$db->query("INSERT INTO `ploopi_mod_rssfeed` VALUES (0, 'http://www.jeuxvideo.com/rss/rss.xml', 'JeuxVideo.com', 'http://www.jeuxvideo.com/', '', 0, 20040920190757, 43200, '', '', 0, $catid, {$_SESSION['ploopi']['userid']}, {$_SESSION['ploopi']['groupid']}, {$this->fields['id']})");
$db->query("INSERT INTO `ploopi_mod_rssfeed` VALUES (0, 'http://manchettes.branchez-vous.com/jouez.xml', 'JOUEZ.com - Actualités', 'http://jouez.branchez-vous.com/', '', 0, 20041021183111, 43200, '', '', 0, $catid, {$_SESSION['ploopi']['userid']}, {$_SESSION['ploopi']['groupid']}, {$this->fields['id']})");

$db->query("INSERT INTO `ploopi_mod_rsscat` VALUES (0, '', 20040920190148, 'Peer To Peer', {$_SESSION['ploopi']['userid']}, {$_SESSION['ploopi']['groupid']}, {$this->fields['id']})");
$catid = $db->insertid();
$db->query("INSERT INTO `ploopi_mod_rssfeed` VALUES (0, 'http://www.ratiatum.com/rss/news.rss', 'Ratiatum.com', 'http://www.ratiatum.com/', '', 0, 20040920190732, 43200, '', '', 0, $catid, {$_SESSION['ploopi']['userid']}, {$_SESSION['ploopi']['groupid']}, {$this->fields['id']})");
$db->query("INSERT INTO `ploopi_mod_rssfeed` VALUES (0, 'http://xml.newsisfree.com//feeds/57/14457.xml', 'OpenFiles', 'http://www.open-files.com/', '', 0, 20040920190825, 43200, '', '', 0, $catid, {$_SESSION['ploopi']['userid']}, {$_SESSION['ploopi']['groupid']}, {$this->fields['id']})");
$db->query("INSERT INTO `ploopi_mod_rssfeed` VALUES (0, 'http://www.bucheron.net/weblogs/rss.php', 'WeBlogs P2P & NTIC', 'http://www.bucheron.net/weblogs/index.php', '', 0, 20040920190828, 43200, '', '', 0, $catid, {$_SESSION['ploopi']['userid']}, {$_SESSION['ploopi']['groupid']}, {$this->fields['id']})");

$db->query("INSERT INTO `ploopi_mod_rsscat` VALUES (0, '', 20040920190148, 'Santé', {$_SESSION['ploopi']['userid']}, {$_SESSION['ploopi']['groupid']}, {$this->fields['id']})");
$catid = $db->insertid();
$db->query("INSERT INTO `ploopi_mod_rssfeed` VALUES (0, 'http://www.exhausmed.com/rss/rss-exhausmed.php', 'Fil d\'informations exhausmed.com', 'http://www.exhausmed.com/', '', 0, 20040920190759, 43200, '', '', 0, $catid, {$_SESSION['ploopi']['userid']}, {$_SESSION['ploopi']['groupid']}, {$this->fields['id']})");

$db->query("INSERT INTO `ploopi_mod_rsscat` VALUES (0, '', 20040920190148, 'Sécurité', {$_SESSION['ploopi']['userid']}, {$_SESSION['ploopi']['groupid']}, {$this->fields['id']})");
$catid = $db->insertid();
$db->query("INSERT INTO `ploopi_mod_rssfeed` VALUES (0, 'http://www.zataz.com/rss/', 'ZATAZ.com', 'http://www.zataz.com/', '', 0, 20040920190731, 43200, '', '', 0, $catid, {$_SESSION['ploopi']['userid']}, {$_SESSION['ploopi']['groupid']}, {$this->fields['id']})");
$db->query("INSERT INTO `ploopi_mod_rssfeed` VALUES (0, 'http://www.digital-connexion.info/backend.php', 'Digital Connexion', 'http://www.digital-connexion.info', '', 0, 20040920190830, 43200, '', '', 0, $catid, {$_SESSION['ploopi']['userid']}, {$_SESSION['ploopi']['groupid']}, {$this->fields['id']})");

$db->query("INSERT INTO `ploopi_mod_rsscat` VALUES (0, '', 20040920190148, 'Films', {$_SESSION['ploopi']['userid']}, {$_SESSION['ploopi']['groupid']}, {$this->fields['id']})");
$catid = $db->insertid();
$db->query("INSERT INTO `ploopi_mod_rssfeed` VALUES (0, 'http://xml.newsisfree.com/feeds/46/8446.xml', 'Allociné: Actualité', 'http://www.allocine.fr/article/default.html', '', 0, 20040920190802, 43200, '', '', 0, $catid, {$_SESSION['ploopi']['userid']}, {$_SESSION['ploopi']['groupid']}, {$this->fields['id']})");

$db->query("INSERT INTO `ploopi_mod_rsscat` VALUES (0, '', 20040920190148, 'Actualités', {$_SESSION['ploopi']['userid']}, {$_SESSION['ploopi']['groupid']}, {$this->fields['id']})");
$catid = $db->insertid();
$db->query("INSERT INTO `ploopi_mod_rssfeed` VALUES (0, 'http://xml.newsisfree.com/feeds/92/1492.xml', 'Yahoo! News France: Multimédia - Informatique', 'http://fr.news.yahoo.com/101/', '', 0, 20040920190806, 43200, '', '', 0, $catid, {$_SESSION['ploopi']['userid']}, {$_SESSION['ploopi']['groupid']}, {$this->fields['id']})");
$db->query("INSERT INTO `ploopi_mod_rssfeed` VALUES (0, 'http://xml.newsisfree.com//feeds/16/616.xml', 'EuroNews (Français)', 'http://www.euronews.net/create_html.php?page=accueil_info&langue=fr', '', 0, 20041022011735, 43200, '', '', 0, $catid, {$_SESSION['ploopi']['userid']}, {$_SESSION['ploopi']['groupid']}, {$this->fields['id']})");
$db->query("INSERT INTO `ploopi_mod_rssfeed` VALUES (0, 'http://www.latribune.fr/rss', 'La Tribune.fr', 'http://www.latribune.fr', '', 0, 20041022011737, 43200, '', '', 0, $catid, {$_SESSION['ploopi']['userid']}, {$_SESSION['ploopi']['groupid']}, {$this->fields['id']})");
$db->query("INSERT INTO `ploopi_mod_rssfeed` VALUES (0, 'http://www.lalibre.be/rss/?section=10', 'Lalibre.be - L\'actu', 'http://www.lalibre.be/index.phtml?id=10', '', 0, 20041022011742, 43200, '', '', 0, $catid, {$_SESSION['ploopi']['userid']}, {$_SESSION['ploopi']['groupid']}, {$this->fields['id']})");
$db->query("INSERT INTO `ploopi_mod_rssfeed` VALUES (0, 'http://xml.newsisfree.com//feeds/96/3296.xml', 'Le Figaro - International', 'http://www.lefigaro.fr/international/', '', 0, 20041022011747, 43200, '', '', 0, $catid, {$_SESSION['ploopi']['userid']}, {$_SESSION['ploopi']['groupid']}, {$this->fields['id']})");
$db->query("INSERT INTO `ploopi_mod_rssfeed` VALUES (0, 'http://xml.newsisfree.com//feeds/67/5567.xml', 'Le Matin: Monde', 'http://www.lematin.ch/nwmatinhome/nwmatinheadactu/actu_monde.html', '', 0, 20041022011749, 43200, '', '', 0, $catid, {$_SESSION['ploopi']['userid']}, {$_SESSION['ploopi']['groupid']}, {$this->fields['id']})");
$db->query("INSERT INTO `ploopi_mod_rssfeed` VALUES (0, 'http://xml.newsisfree.com//feeds/20/820.xml', 'Le Monde : International', 'http://www.lemonde.fr/web/sequence/0,2-3210,1-0,0.html', '', 0, 20041022011751, 43200, '', '', 0, $catid, {$_SESSION['ploopi']['userid']}, {$_SESSION['ploopi']['groupid']}, {$this->fields['id']})");
$db->query("INSERT INTO `ploopi_mod_rssfeed` VALUES (0, 'http://www.monde-diplomatique.fr/recents.xml', 'Le Monde diplomatique', 'http://www.monde-diplomatique.fr/', '', 0, 20041022001303, 43200, '', '', 0, $catid, {$_SESSION['ploopi']['userid']}, {$_SESSION['ploopi']['groupid']}, {$this->fields['id']})");
$db->query("INSERT INTO `ploopi_mod_rssfeed` VALUES (0, 'http://xml.newsisfree.com//feeds/63/3163.xml', 'Le Monde: Articles Recommendés', 'http://www.lemonde.fr/', '', 0, 20041021183124, 43200, '', '', 0, $catid, {$_SESSION['ploopi']['userid']}, {$_SESSION['ploopi']['groupid']}, {$this->fields['id']})");
$db->query("INSERT INTO `ploopi_mod_rssfeed` VALUES (0, 'http://xml.newsisfree.com//feeds/92/3992.xml', 'Le Soir: Monde', 'http://www.lesoir.be/rubriques/mond/page_5178.shtml', '', 0, 20041021183113, 43200, '', '', 0, $catid, {$_SESSION['ploopi']['userid']}, {$_SESSION['ploopi']['groupid']}, {$this->fields['id']})");
$db->query("INSERT INTO `ploopi_mod_rssfeed` VALUES (0, 'http://xml.newsisfree.com//feeds/37/537.xml', 'Libération', 'http://www.liberation.fr/index.php', '', 0, 20041021183113, 43200, '', '', 0, $catid, {$_SESSION['ploopi']['userid']}, {$_SESSION['ploopi']['groupid']}, {$this->fields['id']})");
$db->query("INSERT INTO `ploopi_mod_rssfeed` VALUES (0, 'http://www.rtl.fr/referencement/rtl.asp', 'RTL Info', 'http://www.rtl.fr', '', 0, 20041021183110, 43200, '', '', 0, $catid, {$_SESSION['ploopi']['userid']}, {$_SESSION['ploopi']['groupid']}, {$this->fields['id']})");
$db->query("INSERT INTO `ploopi_mod_rssfeed` VALUES (0, 'http://www.rtl.fr/referencement/rtlinfo.asp', 'RTL Info', 'http://www.rtl.fr/rtlinfo', '', 0, 20040920190822, 43200, '', '', 0, $catid, {$_SESSION['ploopi']['userid']}, {$_SESSION['ploopi']['groupid']}, {$this->fields['id']})");

$db->query("INSERT INTO `ploopi_mod_rsscat` VALUES (0, '', 20040920190148, 'Microsoft', {$_SESSION['ploopi']['userid']}, {$_SESSION['ploopi']['groupid']}, {$this->fields['id']})");
$catid = $db->insertid();
$db->query("INSERT INTO `ploopi_mod_rssfeed` VALUES (0, 'http://www.dotnet-fr.org/backend.php3', 'DotNET-fr', 'http://www.dotnet-fr.org', '', 0, 20040920190825, 43200, '', '', 0, $catid, {$_SESSION['ploopi']['userid']}, {$_SESSION['ploopi']['groupid']}, {$this->fields['id']})");

$db->query("INSERT INTO `ploopi_mod_rsscat` VALUES (0, '', 20040920190148, 'WIFI', {$_SESSION['ploopi']['userid']}, {$_SESSION['ploopi']['groupid']}, {$this->fields['id']})");
$catid = $db->insertid();
$db->query("INSERT INTO `ploopi_mod_rssfeed` VALUES (0, 'http://www.belgiquemobile.be/rss/backend.php', 'BelgiqueMobile.be', 'http://www.belgiquemobile.be/', '', 0, 20040920190831, 43200, '', '', 0, $catid, {$_SESSION['ploopi']['userid']}, {$_SESSION['ploopi']['groupid']}, {$this->fields['id']})");
$db->query("INSERT INTO `ploopi_mod_rssfeed` VALUES (0, 'http://www.wireless-fr.org/spip/backend.php3', 'Fédération France Wireless', 'http://www.wireless-fr.org/spip/', '', 0, 20040920190831, 43200, '', '', 0, $catid, {$_SESSION['ploopi']['userid']}, {$_SESSION['ploopi']['groupid']}, {$this->fields['id']})");
$db->query("INSERT INTO `ploopi_mod_rssfeed` VALUES (0, 'http://www.canardwifi.com/rss.php', 'Canard Wifi, premier blog francais sur les reseaux sans fil 802.11 (wi-fi ou wifi, ...)', 'http://www.canardwifi.com/index.php', '', 0, 20040920190800, 43200, '', '', 0, $catid, {$_SESSION['ploopi']['userid']}, {$_SESSION['ploopi']['groupid']}, {$this->fields['id']})");
$db->query("INSERT INTO `ploopi_mod_rssfeed` VALUES (0, 'http://www.wlanfr.net/backend.rss', 'News Wlanfr.net', 'http://www.wlanfr.net', '', 0, 20040920190801, 43200, '', '', 0, $catid, {$_SESSION['ploopi']['userid']}, {$_SESSION['ploopi']['groupid']}, {$this->fields['id']})");



$db->multiplequeries($sql);

?>