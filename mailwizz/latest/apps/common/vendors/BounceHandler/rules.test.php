<?php
/**
 * This tests the corectness of rules!
 */
// Comment when needed.
exit('');

ini_set('display_errors', 1);
error_reporting(-1);

define('MW_PATH', true);
require_once dirname(__FILE__) . '/BounceHandler.php';

$rules  = require dirname(__FILE__) . '/rules.php';
$string = "";
$string = BounceHandler::stripSpecialChars($string);

$matched= array();

foreach ($rules[BounceHandler::COMMON_RULES] as $info) {
    foreach ($info['regex'] as $regex) {
        echo strtoupper($info['bounceType']) . " bounce testing for: {$regex}";
        if (preg_match($regex, $string, $matches)) {
            echo " >>> Matched";
            $matched[] = array($regex => $info['bounceType']);
        } else {
            echo " >>> Not matched";
        }
        echo PHP_EOL;
    }
}
echo "Matched rules:\n";
print_r($matched);