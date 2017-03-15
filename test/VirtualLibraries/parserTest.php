<?php

namespace VirtualLibraries
{
	/**
	 * COPS (Calibre OPDS PHP Server) test file
	 *
	 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
	 * @author     Jürgen Habelt <juergen@habelt-jena.de>
	 */
	
	require_once 'config.php';

	use PHPUnit\Framework\TestCase;
	use phpDocumentor\Reflection\Types\Boolean;
	
    class parserTest extends TestCase
    {
        /**
         * Match a single string
         * @param string
         * @return NULL|Boolean
         */
        private function matchString($string)
        {
            $parser = new VirtualLibrariesParser("\"$string\"");			// enclose string with quotes
            $res = $parser->match_String() ;
            if ($res === FALSE)
            	return null;
            
            return $res['puretext'];										// must return a match and the original text
        }
        
        /**
         * Helper unaryOp tests a unary operation
         * @param string $op
         * @param string $truth
         * @return NULL|Boolean
         */
        private function unaryOp($op, $truth)
        {
        	$parser = new VirtualLibrariesParser("$op $truth");
        	$res = $parser->match_Disjunction();
        	if ($res === FALSE)
        		return null;
        	
        	return $res['val'];
        }
        
        /**
         * Helper binaryOp tests a binary operation
         * @param string $op
         * @param string $truth1
         * @param string $truth2
         * @return NULL|Boolean
         */
        private function binaryOp($op, $truth1, $truth2)
        {
        	$parser = new VirtualLibrariesParser("$truth1 $op $truth2");
        	$res = $parser->match_Disjunction();
        	if ($res === FALSE)
        		return null;
        		 
        	return $res['val'];
       	}
       	
       	/**
       	 * Helper provides comparison string and returns Boolean test result
       	 * @param string $comparison
       	 * @return NULL|Boolean
       	 */
       	private function compare($comparison)
       	{
       		$parser = new VirtualLibrariesParser($comparison, new ClientSite());
       		$parser->prepare(100);
       		$res = $parser->match_Disjunction();
       		if ($res === FALSE)
       			return null;
       			 
       		return $res['val'];
       	}
        
       	/**
       	 * test of unary not
       	 */
        function testUnaryNot()
        {
        	parent::assertFalse(is_null($this->unaryOp('not', 'true')), 'not \'true\' should return a valid result');
        	parent::assertEquals(false, $this->unaryOp('not', 'true'), 'not \'true\' should return \'false\'');
        	parent::assertEquals(true, $this->unaryOp('not', 'false'), 'not \'false\' should return \'true\'');
        }
        
        /**
         * test of binary or
         */
        function testBinaryOr()
        {
        	parent::assertFalse(is_null($this->binaryOp('or', 'true', 'true')), '\'true\' or \'true\' should return a valid result');
        	parent::assertEquals(false, $this->binaryOp('or', 'false', 'false'), '\'false\' or \'false\' should return \'false\'');
        	parent::assertEquals(true, $this->binaryOp('or', 'false', 'true'), '\'false\' or \'true\' should return \'true\'');
        	parent::assertEquals(true, $this->binaryOp('or', 'true', 'false'), '\'true\' or \'false\' should return \'true\'');
        	parent::assertEquals(true, $this->binaryOp('or', 'true', 'true'), '\'true\' or \'true\' should return \'true\'');
        }
        
        /**
         * test of binary and
         */
        function testBinaryAnd()
        {
        	parent::assertFalse(is_null($this->binaryOp('and', 'true', 'true')), '\'true\' or \'true\' should return a valid result');
        	parent::assertEquals(false, $this->binaryOp('and', 'false', 'false'), '\'false\' or \'false\' should return \'false\'');
        	parent::assertEquals(false, $this->binaryOp('and', 'false', 'true'), '\'false\' or \'true\' should return \'false\'');
        	parent::assertEquals(false, $this->binaryOp('and', 'true', 'false'), '\'true\' or \'false\' should return \'false\'');
        	parent::assertEquals(true, $this->binaryOp('and', 'true', 'true'), '\'true\' or \'true\' should return \'true\'');
        }
        
        /**
         * test a few of the compare expressions
         */
        function testCompare()
        {
        	parent::assertFalse(is_null(self::compare('authors:"Balzac"')), 'authors:"Balzac" should return a valid result');
        	parent::assertEquals(true, self::compare('authors:"Balzac"'), 'authors:"Balzac" should return \'true\'');
        	parent::assertEquals(true, self::compare('authors:true'), 'authors:true should return \'true\'');
        	parent::assertEquals(false, self::compare('authors:false'), 'authors:false should return \'false\'');
        }
        
    }

    /**
     * Injected into parser for tests
     * @author Jürgen
     *
     */
    class ClientSite implements IClientSite
    {
    	/**
    	 * Interface method used by Parser
    	 * {@inheritDoc}
    	 * @see \VirtualLibraries\IClientSite::test()
    	 */
    	function test($sql)
    	{
    		return true;
    	}
    	
    	/**
    	 * Dummy interface method
    	 * {@inheritDoc}
    	 * @see \VirtualLibraries\IClientSite::create()
    	 */
    	function create($parseString)
    	{
    		 
    	}
    	
    	/**
    	 * Dummy interface method
    	 * {@inheritDoc}
    	 * @see \VirtualLibraries\IClientSite::isSelected()
    	 */
    	function  isSelected($id)
    	{
    		 
    	}
    }
}
