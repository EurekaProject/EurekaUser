SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL,
  `groupid` int(11) DEFAULT NULL,
  `is_administrator` tinyint(1) NOT NULL DEFAULT '0',
  `lang` varchar(3) CHARACTER SET ascii COLLATE ascii_bin NOT NULL DEFAULT 'fra',
  `login` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '*',
  `description` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `users`
 ADD UNIQUE KEY `id` (`id`),
 ADD CONSTRAINT FK_users_groupid FOREIGN KEY (`groupid`) REFERENCES `groups` (`id`);;

ALTER TABLE `users`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=1;
