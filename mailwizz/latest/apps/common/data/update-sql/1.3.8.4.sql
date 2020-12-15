--
-- Update sql for MailWizz EMA from version 1.3.8.3 to 1.3.8.4
--

--
-- Alter the campaign table
--
ALTER TABLE `campaign` CHANGE `subject` `subject` VARCHAR(500) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;