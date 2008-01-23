# Update from the 1.8.3 version
# Add the table prefix if needed
# ---------------------------------
ALTER TABLE sites ADD COLUMN stopped TINYINT(1) NOT NULL DEFAULT 0;
ALTER TABLE site_page ADD COLUMN days INT(4) NOT NULL DEFAULT 0;
ALTER TABLE site_page ADD COLUMN depth INT(4) NOT NULL DEFAULT 5;
ALTER TABLE site_page CHANGE num_page links INT(4) NOT NULL DEFAULT 5;
DROP TABLE sites_days_upd;

