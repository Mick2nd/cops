<?php

/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Jürgen Habelt <juergen@habelt-jena.de>
 */

namespace VirtualLibraries;

require_once ("calibrePreferences.php");
require_once (dirname(__DIR__) . "/base.php");
require_once (dirname(__DIR__) . "/OPDS_renderer.php");

use Base;

/**
 * Class supports the use of Calibre Virtual Libraries as Facets
 * @author Jürgen
 *
 */ 
class VirtualLibraries 
{
	const VIRTUAL_LIBRARIES = "virtual_libraries";
	const USE_VIRTUAL_LIBRARIES = "cops_use_virtual_libraries";
	const SAVED_SEARCHES = "saved_searches";
 	
 	private $virtualLibraries;
 	private $savedSearches;
 	private $renderer;
 	
 	/**
 	 * Ctor.
 	 */
 	public function  __construct($renderer)
 	{
 		$this->renderer = $renderer;
 	}
 	
 	/**
 	 * Renders the configured Virtual Libraries as Links to the OPDS catalog
 	 * Provided this mode is enabled in config file
 	 */
 	public function renderLinks()
 	{
 		global $config;
 		
 		if (is_null($config[self::USE_VIRTUAL_LIBRARIES]) || $config[self::USE_VIRTUAL_LIBRARIES] == "0")
 			return false;
 		
        $urlFilter = getURLParam ("search", "");
 		foreach ($this->getVirtualLibraries() as $lib => $filter)
 		{
 			$encodedFilter = bin2hex($filter);
 			$phref = addURLParameter (getQueryString(), "search", $encodedFilter);
 			$link = new \LinkFacet ("?" . $phref, $lib, localize ("tagword.title"), $encodedFilter == $urlFilter);	// TODO: replace tag name
 			$this->renderer->renderLink ($link); 			
 		}
 		 	
 		return true;
	}
	
	private function getVirtualLibraries()
	{
 		if (is_null($this->virtualLibraries))
 		{
 			$vlibs = json_decode(CalibrePreferences::getSetting(self::VIRTUAL_LIBRARIES), true, 2);
 			$vlibs["Alle"] = "";
 			
 			$this->virtualLibraries = $vlibs;
  		}
 				
		return $this->virtualLibraries;
	}
	
	private function getSavedSearches()
	{
		if (is_null($this->savedSearches))
		{
			$this->savedSearches = json_decode(CalibrePreferences::getSetting(self::SAVED_SEARCHES), true, 2);
		}
		
		return $this->savedSearches;
	}
}
 