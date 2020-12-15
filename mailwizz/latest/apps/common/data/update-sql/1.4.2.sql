--
-- Update sql for MailWizz EMA from version 1.4.1 to 1.4.2
--

--
-- campaign option
-- 
ALTER TABLE `campaign_option` ADD `autoresponder_include_current` ENUM('yes','no') NOT NULL DEFAULT 'no' AFTER `autoresponder_include_imported`;

-- 
-- create campaign open action subscriber table
-- 
CREATE TABLE IF NOT EXISTS `campaign_sent_action_subscriber` (
  `action_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `campaign_id` int(11) NOT NULL,
  `list_id` int(11) NOT NULL,
  `action` char(5) NOT NULL DEFAULT 'copy',
  `date_added` datetime NOT NULL,
  `last_updated` datetime NOT NULL,
  PRIMARY KEY (`action_id`),
  KEY `fk_campaign_sent_action_subscriber_campaign1_idx` (`campaign_id`),
  KEY `fk_campaign_sent_action_subscriber_list1_idx` (`list_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- 
-- add the fk to the campaign_sent_action_subscriber table 
-- 
ALTER TABLE `campaign_sent_action_subscriber`
  ADD CONSTRAINT `fk_campaign_sent_action_subscriber_campaign1` FOREIGN KEY (`campaign_id`) REFERENCES `campaign` (`campaign_id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_campaign_sent_action_subscriber_list1` FOREIGN KEY (`list_id`) REFERENCES `list` (`list_id`) ON DELETE CASCADE ON UPDATE NO ACTION;

