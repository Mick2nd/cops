<?php

namespace VirtualLibraries
{
    require_once 'config.php';
    
    use PHPUnit\Framework\TestCase;
    
    /**
     * Performs unit tests on the dbProxy
     * @author Jürgen
     *
     */
    class dbProxyTest extends TestCase
    {
    	private $log;
    	static private $proxy;
    	
    	/**
    	 * Ctor.
    	 */
    	function __construct()
    	{
    		$this->log = \Logger::getLogger(__CLASS__);
    	}
    	
    	/**
    	 * Returns the db path
    	 * @return string
    	 */
    	private function getDbPath()
    	{
    		return __DIR__ . '/../BaseWithSomeBooks/metadata.db';
    	}
    	
    	/**
    	 * One time initialization
    	 * {@inheritDoc}
    	 * @see PHPUnit_Framework_TestCase::setUpBeforeClass()
    	 */
    	static function setUpBeforeClass()
    	{
    		self::$proxy = new DbProxy();    		
    	}
    	
    	/**
    	 * One time cleanup
    	 * {@inheritDoc}
    	 * @see PHPUnit_Framework_TestCase::tearDownAfterClass()
    	 */
    	static function tearDownAfterClass()
    	{
    		self::$proxy->close();
    	}
    	
    	/**
    	 * setUp for each test.
    	 * {@inheritDoc}
    	 * @see PHPUnit_Framework_TestCase::setUp()
    	 */
    	function setUp()
    	{
    	}
    	
        /**
         * Tests if the db file exists
         */
        function testDbFileExists()
        {
        	parent::assertFileExists(self::getDbPath(), 'The db file should exist.');
        	
        	return self::getDbPath();
        }
        
        /**
         * Tests that dbProxy is opened correctly
         * @depends testDbFileExists
         * @param string $dbPath
         */
        function testDbProxyOpen($dbPath)
        {
        	parent::assertTrue(self::$proxy->open($dbPath), 'The db should be opened.');
        }
        
        /**
         * Tests for existing book entries
         * @depends testDbProxyOpen
         */
        function testBookEntries()
        {
        	parent::assertTrue(self::$proxy->test("select id from books where id=3"), 'There should be a book entry with id = 3.');
        	parent::assertFalse(self::$proxy->test("select id from books where id=2000"), 'There should be no book entry with id = 2000.');
        }
        
        /**
         * Tests for book entries with 'Adventures' in the title using the regexp function
         * @depends testDbProxyOpen
         */
        function testRegexp()
        {
        	$result = self::$proxy->executeQueryAll("select id, title from books where title regexp 'Alice''s Adventures'");
        	$this->log->info(var_export($result, true));
        	
        	parent::assertNotEquals(false, $result, 'The db should contain a book entry with \'Alice\'s Adventures...\'');
        	parent::assertNotEquals(0, count($result), 'The db should contain a book entry with \'Alice\'s Adventures...\'');
        	parent::assertEquals(0, strpos($result[0]['title'], 'Alice\'s Adventures'), 'The db should return \'Alice\'s Adventures...\'');
        }
        
        /**
         * Tests for book entries with 'Adventures' in the title using the function group_concat
         * @depends testDbProxyOpen
         */
        function testGroupConcat()
        {
        	$embedded = ColumnInfo::getDefault()->getItem("authors")->getSelect(0);										// contains group_concat
        	$result = self::$proxy->executeQueryAll("select id, title, $embedded \nfrom books where title regexp 'Alice''s Adventures'");
        	
        	parent::assertNotEquals(false, $result, 'The db should contain a book entry with \'Alice\'s Adventures...\'');
       		$title = $result[0]['title'];
       		$authors = $result[0]['authors'];
       		
       		parent::assertEquals('Lewis Carroll', $authors, 'The author should be \'Lewis Carroll\'');
       		$this->log->info("Found using group_concat: '$title' by '$authors'.");
        }
    }
}
