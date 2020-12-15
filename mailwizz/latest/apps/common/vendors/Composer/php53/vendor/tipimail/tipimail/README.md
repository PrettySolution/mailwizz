# Tipimail PHP Library

This is the Tipimail PHP SDK. This SDK contains methods for easily interacting with your Tipimail account. You can send emails, manage your account and retrieve your statistics.

You will find examples in this Readme to get you started. If you need more help, please see our official API documentation at https://docs.tipimail.com/en/integrate/api (French version at https://docs.tipimail.com/fr/integrate/api - All Tipimail documentation at https://docs.tipimail.com).

## Prerequisites

Make sure to have the following details:

* Tipimail API User
* Tipimail API Secret key
* PHP >= 5.3

## Installation

The preferred method of installation is via [Composer](https://getcomposer.org/) or [Packagist](https://packagist.org/). Run the following command to install the package:

```PHP
# Install Composer
curl -sS https://getcomposer.org/installer | php

# Download Tipimail SDK
php composer.phar require tipimail/tipimail
```

## Integrate Tipimail

Next, Compose create an autoloader file, in your application, to automatically load the Tipimail SDK in your project. You just have to add this line:

```PHP
require 'vendor/autoload.php';
use Tipimail\Tipimail;
use Tipimail\Exceptions;
```

You just have to add your credentials to start using the SDK:

```PHP
$tipimail = new Tipimail('API user', 'API key');

```

## Usage

Now, you can use the SDK to do a lot of action with Tipimail:
* Send emails
* Retrieve your analytics
* Manage your account


We return exceptions if the program occurs an error. So we advice to use try/catch feature.

## Examples

### Get stats from email send

```PHP
// StatisticsSends
/*
object(StatisticsSends)[14]
  private 'error' => int 198
  private 'rejected' => int 0
  private 'requested' => int 197573
  private 'deferred' => int 11
  private 'scheduled' => int 0
  private 'filtered' => int 14
  private 'delivered' => int 188148
  private 'hardbounced' => int 1232
  private 'softbounced' => int 87240
  private 'open' => int 909
  private 'click' => int 191
  private 'read' => int 0
  private 'unsubscribed' => int 4
  private 'complaint' => int 68
  private 'opener' => int 603
  private 'clicker' => int 87
*/
try {
	$result = $tipimail->getStatisticsService()->getSends(null, null, null, null, null);
	var_dump($result);
}
catch(Exceptions\TipimailException $e) {
	
}
```

### Get details from a message ID

```php
// StatisticsMessagedetails
// /analytics/message/{messageid}
/*
object(StatisticsMessageDetails)[15]
  private 'id' => string '562a35f99932f6e1a6998ed3' (length=24)
  private 'apiKey' => string '3262b4f287869bbc8ed24d7767f0000b' (length=32)
  private 'createdDate' => string '1445606904' (length=10)
  private 'lastStateDate' => string '1445606905' (length=10)
  private 'msg' =>
	object(StatisticsMessageInfo)[16]
	  private 'from' => string 'support@tipimail.com' (length=20)
	  private 'email' => string 'test@sbr27.net' (length=14)
	  private 'subject' => string 'Tipimail-checker' (length=16)
	  private 'size' => int 184
  private 'lastState' => string 'delivered' (length=9)
  private 'open' => int 0
  private 'click' => int 0
*/
try {
	$result = $tipimail->getStatisticsService()->getMessageDetail('562a35f99932f6e1a6998ed3');
	var_dump($result);
}
catch(Exceptions\TipimailException $e) {
	
}
```

## Support, issue and Feedback

Several resources are available to help you:
* Our [documentation website](https://docs.tipimail.com/) for additional information about our API.
* If you find a bug, please submit the issue in Github directly ([tipimail-php-library Issues](https://github.com/tipimail/tipimail-php-library/issues)).
* If you need additional assistance, contact our support by emails or phone at [https://www.tipimail.com/support](https://www.tipimail.com/support).
