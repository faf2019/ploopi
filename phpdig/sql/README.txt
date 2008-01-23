If there is no update file for a particular version of PhpDig, 
then there were no changes to the database tables for that 
version of PhpDig.

For example, in the below list there were no changes to the 
database tables from PhpDig version 1.8.2 to version 1.8.3, 
as there is no update_db_to_1_8_3.sql file.

sql/init_db.sql
sql/update_db.sql
sql/update_db_to_1_4_4.sql
sql/update_db_to_1_4_5.sql
sql/update_db_to_1_6.sql
sql/update_db_to_1_6_1.sql
sql/update_db_to_1_8_1.sql
sql/update_db_to_1_8_2.sql
sql/update_db_to_1_8_4.sql
sql/update_db_to_1_8_5.sql
sql/update_db_to_1_8_6.sql

The init_db.sql file is for a fresh install. The update_db.sql 
file lists the most recent table changes. The update_db_to_*.sql 
files list the changes between particular versions of PhpDig.

