--
-- Update sql for MailWizz EMA from version 1.2 to 1.3
--

--
-- Table structure for table `campaign_option`
--
CREATE TABLE IF NOT EXISTS `campaign_option` (
  `campaign_id` int(11) NOT NULL,
  `url_tracking` enum('yes','no') NOT NULL DEFAULT 'no',
  `json_feed` enum('yes','no') NOT NULL DEFAULT 'no',
  `xml_feed` enum('yes','no') NOT NULL DEFAULT 'no',
  PRIMARY KEY (`campaign_id`),
  KEY `fk_campaign_option_campaign1_idx` (`campaign_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Insert into `tag_registry`
--
INSERT INTO `tag_registry` (`tag_id`, `tag`, `description`, `date_added`, `last_updated`) VALUES
(NULL, '[XML_FEED_BEGIN]', NULL, '2013-12-09 00:00:00', '2013-12-09 00:00:00'),
(NULL, '[XML_FEED_ITEM_LINK]', NULL, '2013-12-09 00:00:00', '2013-12-09 00:00:00'),
(NULL, '[XML_FEED_ITEM_IMAGE]', NULL, '2013-12-09 00:00:00', '2013-12-09 00:00:00'),
(NULL, '[XML_FEED_ITEM_TITLE]', NULL, '2013-12-09 00:00:00', '2013-12-09 00:00:00'),
(NULL, '[XML_FEED_ITEM_DESCRIPTION]', NULL, '2013-12-09 00:00:00', '2013-12-09 00:00:00'),
(NULL, '[XML_FEED_END]', NULL, '2013-12-09 00:00:00', '2013-12-09 00:00:00'),
(NULL, '[XML_FEED_ITEM_PUBDATE]', NULL, '2013-12-09 00:00:00', '2013-12-09 00:00:00'),
(NULL, '[XML_FEED_ITEM_GUID]', NULL, '2013-12-09 00:00:00', '2013-12-09 00:00:00'),
(NULL, '[JSON_FEED_BEGIN]', NULL, '2013-12-09 00:00:00', '2013-12-09 00:00:00'),
(NULL, '[JSON_FEED_ITEM_LINK]', NULL, '2013-12-09 00:00:00', '2013-12-09 00:00:00'),
(NULL, '[JSON_FEED_ITEM_IMAGE]', NULL, '2013-12-09 00:00:00', '2013-12-09 00:00:00'),
(NULL, '[JSON_FEED_ITEM_TITLE]', NULL, '2013-12-09 00:00:00', '2013-12-09 00:00:00'),
(NULL, '[JSON_FEED_ITEM_DESCRIPTION]', NULL, '2013-12-09 00:00:00', '2013-12-09 00:00:00'),
(NULL, '[JSON_FEED_END]', NULL, '2013-12-09 00:00:00', '2013-12-09 00:00:00'),
(NULL, '[JSON_FEED_ITEM_PUBDATE]', NULL, '2013-12-09 00:00:00', '2013-12-09 00:00:00'),
(NULL, '[JSON_FEED_ITEM_GUID]', NULL, '2013-12-09 00:00:00', '2013-12-09 00:00:00'),
(NULL, '[XML_FEED_ITEM_CONTENT]', NULL, '2013-12-09 00:00:00', '2013-12-09 00:00:00'),
(NULL, '[JSON_FEED_ITEM_CONTENT]', NULL, '2013-12-09 00:00:00', '2013-12-09 00:00:00');

-- --------------------------------------------------------

--
-- Alter `lists` table
--

ALTER TABLE `list` ADD `opt_in` ENUM('double', 'single') NOT NULL DEFAULT 'double' AFTER `visibility`;
ALTER TABLE `list` ADD `opt_out` ENUM('double', 'single') NOT NULL DEFAULT 'double' AFTER `opt_in`;

-- --------------------------------------------------------

--
-- Constraints for table `campaign_option`
--
ALTER TABLE `campaign_option`
  ADD CONSTRAINT `fk_campaign_option_campaign1` FOREIGN KEY (`campaign_id`) REFERENCES `campaign` (`campaign_id`) ON DELETE CASCADE ON UPDATE NO ACTION;

-- --------------------------------------------------------