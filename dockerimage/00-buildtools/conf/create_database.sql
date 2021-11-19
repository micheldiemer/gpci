create database if not exists gpci;
use gpci;
CREATE USER 'gpciweb'@'localhost' IDENTIFIED BY 'VGD1SGX3KR0G7PRJ';
GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, INDEX, DROP, ALTER, CREATE TEMPORARY TABLES, LOCK TABLES ON gpci.*  TO 'gpciweb'@'localhost';

CREATE TABLE animals (      id MEDIUMINT NOT NULL AUTO_INCREMENT,      name CHAR(30) NOT NULL,      PRIMARY KEY (id) );
INSERT INTO animals (name) VALUES ('chien'),('chat'),('pinguin'), ('baleine'),('autruche');