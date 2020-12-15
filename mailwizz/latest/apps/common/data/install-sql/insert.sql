--
-- Insert sql for MailWizz EMA
--

-- --------------------------------------------------------

-- -----------------------------------------------------
-- Dumping data for table `article`
-- -----------------------------------------------------

INSERT INTO `article` (`article_id`, `title`, `slug`, `content`, `status`, `date_added`, `last_updated`) VALUES
(2, 'CAN-SPAM Act: A Compliance Guide for Business', 'can-spam-act-compliance-guide-business', '<p>Do you use email in your business? The CAN-SPAM Act, a law that sets the rules for commercial email, establishes requirements for commercial messages, gives recipients the right to have you stop emailing them, and spells out tough penalties for violations.</p>\n\n<p>Despite its name, the CAN-SPAM Act doesn’t apply just to bulk email. It covers all commercial messages, which the law defines as “any electronic mail message the primary purpose of which is the commercial advertisement or promotion of a commercial product or service,” including email that promotes content on commercial websites. The law makes no exception for business-to-business email. That means all email – for example, a message to former customers announcing a new product line – must comply with the law.</p>\n\n<p>Each separate email in violation of the CAN-SPAM Act is subject to penalties of up to $16,000, so non-compliance can be costly. But following the law isn’t complicated. Here’s a rundown of CAN-SPAM’s main requirements:</p>\n\n<ol style="margin-left:15px;margin-right:0px;"><li><strong>Don’t use false or misleading header information.</strong> Your “From,” “To,” “Reply-To,” and routing information – including the originating domain name and email address – must be accurate and identify the person or business who initiated the message.</li>\n	<li><strong>Don’t use deceptive subject lines.</strong> The subject line must accurately reflect the content of the message.</li>\n	<li><strong>Identify the message as an ad.</strong> The law gives you a lot of leeway in how to do this, but you must disclose clearly and conspicuously that your message is an advertisement.</li>\n	<li><strong>Tell recipients where you’re located.</strong> Your message must include your valid physical postal address. This can be your current street address, a post office box you’ve registered with the U.S. Postal Service, or a private mailbox you’ve registered with a commercial mail receiving agency established under Postal Service regulations.</li>\n	<li><strong>Tell recipients how to opt out of receiving future email from you.</strong> Your message must include a clear and conspicuous explanation of how the recipient can opt out of getting email from you in the future. Craft the notice in a way that’s easy for an ordinary person to recognize, read, and understand. Creative use of type size, color, and location can improve clarity. Give a return email address or another easy Internet-based way to allow people to communicate their choice to you. You may create a menu to allow a recipient to opt out of certain types of messages, but you must include the option to stop all commercial messages from you. Make sure your spam filter doesn’t block these opt-out requests.</li>\n	<li><strong>Honor opt-out requests promptly.</strong> Any opt-out mechanism you offer must be able to process opt-out requests for at least 30 days after you send your message. You must honor a recipient’s opt-out request within 10 business days. You can’t charge a fee, require the recipient to give you any personally identifying information beyond an email address, or make the recipient take any step other than sending a reply email or visiting a single page on an Internet website as a condition for honoring an opt-out request. Once people have told you they don’t want to receive more messages from you, you can’t sell or transfer their email addresses, even in the form of a mailing list. The only exception is that you may transfer the addresses to a company you’ve hired to help you comply with the CAN-SPAM Act.</li>\n	<li><strong>Monitor what others are doing on your behalf.</strong> The law makes clear that even if you hire another company to handle your email marketing, you can’t contract away your legal responsibility to comply with the law. Both the company whose product is promoted in the message and the company that actually sends the message may be held legally responsible.</li>\n</ol>\n \n\n<h3>Need more information?</h3>\nPlease visit <a href="http://www.business.ftc.gov/documents/bus61-can-spam-act-compliance-guide-business" target="_blank">http://www.business.ftc.gov/documents/bus61-can-spam-act-compliance-guide-business</a><br />\n ', 'published', '2013-10-24 11:08:05', '2013-10-25 11:02:06'),
(8, 'Campaign tags and filters', 'campaign-tag-filters', 'When sending a campaign, you are able to use a number of custom tags and filters.<br />\nMost common tags are listed below: <br /><br />\n \n<div class="col-lg-12">\n<table class="table table-bordered table-hover table-striped"><tbody><tr><td>Tag</td>\n			<td>Required</td>\n		</tr><tr><td>[UNSUBSCRIBE_URL]</td>\n			<td>YES</td>\n		</tr><tr><td>[COMPANY_FULL_ADDRESS]</td>\n			<td>YES</td>\n		</tr><tr><td>[UPDATE_PROFILE_URL]</td>\n			<td>NO</td>\n		</tr><tr><td>[WEB_VERSION_URL]</td>\n			<td>NO</td>\n		</tr><tr><td>[CAMPAIGN_URL]</td>\n			<td>NO</td>\n		</tr><tr><td>[LIST_NAME]</td>\n			<td>NO</td>\n		</tr><tr><td>[LIST_SUBJECT]</td>\n			<td>NO</td>\n		</tr><tr><td>[LIST_DESCRIPTION]</td>\n			<td>NO</td>\n		</tr><tr><td>[LIST_FROM_NAME]</td>\n			<td>NO</td>\n		</tr><tr><td>[CURRENT_YEAR]</td>\n			<td>NO</td>\n		</tr><tr><td>[CURRENT_MONTH]</td>\n			<td>NO</td>\n		</tr><tr><td>[CURRENT_DAY]</td>\n			<td>NO</td>\n		</tr><tr><td>[CURRENT_DATE]</td>\n			<td>NO</td>\n		</tr><tr><td>[COMPANY_NAME]</td>\n			<td>NO</td>\n		</tr><tr><td>[COMPANY_ADDRESS_1]</td>\n			<td>NO</td>\n		</tr><tr><td>[COMPANY_ADDRESS_2]</td>\n			<td>NO</td>\n		</tr><tr><td>[COMPANY_CITY]</td>\n			<td>NO</td>\n		</tr><tr><td>[COMPANY_ZONE]</td>\n			<td>NO</td>\n		</tr><tr><td>[COMPANY_ZIP]</td>\n			<td>NO</td>\n		</tr><tr><td>[COMPANY_COUNTRY]</td>\n			<td>NO</td>\n		</tr><tr><td>[COMPANY_PHONE]</td>\n			<td>NO</td>\n		</tr><tr><td>[CAMPAIGN_SUBJECT]</td>\n			<td>NO</td>\n		</tr><tr><td>[CAMPAIGN_TO_NAME]</td>\n			<td>NO</td>\n		</tr><tr><td>[CAMPAIGN_FROM_NAME]</td>\n			<td>NO</td>\n		</tr><tr><td>[CAMPAIGN_REPLY_TO]</td>\n			<td>NO</td>\n		</tr><tr><td>[CAMPAIGN_UID]</td>\n			<td>NO</td>\n		</tr><tr><td>[SUBSCRIBER_UID]</td>\n			<td>NO</td>\n		</tr><tr><td>[EMAIL]</td>\n			<td>NO</td>\n		</tr><tr><td>[FNAME]</td>\n			<td>NO</td>\n		</tr><tr><td>[LNAME]</td>\n			<td>NO</td>\n		</tr></tbody></table></div>\n\n<div class="clearfix"> </div>\nNow, each of the above tags is able to receive a set of filters.<br />\nFilters are a simple way of transforming the tag in a way or another, for example you might want to embed a sharing link to twitter in your campaign, say the campaign url itself.<br /><br />\nUsing only the tags you would embed it like:<br /><br /><code>https://twitter.com/intent/tweet?text=[CAMPAIGN_SUBJECT]&amp;url=[CAMPAIGN_URL] </code><br /><br />\nBut there is a problem, because twitter expects your arguments to be url encoded, and by that, i mean twitter expects to get<br /><br /><code>https://twitter.com/intent/tweet?text=my%20super%20campaign&amp;url=http%3A%2F%2Fwww.domain.com%2Fcampaigns%2F1cart129djat3</code><br /><br />\nbut instead it will get <code>https://twitter.com/intent/tweet?text=my super campaign&amp;url=http://www.domain.com/campaigns/1cart129djat3</code><br /><br />\nIn order to overcome this issue, we will apply filters over our tags, therefore, the twitter url becomes:<br /><br /><code>https://twitter.com/intent/tweet?text=[CAMPAIGN_SUBJECT:filter:urlencode]&amp;url=[CAMPAIGN_URL:filter:urlencode] </code><br /><br />\nPretty simple eh?<br />\nBut we can do even more, let''s say we want to make sure our twitter text starts with a capitalized letter and the rest of the letters will be lowercase.<br />\nIn order to accomplish this, we can apply multiple filters(separate by a pipe) to same tag, for example: <br /><br /><code>https://twitter.com/intent/tweet?text=[CAMPAIGN_SUBJECT:filter:lowercase|ucfirst|urlencode]&amp;url=[CAMPAIGN_URL:filter:urlencode] </code><br />\nPlease note, the order in which you add the filters is the same order they are applied.<br /><br />\nBellow is the entire list of filters, for now there are a few, but in the future the number might increase.\n<div class="clearfix"> </div>\n\n<div class="col-lg-12">\n<table class="table table-bordered table-hover table-striped"><tbody><tr><td>urlencode</td>\n			<td>will urlencode your tag</td>\n		</tr><tr><td>rawurlencode</td>\n			<td>will rawurlencode your url</td>\n		</tr><tr><td>htmlencode</td>\n			<td>will convert html tags into their entities</td>\n		</tr><tr><td>trim</td>\n			<td>will trim the white spaces from begining and end of your tag</td>\n		</tr><tr><td>uppercase</td>\n			<td>will transform your tag in uppercase only chars</td>\n		</tr><tr><td>lowercase</td>\n			<td>will transform your tag in lowercase only chars</td>\n		</tr><tr><td>ucwords</td>\n			<td>will capitalize each first letter from your tag content</td>\n		</tr><tr><td>ucfirst</td>\n			<td>will capitalize only the first letter of your tag</td>\n		</tr><tr><td>reverse</td>\n			<td>will reverse your tag content</td>\n		</tr></tbody></table></div>\n\n<div class="clearfix"> </div>\n', 'published', '2013-10-24 22:47:27', '2013-10-25 11:02:00');

-- --------------------------------------------------------

-- -----------------------------------------------------
-- Dumping data for table `article_category`
-- -----------------------------------------------------

