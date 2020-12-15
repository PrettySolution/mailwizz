--
-- Update sql for MailWizz EMA from version 1.3.1 to 1.3.2
--

-- --------------------------------------------------------
ALTER TABLE `article_category` CHANGE `description` `description` TEXT NULL ;
-- --------------------------------------------------------
ALTER TABLE `campaign` CHANGE `from_name` `from_name` VARCHAR(100) NULL ;
ALTER TABLE `campaign` CHANGE `reply_to` `reply_to` VARCHAR(100) NULL ;
ALTER TABLE `campaign` CHANGE `subject` `subject` VARCHAR(255) NULL ;
ALTER TABLE `campaign` CHANGE `send_at` `send_at` datetime NULL ;
-- --------------------------------------------------------
ALTER TABLE `campaign_bounce_log` CHANGE `message` `message` text NULL ;
ALTER TABLE `campaign_delivery_log` CHANGE `message` `message` text NULL ;
-- --------------------------------------------------------
ALTER TABLE `campaign_track_open` CHANGE `ip_address` `ip_address` CHAR(15) NULL ;
ALTER TABLE `campaign_track_open` CHANGE `user_agent` `user_agent` VARCHAR(255) NULL ;
-- --------------------------------------------------------
ALTER TABLE `campaign_track_unsubscribe` CHANGE `ip_address` `ip_address` CHAR(15) NULL ;
ALTER TABLE `campaign_track_unsubscribe` CHANGE `user_agent` `user_agent` VARCHAR(255) NULL ;
-- --------------------------------------------------------
ALTER TABLE `campaign_track_url` CHANGE `ip_address` `ip_address` CHAR(15) NULL ;
ALTER TABLE `campaign_track_url` CHANGE `user_agent` `user_agent` VARCHAR(255) NULL ;
-- --------------------------------------------------------
ALTER TABLE `customer` CHANGE `timezone` `timezone` varchar(50) NOT NULL ;
ALTER TABLE `customer_password_reset` CHANGE `ip_address` `ip_address` CHAR(15) NULL ;
-- --------------------------------------------------------
ALTER TABLE `delivery_server` 
    CHANGE `password` `password` VARCHAR(150) NULL,
    CHANGE `port` `port` INT(5) NULL DEFAULT  '25',
    CHANGE `timeout` `timeout` INT(3) NULL DEFAULT '30', 
    CHANGE `type` `type` CHAR(20) NOT NULL,
    CHANGE `last_sent` `last_sent` DATETIME NULL;
