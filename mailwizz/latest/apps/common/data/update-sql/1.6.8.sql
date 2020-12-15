--
-- Update sql for MailWizz EMA from version 1.6.7 to 1.6.8
--

-- -----------------------------------------------------
-- Table `campaign_track_open_webhook`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `campaign_track_open_webhook` (
  `webhook_id` INT(11) NOT NULL AUTO_INCREMENT,
  `campaign_id` INT(11) NOT NULL,
  `webhook_url` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`webhook_id`),
  KEY `fk_campaign_track_open_webhook_campaign1_idx` (`campaign_id`),
  CONSTRAINT `fk_campaign_track_open_webhook_campaign1` FOREIGN KEY (`campaign_id`) REFERENCES `campaign` (`campaign_id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE = InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

-- -----------------------------------------------------
-- Table `campaign_track_open_webhook_queue`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `campaign_track_open_webhook_queue` (
  `id` BIGINT(20) NOT NULL AUTO_INCREMENT,
  `webhook_id` INT(11) NOT NULL,
  `track_open_id` BIGINT(20) NOT NULL,
  `retry_count` TINYINT(1) NOT NULL DEFAULT 0,
  `next_retry` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_campaign_track_open_webhook_queue_campaign_track_open_we_idx` (`webhook_id`),
  KEY `fk_campaign_track_open_webhook_queue_campaign_track_open1_idx` (`track_open_id`),
  KEY `campaign_track_open_webhook_retry_next` (`retry_count`, `next_retry`),
  CONSTRAINT `fk_campaign_track_open_webhook_queue_campaign_track_open_webh1` FOREIGN KEY (`webhook_id`) REFERENCES `campaign_track_open_webhook` (`webhook_id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `fk_campaign_track_open_webhook_queue_campaign_track_open1` FOREIGN KEY (`track_open_id`) REFERENCES `campaign_track_open` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE = InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

-- -----------------------------------------------------
-- Table `campaign_track_url_webhook`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `campaign_track_url_webhook` (
  `webhook_id` INT(11) NOT NULL AUTO_INCREMENT,
  `campaign_id` INT(11) NOT NULL,
  `webhook_url` VARCHAR(255) NOT NULL,
  `track_url` TEXT NOT NULL,
  `track_url_hash` CHAR(40) NOT NULL,
  PRIMARY KEY (`webhook_id`),
  KEY `fk_campaign_track_url_webhook_campaign1_idx` (`campaign_id`),
  KEY `campaign_track_url_webhook_campaign_hash` (`campaign_id`, `track_url_hash`),
  CONSTRAINT `fk_campaign_track_url_webhook_campaign1` FOREIGN KEY (`campaign_id`) REFERENCES `campaign` (`campaign_id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE = InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

-- -----------------------------------------------------
-- Table `campaign_track_url_webhook_queue`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `campaign_track_url_webhook_queue` (
  `id` BIGINT(20) NOT NULL AUTO_INCREMENT,
  `webhook_id` INT(11) NOT NULL,
  `track_url_id` BIGINT(20) NOT NULL,
  `retry_count` TINYINT(1) NOT NULL DEFAULT 0,
  `next_retry` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_campaign_track_url_webhook_queue_campaign_track_url_webh_idx` (`webhook_id`),
  KEY `fk_campaign_track_url_webhook_queue_campaign_track_url1_idx` (`track_url_id`),
  KEY `campaign_track_url_webhook_retry_next` (`retry_count`, `next_retry`),
  CONSTRAINT `fk_campaign_track_url_webhook_queue_campaign_track_url_webhook1` FOREIGN KEY (`webhook_id`) REFERENCES `campaign_track_url_webhook` (`webhook_id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `fk_campaign_track_url_webhook_queue_campaign_track_url1` FOREIGN KEY (`track_url_id`) REFERENCES `campaign_track_url` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE = InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;