INSERT INTO `article_category` (`category_id`, `parent_id`, `name`, `slug`, `description`, `status`, `date_added`, `last_updated`) VALUES
(4, NULL, 'Informations', 'informations', '', 'active', '2013-10-25 11:01:50', '2013-10-25 11:01:50');

-- --------------------------------------------------------

-- -----------------------------------------------------
-- Dumping data for table `article_to_category`
-- -----------------------------------------------------

INSERT INTO `article_to_category` (`article_id`, `category_id`) VALUES
(2, 4),
(8, 4);

-- --------------------------------------------------------

-- -----------------------------------------------------
-- Dumping data for table `list_field_type`
-- -----------------------------------------------------

INSERT INTO `list_field_type` (`type_id`, `name`, `identifier`, `class_alias`, `description`, `date_added`, `last_updated`) VALUES
(NULL, 'Text', 'text', 'customer.components.field-builder.text.FieldBuilderTypeText', 'Text', NOW(), NOW()),
(NULL, 'Dropdown', 'dropdown', 'customer.components.field-builder.dropdown.FieldBuilderTypeDropdown', 'Dropdown', NOW(), NOW()),
(NULL, 'Multiselect', 'multiselect', 'customer.components.field-builder.multiselect.FieldBuilderTypeMultiselect', 'Multiselect', NOW(), NOW()),
(NULL, 'Date', 'date', 'customer.components.field-builder.date.FieldBuilderTypeDate', 'Date', NOW(), NOW()),
(NULL, 'Datetime', 'datetime', 'customer.components.field-builder.datetime.FieldBuilderTypeDatetime', 'Datetime', NOW(), NOW()),
(NULL, 'Textarea', 'textarea', 'customer.components.field-builder.textarea.FieldBuilderTypeTextarea', 'Textarea', NOW(), NOW()),
(NULL, 'Country', 'country', 'customer.components.field-builder.country.FieldBuilderTypeCountry', 'Country', NOW(), NOW()),
(NULL, 'State', 'state', 'customer.components.field-builder.state.FieldBuilderTypeState', 'State', NOW(), NOW()),
(NULL, 'Checkbox List', 'checkboxlist', 'customer.components.field-builder.checkboxlist.FieldBuilderTypeCheckboxlist', 'Checkbox List', NOW(), NOW()),
(NULL, 'Radio List', 'radiolist', 'customer.components.field-builder.radiolist.FieldBuilderTypeRadiolist', 'Radio List', NOW(), NOW()),
(NULL, 'Geo Country', 'geocountry', 'customer.components.field-builder.geocountry.FieldBuilderTypeGeocountry', 'Geo Country', NOW(), NOW()),
(NULL, 'Geo State', 'geostate', 'customer.components.field-builder.geostate.FieldBuilderTypeGeostate', 'Geo State', NOW(), NOW()),
(NULL, 'Geo City', 'geocity', 'customer.components.field-builder.geocity.FieldBuilderTypeGeocity', 'Geo City', NOW(), NOW()),
(NULL, 'Checkbox', 'checkbox', 'customer.components.field-builder.checkbox.FieldBuilderTypeCheckbox', 'Checkbox', NOW(), NOW()),
(NULL, 'Consent Checkbox', 'consentcheckbox', 'customer.components.field-builder.consentcheckbox.FieldBuilderTypeConsentCheckbox', 'Consent Checkbox', NOW(), NOW()),
(NULL, 'Years Range', 'yearsrange', 'customer.components.field-builder.yearsrange.FieldBuilderTypeYearsRange', 'Years Range', NOW(), NOW()),
(NULL, 'Phone Number', 'phonenumber', 'customer.components.field-builder.phonenumber.FieldBuilderTypePhonenumber', 'Phone Number', NOW(), NOW()),
(NULL, 'Email', 'email', 'customer.components.field-builder.email.FieldBuilderTypeEmail', 'Email', NOW(), NOW()),
(NULL, 'Url', 'url', 'customer.components.field-builder.url.FieldBuilderTypeUrl', 'Url', NOW(), NOW()),
(NULL, 'Rating', 'rating', 'customer.components.field-builder.rating.FieldBuilderTypeRating', 'Rating', NOW(), NOW());


-- --------------------------------------------------------

-- -----------------------------------------------------
-- Dumping data for table `list_page_type`
-- -----------------------------------------------------

