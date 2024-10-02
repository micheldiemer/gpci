-- --------------------------------------------------------
--
-- Base de données
--


-- DROP DATABASE IF EXISTS gpci_preprod;
-- CREATE DATABASE gpci_preprod CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- --------------------------------------------------------
--
-- Structure de la table `salles`
--

DROP TABLE IF EXISTS salles;
CREATE TABLE salles (nom varchar(25) NOT NULL, id int(11) NOT NULL AUTO_INCREMENT, PRIMARY KEY (id));

--
-- Données de la table `salles`
--
INSERT INTO salles (id, nom) VALUES (1, "Salle 1");
INSERT INTO salles (id, nom) VALUES (2, "Salle 2");
INSERT INTO salles (id, nom) VALUES (3, "Salle 3 - Informatique");
INSERT INTO salles (id, nom) VALUES (4, "Salle 4");
INSERT INTO salles (id, nom) VALUES (5, "Salle 5");
INSERT INTO salles (id, nom) VALUES (6, "Salle 6 - Réunion");
INSERT INTO salles (id, nom) VALUES (7, "Salle 7");
INSERT INTO salles (id, nom) VALUES (8, "Salle 8");
INSERT INTO salles (id, nom) VALUES (99, "(voir écran)");

-- INSERT INTO salles (id, nom) VALUES (9, "Bureau Direction");
-- INSERT INTO salles (id, nom) VALUES (10, "Salle Formateurs");
-- INSERT INTO salles (id, nom) VALUES (11, "Cafétéria");

--
-- Structure de la table `cours`
--
ALTER TABLE cours ADD id_Salles int(11) NOT NULL DEFAULT 99;
UPDATE cours set id_Salles = 99;
ALTER TABLE cours ADD CONSTRAINT FK_Cours_id_Salles FOREIGN KEY (id_Salles) REFERENCES salles (id);


ALTER TABLE cours ALTER COLUMN assignationSent SET DEFAULT 0;