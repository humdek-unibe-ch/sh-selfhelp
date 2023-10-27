/*
give the user priviliges to create, delete, insert, update
remove the custom priveleges sql script
*/
CREATE USER '__experiment_name__'@'localhost' IDENTIFIED BY '__password__';
CREATE DATABASE __experiment_name__ CHARACTER SET utf8 COLLATE utf8_general_ci;
GRANT all privileges ON __experiment_name__.* TO __experiment_name__@localhost;
FLUSH PRIVILEGES;