INSERT INTO `list_page_type` (`type_id`, `name`, `slug`, `description`, `content`, `full_html`, `meta_data`, `date_added`, `last_updated`) VALUES
(1, 'Subscribe form', 'subscribe-form', 'When the user will reach the subscription form, he will see this page .', '<div class="box box-primary borderless">\n<div class="box-header">\n<h3 class="box-title">[LIST_NAME]</h3>\n</div>\n\n<div class="box-body">\n<div class="callout callout-info">We''re happy you decided to subscribe to our email list.<br />\nPlease take a few seconds and fill in the list details in order to subscribe to our list.<br />\nYou will receive an email to confirm your subscription, just to be sure this is your email address.</div>\n[LIST_FIELDS]</div>\n\n<div class="box-footer">\n<div class="pull-right">[SUBMIT_BUTTON]</div>\n\n<div class="clearfix"> </div>\n</div>\n</div>\n', 'no', 0x613a303a7b7d, '2013-09-02 21:47:32', '2014-03-15 14:54:24'),
(2, 'Pending subscribe', 'subscribe-pending', 'After the user will submit the subscription form, he will see this page.', '<div class="box box-primary borderless">\n<div class="box-header">\n<h3 class="box-title">[LIST_NAME]</h3>\n</div>\n\n<div class="box-body">\n<div class="callout callout-info">Please check your email address in order to confirm your subscription.<br />\nThanks.</div>\n</div>\n</div>\n', 'no', 0x613a303a7b7d, '2013-09-02 21:47:56', '2014-03-15 14:54:24'),
(3, 'Subscription confirmed', 'subscribe-confirm', 'After the user will click the confirmation link from within the email, he will see this page.', '<div class="box box-primary borderless">\n<div class="box-header">\n<h3 class="box-title">[LIST_NAME]</h3>\n</div>\n\n<div class="box-body">\n<div class="callout callout-info">Congratulations, your subscription is now complete.<br />\nYou can always update your profile by visiting the following url:<br /><a href="[UPDATE_PROFILE_URL]">Update profile</a></div>\n</div>\n</div>\n', 'no', 0x613a303a7b7d, '2013-09-02 21:48:48', '2014-03-15 14:54:24'),
(4, 'Update Profile', 'update-profile', 'This page will contain all the elements the subscription form contains, the only difference is the heading message.', '<div class="box box-primary borderless">\n<div class="box-header">\n<h3 class="box-title">[LIST_NAME]</h3>\n</div>\n\n<div class="box-body">\n<div class="callout callout-info">Use this form to update your profile information.</div>\n[LIST_FIELDS]</div>\n\n<div class="box-footer">\n<div class="pull-right">[SUBMIT_BUTTON]</div>\n\n<div class="clearfix"> </div>\n</div>\n</div>\n', 'no', 0x613a303a7b7d, '2013-09-02 21:49:40', '2014-03-15 14:54:24'),
(5, 'Unsubscribe form', 'unsubscribe-form', 'This is the form the user will see when following the unsubscribe link.', '<div class="box box-primary borderless">\n<div class="box-header">\n<h3 class="box-title">[LIST_NAME]</h3>\n</div>\n\n<div class="box-body">\n<div class="callout callout-info">We''re sorry to see you go, but hey, no hard feelings, hopefully we will see you back one day.<br />\nPlease fill in your email address in order to unsubscribe from the list.<br />\nYou will receive an email to confirm your unsubscription, just to make sure this is not an accident or somebody else tries to unsubscribe you.</div>\n[UNSUBSCRIBE_EMAIL_FIELD]<br />\n[UNSUBSCRIBE_REASON_FIELD]</div>\n\n<div class="box-footer">\n<div class="pull-right">[SUBMIT_BUTTON]</div>\n\n<div class="clearfix"> </div>\n</div>\n</div>\n', 'no', 0x613a303a7b7d, '2013-09-03 11:18:44', '2014-03-15 14:54:24'),
(6, 'Unsubscribe confirmation', 'unsubscribe-confirm', 'When the user clicks on the unsubscribe link from within the email, he will see this page.', '<div class="box box-primary borderless">\n<div class="box-header">\n<h3 class="box-title">[LIST_NAME]</h3>\n</div>\n\n<div class="box-body">\n<div class="callout callout-info">You were successfully removed from the [LIST_NAME] list.<br />\nHopefully you will come back one day.<br /><br />\nHaving doubts?<br />\nPlease click <a href="[SUBSCRIBE_URL]">here</a> in order to subscribe again to the list.</div>\n</div>\n</div>\n', 'no', 0x613a303a7b7d, '2013-09-03 12:14:37', '2014-03-15 14:54:24'),
(7, 'Subscribe confirm email', 'subscribe-confirm-email', 'The email the user receives with the confirmation link', '<!DOCTYPE html>\n<html><head><title>[LIST_NAME]</title><meta content="utf-8" name="charset"><style type="text/css">\n\n	\n	\n	#outlook a{padding:0;}\n	body {width:100% !important; -webkit-text-size-adjust:none; margin:0; padding:0; font-family:  sans-serif; background: #f5f5f5; font-size:12px;}\n	img {border:0;height:auto;line-height:100%;outline:none;text-decoration:none;}\n	table td{border-collapse:collapse;}\n	a {color: #008ca9;text-decoration:none}\n	a:hover {color: #008ca9;text-decoration:none;}\n	#wrap {background:#f5f5f5; padding:10px;}\n	table#main-table {-webkit-border-radius:5px;-moz-border-radius:5px;border-radius:5px; border:1px solid #008ca9; overflow:hidden; background: #FFFFFF; width: 600px}\n	h1{padding:0; margin:0; font-family: sans-serif;font-size:25px;font-style:italic;color:#FFFFFF; font-weight:bold;}\n	h1 small{font-size:13px;font-weight:normal; font-family:  sans-serif; font-style:italic;}\n	h6{font-size:10px;color:#FFFFFF;margin:0;padding:0;font-weight:normal}\n	.darkbg {background: #008ca9}\n	input{outline:none}\n</style>\n</head><body dir="undefined" style="width:100%;-webkit-text-size-adjust:none;margin:0;padding:0;font-family:sans-serif;background:#f5f5f5;font-size:12px">\n                \n            <div id="wrap" style="background:#f5f5f5;padding:10px">\n<table align="center" border="0" cellpadding="0" cellspacing="0" id="main-table" style="-webkit-border-radius:5px;-moz-border-radius:5px;border-radius:5px;border:1px solid #008ca9;overflow:hidden;background:#FFFFFF;width:600px"><tbody><tr><td class="darkbg" style="border-collapse:collapse;background:#008ca9">\n			<table border="0" cellpadding="0" cellspacing="20" width="100%"><tbody><tr><td style="border-collapse:collapse">\n						<h1 style="padding:0;margin:0;font-family:sans-serif;font-size:25px;font-style:italic;color:#FFFFFF;font-weight:bold">[LIST_NAME] <small style="font-size:13px;font-weight:normal;font-family:sans-serif;font-style:italic">[COMPANY_NAME]</small></h1>\n						</td>\n					</tr></tbody></table></td>\n		</tr><tr><td style="border-collapse:collapse">\n			<table border="0" cellpadding="20" cellspacing="0" width="100%"><tbody><tr><td style="border-collapse:collapse">&nbsp;</td>\n					</tr><tr><td style="border-collapse:collapse">Please click <a href="[SUBSCRIBE_URL]" style="color:#008ca9;text-decoration:none">here</a> in order to complete your subscription.<br>\n						If for any reason you cannot access the link, please copy the following url into your browser address bar:<br>\n						[SUBSCRIBE_URL]</td>\n					</tr><tr><td style="border-collapse:collapse">&nbsp;</td>\n					</tr></tbody></table></td>\n		</tr><tr><td class="darkbg" style="padding:10px;border-collapse:collapse;background:#008ca9">\n			<h6 style="font-size:10px;color:#FFFFFF;margin:0;padding:0;font-weight:normal">&copy; [CURRENT_YEAR] [COMPANY_NAME]. All rights reserved</h6>\n			</td>\n		</tr></tbody></table></div></body></html>\n', 'yes', 0x613a303a7b7d, '2013-09-05 13:39:56', '2014-03-15 14:54:24'),
(8, 'Unsubscribe confirm email', 'unsubscribe-confirm-email', 'The email the user receives with the confirmation link to unsubscribe', '<!DOCTYPE html>\n<html><head><title>[LIST_NAME]</title><meta content="utf-8" name="charset"><style type="text/css">\n\n	\n	\n	#outlook a{padding:0;}\n	body {width:100% !important; -webkit-text-size-adjust:none; margin:0; padding:0; font-family:  sans-serif; background: #f5f5f5; font-size:12px;}\n	img {border:0;height:auto;line-height:100%;outline:none;text-decoration:none;}\n	table td{border-collapse:collapse;}\n	a {color: #008ca9;text-decoration:none}\n	a:hover {color: #008ca9;text-decoration:none;}\n	#wrap {background:#f5f5f5; padding:10px;}\n	table#main-table {-webkit-border-radius:5px;-moz-border-radius:5px;border-radius:5px; border:1px solid #008ca9; overflow:hidden; background: #FFFFFF; width: 600px}\n	h1{padding:0; margin:0; font-family: sans-serif;font-size:25px;font-style:italic;color:#FFFFFF; font-weight:bold;}\n	h1 small{font-size:13px;font-weight:normal; font-family:  sans-serif; font-style:italic;}\n	h6{font-size:10px;color:#FFFFFF;margin:0;padding:0;font-weight:normal}\n	.darkbg {background: #008ca9}\n	input{outline:none}\n</style>\n</head><body dir="undefined" style="width:100%;-webkit-text-size-adjust:none;margin:0;padding:0;font-family:sans-serif;background:#f5f5f5;font-size:12px">\n                \n            <div id="wrap" style="background:#f5f5f5;padding:10px">\n<table align="center" border="0" cellpadding="0" cellspacing="0" id="main-table" style="-webkit-border-radius:5px;-moz-border-radius:5px;border-radius:5px;border:1px solid #008ca9;overflow:hidden;background:#FFFFFF;width:600px"><tbody><tr><td class="darkbg" style="border-collapse:collapse;background:#008ca9">\n			<table border="0" cellpadding="0" cellspacing="20" width="100%"><tbody><tr><td style="border-collapse:collapse">\n						<h1 style="padding:0;margin:0;font-family:sans-serif;font-size:25px;font-style:italic;color:#FFFFFF;font-weight:bold">[LIST_NAME] <small style="font-size:13px;font-weight:normal;font-family:sans-serif;font-style:italic">[COMPANY_NAME]</small></h1>\n						</td>\n					</tr></tbody></table></td>\n		</tr><tr><td style="border-collapse:collapse">\n			<table border="0" cellpadding="20" cellspacing="0" width="100%"><tbody><tr><td style="border-collapse:collapse">&nbsp;</td>\n					</tr><tr><td style="border-collapse:collapse">Please click <a href="[UNSUBSCRIBE_URL]" style="color:#008ca9;text-decoration:none">here</a> in order to unsubscribe.<br>\n						If for any reason you cannot access the link, please copy the following url into your browser address bar:<br>\n						[UNSUBSCRIBE_URL]</td>\n					</tr><tr><td style="border-collapse:collapse">&nbsp;</td>\n					</tr></tbody></table></td>\n		</tr><tr><td class="darkbg" style="padding:10px;border-collapse:collapse;background:#008ca9">\n			<h6 style="font-size:10px;color:#FFFFFF;margin:0;padding:0;font-weight:normal">&copy; [CURRENT_YEAR] [COMPANY_NAME]. All rights reserved</h6>\n			</td>\n		</tr></tbody></table></div></body></html>\n', 'yes', 0x613a303a7b7d, '2013-09-05 13:39:56', '2014-03-15 14:54:24'),
(9, 'Welcome email', 'welcome-email', 'The email the user receives after he successfully subscribes into the list', '<!DOCTYPE html>\n<html><head><title>[LIST_NAME]</title><meta content="utf-8" name="charset">\n<style type="text/css">\n#outlook a{padding:0;}\n	body {width:100% !important; -webkit-text-size-adjust:none; margin:0; padding:0; font-family:  sans-serif; background: #f5f5f5; font-size:12px;}\n	img {border:0;height:auto;line-height:100%;outline:none;text-decoration:none;}\n	table td{border-collapse:collapse;}\n	a {color: #008ca9;text-decoration:none}\n	a:hover {color: #008ca9;text-decoration:none;}\n	#wrap {background:#f5f5f5; padding:10px;}\n	table#main-table {-webkit-border-radius:5px;-moz-border-radius:5px;border-radius:5px; border:1px solid #008ca9; overflow:hidden; background: #FFFFFF; width: 600px}\n	h1{padding:0; margin:0; font-family: sans-serif;font-size:25px;font-style:italic;color:#FFFFFF; font-weight:bold;}\n	h1 small{font-size:13px;font-weight:normal; font-family:  sans-serif; font-style:italic;}\n	h6{font-size:10px;color:#FFFFFF;margin:0;padding:0;font-weight:normal}\n	.darkbg {background: #008ca9}\n	input{outline:none}\n</style>\n</head><body style="width:100%;-webkit-text-size-adjust:none;margin:0;padding:0;font-family:sans-serif;background:#f5f5f5;font-size:12px">\n                \n            <div id="wrap" style="background:#f5f5f5;padding:10px">\n<table align="center" border="0" cellpadding="0" cellspacing="0" id="main-table" style="-webkit-border-radius:5px;-moz-border-radius:5px;border-radius:5px;border:1px solid #008ca9;overflow:hidden;background:#FFFFFF;width:600px"><tbody><tr><td class="darkbg" style="border-collapse:collapse;background:#008ca9">\n			<table border="0" cellpadding="0" cellspacing="20" width="100%"><tbody><tr><td style="border-collapse:collapse">\n						<h1 style="padding:0;margin:0;font-family:sans-serif;font-size:25px;font-style:italic;color:#FFFFFF;font-weight:bold">[LIST_NAME] <small style="font-size:13px;font-weight:normal;font-family:sans-serif;font-style:italic">[COMPANY_NAME]</small></h1>\n						</td>\n					</tr></tbody></table></td>\n		</tr><tr><td style="border-collapse:collapse">\n			<table border="0" cellpadding="20" cellspacing="0" width="100%"><tbody><tr><td style="border-collapse:collapse">&nbsp;</td>\n					</tr><tr><td style="border-collapse:collapse">Thank you for subscribing into [LIST_NAME] email list.<br>\n						You can update your information at any time by clicking <a href="[UPDATE_PROFILE_URL]" style="color:#008ca9;text-decoration:none">here</a>.<br>\n						Thank you.</td>\n					</tr><tr><td style="border-collapse:collapse">&nbsp;</td>\n					</tr></tbody></table></td>\n		</tr><tr><td class="darkbg" style="padding:10px;border-collapse:collapse;background:#008ca9">\n			<h6 style="font-size:10px;color:#FFFFFF;margin:0;padding:0;font-weight:normal">&copy; [CURRENT_YEAR] [COMPANY_NAME]. All rights reserved</h6>\n			</td>\n		</tr></tbody></table></div></body></html>\n', 'yes', 0x613a303a7b7d, '2013-09-05 13:39:56', '2015-03-19 11:13:09'),
(10, 'Subscription confirmed approval', 'subscribe-confirm-approval', 'After the user will click the confirmation link from within the email, if the list requires confirm approval, he will see this page.', '<div class="box box-primary borderless">\n<div class="box-header">\n<h3 class="box-title">[LIST_NAME]</h3>\n</div>\n\n<div class="box-body">\n<div class="callout callout-info">Congratulations, your subscription is now complete and awaiting approval.<br />\nOnce the approval process is done, you will get a confirmation email with further instructions.<br />\nThanks.</div>\n</div>\n</div>\n', 'no', 0x613a303a7b7d, '2013-09-02 21:48:48', '2014-03-15 14:54:24'),
(11, 'Subscription confirmed approval email', 'subscribe-confirm-approval-email', 'The email the user receives after his subscription is approved.', '<!DOCTYPE html>\r\n<html><head><title>[LIST_NAME]</title><meta content="utf-8" name="charset">\r\n<style type="text/css">\r\n#outlook a{padding:0;}\r\n	body {width:100% !important; -webkit-text-size-adjust:none; margin:0; padding:0; font-family:  sans-serif; background: #f5f5f5; font-size:12px;}\r\n	img {border:0;height:auto;line-height:100%;outline:none;text-decoration:none;}\r\n	table td{border-collapse:collapse;}\r\n	a {color: #008ca9;text-decoration:none}\r\n	a:hover {color: #008ca9;text-decoration:none;}\r\n	#wrap {background:#f5f5f5; padding:10px;}\r\n	table#main-table {-webkit-border-radius:5px;-moz-border-radius:5px;border-radius:5px; border:1px solid #008ca9; overflow:hidden; background: #FFFFFF; width: 600px}\r\n	h1{padding:0; margin:0; font-family: sans-serif;font-size:25px;font-style:italic;color:#FFFFFF; font-weight:bold;}\r\n	h1 small{font-size:13px;font-weight:normal; font-family:  sans-serif; font-style:italic;}\r\n	h6{font-size:10px;color:#FFFFFF;margin:0;padding:0;font-weight:normal}\r\n	.darkbg {background: #008ca9}\r\n	input{outline:none}\r\n</style>\r\n</head><body style="width:100%;-webkit-text-size-adjust:none;margin:0;padding:0;font-family:sans-serif;background:#f5f5f5;font-size:12px">\r\n                \r\n            <div id="wrap" style="background:#f5f5f5;padding:10px">\r\n<table align="center" border="0" cellpadding="0" cellspacing="0" id="main-table" style="-webkit-border-radius:5px;-moz-border-radius:5px;border-radius:5px;border:1px solid #008ca9;overflow:hidden;background:#FFFFFF;width:600px"><tbody><tr><td class="darkbg" style="border-collapse:collapse;background:#008ca9">\r\n			<table border="0" cellpadding="0" cellspacing="20" width="100%"><tbody><tr><td style="border-collapse:collapse">\r\n						<h1 style="padding:0;margin:0;font-family:sans-serif;font-size:25px;font-style:italic;color:#FFFFFF;font-weight:bold">[LIST_NAME] <small style="font-size:13px;font-weight:normal;font-family:sans-serif;font-style:italic">[COMPANY_NAME]</small></h1>\r\n						</td>\r\n					</tr></tbody></table></td>\r\n		</tr><tr><td style="border-collapse:collapse">\r\n			<table border="0" cellpadding="20" cellspacing="0" width="100%"><tbody><tr><td style="border-collapse:collapse">&nbsp;</td>\r\n					</tr><tr><td style="border-collapse:collapse">Congratulations, <br />Your subscription into [LIST_NAME] email list is now approved.<br>\r\n						You can update your information at any time by clicking <a href="[UPDATE_PROFILE_URL]" style="color:#008ca9;text-decoration:none">here</a>.<br>\r\n						Thank you.</td>\r\n					</tr><tr><td style="border-collapse:collapse">&nbsp;</td>\r\n					</tr></tbody></table></td>\r\n		</tr><tr><td class="darkbg" style="padding:10px;border-collapse:collapse;background:#008ca9">\r\n			<h6 style="font-size:10px;color:#FFFFFF;margin:0;padding:0;font-weight:normal">&copy; [CURRENT_YEAR] [COMPANY_NAME]. All rights reserved</h6>\r\n			</td>\r\n		</tr></tbody></table></div></body></html>\r\n', 'yes', 0x613a303a7b7d, '2013-09-05 13:39:56', '2015-03-19 11:13:09');


