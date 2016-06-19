SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";
CREATE TABLE IF NOT EXISTS `capabilities` (
  `id` TINYINT NOT NULL,
  `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(100) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `capabilities` (`id`, `name`, `description`) VALUES
(1, 'chpasswd', 'Gestion des utilisateurs / groupes'),
(2, 'chglogin', 'Gestion des utilisateurs / groupes'),
(3, 'chggroup', 'Gestion des utilisateurs / groupes'),
(4, 'ACCOUNT_MANAGE', 'Gestion des utilisateurs / groupes'),
(5, 'PROJECT_USER', 'Gestion de projet'),
(6, 'PROJECTS_MANAGER', 'Visualisation des projets'),
(7, 'TEAM_LEADER', 'Gestion des effectifs'),
(8, 'GENERIC_TASK', 'création de tâches pour l''ensemble de l''équipe');

ALTER TABLE `capabilities`
 ADD PRIMARY KEY (`id`);

ALTER TABLE `capabilities`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=9;
