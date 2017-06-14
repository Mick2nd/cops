<?php

namespace VirtualLibraries;

/**
 * COPS (Calibre OPDS PHP Server) test file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Jürgen Habelt <juergen@habelt-jena.de>
 */

require_once 'config.php';

use PHPUnit\Framework\TestCase;

/**
 * Test class to be instantiated lazily
 * There will be 2 instances: $myLazyA, $myLazyB
 * @author jsoft
 *
 */
class myLazy
{
	public $value;
	
	public function __construct($value)
	{
		$this->value = $value;
	}
}

/**
 * Test fixture for myLazy / Lazy
 * @author jsoft
 *
 */
class lazyTest extends TestCase
{
	static private $myLazyA;
	static private $myLazyB;
	
	/**
	 * Ctor
	 */
	function __construct()
	{
	}
	
	/**
	 * Creator function, used to instantiate $myLazyB.
	 * @return \VirtualLibraries\myLazy
	 */
	static function creator()
	{
		return new myLazy(6);
	}
	
	/**
	 * One time initialization per test case
	 */
	static function setUpBeforeClass()
	{
		self::$myLazyA = new Lazy(function () { return new myLazy(5); });
		self::$myLazyB = new Lazy(array('\VirtualLibraries\lazyTest', 'creator'));
	}
	
	/**
	 * testA - executed first
	 */
	function testA()
	{
		parent::assertSame(5, self::$myLazyA->getValue()->value);
		
		self::$myLazyA->getValue()->value = 15;
		parent::assertSame(15, self::$myLazyA->getValue()->value);
	}
	
	/**
	 * testB - executed second
	 * @depends testA
	 */
	function testB()
	{
		parent::assertSame(6, self::$myLazyB->getValue()->value);
		
		self::$myLazyB->getValue()->value = 16;
		parent::assertSame(16, self::$myLazyB->getValue()->value);
	
		parent::assertSame(15, self::$myLazyA->getValue()->value);
	}
}
