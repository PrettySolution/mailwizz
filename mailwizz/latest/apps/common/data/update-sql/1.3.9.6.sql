--
-- Update sql for MailWizz EMA from version 1.3.9.5 to 1.3.9.6
--

--
-- Dumping data for table `start_page`
--
INSERT INTO `start_page` (`page_id`, `application`, `route`, `icon`, `icon_color`, `heading`, `content`, `date_added`, `last_updated`) VALUES
(NULL, 'customer', 'campaigns_stats/index', 'fa-envelope', '', 'Campaigns stats', 'This area shows overview reports for sent campaigns,<br />\nso you will have to create and send at least one campaign in order to view information here.', '2017-04-07 09:18:28', '2017-04-07 09:18:28');
