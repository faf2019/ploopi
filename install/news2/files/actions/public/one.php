<?php

/**
 * Affichage d'une news
 *
 */

$news = new ploopi\news2\news2();

/**
 * On vérifie que l'identifiant de news est valide
 */
if (!empty($_GET['id']) && is_numeric($_GET['id']) && $news->open($_GET['id'])) {
	// Incrémente le nombre de clicks pour la news
    if (ploopi\session::setflag('news_nbclick',$_GET['id'])) {
        $news->fields['nbclick']++;
        $news->save();
    }

    echo ploopi\skin::get()->open_simplebloc(ploopi\str::htmlentities($news->fields['title']));

    $localdate = ploopi\date::timestamp2local($news->fields['date_publish']);
    $source = ($news->fields['source']=='') ? 'Inconnue' : $news->fields['source'];
    $newscat = new ploopi\news2\news2cat();
    $cat = ($newscat->open($news->fields['id_cat'])) ? $newscat->fields['title'] : 'Inconnue';

    ?>
    <div class="news">
		<table style="width:100%;<?php if ($news->fields['hot']) echo 'border:2px solid red;'; ?>">
			<tr>
				<td style="width:35%;"><img src="./<?php echo $news->fields['background'];?>" 
					style="display:block;max-width:90%;margin:0 auto;"/>
				</td>
				<td>
					<div><b>Publié le</b> <?php echo ploopi\str::htmlentities($localdate['date']); ?> 
						à <?php echo ploopi\str::htmlentities($localdate['time']); ?>
						<?php
							$user = new ploopi\user();
							if ($user->open($news->fields['id_user'])) 
								echo " par {$user->fields['firstname']} {$user->fields['lastname']}";
						?>
					</div>
					<div><b><?php echo 'Catégorie' ?></b>:&nbsp;<?php echo ploopi\str::htmlentities($cat); ?></div>
					<div><b><?php echo 'Source'; ?></b>:&nbsp;<?php echo ploopi\str::htmlentities($source); ?></div>
					<?php
					if ($news->fields['url']!='') {
						if ($news->fields['urltitle'] == '') {$urltitle = 'Lien';} else {$urltitle = $news->fields['urltitle'];}
						?>
						    <div><b><?php echo 'Lien'; ?></b>:&nbsp;
								<a target="_blank" href="<?php echo $news->fields['url']; ?>">
									<?php echo ploopi\str::htmlentities($urltitle); ?>
								</a>
							</div>
						<?php
					}
					?>
					<div><b><?php echo 'Lectures'; ?></b>:&nbsp;<?php echo $news->fields['nbclick']; ?></div>
					<div><?php echo $news->fields['content']; ?></div>
				</td>
			</tr>
		</table>
    </div>
    <div style="clear:both;border-top:1px solid #c0c0c0;">
        <?php ploopi\annotation::display(ploopi\news2\tools::OBJECT_NEWS2, $news->fields['id'], $news->fields['title']); ?>
    </div>
    <?php

    echo ploopi\skin::get()->close_simplebloc();
}


