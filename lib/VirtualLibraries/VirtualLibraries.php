<?php

/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Jürgen Habelt <juergen@habelt-jena.de>
 */

namespace VirtualLibraries;

/**
 * Class supports the use of Calibre Virtual Libraries as Facets
 * @author Jürgen
 *
 */ 
class VirtualLibraries 
{
	use Singleton;																// this must be a singleton
	
	const VIRTUAL_LIBRARIES = "virtual_libraries";
	const USE_VIRTUAL_LIBRARIES = "cops_use_virtual_libraries";
	const SAVED_SEARCHES = "saved_searches";
 	
 	private $virtualLibraries;
 	private $savedSearches;
 	private $renderer;
 	
 	/**
 	 * Ctor.
 	 */
 	private function  __construct()
 	{

 	}
 	
 	/**
 	 * Used to inject the opds renderer into this singleton instance
 	 * @param \OPDSRenderer $renderer
 	 */
 	public function setRenderer($renderer)
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
 		
 		$urlName = getURLParam ("search", $this->encodeName(localize("allbooks.title")));
 		foreach ($this->getVirtualLibraries() as $lib => $filter)
 		{
 			$encodedName = $this->encodeName($lib);
 			$phref = addURLParameter (getQueryString(), "search", $encodedName);
 			$link = new \LinkFacet ("?" . $phref, $lib, localize ("virtuallib.title"), $encodedName == $urlName);
 			$this->renderer->renderLink ($link); 			
 		}
 		 	
 		return true;
	}
	
	/**
	 * Returns the VL definition string for a given name
	 * @param string $name
	 * @return mixed|string
	 */
	public function getFilter($name)
	{
		$name = $this->decodeName($name);
		
		if (array_key_exists($name, $this->getVirtualLibraries()))
		{
			return $this->getVirtualLibraries()[$name];
		}
		
		return '';
	}
	
	/**
	 * Encapsulates the encoding strategy for the VL name
	 * @param string $name
	 * @return string
	 */
	private function encodeName($name)
	{
		return urlencode($name);
	}
	
	/**
	 * Encapsulates the decoding strategy for the VL name
	 * @param string $encodedName
	 * @return string
	 */
	private function decodeName($encodedName)
	{
		return urldecode($encodedName);
	}
	
	/**
	 * Reads the Virtual Libraries from the db, if not already done so
	 * @return array
	 */
	private function getVirtualLibraries()
	{
 		if (is_null($this->virtualLibraries))
 		{
 			$vlibs = json_decode(CalibrePreferences::getSetting(self::VIRTUAL_LIBRARIES), true, 2);
 			$vlibs[localize("allbooks.title")] = "";
 			
 			$this->virtualLibraries = $vlibs;
  		}
 				
		return $this->virtualLibraries;
	}
	
	/**
	 * Reads the Saved Searches from the db, if not already done so
	 * @return array
	 */
	private function getSavedSearches()
	{
		if (is_null($this->savedSearches))
		{
			$this->savedSearches = json_decode(CalibrePreferences::getSetting(self::SAVED_SEARCHES), true, 2);
		}
		
		return $this->savedSearches;
	}
}
 