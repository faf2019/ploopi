-- Albums : colonnes lues et écrites par le module, jamais créées par le schéma.
-- Sans elles, l'enregistrement d'une galerie échouait (colonne inconnue).
ALTER TABLE `ploopi_mod_nanogallery` ADD `useAlbums` TINYINT(1) NOT NULL DEFAULT 0 AFTER `id_folder`;
ALTER TABLE `ploopi_mod_nanogallery` ADD `flatAlbums` TINYINT(1) NOT NULL DEFAULT 0 AFTER `useAlbums`;
