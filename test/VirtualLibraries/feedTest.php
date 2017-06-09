<?php

namespace VirtualLibraries;

/**
 * COPS (Calibre OPDS PHP Server) test file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Jürgen Habelt <juergen@habelt-jena.de>
 */

require_once 'config.php';

/**
 * Tests a number of virtual libraries
 * Each library is tested against the number of entries it selects
 * @author jsoft
 *
 */
class feedTest extends \PHPUnit_Extensions_Selenium2TestCase
{
	private $driver;
	
	/**
	 * setUp
	 * {@inheritDoc}
	 * @see PHPUnit_Framework_TestCase::setUp()
	 */
	function setUp()
	{
		parent::setBrowser('firefox');
		parent::setHost('127.0.0.1');
		parent::setPort(4444);
		parent::setBrowserUrl('http://localhost/copsgit');
		parent::setDefaultWaitUntilTimeout(10000);
	}
	
	/**
	 * tearDown
	 * {@inheritDoc}
	 * @see PHPUnit_Framework_TestCase::tearDown()
	 */
	function tearDown()
	{
		
	}
	
	/**
	 * Data provider for the test
	 * @return string[][]|number[][]
	 */
	function virtualLibraryDataProvider()
	{
		return array(
				array('name' => 'Alle Bücher', 'noEntries' => 28),
				array('name' => 'Beinhaltet Beschreibung', 'noEntries' => 19),
				array('name' => 'Beinhaltet keine Veröffentlichung', 'noEntries' => 21),
				array('name' => 'Bestandteil von Serien', 'noEntries' => 20),
				array('name' => 'Mathematik', 'noEntries' => 8),
				array('name' => 'Matlab', 'noEntries' => 4),
		);
	}
	
	/**
	 * Test of a Virtual Library
	 * @dataProvider virtualLibraryDataProvider
	 * @param string $name
	 * @param number $noEntries
	 */
	function testVirtualLibrary($name, $noEntries)
	{
		$encodedName = urlencode($name);														// invoke the Cops opds catalog
		parent::url("feed.php?page=10&search=$encodedName");
		
		parent::waitUntil(																		// wait until page is present
				function () 
				{ 
					// return parent::element(parent::using('id')->value('feedBody')) != null;
					return parent::byId('feedBody') != null; 
				});
		
		parent::assertCount(																	// assert the number of entries with test library
				$noEntries, 
				parent::elements(parent::using('css selector')->value('.entry')));
	}
}