-- --------------------------------------------------------

-- -----------------------------------------------------
-- Dumping data for table `list_segment_operator`
-- -----------------------------------------------------

INSERT INTO `list_segment_operator` (`operator_id`, `name`, `slug`, `date_added`, `last_updated`) VALUES
(1, 'is', 'is', '2013-09-06 16:06:01', '2013-09-06 16:06:01'),
(2, 'is not', 'is-not', '2013-09-06 16:06:01', '2013-09-06 16:06:01'),
(3, 'contains', 'contains', '2013-09-06 16:06:01', '2013-09-06 16:06:01'),
(4, 'not contains', 'not-contains', '2013-09-06 16:06:01', '2013-09-06 16:06:01'),
(5, 'starts with', 'starts', '2013-09-06 16:06:01', '2013-09-06 16:06:01'),
(6, 'ends with', 'ends', '2013-09-06 16:06:01', '2013-09-06 16:06:01'),
(7, 'is greater than', 'greater', '2013-09-06 16:06:01', '2013-09-06 16:06:01'),
(8, 'is less than', 'less', '2013-09-06 16:06:01', '2013-09-06 16:06:01'),
(9, 'not starts with', 'not-starts', '2013-09-06 16:06:01', '2013-09-06 16:06:01'),
(10, 'not ends with', 'not-ends', '2013-09-06 16:06:01', '2013-09-06 16:06:01');

-- --------------------------------------------------------

-- -----------------------------------------------------
-- Dumping data for table `option`
-- -----------------------------------------------------

