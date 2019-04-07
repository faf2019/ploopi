UPDATE ploopi_mod_doc_parser SET `path` = 'pdftotext -nopgbrk %f -' WHERE id = 2;
UPDATE ploopi_mod_doc_parser SET `path` = 'cat %f' WHERE id = 3;
UPDATE ploopi_mod_doc_parser SET `path` = 'unoconv --format=txt --stdout %f' WHERE id IN(7,8,13,14,15);
INSERT INTO `ploopi_mod_doc_parser` (`label`, `path`, `extension`) VALUES
('Office Open XML XLSX', 'unoconv --format=txt --stdout %f', 'xlsx'),
('Office Open XML DOCX', 'unoconv --format=txt --stdout %f', 'docx');
