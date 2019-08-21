CREATE DATABASE IF NOT EXISTS flagsdb;

USE flagsdb;

CREATE TABLE IF NOT EXISTS flagstable (
       id int(11) NOT NULL AUTO_INCREMENT,
       post_nr int(11) NOT NULL,
       board varchar(5) NOT NULL,
       region varchar(255) NOT NULL,
       PRIMARY KEY (`id`)
       );

CREATE USER IF NOT EXISTS flags@localhost IDENTIFIED BY 'default';
GRANT ALL PRIVILEGES ON flagsdb.* to flags@localhost;