INSERT INTO `option` (`category`, `key`, `value`, `is_serialized`, `date_added`, `last_updated`) VALUES
('system', 'common', 0x6170705f6e616d65, 0, '2013-10-29 23:31:29', '2013-10-29 23:31:29'),
('system.campaign.attachments', 'allowed_extensions', 0x613a393a7b693a303b733a333a22706466223b693a313b733a333a22646f63223b693a323b733a343a22646f6378223b693a333b733a333a22786c73223b693a343b733a343a22786c7378223b693a353b733a333a22707074223b693a363b733a343a2270707478223b693a373b733a333a227a6970223b693a383b733a333a22726172223b7d, 1, '2014-01-14 09:57:08', '2014-01-18 11:09:50'),
('system.campaign.attachments', 'allowed_files_count', 0x35, 0, '2014-01-14 10:07:14', '2014-01-14 10:07:14'),
('system.campaign.attachments', 'allowed_file_size', 0x31303438353736, 0, '2014-01-14 10:07:14', '2014-01-14 10:12:53'),
('system.campaign.attachments', 'allowed_mime_types', 0x613a34313a7b693a303b733a31353a226170706c69636174696f6e2f706466223b693a313b733a31373a226170706c69636174696f6e2f782d706466223b693a323b733a31393a226170706c69636174696f6e2f6163726f626174223b693a333b733a32303a226170706c69636174696f6e732f766e642e706466223b693a343b733a383a22746578742f706466223b693a353b733a31303a22746578742f782d706466223b693a363b733a31383a226170706c69636174696f6e2f6d73776f7264223b693a373b733a31353a226170706c69636174696f6e2f646f63223b693a383b733a393a226170706c2f74657874223b693a393b733a32323a226170706c69636174696f6e2f766e642e6d73776f7264223b693a31303b733a32333a226170706c69636174696f6e2f766e642e6d732d776f7264223b693a31313b733a31393a226170706c69636174696f6e2f77696e776f7264223b693a31323b733a31363a226170706c69636174696f6e2f776f7264223b693a31333b733a31383a226170706c69636174696f6e2f782d6d737736223b693a31343b733a32303a226170706c69636174696f6e2f782d6d73776f7264223b693a31353b733a37313a226170706c69636174696f6e2f766e642e6f70656e786d6c666f726d6174732d6f6666696365646f63756d656e742e776f726470726f63657373696e676d6c2e646f63756d656e74223b693a31363b733a32343a226170706c69636174696f6e2f766e642e6d732d657863656c223b693a31373b733a31393a226170706c69636174696f6e2f6d73657863656c223b693a31383b733a32313a226170706c69636174696f6e2f782d6d73657863656c223b693a31393b733a32323a226170706c69636174696f6e2f782d6d732d657863656c223b693a32303b733a31393a226170706c69636174696f6e2f782d657863656c223b693a32313b733a32363a226170706c69636174696f6e2f782d646f735f6d735f657863656c223b693a32323b733a31353a226170706c69636174696f6e2f786c73223b693a32333b733a36353a226170706c69636174696f6e2f766e642e6f70656e786d6c666f726d6174732d6f6666696365646f63756d656e742e73707265616473686565746d6c2e7368656574223b693a32343b733a32393a226170706c69636174696f6e2f766e642e6d732d706f776572706f696e74223b693a32353b733a32343a226170706c69636174696f6e2f6d73706f776572706f696e74223b693a32363b733a32353a226170706c69636174696f6e2f6d732d706f776572706f696e74223b693a32373b733a32323a226170706c69636174696f6e2f6d73706f776572706e74223b693a32383b733a32383a226170706c69636174696f6e2f766e642d6d73706f776572706f696e74223b693a32393b733a32323a226170706c69636174696f6e2f706f776572706f696e74223b693a33303b733a32343a226170706c69636174696f6e2f782d706f776572706f696e74223b693a33313b733a31353a226170706c69636174696f6e2f782d6d223b693a33323b733a37333a226170706c69636174696f6e2f766e642e6f70656e786d6c666f726d6174732d6f6666696365646f63756d656e742e70726573656e746174696f6e6d6c2e70726573656e746174696f6e223b693a33333b733a31353a226170706c69636174696f6e2f7a6970223b693a33343b733a31373a226170706c69636174696f6e2f782d7a6970223b693a33353b733a32383a226170706c69636174696f6e2f782d7a69702d636f6d70726573736564223b693a33363b733a32343a226170706c69636174696f6e2f6f637465742d73747265616d223b693a33373b733a32323a226170706c69636174696f6e2f782d636f6d7072657373223b693a33383b733a32343a226170706c69636174696f6e2f782d636f6d70726573736564223b693a33393b733a31353a226d756c7469706172742f782d7a6970223b693a34303b733a32383a226170706c69636174696f6e2f782d7261722d636f6d70726573736564223b7d, 1, '2014-01-14 09:57:08', '2014-01-18 11:09:50'),
('system.campaign.attachments', 'enabled', 0x6e6f, 0, '2014-01-14 09:57:08', '2014-01-14 10:09:42'),
('system.common', 'api_status', 0x6f6e6c696e65, 0, '2013-10-16 11:37:11', '2013-10-16 11:37:20'),
('system.common', 'app_name', 0x4d61696c57697a7a20454d53, 0, '2013-10-29 23:33:18', '2013-10-29 23:33:18'),
('system.common', 'app_version', 0x312e30, 0, '2013-10-29 23:33:18', '2013-10-29 23:33:18'),
('system.common', 'clean_urls', 0x30, 0, '2013-09-04 09:43:07', '2013-11-01 13:43:11'),
('system.common', 'site_description', 0x456d61696c206d61726b6574696e67206170706c69636174696f6e, 0, '2013-09-04 08:47:17', '2013-10-26 19:39:46'),
('system.common', 'site_keywords', 0x656d61696c2c206d61726b6574696e672c20656d61696c206d61726b6574696e672c20656d61696c2064656c69766572792c2064656c69766572792c20696e626f782064656c6976657279, 0, '2013-09-04 08:47:17', '2013-10-26 20:06:12'),
('system.common', 'site_name', 0x4d61696c57697a7a, 0, '2013-09-04 08:39:08', '2013-09-04 08:39:08'),
('system.common', 'site_offline_message', 0x4170706c69636174696f6e2063757272656e746c79206f66666c696e652e2054727920616761696e206c6174657221, 0, '2013-09-22 23:26:00', '2013-09-22 23:26:40'),
('system.common', 'site_status', 0x6f6e6c696e65, 0, '2013-09-22 22:47:38', '2013-10-14 00:03:42'),
('system.common', 'site_tagline', 0x456d61696c206d61726b6574696e67206170706c69636174696f6e, 0, '2013-09-04 08:39:08', '2013-10-26 19:39:46'),
('system.cron.process_delivery_bounce', 'max_fatal_errors', 0x31, 0, '2013-10-07 11:36:23', '2013-10-22 20:32:22'),
('system.cron.process_delivery_bounce', 'max_hard_bounce', 0x31, 0, '2013-10-07 11:36:23', '2013-10-22 20:32:22'),
('system.cron.process_delivery_bounce', 'max_soft_bounce', 0x35, 0, '2013-10-07 11:36:23', '2013-10-07 11:36:23'),
('system.cron.process_delivery_bounce', 'max_soft_errors', 0x35, 0, '2013-10-07 11:36:23', '2013-10-07 11:36:23'),
('system.cron.process_delivery_bounce', 'memory_limit', '', 0, '2013-10-07 11:36:23', '2013-11-01 13:42:47'),
('system.cron.process_delivery_bounce', 'process_at_once', 0x313030, 0, '2013-10-07 11:36:23', '2013-10-07 11:36:23'),
('system.cron.send_campaigns', 'campaigns_at_once', 0x35, 0, '2013-10-07 11:12:18', '2013-11-01 13:41:55'),
('system.cron.send_campaigns', 'change_server_at', 0x323030, 0, '2013-10-07 13:08:56', '2013-10-16 23:56:27'),
('system.cron.send_campaigns', 'emails_per_minute', 0x313030, 0, '2013-10-07 11:12:18', '2013-10-07 11:12:18'),
('system.cron.send_campaigns', 'memory_limit', '', 0, '2013-10-07 11:12:18', '2013-11-01 13:42:47'),
('system.cron.send_campaigns', 'parallel_processes_per_campaign', 0x33, 0, '2014-02-16 22:39:58', '2014-02-16 22:39:58'),
('system.cron.send_campaigns', 'pause', 0x3130, 0, '2013-10-07 11:12:18', '2013-10-08 23:57:33'),
('system.cron.send_campaigns', 'send_at_once', 0x3530, 0, '2013-10-07 11:12:18', '2013-11-01 13:41:55'),
('system.cron.send_campaigns', 'subscribers_at_once', 0x313030, 0, '2013-10-07 11:12:18', '2013-11-01 13:41:55'),
('system.cron.send_campaigns', 'auto_adjust_campaigns_at_once', 0x6e6f, 0, '2018-12-05 08:51:26', '2018-12-05 08:51:26'),
('system.email_blacklist', 'local_check', 0x796573, 0, '2014-01-09 14:48:41', '2014-01-09 15:18:22'),
('system.email_blacklist', 'remote_dnsbls', 0x613a323a7b693a303b733a31343a22626c2e7370616d636f702e6e6574223b693a313b733a31363a227a656e2e7370616d686175732e6f7267223b7d, 1, '2014-01-09 14:48:41', '2014-01-09 16:21:11'),
('system.email_templates', 'common', 0x3c21444f43545950452068746d6c3e0d0a3c68746d6c3e0d0a3c686561643e0d0a093c7469746c653e4d61696c57697a7a3c2f7469746c653e0d0a093c6d65746120636f6e74656e743d227574662d3822206e616d653d2263686172736574223e0d0a093c7374796c6520747970653d22746578742f637373223e236f75746c6f6f6b20617b70616464696e673a303b7d0d0a09626f6479207b77696474683a313030252021696d706f7274616e743b202d7765626b69742d746578742d73697a652d61646a7573743a6e6f6e653b206d617267696e3a303b2070616464696e673a303b20666f6e742d66616d696c793a20274f70656e2053616e73272c2073616e732d73657269663b206261636b67726f756e643a20236635663566353b20666f6e742d73697a653a313270783b7d0d0a09696d67207b626f726465723a303b6865696768743a6175746f3b6c696e652d6865696768743a313030253b6f75746c696e653a6e6f6e653b746578742d6465636f726174696f6e3a6e6f6e653b7d0d0a097461626c652074647b626f726465722d636f6c6c617073653a636f6c6c617073653b7d0d0a0961207b636f6c6f723a20233030386361393b746578742d6465636f726174696f6e3a6e6f6e657d0d0a09613a686f766572207b636f6c6f723a20233030386361393b746578742d6465636f726174696f6e3a6e6f6e653b7d0d0a093c2f7374796c653e0d0a3c2f686561643e0d0a3c626f6479206267636f6c6f723d222366356635663522206469723d22756e646566696e656422207374796c653d2277696474683a313030253b2d7765626b69742d746578742d73697a652d61646a7573743a6e6f6e653b6d617267696e3a303b70616464696e673a303b666f6e742d66616d696c793a26616d703b233033393b4f70656e2053616e7326616d703b233033393b2c73616e732d73657269663b6261636b67726f756e643a236635663566353b666f6e742d73697a653a31327078223e0d0a3c646976207374796c653d226261636b67726f756e643a236635663566353b70616464696e673a313070783b223e0d0a3c7461626c6520616c69676e3d2263656e74657222206267636f6c6f723d22234646464646462220626f726465723d2230222063656c6c70616464696e673d2230222063656c6c73706163696e673d223022207374796c653d222d7765626b69742d626f726465722d7261646975733a3570783b2d6d6f7a2d626f726465722d7261646975733a3570783b626f726465722d7261646975733a3570783b626f726465723a31707820736f6c696420233030386361393b6f766572666c6f773a68696464656e3b222077696474683d22363030223e0d0a093c74626f64793e0d0a09093c74723e0d0a0909093c7464206267636f6c6f723d222330303863613922207374796c653d22626f726465722d636f6c6c617073653a636f6c6c617073653b223e0d0a0909093c7461626c6520626f726465723d2230222063656c6c70616464696e673d2230222063656c6c73706163696e673d223230222077696474683d2231303025223e0d0a090909093c74626f64793e0d0a09090909093c74723e0d0a0909090909093c7464207374796c653d22666f6e742d73697a653a323570783b666f6e742d7374796c653a6974616c69633b626f726465722d636f6c6c617073653a636f6c6c617073653b223e3c666f6e7420636f6c6f723d2223464646464646223e3c7370616e207374796c653d22666f6e742d66616d696c793a274e6f746f2053616e73272c2073616e732d73657269663b223e3c7374726f6e673e4d61696c57697a7a3c2f7374726f6e673e3c2f7370616e3e203c7370616e207374796c653d22666f6e742d73697a653a3530253b666f6e742d66616d696c793a274f70656e2053616e73272c2073616e732d73657269663b223e456d61696c206d61726b6574696e67206170706c69636174696f6e3c2f7370616e3e3c2f666f6e743e3c2f74643e0d0a09090909093c2f74723e0d0a090909093c2f74626f64793e0d0a0909093c2f7461626c653e0d0a0909093c2f74643e0d0a09093c2f74723e0d0a09093c74723e0d0a0909093c7464207374796c653d22626f726465722d636f6c6c617073653a636f6c6c617073653b223e0d0a0909093c7461626c6520626f726465723d2230222063656c6c70616464696e673d223230222063656c6c73706163696e673d2230222077696474683d2231303025223e0d0a090909093c74626f64793e0d0a09090909093c74723e0d0a0909090909093c7464207374796c653d22626f726465722d636f6c6c617073653a636f6c6c617073653b223ec2a03c2f74643e0d0a09090909093c2f74723e0d0a09090909093c74723e0d0a0909090909093c7464207374796c653d22666f6e742d66616d696c793a274f70656e2053616e73272c2073616e732d73657269663b666f6e742d73697a653a313270783b626f726465722d636f6c6c617073653a636f6c6c617073653b223e5b434f4e54454e545d3c2f74643e0d0a09090909093c2f74723e0d0a09090909093c74723e0d0a0909090909093c7464207374796c653d22626f726465722d636f6c6c617073653a636f6c6c617073653b223ec2a03c2f74643e0d0a09090909093c2f74723e0d0a090909093c2f74626f64793e0d0a0909093c2f7461626c653e0d0a0909093c2f74643e0d0a09093c2f74723e0d0a09093c74723e0d0a0909093c7464206267636f6c6f723d222330303863613922207374796c653d2270616464696e673a313070783b626f726465722d636f6c6c617073653a636f6c6c617073653b223e3c666f6e7420636f6c6f723d2223464646464646223e3c7370616e207374796c653d22666f6e742d73697a653a313070783b666f6e742d66616d696c793a274f70656e2053616e73272c2073616e732d73657269663b223ec2a92032303136204d61696c57697a7a2e20416c6c207269676874732072657365727665643c2f7370616e3e3c2f666f6e743e3c2f74643e0d0a09093c2f74723e0d0a093c2f74626f64793e0d0a3c2f7461626c653e0d0a3c2f6469763e0d0a3c2f626f64793e0d0a3c2f68746d6c3e0d0a, 0, '2013-10-14 10:18:10', '2016-10-27 20:33:19'),
('system.exporter', 'memory_limit', '', 0, '2013-09-29 22:23:10', '2013-11-01 13:42:33'),
('system.exporter', 'pause', 0x31, 0, '2013-09-29 22:23:10', '2013-09-29 22:24:41'),
('system.exporter', 'process_at_once', 0x353030, 0, '2013-09-29 22:23:10', '2013-09-29 22:24:41'),
('system.extension.ckeditor', 'status', 0x656e61626c6564, 0, '2013-11-07 10:33:38', '2013-11-07 10:33:38'),
('system.importer', 'file_size_limit', 0x31303438353736, 0, '2013-09-28 11:51:35', '2013-11-01 13:42:33'),
('system.importer', 'import_at_once', 0x313030, 0, '2013-09-28 11:51:35', '2013-11-01 13:42:33'),
('system.importer', 'memory_limit', '', 0, '2013-09-28 11:51:35', '2013-11-01 13:42:33'),
('system.importer', 'pause', 0x31, 0, '2013-09-28 11:51:35', '2013-11-01 13:42:33'),
('system.customer_sending', 'quota_notify_email_content', 0x48656c6c6f205b46554c4c5f4e414d455d2c20c2a03c6272202f3e3c6272202f3e0a596f7572206d6178696d756d20616c6c6f7765642073656e64696e672071756f74612069732073657420746f205b51554f54415f544f54414c5d20656d61696c7320616e6420796f752063757272656e746c7920686176652073656e74205b51554f54415f55534147455d20656d61696c732c207768696368206d65616e7320796f7520686176652075736564205b51554f54415f55534147455f50455243454e545d206f6620796f757220616c6c6f7765642073656e64696e672071756f7461213c6272202f3e0a4f6e636520796f75722073656e64696e672071756f7461206973206f7665722c20796f752077696c6c206e6f742062652061626c6520746f2073656e6420616e7920656d61696c73213c6272202f3e3c6272202f3e0a506c65617365206d616b65207375726520796f752072656e657720796f75722073656e64696e672071756f74612e3c6272202f3e0a5468616e6b20796f7521, 0, '2016-10-31 17:55:19', '2016-10-31 18:43:04');

