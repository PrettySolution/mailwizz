--
-- Update sql for MailWizz EMA from version 1.3.5.2 to 1.3.5.3
--

--
-- Campaign option
--
ALTER TABLE `campaign_option` 
    ADD `cronjob` VARCHAR(255) NULL AFTER `regular_open_unopen_campaign_id`,
    ADD `cronjob_enabled` TINYINT(1) NOT NULL DEFAULT '0' AFTER `cronjob`;

--
-- List table
--
ALTER TABLE `list` ADD `welcome_email` enum('yes','no') NOT NULL DEFAULT 'no' AFTER `merged`;

--
-- List page type
--

INSERT INTO `list_page_type` (`type_id`, `name`, `slug`, `description`, `content`, `full_html`, `meta_data`, `date_added`, `last_updated`) VALUES
(null, 'Welcome email', 'welcome-email', 'The email the user receives after he successfully subscribes into the list', '<!DOCTYPE html>\n<html><head><title>[LIST_NAME]</title><meta content="utf-8" name="charset">\n<style type="text/css">\n#outlook a{padding:0;}\n	body {width:100% !important; -webkit-text-size-adjust:none; margin:0; padding:0; font-family:  sans-serif; background: #f5f5f5; font-size:12px;}\n	img {border:0;height:auto;line-height:100%;outline:none;text-decoration:none;}\n	table td{border-collapse:collapse;}\n	a {color: #367fa9;text-decoration:none}\n	a:hover {color: #367fa9;text-decoration:none;}\n	#wrap {background:#f5f5f5; padding:10px;}\n	table#main-table {-webkit-border-radius:5px;-moz-border-radius:5px;border-radius:5px; border:1px solid #367fa9; overflow:hidden; background: #FFFFFF; width: 600px}\n	h1{padding:0; margin:0; font-family: sans-serif;font-size:25px;font-style:italic;color:#FFFFFF; font-weight:bold;}\n	h1 small{font-size:13px;font-weight:normal; font-family:  sans-serif; font-style:italic;}\n	h6{font-size:10px;color:#FFFFFF;margin:0;padding:0;font-weight:normal}\n	.darkbg {background: #367fa9}\n	input{outline:none}\n</style>\n</head><body style="width:100%;-webkit-text-size-adjust:none;margin:0;padding:0;font-family:sans-serif;background:#f5f5f5;font-size:12px">\n                \n            <div id="wrap" style="background:#f5f5f5;padding:10px">\n<table align="center" border="0" cellpadding="0" cellspacing="0" id="main-table" style="-webkit-border-radius:5px;-moz-border-radius:5px;border-radius:5px;border:1px solid #367fa9;overflow:hidden;background:#FFFFFF;width:600px"><tbody><tr><td class="darkbg" style="border-collapse:collapse;background:#367fa9">\n			<table border="0" cellpadding="0" cellspacing="20" width="100%"><tbody><tr><td style="border-collapse:collapse">\n						<h1 style="padding:0;margin:0;font-family:sans-serif;font-size:25px;font-style:italic;color:#FFFFFF;font-weight:bold">[LIST_NAME] <small style="font-size:13px;font-weight:normal;font-family:sans-serif;font-style:italic">[COMPANY_NAME]</small></h1>\n						</td>\n					</tr></tbody></table></td>\n		</tr><tr><td style="border-collapse:collapse">\n			<table border="0" cellpadding="20" cellspacing="0" width="100%"><tbody><tr><td style="border-collapse:collapse">&nbsp;</td>\n					</tr><tr><td style="border-collapse:collapse">Thank you for subscribing into [LIST_NAME] email list.<br>\n						You can update your information at any time by clicking <a href="[UPDATE_PROFILE_URL]" style="color:#367fa9;text-decoration:none">here</a>.<br>\n						Thank you.</td>\n					</tr><tr><td style="border-collapse:collapse">&nbsp;</td>\n					</tr></tbody></table></td>\n		</tr><tr><td class="darkbg" style="padding:10px;border-collapse:collapse;background:#367fa9">\n			<h6 style="font-size:10px;color:#FFFFFF;margin:0;padding:0;font-weight:normal">&copy; [CURRENT_YEAR] [COMPANY_NAME]. All rights reserved</h6>\n			</td>\n		</tr></tbody></table></div></body></html>\n', 'yes', 0x613a303a7b7d, '2013-09-05 13:39:56', '2015-03-19 11:13:09');
