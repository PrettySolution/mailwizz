<?php
namespace Tipimail\Exceptions;

class TipimailNotFoundException extends TipimailException {
	
	public function __construct($error, $code) {
		parent::__construct($error, $code);
	}
	
}