-- --------------------------------------------------------
ALTER TABLE `list_field_type` CHANGE `description` `description` VARCHAR(255) NULL ;
ALTER TABLE `list_subscriber` CHANGE `ip_address` `ip_address` CHAR(15) NULL ;
ALTER TABLE `list_field_value` CHANGE `value` `value` VARCHAR(255) NULL ;
-- --------------------------------------------------------
ALTER TABLE `user_password_reset` CHANGE `ip_address` `ip_address` CHAR(15) NULL ;
-- --------------------------------------------------------
INSERT INTO `option` (`category`, `key`, `value`, `is_serialized`, `date_added`, `last_updated`) VALUES
('system.email_blacklist', 'local_check', 0x796573, 0, '2014-01-09 14:48:41', '2014-01-09 15:18:22'),
('system.email_blacklist', 'remote_check', 0x6e6f, 0, '2014-01-09 14:48:41', '2014-01-09 21:45:18'),
('system.email_blacklist', 'remote_dnsbls', 0x613a323a7b693a303b733a31343a22626c2e7370616d636f702e6e6574223b693a313b733a31363a227a656e2e7370616d686175732e6f7267223b7d, 1, '2014-01-09 14:48:41', '2014-01-09 16:21:11'),
('system.campaign.attachments', 'allowed_extensions', 0x613a393a7b693a303b733a333a22706466223b693a313b733a333a22646f63223b693a323b733a343a22646f6378223b693a333b733a333a22786c73223b693a343b733a343a22786c7378223b693a353b733a333a22707074223b693a363b733a343a2270707478223b693a373b733a333a227a6970223b693a383b733a333a22726172223b7d, 1, '2014-01-14 09:57:08', '2014-01-18 11:09:50'),
('system.campaign.attachments', 'allowed_files_count', 0x35, 0, '2014-01-14 10:07:14', '2014-01-14 10:07:14'),
('system.campaign.attachments', 'allowed_file_size', 0x31303438353736, 0, '2014-01-14 10:07:14', '2014-01-14 10:12:53'),
('system.campaign.attachments', 'allowed_mime_types', 0x613a34313a7b693a303b733a31353a226170706c69636174696f6e2f706466223b693a313b733a31373a226170706c69636174696f6e2f782d706466223b693a323b733a31393a226170706c69636174696f6e2f6163726f626174223b693a333b733a32303a226170706c69636174696f6e732f766e642e706466223b693a343b733a383a22746578742f706466223b693a353b733a31303a22746578742f782d706466223b693a363b733a31383a226170706c69636174696f6e2f6d73776f7264223b693a373b733a31353a226170706c69636174696f6e2f646f63223b693a383b733a393a226170706c2f74657874223b693a393b733a32323a226170706c69636174696f6e2f766e642e6d73776f7264223b693a31303b733a32333a226170706c69636174696f6e2f766e642e6d732d776f7264223b693a31313b733a31393a226170706c69636174696f6e2f77696e776f7264223b693a31323b733a31363a226170706c69636174696f6e2f776f7264223b693a31333b733a31383a226170706c69636174696f6e2f782d6d737736223b693a31343b733a32303a226170706c69636174696f6e2f782d6d73776f7264223b693a31353b733a37313a226170706c69636174696f6e2f766e642e6f70656e786d6c666f726d6174732d6f6666696365646f63756d656e742e776f726470726f63657373696e676d6c2e646f63756d656e74223b693a31363b733a32343a226170706c69636174696f6e2f766e642e6d732d657863656c223b693a31373b733a31393a226170706c69636174696f6e2f6d73657863656c223b693a31383b733a32313a226170706c69636174696f6e2f782d6d73657863656c223b693a31393b733a32323a226170706c69636174696f6e2f782d6d732d657863656c223b693a32303b733a31393a226170706c69636174696f6e2f782d657863656c223b693a32313b733a32363a226170706c69636174696f6e2f782d646f735f6d735f657863656c223b693a32323b733a31353a226170706c69636174696f6e2f786c73223b693a32333b733a36353a226170706c69636174696f6e2f766e642e6f70656e786d6c666f726d6174732d6f6666696365646f63756d656e742e73707265616473686565746d6c2e7368656574223b693a32343b733a32393a226170706c69636174696f6e2f766e642e6d732d706f776572706f696e74223b693a32353b733a32343a226170706c69636174696f6e2f6d73706f776572706f696e74223b693a32363b733a32353a226170706c69636174696f6e2f6d732d706f776572706f696e74223b693a32373b733a32323a226170706c69636174696f6e2f6d73706f776572706e74223b693a32383b733a32383a226170706c69636174696f6e2f766e642d6d73706f776572706f696e74223b693a32393b733a32323a226170706c69636174696f6e2f706f776572706f696e74223b693a33303b733a32343a226170706c69636174696f6e2f782d706f776572706f696e74223b693a33313b733a31353a226170706c69636174696f6e2f782d6d223b693a33323b733a37333a226170706c69636174696f6e2f766e642e6f70656e786d6c666f726d6174732d6f6666696365646f63756d656e742e70726573656e746174696f6e6d6c2e70726573656e746174696f6e223b693a33333b733a31353a226170706c69636174696f6e2f7a6970223b693a33343b733a31373a226170706c69636174696f6e2f782d7a6970223b693a33353b733a32383a226170706c69636174696f6e2f782d7a69702d636f6d70726573736564223b693a33363b733a32343a226170706c69636174696f6e2f6f637465742d73747265616d223b693a33373b733a32323a226170706c69636174696f6e2f782d636f6d7072657373223b693a33383b733a32343a226170706c69636174696f6e2f782d636f6d70726573736564223b693a33393b733a31353a226d756c7469706172742f782d7a6970223b693a34303b733a32383a226170706c69636174696f6e2f782d7261722d636f6d70726573736564223b7d, 1, '2014-01-14 09:57:08', '2014-01-18 11:09:50'),
('system.campaign.attachments', 'enabled', 0x6e6f, 0, '2014-01-14 09:57:08', '2014-01-14 10:09:42');
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `campaign_to_delivery_server` (
  `campaign_id` int(11) NOT NULL,
  `server_id` int(11) NOT NULL,
  PRIMARY KEY (`campaign_id`,`server_id`),
  KEY `fk_campaign_to_delivery_server_delivery_server1_idx` (`server_id`),
  KEY `fk_campaign_to_delivery_server_campaign1_idx` (`campaign_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
-- --------------------------------------------------------
ALTER TABLE `campaign_to_delivery_server`
  ADD CONSTRAINT `fk_campaign_to_delivery_server_campaign1` FOREIGN KEY (`campaign_id`) REFERENCES `campaign` (`campaign_id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_campaign_to_delivery_server_delivery_server1` FOREIGN KEY (`server_id`) REFERENCES `delivery_server` (`server_id`) ON DELETE CASCADE ON UPDATE NO ACTION;
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `campaign_attachment` (
  `attachment_id` int(11) NOT NULL AUTO_INCREMENT,
  `campaign_id` int(11) NOT NULL,
  `file` varchar(255) NOT NULL,
  `name` varchar(100) NOT NULL,
  `size` int(11) NOT NULL DEFAULT '0',
  `extension` CHAR(10) NOT NULL,
  `mime_type` varchar(50) NOT NULL,
  `date_added` datetime NOT NULL,
  `last_updated` datetime NOT NULL,
  PRIMARY KEY (`attachment_id`),
  KEY `fk_campaign_attachment_campaign1_idx` (`campaign_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
-- --------------------------------------------------------
ALTER TABLE `campaign_attachment`
  ADD CONSTRAINT `fk_campaign_attachment_campaign1` FOREIGN KEY (`campaign_id`) REFERENCES `campaign` (`campaign_id`) ON DELETE CASCADE ON UPDATE NO ACTION;
-- --------------------------------------------------------