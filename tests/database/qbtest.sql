--
-- Database: `qbtest`
--
CREATE DATABASE IF NOT EXISTS `qbtest`;
USE `qbtest`;

CREATE TABLE IF NOT EXISTS `candidates` (
  `id` int(11) NOT NULL,
  `first_name` varchar(255) NOT NULL,
  `last_name` varchar(255) NOT NULL,
  `tussenvoegsel` varchar(255) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;

ALTER TABLE `candidates`
ADD PRIMARY KEY (`id`);

ALTER TABLE `candidates`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=1;

INSERT INTO `candidates` (`id`, `first_name`, `last_name`, `tussenvoegsel`) VALUES
(1, 'Dennis', 'Slimmers', 'geen'),
(2, 'Bas', 'Ploeg', 'van der'),
(3, 'Xander', 'Dijk', 'geen'),
(4, 'Edwin', 'Leeuwen', 'van'),
(5, 'Frank', 'Nouland', 'van den');


CREATE TABLE IF NOT EXISTS `vacancies` (
  `id` int(11) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `cid` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

ALTER TABLE `vacancies`
ADD PRIMARY KEY (`id`);

ALTER TABLE `vacancies`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

INSERT INTO `vacancies` (`id`, `title`, `description`, `cid`) VALUES
  (1, 'Senior PHP Developer', 'This is the description of vacancy 1', 1),
  (2, 'Junior Ruby on Rails Developer', 'This is the description of vacancy 2', 1);





