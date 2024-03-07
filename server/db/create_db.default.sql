/*
give the user priviliges to create, delete, insert, update
remove the custom priveleges sql script
*/
INSTALL PLUGIN validate_password SONAME 'validate_password.so';
SET GLOBAL validate_password.policy=1;
CREATE USER '__project_name__'@'localhost' IDENTIFIED WITH mysql_native_password BY '__password__';
CREATE DATABASE __project_name__ CHARACTER SET utf8 COLLATE utf8_general_ci;
GRANT all privileges ON __project_name__.* TO __project_name__@localhost;
FLUSH PRIVILEGES;