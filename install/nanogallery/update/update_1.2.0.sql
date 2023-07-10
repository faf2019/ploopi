ALTER TABLE `ploopi_mod_nanogallery` ADD `flatAlbums` TINYINT(1) NOT NULL DEFAULT '0' AFTER `useAlbums`;
ALTER TABLE `ploopi_mod_nanogallery`  ALTER `breadcrumbAutoHideTopLevel` SET DEFAULT '0';
ALTER TABLE `ploopi_mod_nanogallery`  ALTER `breadcrumbOnlyCurrentLevel` SET DEFAULT '0';
ALTER TABLE `ploopi_mod_nanogallery`  ALTER `galleryFilterTags` SET DEFAULT 'true';