-- --------------------------------------------------------

-- -----------------------------------------------------
-- Dumping data for table `tag_registry`
-- -----------------------------------------------------

INSERT INTO `tag_registry` (`tag_id`, `tag`, `description`, `date_added`, `last_updated`) VALUES
(NULL, '[LIST_NAME]', NULL, '2013-10-25 03:08:28', '2013-10-25 03:08:28'),
(NULL, '[LIST_FIELDS]', NULL, '2013-10-25 03:08:28', '2013-10-25 03:08:28'),
(NULL, '[UNSUBSCRIBE_EMAIL_FIELD]', NULL, '2013-10-25 03:08:28', '2013-10-25 03:08:28'),
(NULL, '[UPDATE_PROFILE_URL]', NULL, '2013-10-25 03:08:28', '2013-10-25 03:08:28'),
(NULL, '[CURRENT_YEAR]', NULL, '2013-10-25 03:08:28', '2013-10-25 03:08:28'),
(NULL, '[SUBSCRIBE_URL]', NULL, '2013-10-25 03:08:28', '2013-10-25 03:08:28'),
(NULL, '[CHARSET]', NULL, '2013-10-25 03:08:28', '2013-10-25 03:08:28'),
(NULL, '[UNSUBSCRIBE_URL]', NULL, '2013-10-25 03:08:28', '2013-10-25 03:08:28'),
(NULL, '[SUBMIT_BUTTON]', NULL, '2013-10-25 03:08:28', '2013-10-25 03:08:28'),
(NULL, '[COMPANY_NAME]', NULL, '2013-10-25 03:08:28', '2013-10-25 03:08:28'),
(NULL, '[COMPANY_COUNTRY]', NULL, '2013-10-25 03:08:28', '2013-10-25 03:08:28'),
(NULL, '[COMPANY_ZONE]', NULL, '2013-10-25 03:08:28', '2013-10-25 03:08:28'),
(NULL, '[COMPANY_CITY]', NULL, '2013-10-25 03:08:28', '2013-10-25 03:08:28'),
(NULL, '[COMPANY_ADDRESS_1]', NULL, '2013-10-25 03:08:28', '2013-10-25 03:08:28'),
(NULL, '[COMPANY_PHONE]', NULL, '2013-10-25 03:08:28', '2013-10-25 03:08:28'),
(NULL, '[COMPANY_ADDRESS_2]', NULL, '2013-10-25 03:08:28', '2013-10-25 03:08:28'),
(NULL, '[CURRENT_MONTH]', NULL, '2013-10-25 03:08:28', '2013-10-25 03:08:28'),
(NULL, '[CURRENT_DAY]', NULL, '2013-10-25 03:08:28', '2013-10-25 03:08:28'),
(NULL, '[CURRENT_DATE]', NULL, '2013-10-25 03:08:28', '2013-10-25 03:08:28'),
(NULL, '[WEB_VERSION_URL]', NULL, '2013-10-25 03:08:28', '2013-10-25 03:08:28'),
(NULL, '[LIST_DESCRIPTION]', NULL, '2013-10-25 03:08:28', '2013-10-25 03:08:28'),
(NULL, '[LIST_FROM_NAME]', NULL, '2013-10-25 03:08:28', '2013-10-25 03:08:28'),
(NULL, '[CAMPAIGN_SUBJECT]', NULL, '2013-10-25 03:08:28', '2013-10-25 03:08:28'),
(NULL, '[CAMPAIGN_FROM_NAME]', NULL, '2013-10-25 03:08:28', '2013-10-25 03:08:28'),
(NULL, '[CAMPAIGN_REPLY_TO]', NULL, '2013-10-25 03:08:28', '2013-10-25 03:08:28'),
(NULL, '[COMPANY_FULL_ADDRESS]', NULL, '2013-10-25 03:08:28', '2013-10-25 03:08:28'),
(NULL, '[COMPANY_ZIP]', NULL, '2013-10-25 03:08:28', '2013-10-25 03:08:28'),
(NULL, '[CAMPAIGN_TO_NAME]', NULL, '2013-10-25 03:08:28', '2013-10-25 03:08:28'),
(NULL, '[LIST_SUBJECT]', NULL, '2013-10-25 03:08:28', '2013-10-25 03:08:28'),
(NULL, '[CAMPAIGN_URL]', NULL, '2013-10-25 03:08:28', '2013-10-25 03:08:28'),
(NULL, '[CAMPAIGN_UID]', NULL, '2013-10-25 03:08:28', '2013-10-25 03:08:28'),
(NULL, '[SUBSCRIBER_UID]', NULL, '2013-10-25 03:08:28', '2013-10-25 03:08:28'),
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
(NULL, '[JSON_FEED_ITEM_CONTENT]', NULL, '2013-12-09 00:00:00', '2013-12-09 00:00:00'),
(NULL, '[CAMPAIGN_FROM_EMAIL]', NULL, '2014-02-02 00:00:00', '2014-02-02 00:00:00'),
(NULL, '[LIST_FROM_EMAIL]', NULL, '2014-02-02 00:00:00', '2014-02-02 00:00:00'),
(NULL, '[SUBSCRIBER_DATE_ADDED]', NULL, '2014-06-23 00:00:00', '2014-06-23 00:00:00'),
(NULL, '[SUBSCRIBER_DATE_ADDED_LOCALIZED]', NULL, '2014-06-23 00:00:00', '2014-06-23 00:00:00'),
(NULL, '[DATE]', NULL, '2014-06-23 00:00:00', '2014-06-23 00:00:00'),
(NULL, '[DATETIME]', NULL, '2014-06-23 00:00:00', '2014-06-23 00:00:00'),
(NULL, '[FORWARD_FRIEND_URL]', NULL, '2014-08-31 00:00:00', '2014-08-31 00:00:00'),
(NULL, '[CAMPAIGN_NAME]', NULL, '2014-08-31 00:00:00', '2014-08-31 00:00:00'),
(NULL, '[DIRECT_UNSUBSCRIBE_URL]', NULL, '2014-08-31 00:00:00', '2014-08-31 00:00:00'),
(NULL, '[RANDOM_CONTENT]', NULL, '2014-11-18 00:00:00', '2014-11-18 00:00:00'),
(NULL, '[CAMPAIGN_REPORT_ABUSE_URL]', NULL, '2014-11-18 00:00:00', '2014-11-18 00:00:00');

-- --------------------------------------------------------

-- -----------------------------------------------------
-- Dumping data for table `currency`
-- -----------------------------------------------------

INSERT INTO `currency` (`currency_id`, `name`, `code`, `value`, `is_default`, `status`, `date_added`, `last_updated`) VALUES
(1, 'US Dollar', 'USD', '1.00000000', 'yes', 'active', '2014-05-17 00:00:00', '2014-05-17 00:00:00');

-- --------------------------------------------------------

-- -----------------------------------------------------
-- Dumping data for table `company_type`
-- -----------------------------------------------------

INSERT INTO `company_type` (`type_id`, `name`, `date_added`, `last_updated`) VALUES
(NULL, 'Agriculture and Food Services', '2014-08-31 09:45:05', '2014-08-31 09:45:05'),
(NULL, 'Architecture and Construction', '2014-08-31 09:45:05', '2014-08-31 09:45:05'),
(NULL, 'Arts and Artists', '2014-08-31 09:45:05', '2014-08-31 09:45:05'),
(NULL, 'Beauty and Personal Care', '2014-08-31 09:45:05', '2014-08-31 09:45:05'),
(NULL, 'Business and Finance', '2014-08-31 09:45:05', '2014-08-31 09:45:05'),
(NULL, 'Computers and Electronics', '2014-08-31 09:45:05', '2014-08-31 09:45:05'),
(NULL, 'Construction ', '2014-08-31 09:45:05', '2014-08-31 09:45:05'),
(NULL, 'Consulting', '2014-08-31 09:45:05', '2014-08-31 09:45:05'),
(NULL, 'Creative Services/Agency', '2014-08-31 09:45:05', '2014-08-31 09:45:05'),
(NULL, 'Daily Deals/E-Coupons', '2014-08-31 09:45:05', '2014-08-31 09:45:05'),
(NULL, 'eCommerce', '2014-08-31 09:45:05', '2014-08-31 09:45:05'),
(NULL, 'Education and Training', '2014-08-31 09:45:05', '2014-08-31 09:45:05'),
(NULL, 'Entertainment and Events', '2014-08-31 09:45:05', '2014-08-31 09:45:05'),
(NULL, 'Gambling', '2014-08-31 09:45:05', '2014-08-31 09:45:05'),
(NULL, 'Games', '2014-08-31 09:45:05', '2014-08-31 09:45:05'),
(NULL, 'Government', '2014-08-31 09:45:05', '2014-08-31 09:45:05'),
(NULL, 'Health and Fitness', '2014-08-31 09:45:05', '2014-08-31 09:45:05'),
(NULL, 'Hobbies', '2014-08-31 09:45:05', '2014-08-31 09:45:05'),
(NULL, 'Home and Garden', '2014-08-31 09:45:05', '2014-08-31 09:45:05'),
(NULL, 'Insurance', '2014-08-31 09:45:05', '2014-08-31 09:45:05'),
(NULL, 'Legal', '2014-08-31 09:45:05', '2014-08-31 09:45:05'),
(NULL, 'Manufacturing', '2014-08-31 09:45:05', '2014-08-31 09:45:05'),
(NULL, 'Marketing and Advertising', '2014-08-31 09:45:05', '2014-08-31 09:45:05'),
(NULL, 'Media and Publishing', '2014-08-31 09:45:05', '2014-08-31 09:45:05'),
(NULL, 'Medical, Dental, and Healthcare', '2014-08-31 09:45:05', '2014-08-31 09:45:05'),
(NULL, 'Mobile', '2014-08-31 09:45:05', '2014-08-31 09:45:05'),
(NULL, 'Music and Musicians', '2014-08-31 09:45:05', '2014-08-31 09:45:05'),
(NULL, 'Non-Profit', '2014-08-31 09:45:05', '2014-08-31 09:45:05'),
(NULL, 'Pharmaceuticals', '2014-08-31 09:45:05', '2014-08-31 09:45:05'),
(NULL, 'Photo and Video', '2014-08-31 09:45:05', '2014-08-31 09:45:05'),
(NULL, 'Politics', '2014-08-31 09:45:05', '2014-08-31 09:45:05'),
(NULL, 'Professional Services', '2014-08-31 09:45:05', '2014-08-31 09:45:05'),
(NULL, 'Public Relations', '2014-08-31 09:45:05', '2014-08-31 09:45:05'),
(NULL, 'Real Estate', '2014-08-31 09:45:05', '2014-08-31 09:45:05'),
(NULL, 'Recruitment and Staffing', '2014-08-31 09:45:05', '2014-08-31 09:45:05'),
(NULL, 'Religion', '2014-08-31 09:45:05', '2014-08-31 09:45:05'),
(NULL, 'Restaurant and Venue', '2014-08-31 09:45:05', '2014-08-31 09:45:05'),
(NULL, 'Retail', '2014-08-31 09:45:05', '2014-08-31 09:45:05'),
(NULL, 'Social Networks and Online Communities', '2014-08-31 09:45:05', '2014-08-31 09:45:05'),
(NULL, 'Software and Web App', '2014-08-31 09:45:05', '2014-08-31 09:45:05'),
(NULL, 'Sports', '2014-08-31 09:45:05', '2014-08-31 09:45:05'),
(NULL, 'Telecommunications', '2014-08-31 09:45:05', '2014-08-31 09:45:05'),
(NULL, 'Travel and Transportation', '2014-08-31 09:45:05', '2014-08-31 09:45:05'),
(NULL, 'Vitamin supplements', '2014-08-31 09:45:05', '2014-08-31 09:45:05'),
(NULL, 'Other', '2014-08-31 09:45:05', '2014-08-31 09:45:05');

