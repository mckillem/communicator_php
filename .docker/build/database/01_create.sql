CREATE TABLE IF NOT EXISTS `user` (
                                      `user_id` int(11) NOT NULL AUTO_INCREMENT,
                                      `firstname` varchar(255) COLLATE utf8_czech_ci NOT NULL,
                                      `lastname` varchar(255) COLLATE utf8_czech_ci NOT NULL,
                                      `email` varchar(255) COLLATE utf8_czech_ci NOT NULL,
                                      `password` varchar(60) COLLATE utf8_czech_ci NOT NULL,
                                      `role` enum('member','admin') COLLATE utf8_czech_ci NOT NULL DEFAULT 'member',
                                      PRIMARY KEY (`user_id`),
                                      UNIQUE KEY `email` (`email`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

# heslo itnetwork
INSERT INTO `user` (`user_id`, `firstname`, `lastname`, `email`, `password`, `role`) VALUES
    (1, 'Matěj', 'Musil', 'matej@email.cz', '$2y$10$arJIJie/xGoqZayCro4yZ.pPEkt9Ps4DJBNZAHSZ/rvbOkj//K/tq', 'admin'),
    (2, 'Lada', 'Nie', 'lada@email.cz', '$2y$10$arJIJie/xGoqZayCro4yZ.pPEkt9Ps4DJBNZAHSZ/rvbOkj//K/tq', 'admin');

CREATE TABLE `message`
(
    `message_id`       INT(11)      NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `text` VARCHAR(255) NOT NULL,
    `read` boolean NOT NULL default 0,
    `delivered` boolean NOT NULL default 0,
    `deleted` boolean NOT NULL default 0,
    `createdAt` datetime NOT NULL,
    `deletedAt` datetime NOT NULL,
    `deliveredAt` datetime NOT NULL,
    `readAt` datetime NOT NULL,
    `createdBy` int(11) NOT NULL
) ENGINE = InnoDB
  CHARSET = utf8;

# INSERT INTO `message` (`message_id`, `text`, `isbn`, `pages`, `date`, `read`, `own`, `description`, `createdAt`, `createdBy`)
# VALUES (1, 'Atomic habits', 'koko', 100, '2013',  true, false, 'něco o knize', '2000-01-01 01:03:38', 1),
#        (2, 'Ultralearning', 'koko', 100, '2013',  true, false, 'něco o knize', '2000-01-01 01:03:38', 1),
#        (3, 'The answer', 'koko', 100, '2013',  true, false, 'něco o knize', '2000-01-01 01:03:38', 1),
#        (4, 'Scattered minds', 'koko', 100, '2013',  true, false, 'něco o knize', '2000-01-01 01:03:38', 1),
#        (5, 'Hledání knih', 'koko', 342, '1992', true, false, 'něco o knize', '2000-01-01 01:03:38', 1);

CREATE TABLE `message_user`
(
        `id` int(11) NOT NULL AUTO_INCREMENT,
         `message_id` int(11) NOT NULL,
         `user_id` int(11) NOT NULL,
         PRIMARY KEY (`id`),
         KEY `message_id` (`message_id`),
         KEY `user_id` (`user_id`),
         CONSTRAINT `message_user_ibfk_11` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
         CONSTRAINT `message_user_ibfk_10` FOREIGN KEY (`message_id`) REFERENCES `message` (`message_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=46 DEFAULT CHARSET=utf8;

# INSERT INTO `message_user` (`id`, `message_id`, `user_id`)
# VALUES (20,	1,	2),
#        (21,	2,	3),
#        (22,	3,	1),
#        (23,	4,	4),
#        (24,	5,	2);