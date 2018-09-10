UPDATE ploopi_mod_doc_parser SET `path` = 'pdftotext -enc Latin1 -nopgbrk %f -' WHERE id = 2;
UPDATE ploopi_mod_doc_parser SET `path` = 'cat %f | iconv -c -f $(file -b --mime-encoding %f) -t ISO-8859-1//TRANSLIT' WHERE id = 3;
UPDATE ploopi_mod_doc_parser SET `path` = 'unoconv --format=txt --stdout %f' WHERE id IN(7,8,13,14,15);

