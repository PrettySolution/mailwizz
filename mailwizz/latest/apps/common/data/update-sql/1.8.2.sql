--
-- Update sql for MailWizz EMA from version 1.8.1 to 1.8.2
--

--
-- Table structure for table `campaign_group_block_subscriber`
--

DROP TABLE IF EXISTS `campaign_group_block_subscriber`;
CREATE TABLE IF NOT EXISTS `campaign_group_block_subscriber` (
  `group_id` INT(11) NOT NULL,
  `subscriber_id` INT(11) NOT NULL,
  PRIMARY KEY (`group_id`, `subscriber_id`),
  KEY `fk_campaign_group_block_subscriber_campaign_group1_idx` (`group_id`),
  KEY `fk_campaign_group_block_subscriber_list_subscriber1_idx` (`subscriber_id`)
) ENGINE = InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Constraints for table `campaign_group_block_subscriber`
--
ALTER TABLE `campaign_group_block_subscriber`
    ADD CONSTRAINT `fk_campaign_group_block_subscriber_campaign_group1` FOREIGN KEY (`group_id`) REFERENCES `campaign_group` (`group_id`) ON DELETE CASCADE ON UPDATE NO ACTION,
    ADD CONSTRAINT `fk_campaign_group_block_subscriber_list_subscriber1` FOREIGN KEY (`subscriber_id`) REFERENCES `list_subscriber` (`subscriber_id`) ON DELETE CASCADE ON UPDATE NO ACTION;