-- --------------------------------------------------------

-- -----------------------------------------------------
-- Dumping data for table `start_page`
-- -----------------------------------------------------

INSERT INTO `start_page` (`page_id`, `application`, `route`, `icon`, `heading`, `content`, `date_added`, `last_updated`) VALUES
(NULL, 'customer', 'campaigns/index', 'fa-envelope', 'Create your first campaign', 'Start creating your first campaign to reach your target audience.<br />\nYou can create Regular, Recurring or Autoresponder campaigns that target one<br />\nor more lists or even one or more segments of your lists and schedule them for sending at the right time.', '2017-03-09 12:17:05', '2017-03-14 07:50:28'),
(NULL, 'customer', 'lists/index', 'glyphicon-list-alt', 'Create your first email list', 'Start creating your first email list, add subscribers to it, edit it\'s forms and pages<br />\nand create custom fields that you can later use for segmentation.', '2017-03-09 12:18:39', '2017-03-14 07:49:48'),
(NULL, 'customer', 'templates/index', 'glyphicon-text-width', 'Create your first email template', 'Create your first email template that you can later use in campaigns.<br />\nYou can set the base template here and edit it further in campaigns specifically for the given campaign.', '2017-03-10 07:09:26', '2017-03-14 07:49:12'),
(NULL, 'customer', 'delivery_servers/index', 'glyphicon-send', 'Create your first delivery server', 'Delivery servers are responsible for deliverying the emails to the subscribers.<br />\nYou have a wide range of delivery server types you can choose from. ', '2017-03-10 07:11:32', '2017-03-14 07:47:23'),
(NULL, 'customer', 'bounce_servers/index', 'glyphicon-filter', 'Create your first bounce server', 'Bounce servers are used to take action against the email addresses<br />\nof the subscribers that bounce back when campaigns are sent to them.', '2017-03-10 07:13:52', '2017-03-14 07:47:10'),
(NULL, 'customer', 'feedback_loop_servers/index', 'glyphicon-transfer', 'Create your first feedback loop server', 'Feedback loop servers will help monitoring the abuse reports that subscribers do<br />\nand take proper action when it finds such reports.', '2017-03-10 07:15:11', '2017-03-14 07:48:06'),
(NULL, 'customer', 'sending_domains/index', 'glyphicon-globe', 'Create your first sending domain', 'Sending domains will match the FROM address of the email campaigns and<br />\nwill add proper DKIM signatures to the email headers, thus increasing the chances for the emails to land inbox.', '2017-03-10 07:16:53', '2017-03-14 07:45:58'),
(NULL, 'customer', 'tracking_domains/index', 'glyphicon-globe', 'Create your first tracking domain', 'Tracking domains allow masking of the domains used in the tracking urls<br />\nfrom email campaigns with other domains that you specify here.', '2017-03-10 07:17:18', '2017-03-14 07:44:52'),
(NULL, 'customer', 'api_keys/index', 'glyphicon-star', 'Create your API keys', 'If you need to connect to the system from a 3rd-party app, then using the API is the best way to do it.<br />\nStart by generating a set of API keys to access the API.', '2017-03-10 07:20:19', '2017-03-14 07:43:45'),
(NULL, 'customer', 'campaign_groups/index', 'glyphicon-folder-close', 'Create your first campaign group', 'You might find it easier to manage your email campaigns if you group them together in groups that make more sense to you.<br />\nYou can later filter your campaigns by the groups you create here.', '2017-03-10 07:22:13', '2017-03-14 07:42:53'),
(NULL, 'customer', 'list_subscribers/index', 'fa-user-plus', 'Create your list first subscriber', 'You can create a new subscriber, or use the list import feature to import subscribers in bulk.', '2017-03-10 07:25:55', '2017-03-14 07:41:57'),
(NULL, 'customer', 'list_segments/index', 'glyphicon-cog', 'Create your list first segment', 'You can segment the list subscribers based on the custom fields defined in this list<br />\nand you can also send email campaigns to segments only instead of sending to the whole list.', '2017-03-10 07:26:49', '2017-03-14 07:41:34'),
(NULL, 'customer', 'campaign_tags/index', 'glyphicon-tag', 'Create your first campaign tag', 'Create custom tags to be used inside campaigns, in addition to the<br />\nregular tags available already for each campaign you create.', '2017-03-10 07:28:24', '2017-03-14 07:56:43'),
(NULL, 'backend', 'user_groups/index', 'glyphicon-user', 'Create the first user group', 'User groups allow additional access in the backend area of the system.<br />\nYou can decide exactly to what areas the users in the groups are allowed.', '2017-03-10 07:46:55', '2017-03-14 07:37:12'),
(NULL, 'backend', 'price_plans/index', 'glyphicon-credit-card', 'Create the first price plan', 'Start adding price plans to the system so that the customers can buy them.', '2017-03-10 07:50:15', '2017-03-14 07:36:37'),
(NULL, 'backend', 'orders/index', 'glyphicon-credit-card', 'Create the first order', 'If the system customers didn\'t buy any price plan yet,<br />you can manually create orders in the name of the existing customers.<br />\n ', '2017-03-10 07:52:28', '2017-03-14 07:35:42'),
(NULL, 'backend', 'promo_codes/index', 'fa-code', 'Create the first promo code', 'Start adding promotional codes that can later be used by<br />customers when they will purchase any of the available price plans.', '2017-03-10 07:53:17', '2017-03-14 07:33:49'),
(NULL, 'backend', 'taxes/index', 'fa-dollar', 'Create the first tax for orders', 'Create the tax rates that will apply for the customers of this system.', '2017-03-10 07:55:01', '2017-03-14 07:33:10'),
(NULL, 'backend', 'customers/index', 'glyphicon-user', 'Create the first customer', 'Create the first system customer which will be able to manage email lists, subscribers, campaigns and much more.<br />\nCustomers can be part of customer groups for easier management.', '2017-03-10 07:57:06', '2017-03-14 07:32:26'),
(NULL, 'backend', 'customer_groups/index', 'glyphicon-folder-close', 'Create the first customer group', 'You can create groups with various settings, permissions and quotas and assign customers to these groups.<br />\nYou can also assign customer groups with price plans.', '2017-03-10 07:58:47', '2017-03-14 07:30:32'),
(NULL, 'backend', 'lists/index', 'glyphicon-list-alt', 'Monitor system wide email lists', 'When lists will be created from the customers area, you\'ll see them here too for easier monitoring.', '2017-03-10 08:00:12', '2017-03-14 07:29:09'),
(NULL, 'backend', 'campaigns/index', 'fa-envelope', 'Monitor system wide campaigns', 'When campaigns will be created from the customers area, you\'ll see them here too for easier monitoring.', '2017-03-10 08:01:56', '2017-03-14 07:28:42'),
(NULL, 'backend', 'delivery_servers/index', 'glyphicon-send', 'Create the first delivery server', 'Delivery servers are responsible for deliverying the emails to the subscribers.<br />\nYou have a wide range of delivery server types you can choose from. ', '2017-03-10 08:04:09', '2017-03-14 07:27:52'),
(NULL, 'backend', 'bounce_servers/index', 'glyphicon-filter', 'Create the first bounce server', 'Bounce servers are used to take action against the email addresses<br />\nof the subscribers that bounce back when campaigns are sent to them.', '2017-03-10 08:05:32', '2017-03-14 07:27:09'),
(NULL, 'backend', 'feedback_loop_servers/index', 'glyphicon-transfer', 'Create the first feedback loop server', 'Feedback loop servers will help monitoring the abuse reports that subscribers do<br />\nand take proper action when it finds such reports.', '2017-03-10 08:06:44', '2017-03-14 07:26:23'),
(NULL, 'backend', 'sending_domains/index', 'glyphicon-globe', 'Create the first sending domain', 'Sending domains will match the FROM address of the email campaigns and<br />\nwill add proper DKIM signatures to the email headers, thus increasing the chances for the emails to land inbox.', '2017-03-10 08:08:16', '2017-03-14 07:25:15'),
(NULL, 'backend', 'tracking_domains/index', 'glyphicon-flash', 'Create the first tracking domain', 'Tracking domains allow masking of the domains used in the tracking urls<br />\nfrom email campaigns with other domains that you specify here.', '2017-03-10 08:09:24', '2017-03-14 07:24:23'),
(NULL, 'backend', 'email_templates_gallery/index', 'glyphicon-text-width', 'Create the email templates gallery', 'All the email templates you create here will be visible in the customers area<br />\nwhere customers can import them into their own accounts and change them as they wish.', '2017-03-10 08:11:32', '2017-03-14 07:23:13'),
(NULL, 'backend', 'email_blacklist/index', 'glyphicon-ban-circle', 'Manage the email blacklist', 'Start adding emails in the global email blacklist to prevent sending to them or being added in the system from email lists, registrations and so on.<br />\nThis is a global email blacklist that applies to absolutely each email from the system.', '2017-03-10 08:16:17', '2017-03-14 07:20:59'),
(NULL, 'backend', 'email_blacklist_monitors/index', 'glyphicon-ban-circle', 'Create the first email blacklist monitor', 'Sometimes, emails can be automatically added in the global blacklisted for false reasons and when this happens,<br />\nyou need a way to monitor the email blacklist to remove such false positives.', '2017-03-10 08:18:17', '2017-03-14 07:18:59'),
(NULL, 'customer', 'email_blacklist/index', 'glyphicon-ban-circle', 'Manage your email blacklist', 'Create your own email blacklist to include subscribers that will never receive emails<br />\nfrom you and that will never be added to your email lists.', '2017-03-10 09:38:03', '2017-03-14 07:39:13'),
(NULL, 'customer', 'price_plans/orders', 'glyphicon-credit-card', 'Create your first order', 'When you purchase a price plan you will see the order details here.', '2017-03-14 07:52:22', '2017-03-14 07:55:11'),
(NULL, 'customer', 'dashboard/index', 'glyphicon-dashboard', 'Welcome', 'You will see more info on this page after you start using the system and<br />\ncreate your first email list and send your first email campaign.<br /><br /><a class=\"btn btn-primary btn-flat\" href=\"[CUSTOMER_BASE_URL]lists/create\"><span class=\"glyphicon glyphicon-list-alt\"><!-- --></span> Create your first email list</a>   <a class=\"btn btn-primary btn-flat\" href=\"[CUSTOMER_BASE_URL]campaigns/create\"><span class=\"glyphicon glyphicon-envelope\"><!-- --></span> Create your first email campaign</a>', '2017-03-15 08:28:00', '2017-03-15 09:21:20'),
(NULL, 'backend', 'dashboard/index', 'glyphicon-dashboard', 'Welcome', 'The dashboard will be populated with more info once<br />\nyou and/or your customers start using the system and add content to it.', '2017-03-15 09:42:04', '2017-03-15 09:47:25'),
(NULL, 'customer', 'account/disable', 'glyphicon-ban-circle', 'Disable my account', 'Once you disable your account, all your lists, segments, campaigns and subscribers will be removed from our system.<br />\nWe will keep your account disabled for a period of time and if you don\'t login anymore, we will simply remove it for good.<br />\nYou can reactivate your account at any time by simply logging into the system.<br /><br /><button class=\"btn btn-danger btn-flat\" type=\"submit\" value=\"1\"><span class=\"glyphicon glyphicon-ban-circle\"> </span>Disable account</button>', '2017-03-28 07:06:04', '2017-03-28 07:13:08'),
(NULL, 'backend', 'email_templates_categories/index', 'glyphicon-book', 'Create first template category', 'You can categorize the email templates so that it will be easier to group and find them.', '2017-03-30 07:13:29', '2017-03-30 07:14:32'),
(NULL, 'customer', 'templates_categories/index', 'glyphicon-book', 'Create your first template category', 'You can categorize the email templates so that it will be easier to group and find them.', '2017-03-30 07:15:31', '2017-03-30 07:15:31'),
(NULL, 'customer', 'campaigns_stats/index', 'fa-envelope', 'Campaigns stats', 'This area shows overview reports for sent campaigns,<br />\nso you will have to create and send at least one campaign in order to view information here.', '2017-04-07 09:18:28', '2017-04-07 09:18:28'),
(NULL, 'customer', 'suppression_lists/index', 'glyphicon-ban-circle', 'Manage your suppression lists', 'Create your own suppression lists where you can import email addresses that will never receive emails from you.<br />\nYou will be able to select these lists to be used in various places, such as when sending a campaign.', '2017-09-25 11:40:28', '2017-09-25 11:40:28'),
(NULL, 'backend', 'email_box_monitors/index', 'glyphicon-transfer', 'Create the first email box monitor', 'Email box monitors will help monitoring given email boxes and<br />\ntake actions against subscribers based on the contents of the incoming emails.', '2017-10-18 09:39:58', '2017-10-18 09:39:58'),
(NULL, 'customer', 'email_box_monitors/index', 'glyphicon-transfer', 'Create the first email box monitor', 'Email box monitors will help monitoring given email boxes and<br />\ntake actions against subscribers based on the contents of the incoming emails.', '2017-10-18 09:40:41', '2017-10-18 09:40:41'),
(NULL, 'backend', 'pages/index', 'fa-file', 'Create your first page', 'This area allows you to create simple pages for frontend.<br />\nIt is suited for pages like \"Terms and Conditions\", \"Privacy policy\",  but also any page where you want to showcase various info.', '2018-05-09 06:27:35', '2018-05-09 06:30:03');


