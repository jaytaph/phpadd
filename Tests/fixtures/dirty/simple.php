<?php

class Fixture_InvalidMissingExample
{
	/**
	 * Some description here
	 *
	 * @param stdClass $my
	 * @return string
	 */
	public function invalidMethod(stdClass $my, $name) {}
}

class Fixture_InvalidRemovedExample
{
	/**
	 * Some description here
	 *
	 * @param stdClass $my
	 * @param string $name
	 * @return string
	 */
	public function invalidMethod(stdClass $my) {}
}

class Fixture_InvalidMultiExample
{
	/**
	 * Some description here
	 *
	 * @param StdClass $my
	 * @param string $name
	 * @param mixed $nonexisting
	 * @return string
	 */
	public function invalidMethod(stdClass $my) {}
}

class Fixture_SwitchedExample
{
	/**
	 * Some description here
	 *
	 * @param mixed $var1
	 * @param mixed $var2
	 * @param mixed $var3
	 * @return string
	 */
	public function invalidMethod($var3, $var2, $var1) {}
}


class Fixture_NodocBLock
{
	public function invalidMethod(stdClass $my) {}
}
