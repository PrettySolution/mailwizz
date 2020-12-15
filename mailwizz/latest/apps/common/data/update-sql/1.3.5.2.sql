--
-- Update sql for MailWizz EMA from version 1.3.5.1 to 1.3.5.2
--

ALTER TABLE `list` ADD `merged` ENUM('yes','no') NOT NULL DEFAULT 'no' AFTER `opt_out`;