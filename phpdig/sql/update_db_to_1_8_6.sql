# Update from the 1.8.5 version
# Add the table prefix if needed
# ---------------------------------
ALTER TABLE excludes DROP INDEX ex_id;
ALTER TABLE includes DROP INDEX in_id;
ALTER TABLE keywords DROP INDEX key_id;
ALTER TABLE logs DROP INDEX l_id;
ALTER TABLE sites DROP INDEX site_id;
ALTER TABLE spider DROP INDEX spider_id;
ALTER TABLE tempspider DROP INDEX id;
