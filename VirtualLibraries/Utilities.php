<?php

namespace VirtualLibraries;

require_once dirname(__DIR__) . '/vendor/autoload.php';

/**
 * Class to log and print dianostic info
 * @author Jürgen
 *
 */
abstract class Diagnostic
{
	static $log;
	static $enabled = false;
	
	/**
	 * Performs the output
	 */
	static function diagnosticPrint($content)
	{
		if (!self::$log)
			self::$log = \Logger::getLogger('Diagnostic');
		
		if (self::$log)
			self::$log->debug($content);
		
		if (self::$enabled)
			print ("$content\n");
	}
	
	/**
	 * Enables / disables the output to the Console
	 * @param bool $enable
	 */
	static function enable($enable = true)
	{
		self::$enabled = $enable;
	}
	
	/**
	 * Queries the enabled flag
	 * @return boolean|bool
	 */
	static function enabled()
	{
		return self::$enabled;
	}
}

/**
 * Emulates the well-known HashSet
 */
class HashSet
{
    /**
     * Ctor. Expects a non-associative array (strings as values)
     */
    public function __construct($array)
    {
        $this->hashset = array();
        foreach ($array as $item)
        {
            $this->hashset[$item] = true;
        }
    }
    
    /*
     * Unifies 2 arrays or HashSets
     */
    static public function UnionS($ob1, $ob2)
    {
        if (gettype($ob1) === 'array')
            $ob1 = new HashSet($ob1);
        if (gettype($ob2) === 'array')
            $ob2 = new HashSet($ob2);
        
        return new HashSet(array_merge($ob1->hashset, $ob2->hashset));
    }
    
    /*
     * Unifies 2 arrays or HashSets, the instance version
     */
    public function Union($ob)
    {
        return HashSet::UnionS($this, $ob);
    }
    
    /*
     * Returns whether a given key exists
     */
    public function KeyExists($key)
    {
        return array_key_exists($key, $this->hashset);
    }
    
    /*
     * Returns all keys in the HashSet
     */
    public function Keys()
    {
        return array_keys($this->hashset);
    }

    private $hashset;
}
