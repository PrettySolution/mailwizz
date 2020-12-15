<?php
namespace Tipimail\Exceptions;

class TipimailException extends \Exception {
	
	public function __construct($error, $code) {
		parent::__construct("[" . $code . "]: " . $error, $code);
	}
	
}