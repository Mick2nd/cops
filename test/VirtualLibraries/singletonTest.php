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
 * Define a class using Singleton
 * @author jsoft
 *
 */
class mySingletonA
{
	use Singleton;
	private $value;
	
	/**
	 * ctor
	 */
	protected function __construct()
	{
		$this->value = 0;
	}
	
	/**
	 * This sets an internal value
	 * @param unknown $value
	 * @return unknown
	 */
	public function set($value)
	{
		return $this->value = $value;
	}
	
	/**
	 * This adds to the internal value
	 * @param unknown $value
	 * @return unknown
	 */
	public function add($value)
	{
		return $this->value += $value;
	}
}

/**
 * Define a second class using Singleton
 * @author jsoft
 *
 */
class mySingletonB
{
	use Singleton;
	private $value;
	
	/**
	 * ctor
	 */
	protected function __construct()
	{
		$this->value = 0;
	}
	
	/**
	 * This sets an internal value
	 * @param unknown $value
	 * @return unknown
	 */
	public function setB($value)
	{
		return $this->value = $value;
	}
	
	/**
	 * This adds to the internal value
	 * @param unknown $value
	 * @return unknown
	 */
	public function addB($value)
	{
		return $this->value += $value;
	}
}

/**
 * My test fixture
 * @author jsoft
 *
 */
class singletonTest extends TestCase
{
	/**
	 * Test the add function
	 */
	function testAdd()
	{
		mySingletonA::getInstance()->set(3);
		parent::assertSame(5, mySingletonA::getInstance()->add(2));
	}
	
	/**
	 * Test : can different singletons live together?
	 */
	function testMixedInvocations()
	{
		mySingletonA::getInstance()->set(3);
		mySingletonB::getInstance()->setB(5);
		
		parent::assertSame(5, mySingletonA::getInstance()->add(2));
		parent::assertSame(12, mySingletonB::getInstance()->addB(7));
	}
	
	/**
	 * @expectedException ErrorException
	 */
	function testShouldThrow()
	{
		parent::markTestSkipped('We would cause a fatal error otherwise');
		//$a = new mySingletonA();
	}
}
