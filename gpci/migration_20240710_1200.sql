-- Version du serveur :  5.6.25-log
-- Version de PHP :  5.6.11

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;



-- --------------------------------------------------------
--
-- Structure de la table `salles`
--

CREATE TABLE IF NOT EXISTS `salles` (
  `nom` varchar(25) NOT NULL,
  `id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

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
INSERT INTO salles (id, nom) VALUES (9, "Bureau Direction");
INSERT INTO salles (id, nom) VALUES (10, "Salle Formateurs");
INSERT INTO salles (id, nom) VALUES (11, "Cafétéria");
INSERT INTO salles (id, nom) VALUES (99, "Une salle de cours");

--
-- Structure de la table `cours`
--
ALTER TABLE `cours` ADD `id_Salles` int(11) NOT NULL;
UPDATE cours set id_Salles = 99;
ALTER TABLE `cours` ADD CONSTRAINT  `FK_Cours_id_Salles` FOREIGN KEY (`id_Salles`) REFERENCES `salles` (`id`);


/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
