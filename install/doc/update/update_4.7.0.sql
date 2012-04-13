UPDATE `ploopi_mod_doc_folder` SET `readonly` = `readonly_content`;
ALTER TABLE `ploopi_mod_doc_folder` DROP `readonly_content`;
UPDATE `ploopi_mod_doc_file` fi, `ploopi_mod_doc_folder` fo SET fi.`readonly` = fo.`readonly` WHERE fi.`id_folder` = fo.`id`;
