<?php

namespace VirtualLibraries;

/**
 * Implements the Singleton pattern
 * @author jsoft
 *
 */
trait Singleton
{
	static private $instance;
	
	/**
	 * Returns the one and only instance of a derived class.
	 * @return \VirtualLibraries\Singleton
	 */
	static public function getInstance()
	{
		if (!isset(self::$instance))
		{
			self::$instance = new static();
		}
		
		return self::$instance;
	}
}