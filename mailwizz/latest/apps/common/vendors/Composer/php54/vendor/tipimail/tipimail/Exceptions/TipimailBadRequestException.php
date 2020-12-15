<?php
namespace Tipimail\Exceptions;

class TipimailBadRequestException extends TipimailException {
	
	public function __construct($error, $code) {
		parent::__construct($error, $code);
	}
	
}