-- --------------------------------------------------------

-- -----------------------------------------------------
-- Dumping data for table `page`
-- -----------------------------------------------------

INSERT INTO `page` (`page_id`, `title`, `slug`, `content`, `status`, `date_added`, `last_updated`) VALUES
(NULL, 'Terms and conditions', 'terms-and-conditions', 'Content coming soon', 'active', '2018-05-09 06:38:54', '2018-05-09 06:38:54'),
(NULL, 'Privacy policy', 'privacy-policy', 'Content coming soon', 'active', '2018-05-09 06:39:09', '2018-05-09 06:39:09');


-- --------------------------------------------------------

-- -----------------------------------------------------
-- Dumping data for table `survey_field_type`
-- -----------------------------------------------------

INSERT INTO `survey_field_type` (`type_id`, `name`, `identifier`, `class_alias`, `description`, `date_added`, `last_updated`) VALUES
(NULL, 'Text', 'text', 'customer.components.survey-field-builder.text.FieldBuilderTypeText', 'Text', NOW(), NOW()),
(NULL, 'Number', 'number', 'customer.components.survey-field-builder.number.FieldBuilderTypeNumber', 'Number', NOW(), NOW()),
(NULL, 'Geo City', 'geocity', 'customer.components.survey-field-builder.geocity.FieldBuilderTypeGeocity', 'Geo City', NOW(), NOW()),
(NULL, 'Geo Country', 'geocountry', 'customer.components.survey-field-builder.geocountry.FieldBuilderTypeGeocountry', 'Geo Country', NOW(), NOW()),
(NULL, 'Geo State', 'geostate', 'customer.components.survey-field-builder.geostate.FieldBuilderTypeGeostate', 'Geo State', NOW(), NOW()),
(NULL, 'Checkbox', 'checkbox', 'customer.components.survey-field-builder.checkbox.FieldBuilderTypeCheckbox', 'Checkbox', NOW(), NOW()),
(NULL, 'Checkbox List', 'checkboxlist', 'customer.components.survey-field-builder.checkboxlist.FieldBuilderTypeCheckboxlist', 'Checkbox List', NOW(), NOW()),
(NULL, 'Radio List', 'radiolist', 'customer.components.survey-field-builder.radiolist.FieldBuilderTypeRadiolist', 'Radio List', NOW(), NOW()),
(NULL, 'Consent Checkbox', 'consentcheckbox', 'customer.components.survey-field-builder.consentcheckbox.FieldBuilderTypeConsentCheckbox', 'Consent Checkbox', NOW(), NOW()),
(NULL, 'Dropdown', 'dropdown', 'customer.components.survey-field-builder.dropdown.FieldBuilderTypeDropdown', 'Dropdown', NOW(), NOW()),
(NULL, 'Multiselect', 'multiselect', 'customer.components.survey-field-builder.multiselect.FieldBuilderTypeMultiselect', 'Multiselect', NOW(), NOW()),
(NULL, 'Date', 'date', 'customer.components.survey-field-builder.date.FieldBuilderTypeDate', 'Date', NOW(), NOW()),
(NULL, 'Datetime', 'datetime', 'customer.components.survey-field-builder.datetime.FieldBuilderTypeDatetime', 'Datetime', NOW(), NOW()),
(NULL, 'Phone Number', 'phonenumber', 'customer.components.survey-field-builder.phonenumber.FieldBuilderTypePhonenumber', 'Phone Number', NOW(), NOW()),
(NULL, 'Email', 'email', 'customer.components.survey-field-builder.email.FieldBuilderTypeEmail', 'Email', NOW(), NOW()),
(NULL, 'Url', 'url', 'customer.components.survey-field-builder.url.FieldBuilderTypeUrl', 'Url', NOW(), NOW()),
(NULL, 'Rating', 'rating', 'customer.components.survey-field-builder.rating.FieldBuilderTypeRating', 'Rating', NOW(), NOW()),
(NULL, 'Country', 'country', 'customer.components.survey-field-builder.country.FieldBuilderTypeCountry', 'Country', NOW(), NOW()),
(NULL, 'State', 'state', 'customer.components.survey-field-builder.state.FieldBuilderTypeState', 'State', NOW(), NOW()),
(NULL, 'Years Range', 'yearsrange', 'customer.components.survey-field-builder.yearsrange.FieldBuilderTypeYearsRange', 'Years Range', NOW(), NOW()),
(NULL, 'Textarea', 'textarea', 'customer.components.survey-field-builder.textarea.FieldBuilderTypeTextarea', 'Textarea', NOW(), NOW());
-- --------------------------------------------------------


-- -----------------------------------------------------
-- Dumping data for table `survey_segment_operator`
-- -----------------------------------------------------

INSERT INTO `survey_segment_operator` (`operator_id`, `name`, `slug`, `date_added`, `last_updated`) VALUES
(1, 'is', 'is', NOW(), NOW()),
(2, 'is not', 'is-not', NOW(), NOW()),
(3, 'contains', 'contains', NOW(), NOW()),
(4, 'not contains', 'not-contains', NOW(), NOW()),
(5, 'starts with', 'starts', NOW(), NOW()),
(6, 'ends with', 'ends', NOW(), NOW()),
(7, 'is greater than', 'greater', NOW(), NOW()),
(8, 'is less than', 'less', NOW(), NOW()),
(9, 'not starts with', 'not-starts', NOW(), NOW()),
(10, 'not ends with', 'not-ends', NOW(), NOW());

-- --------------------------------------------------------
