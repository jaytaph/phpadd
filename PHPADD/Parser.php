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

class PHPADD_Parser
{
	private $reflection;

	public function __construct($class)
	{
		$this->reflection = new ReflectionClass($class);
	}

	/**
	 * Analyzes the class with the given filtering level.
	 *
	 * @param PHPADD_Filter $filter
	 * @return PHPADD_Result_Class Found mess
	 */
	public function analyze($classname, PHPADD_Filter $filter)
	{

		$mess = new PHPADD_Result_Class($this->reflection);

		foreach ($this->reflection->getMethods($filter->getLevel()) as $method) {
			/** @var $method ReflectionMethod */

			if ($this->reflection->name !== $method->getDeclaringClass()->name) {
				// is not in this class
				continue;
			}

			$issues = array();
			if ($this->isDocBlockMissing($method)) {
				$issues[] = new PHPADD_Result_Mess_Detail_Docblock(null, null, new StdClass());
			} else {
				$issues = $this->validateDocBlock($method);
			}

			$block = new PHPADD_Result_Method($method, $issues);
			$mess->addMethod($block);
		}

		return $mess;
	}

	private function isDocBlockMissing(ReflectionMethod $method)
	{
		return $method->getDocComment() === false;
	}

	private function getPhpParams(ReflectionMethod $method)
	{
		$params = array();

		foreach ($method->getParameters() as $parameter)
		{
			$param = new StdClass();
			$param->name = '$' . $parameter->getName();

			if ($parameter->isArray()) {
				$param->type = 'array';
			} else {
				$type = $parameter->getClass();
				if ($type) {
					$param->type = $type->getName();
				} else {
					$param->type = null;
				}
			}
			$params[] = $param;
		}

		return $params;
	}

	private function getDocBlockParams(ReflectionMethod $method)
	{
		$params = array();

		$excluded = array('int', 'integer', 'float', 'double', 'bool', 'boolean', 'string', 'mixed');
		$annotations = $this->parseAnnotations($method->getDocComment());

		if (isset($annotations['param'])) {
			foreach ($annotations['param'] as $parameter)
			{
				// @TODO: make sure it works when we only have "@param <type>"
				list($type, $name) = preg_split("/[\s]+/", $parameter);

				$param = new StdClass();
				if (!in_array($type, $excluded)) {
					$param->type = $type;
					$param->name = $name;
				} else {
					$param->type = null;
					$param->name = $name;
				}
				$params[] = $param;
			}
		}

		return $params;
	}

	/**
	 * 
	 *
	 * @param ReflectionMethod $method
	 * @return array Issues in the docblock
	 */
	public function validateDocBlock(ReflectionMethod $method)
	{
		$issues = array();

		$phpParams = $this->getPhpParams($method);
		$docParams = $this->getDocBlockParams($method);

		// php parameters are leading
		foreach ($phpParams as $phpIndex => $phpParam) {
			$found = false;
			foreach ($docParams as $docIndex => $docParam) {
				if ($phpParam == $docParam)
				{
					// Found the correct parameter, but still need to find out if it's in the right order..
					if ($phpIndex != $docIndex) {
						$issues[] = new PHPADD_Result_Mess_Detail_Order($phpIndex, $docIndex, $phpParam); 
						break;
					}
					$found = true;
					break;
				}
			}

			if (! $found) {
				$issues[] = new PHPADD_Result_Mess_Detail_Missing($phpIndex, null, $phpParam);
			}
		}


		foreach ($docParams as $docIndex => $docParam) {
			$found = false;
			foreach ($phpParams as $phpIndex => $phpParam) {
				if ($docParam == $phpParam) {
					$found = true;
					break;
				}
			}

			if (! $found) {
				$issues[] = new PHPADD_Result_Mess_Detail_Unexpected(null, $docIndex, $docParam);
			}
		}

		return $issues;
	}


	private function parseAnnotations($docblock)
	{
		$annotations = array();

		if (preg_match_all('/@(?P<name>[A-Za-z_-]+)(?:[ \t]+(?P<value>.*?))?[ \t]*\r?$/m', $docblock, $matches)) {
			$numMatches = count($matches[0]);

			for ($i = 0; $i < $numMatches; ++$i) {
				$annotations[$matches['name'][$i]][] = $matches['value'][$i];
			}
		}

		return $annotations;
	}

}