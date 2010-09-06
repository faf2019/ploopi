UPDATE ploopi_mod_forms_reply_field frf, ploopi_mod_forms_field ff
SET frf.value = concat(substring(frf.value,7,4), substring(frf.value,4,2), substring(frf.value,1,2))
WHERE frf.id_field = ff.id
AND ff.format = 'date';

ALTER TABLE `ploopi_mod_forms_reply` ADD INDEX ( `date_validation` );
ALTER TABLE `ploopi_mod_forms_reply` ADD INDEX ( `id_record` );


ALTER TABLE `ploopi_mod_forms_form` DROP `tablename`;