SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";
CREATE TABLE IF NOT EXISTS `groupscapabilities` (
  `groupid` int(11) NOT NULL,
  `capabilityid` int(11) NOT NULL,
  `mode` varchar(3) COLLATE utf8_unicode_ci NOT NULL DEFAULT '777'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `groupscapabilities`
 ADD PRIMARY KEY (`groupid`,`capabilityid`),
 ADD CONSTRAINT FK_groupscapabilities_groupid FOREIGN KEY (`groupid`) REFERENCES `groups` (`id`),
 ADD CONSTRAINT FK_groupscapabilities_capabilityid FOREIGN KEY (`capabilityid`) REFERENCES `capabilities` (`id`);

INSERT INTO `groupscapabilities` (`groupid`, `capabilityid`, `mode`) VALUES
(0, 1, '777'),
(0, 2, '777'),
(0, 3, '777'),
(0, 4, '777'),
(2, 5, '750'),
(3, 5, '750'),
(4, 5, '750'),
(3, 6, '750'),
(4, 6, '750'),
(4, 7, '750'),
(4, 8, '750');
