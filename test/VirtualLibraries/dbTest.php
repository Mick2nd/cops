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
    class dbTest extends TestCase
    {
    	private $log;
    	
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
         * Tests if the db file exists
         */
        function testDbExists()
        {
        	parent::assertTrue(file_exists(self::getDbPath()), 'The db file should exist.');
        }
        
        /**
         * Tests for existing book entries
         */
        function testBookEntries()
        {
        	$proxy = new DbProxy();
        	
        	parent::assertTrue($proxy->open(self::getDbPath()), 'The db should be opened.');
        	parent::assertTrue($proxy->test("select id from books where id=3"), 'There should be a book entry with id = 3.');
        	parent::assertFalse($proxy->test("select id from books where id=2000"), 'There should be no book entry with id = 2000.');

       		$proxy->close();
        }
        
        /**
         * Tests for book entries with 'Adventures' in the title using the regexp function
         */
        function testRegexp()
        {
        	$proxy = new DbProxy();
        	 
        	parent::assertTrue($proxy->open(self::getDbPath()), 'The db should be opened.');
        	
        	$result = $proxy->executeQueryAll("select id, title from books where title regexp 'Adventures'");
        	$this->log->info(var_export($result, true));
        	
        	parent::assertNotEquals(false, $result, 'The db should contain a book entry with \'Alice\'s Adventures...\'');
        	parent::assertNotEquals(0, count($result), 'The db should contain a book entry with \'Alice\'s Adventures...\'');
        	parent::assertNotEquals(false, strpos($result[0]['title'], 'Adventures'), 'The db should return \'Alice\'s Adventures...\'');
        	
        	$proxy->close();
        }
        
        /**
         * Tests for book entries with 'Adventures' in the title using the function group_concat
         */
        function testGroupConcat()
        {
        	$proxy = new DbProxy();
        	
        	parent::assertTrue($proxy->open(self::getDbPath()), 'The db should be opened.');
        	
        	$embedded = ForeignColumns::getDefault()->getItem("authors")->getSelect(0);										// contains group_concat
           	$result = $proxy->executeQueryAll("select id, title, $embedded \nfrom books where title regexp 'Adventures'");
        	
        	parent::assertNotEquals(false, $result, 'The db should contain a book entry with \'Alice\'s Adventures...\'');
       		$title = $result[0]['title'];
       		$authors = $result[0]['authors'];
       		$this->log->info("Found using group_concat: '$title' by '$authors'.");
        	
        	$proxy->close();
        
        }
    }
}
