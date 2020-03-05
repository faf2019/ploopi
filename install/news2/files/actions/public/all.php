<?php
/**
 * Affichage de la liste des news
 */
use ploopi\news2;

echo ploopi\skin::get()->open_simplebloc('Liste des news');

$moduleid = $this->getModuleId();
$newsList = news2\tools::getNews($moduleid);

if ($newsList->numrows()>0)
{
	$i=0;
    while ($fields = $newsList->fetchrow()) {
		// Retour à gauche quelles que soient les hauteurs relatives des news précédentes
		if (++$i % 2) echo '<div style="clear: both;font-size:1%;">&nbsp;</div>';

        $source = ($fields['source']=='') ? 'Inconnue' : $fields['source'];
        $localdate = ploopi\date::timestamp2local($fields['date_publish']);
		$url = ploopi\crypt::urlencode("admin.php?entity=public&action=one&id={$fields['id']}"); 
 		?> 
		<div class="news" style="width:49%; float:left;"> 
		<?php
       	echo ploopi\skin::get()->open_simplebloc('<a href="'.$url.'">'.ploopi\str::htmlentities($fields['title']).'</a>');
        ?>
			<table style="width:100%;<?php if ($fields['hot']) echo 'border:2px solid red;'; ?>">
				<tr>
					<td style="width:20%;"><img src="./<?php echo $fields['background'];?>" style="display:block;max-width:90%;margin:0 auto;"/></td>
					<td>
						<div><b>Publié le</b> <?php echo ploopi\str::htmlentities($localdate['date']); ?> à <?php 
							echo ploopi\str::htmlentities($localdate['time']); 
							$user = new ploopi\user();
							if ($user->open($fields['id_user'])) 
								echo ploopi\str::htmlentities(" par {$user->fields['firstname']} {$user->fields['lastname']}");
							?>
						</div>
						<div><b><?php echo 'Catégorie' ?></b>:&nbsp;<?php echo ploopi\str::htmlentities($fields['titlecat']); ?></div>
						<div><b><?php echo 'Source'; ?></b>:&nbsp;<?php echo ploopi\str::htmlentities($source); ?></div>
						<?php
						if ($fields['url']!='')
						{
							if ($fields['urltitle']=='') {$urltitle = 'Lien';} else {$urltitle = $fields['urltitle'];}
							?>
							<div><b>
							<?php echo 'Lien'; ?></b>:&nbsp;<a target="_blank" href="<?php echo $fields['url']; ?>">
							<?php echo ploopi\str::htmlentities($urltitle); ?></a></div>
							<?php
						}
						?>
						<div><b><?php echo 'Lectures'; ?></b>:&nbsp;<?php echo $fields['nbclick']; ?></div>
						<div><?php echo ploopi\str::htmlentities($fields['content']); ?></div>
					</td>
				</tr>
			</table>
			<div style="clear:both;border-top:1px solid #c0c0c0;">
				<?php ploopi\annotation::display(news2\tools::OBJECT_NEWS2, $fields['id'], $fields['title']); ?>
			</div>
		</div>
        <?php
        echo ploopi\skin::get()->close_simplebloc();
	}
} else {
    echo ploopi\skin::get()->open_simplebloc();
    ?><div class="news"><div>Aucune news</div></div><?php
    echo ploopi\skin::get()->close_simplebloc();
}

echo ploopi\skin::get()->close_simplebloc();

