<?php

require_once '../PHPADD/Parser.php';
require_once 'fixtures/sample.classes';
require_once 'fixtures/extension.classes';

class ParserTest extends PHPUnit_Framework_TestCase
{
	public function setUp()
	{
		$this->filter = new PHPADD_Filter();
	}

	public function testAnalyzesAllMethods()
	{
		$parser = new PHPADD_Parser('Example');
		$analysys = $parser->analyze($this->filter);

		$missing = $analysys->getMissingBlocks();
		$outdated = $analysys->getOutdatedBlocks();

		$this->assertEquals(3, count($missing));
		$this->assertEquals(0, count($outdated));
	}

	public function testIgnoresBlankSpaces()
	{
		$parser = new PHPADD_Parser('ValidWithSpacesExample');
		$analysys = $parser->analyze($this->filter);

		$missing = $analysys->getMissingBlocks();
		$outdated = $analysys->getOutdatedBlocks();

		$this->assertEquals(0, count($missing));
		$this->assertEquals(0, count($outdated));
	}

	public function testAnalyzesOnlyPublicMethods()
	{
		$parser = new PHPADD_Parser('Example');
		$noProtectedFilter = new PHPADD_Filter(false, false);
		$analysys = $parser->analyze($noProtectedFilter);

		$missing = $analysys->getMissingBlocks();
		$outdated = $analysys->getOutdatedBlocks();

		$this->assertEquals(1, count($missing));
		$this->assertEquals(0, count($outdated));
		$this->assertEquals('publicMethod', $missing[0]->getName());
	}

	public function testDetectsMissingParametersInDocBlocks()
	{
		$parser = new PHPADD_Parser('InvalidMissingExample');
		$analysys = $parser->analyze($this->filter);

		$missing = $analysys->getMissingBlocks();
		$outdated = $analysys->getOutdatedBlocks();

		$this->assertEquals(0, count($missing));
		$this->assertEquals(1, count($outdated));
		$detail = $outdated[0]->getDetail();

		$this->assertEquals(1, count($detail));
		$this->assertEquals('missing-param', $detail[0]['type']);
		$this->assertEquals('$name', $detail[0]['name']);
	}

	public function testDetectsMissingParametersInPhp()
	{
		$parser = new PHPADD_Parser('InvalidRemovedExample');
		$analysys = $parser->analyze($this->filter);

		$missing = $analysys->getMissingBlocks();
		$outdated = $analysys->getOutdatedBlocks();

		$this->assertEquals(0, count($missing));
		$this->assertEquals(1, count($outdated));
		$detail = $outdated[0]->getDetail();

		$this->assertEquals(1, count($detail));
		$this->assertEquals('unexpected-param', $detail[0]['type']);
		$this->assertEquals('$name', $detail[0]['name']);
	}

	/**
	 * @dataProvider validClasses
	 */
	public function testSkipsValidDocBlocks($className)
	{
		$parser = new PHPADD_Parser($className);
		$analysys = $parser->analyze(new PHPADD_Filter(false, false));

		$missing = $analysys->getMissingBlocks();
		$outdated = $analysys->getOutdatedBlocks();

		$this->assertEquals(0, count($missing));
		$this->assertEquals(0, count($outdated));
	}

	public function validClasses()
	{
		return array(
			array('ValidExample'),
			array('ValidComplexExample'),
			array('ValidOnlyPublicExample'),
		);
	}

	/**
	 * @dataProvider oneChangeClasses
	 */
	public function testFindsInvalidDocBlocks($className, $error)
	{
		$parser = new PHPADD_Parser($className);
		$analysys = $parser->analyze($this->filter);

		$missing = $analysys->getMissingBlocks();
		$outdated = $analysys->getOutdatedBlocks();

		$this->assertEquals(0, count($missing));

		switch ($error) {
			case 'changed':
				$this->assertEquals(1, count($outdated));
				$this->assertEquals(2, count($outdated[0]->getDetail()));
				break;
			case 'removed':
				$this->assertEquals(1, count($outdated));
				$this->assertEquals(1, count($outdated[0]->getDetail()));
				break;
			case 'added':
				$this->assertEquals(1, count($outdated));
				$this->assertEquals(1, count($outdated[0]->getDetail()));
				break;
		}
	}

	public function oneChangeClasses()
	{
		return array(
			array('OneChangeExampleTypeChanged', 'changed'),
			array('OneChangeExampleNameChanged', 'changed'),
			array('OneChangeExampleRemovedParameter', 'removed'),
			array('OneChangeExampleAddedParameter', 'added'),
		);
	}

	public function testIgnoresMethodsOfParentClasses()
	{
		$parser = new PHPADD_Parser('Extension_Extended');
		$analysys = $parser->analyze($this->filter);

		$missing = $analysys->getMissingBlocks();
		$outdated = $analysys->getOutdatedBlocks();

		$this->assertEquals(1, count($missing));
		$this->assertEquals(0, count($outdated));
		$this->assertEquals('b', $missing[0]->getName());
	}

}
