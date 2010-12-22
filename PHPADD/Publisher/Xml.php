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

class PHPADD_Publisher_Xml extends PHPADD_Publisher_Abstract
{
	protected $dom;
	protected $classElement;

	public function __construct($argument)
	{
		parent::__construct($argument);
		$this->dom = new DomDocument('1.0');
	}

	public function publish(PHPADD_Result_Analysis $mess)
	{
		foreach ($mess->getDirtyFiles() as $file) {
			$attributes = array ("name" => $file->getName());
			if ($file->isClean()) {
				$attributes['clean'] = true;
			}
			$file_element = $this->createXMLElement('file', $attributes);

			foreach ($file->getClasses() as $class) {
				$attributes = array ();
				$attributes['name'] = $class->getName();
				$attributes['line'] = $class->getStartline();
				if ($class->isClean()) {
					$attributes['clean'] = true;
				}
				$class_element = $this->createXMLElement('class', $attributes);

				foreach ($class->getMethods() as $method) {
					$attributes = array ();
					$attributes['name'] = $method->getName();
					$attributes['line'] = $method->getStartline();
					if ($method->hasDocBlock()) {
						$attributes['docblockline'] = $method->getDocBlockStartLine();
					}
					if ($method->isClean()) {
						$attributes['clean'] = true;
					}
					$method_element = $this->createXMLElement('method', $attributes);

					foreach ($method->getIssues() as $issue) {
						$attributes = array ();
						$attributes['error'] = $issue->getDetailType();
						if ($issue->getPhpIndex() !== null) {
							$attributes['phpindex'] = $issue->getPhpIndex();
						}
						if ($issue->getDocIndex() !== null) {
							$attributes['docindex'] = $issue->getDocIndex(); 
						}

						if ($issue->getParamType() !== "") {
							$attributes['param_type'] = $issue->getParamType();
						}
						if ($issue->getParamName() !== "") {
							$attributes['param_name'] = $issue->getParamName();
						}
						$issue = $this->createXMLElement('issue', $attributes);
						$method_element->appendChild($issue);
					}
					$class_element->appendChild($method_element);
				}
				$file_element->appendChild($class_element);
			}
			$this->dom->appendChild($file_element);
		}

		$this->dom->formatOutput = true;
		$this->dom->save($this->destination);
	}

	protected function createXMLElement($name, $attributes)
	{
		$element = $this->dom->createElement($name);
		foreach ($attributes as $key => $value) {
			$attr_element = $this->dom->createAttribute($key);
			$attr_element->appendChild($this->dom->createTextNode($value));

			$element->appendChild($attr_element);
		}
		return $element;
	}
}