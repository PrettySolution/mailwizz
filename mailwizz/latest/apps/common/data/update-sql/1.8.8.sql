--
-- Update sql for MailWizz EMA from version 1.8.7 to 1.8.8
--

--
-- Table structure for table `cache`
--
DROP TABLE IF EXISTS `cache`;
CREATE TABLE IF NOT EXISTS `cache` (
    `id` char(32) NOT NULL,
    `expire` int(11) NULL,
    `value` longblob,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;