<?php

namespace VirtualLibraries;

require_once dirname(__DIR__) . '/log4php/Logger.php';

\Logger::configure(dirname(__DIR__) . '/config.xml');
$log = \Logger::getLogger("Diagnostic");

$diagnosticPrintEnabled = false;

/**
 * Prints a string but only if enabled.
 * @param unknown $content
 */
function diagnosticPrint($content)
{
    global $diagnosticPrintEnabled;
    global $log;
    
    $log->debug($content);
    
    if ($diagnosticPrintEnabled)
        print ($content);
}

/**
 * Returns the diagnostic Print Enabled flag
 * @return boolean
 */
function diagnosticPrintEnabled()
{
    global $diagnosticPrintEnabled;
    
    return $diagnosticPrintEnabled;
}

/**
 * Enables / disables the diagnostic print during runtime
 */
function enableDiagnosticPrint($enable = true)
{
    global $diagnosticPrintEnabled;
    $diagnosticPrintEnabled = $enable;
}

/*
 * Emulates the well-known HashSet
 */
class HashSet
{
    /*
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
