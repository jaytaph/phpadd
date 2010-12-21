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
 * @author  Francesco Montefoschi
 * @license http://www.gnu.org/licenses/gpl-3.0.html  GNU GPL 3.0
 */

class PHPADD_Result_Class
{
	private $methods = array();
	private $reflection;
	private $file;

	function __construct(PHPADD_Result_File $file, ReflectionClass $reflection) {
		$this->file = $file;
		$this->reflection = $reflection;
	}

	public function getFile() {
		return $this->file;
	}

	public function getName() {
		return $this->reflection->getName();
	}

	public function getStartline() {
		return $this->reflection->getStartLine();
	}

	public function methodCount()
	{
		return count($this->methods);
	}

	public function addMethod(PHPADD_Result_Method $method)
	{
		$this->methods[] = $method;
	}

	public function getMethods() {
		return $this->methods;
	}

	public function getMissingBlocks()
	{
		return $this->_getBlocks(PHPADD_Result_Mess_Detail_Abstract::MISSING_PARAM);
	}

	public function getOutdatedBlocks()
	{
		return $this->_getBlocks(PHPADD_Result_Mess_Detail_Abstract::UNEXPECTED_PARAM);
	}

	public function getUnorderedBlocks()
	{
		return $this->_getBlocks(PHPADD_Result_Mess_Detail_Abstract::WRONG_ORDER);
	}

	public function getNodocblockBlocks()
	{
		return $this->_getBlocks(PHPADD_Result_Mess_Detail_Abstract::MISSING_DOCBLOCK);
	}
	
	public function getRegularBlocks()
	{
//		$count = 0;
//		foreach ($this->methods as $method) {
//			if ($method->isClean())
//			{
//			}
//		}
//		return $count;
	}

	protected function _getBlocks($type) {
		$issues = array();
		foreach ($this->methods as $method) {
			foreach ($method->getIssues() as $issue) {
				if ($issue->getType() == $type) {
					$issues[] = $issue;
				}
			}
		}
		return $issues;
	}

	public function isClean()
	{
		foreach ($this->getMethods() as $method) {
			if (! $method->isClean()) return false;
		}
		return true;
	}
}