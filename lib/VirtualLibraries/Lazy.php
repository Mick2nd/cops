<?php

namespace VirtualLibraries;

/**
 * Implements the Lazy pattern
 * @author jsoft
 *
 */
class Lazy
{
	private $lazy;
	private $creator;
	
	/**
	 * During construction a creator/factory function is expected 
	 * @param callable $creator
	 */
	public function __construct($creator)
	{
		$this->creator = $creator;
	}
	
	/**
	 * This function lazily creates and returns the requested object
	 * @return type created by factory method
	 */
	public function getValue()
	{
		if (!isset($this->lazy))
		{
			$this->lazy = call_user_func($this->creator);
		}
		
		return $this->lazy;
	}
}