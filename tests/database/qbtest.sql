--
-- Database: `qbtest`
--
CREATE DATABASE IF NOT EXISTS `qbtest`;
USE `qbtest`;

CREATE TABLE IF NOT EXISTS `candidates` (
  `id` int(11) NOT NULL,
  `voornaam` varchar(255) NOT NULL,
  `achternaam` varchar(255) NOT NULL,
  `tussenvoegsel` varchar(255) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;

ALTER TABLE `candidates`
ADD PRIMARY KEY (`id`);

ALTER TABLE `candidates`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=1;

INSERT INTO `candidates` (`id`, `voornaam`, `achternaam`, `tussenvoegsel`) VALUES
(1, 'Dennis', 'Slimmers', 'geen'),
(2, 'Bas', 'Ploeg', 'van der'),
(3, 'Xander', 'Dijk', 'geen'),
(4, 'Edwin', 'Leeuwen', 'van'),
(5, 'Frank', 'Nouland', 'van den')


