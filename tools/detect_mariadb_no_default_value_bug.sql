/** Champs à corriger */
SELECT  table_schema, table_name, column_name
FROM    information_schema.columns
WHERE   table_schema = 'ploopi'
AND     ISNULL(column_default)
AND     is_nullable = 'NO'
AND     extra != 'auto_increment';

/** Champs à corriger (pas obligatoire) */
SELECT  table_schema, table_name, column_name
FROM    information_schema.columns
WHERE   table_schema = 'ploopi'
AND     is_nullable = 'YES';

AND     ISNULL(column_default)


ALTER TABLE `ploopi_mod_doc_file_draft`
    CHANGE `md5id` `md5id` CHAR(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',



SELECT  *
FROM    information_schema.columns
WHERE   table_schema = 'ploopi'
AND     ISNULL(column_default)
AND     is_nullable = 'NO'
AND     extra != 'auto_increment';

SELECT  GROUP_CONCAT(
            CONCAT('UPDATE `',table_name,'` SET `',column_name,'` = ',IF(column_type LIKE '%int%' OR column_type LIKE '%double%' OR column_type LIKE '%float%', '0 ', '\'\''),' WHERE ISNULL(`',column_name,'`);\n',
            'ALTER TABLE `',
            table_name,
            '` CHANGE `',
            column_name,
            '` `',
            column_name,
            '` ',
            column_type,
            ' ',
            'NOT NULL DEFAULT ',
            IF(column_type LIKE '%char%' OR column_type LIKE '%text%' OR column_type LIKE '%blob%', '\'\' ', ''),
            IF(column_type LIKE '%int%' OR column_type LIKE '%double%' OR column_type LIKE '%float%', '0 ', ''),
            extra,
            ' COMMENT \'',
            REPLACE(column_comment, '\'', '\\\''),
            '\' ;')
        SEPARATOR '\n') as script
FROM    information_schema.columns
WHERE   table_schema = 'ploopi'
AND     ((ISNULL(column_default)
AND     is_nullable = 'NO') OR is_nullable = 'YES')
AND     extra != 'auto_increment'
