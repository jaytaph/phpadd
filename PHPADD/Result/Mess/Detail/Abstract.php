<?php

/**
 * phpadd - abandoned docblocks detector
 * Copyright (C) 2010 Francesco Montefoschi <francesco.monte@gmail.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package phpadd
 * @author  Joshua Thijssen
 * @license http://www.gnu.org/licenses/gpl-3.0.html  GNU GPL 3.0
 */

abstract class PHPADD_Result_Mess_Detail_Abstract {
	const MISSING_DOCBLOCK  = 0;
	const MISSING_PARAM     = 1;
	const UNEXPECTED_PARAM  = 2;
	const WRONG_ORDER       = 3;

	protected $type;
	protected $php_index = null;
	protected $doc_index = null;
	protected $param_name = "";
	protected $param_type = "";
	protected $method;

	public function __construct($php_index, $doc_index, stdClass $param) {
		$this->php_index = $php_index;
		$this->doc_index = $doc_index;
		if (isset($param->name)) {
			$this->param_name = $param->name;
		}
		if (isset($param->type)) {
			$this->param_type = $param->type;
		}
	}


	public function setMethod(PHPADD_Result_Method $method) {
		$this->method = $method;
	}
	public function getMethod() {
		return $this->method;
	}

	public function getType() {
		return $this->type;
	}

	public function getParamName() {
		return $this->param_name;
	}
	public function getParamType() {
		return $this->param_type;
	}
	public function getPhpIndex() {
		return $this->php_index;
	}
	public function getDocIndex() {
		return $this->doc_index;
	}

	public function getDetailType()
	{
		switch ($this->type) {
			case PHPADD_Result_Mess_Detail_Abstract::MISSING_PARAM :
				return 'Missing parameter';
			case PHPADD_Result_Mess_Detail_Abstract::UNEXPECTED_PARAM :
				return 'Unexpected parameter';
			case PHPADD_Result_Mess_Detail_Abstract::WRONG_ORDER :
				return 'Parameter in wrong order';
			case PHPAdd_Result_Mess_Detail_Abstract::MISSING_DOCBLOCK :
				return 'No docblock found';
		}
	}
}