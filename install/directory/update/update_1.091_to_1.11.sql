DELETE dims_mod_directory_contact
FROM dims_mod_directory_contact
LEFT JOIN dims_user u ON u.id = dims_mod_directory_contact.id_user 
WHERE isnull(u.login);
