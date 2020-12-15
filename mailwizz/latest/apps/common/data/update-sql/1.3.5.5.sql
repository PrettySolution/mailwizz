--
-- Update sql for MailWizz EMA from version 1.3.5.4 to 1.3.5.5
--

--
-- Alter bounce_server table
--
ALTER TABLE `bounce_server` 
    ADD `disable_authenticator` VARCHAR(50) NULL AFTER `locked`, 
    ADD `search_charset` VARCHAR(50) NOT NULL DEFAULT 'UTF-8' AFTER `disable_authenticator`, 
    ADD `delete_all_messages` ENUM('yes','no') NOT NULL DEFAULT 'no' AFTER `search_charset`;

-- --------------------------------------------------------
    
--
-- Alter feedback_loop_server table
--
ALTER TABLE `feedback_loop_server` 
    ADD `disable_authenticator` VARCHAR(50) NULL AFTER `locked`, 
    ADD `search_charset` VARCHAR(50) NOT NULL DEFAULT 'UTF-8' AFTER `disable_authenticator`, 
    ADD `delete_all_messages` ENUM('yes','no') NOT NULL DEFAULT 'no' AFTER `search_charset`;

-- --------------------------------------------------------
