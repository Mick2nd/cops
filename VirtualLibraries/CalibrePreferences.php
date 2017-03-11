<?php

namespace VirtualLibraries;

/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     J�rgen Habelt <juergen@habelt-jena.de>
 */

require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/base.php';

abstract class CalibrePreferences
{
	/**
	 * Return an arbitrary preference values when $key is given
	 * @param string $key
	 */
	public static function getSetting($key) 
	{
		$fquery = "select val from preferences where key = '" . $key . "'";
		$result = \Base::getDb ()->prepare ($fquery);
        $result->execute ();
        return $result->fetchColumn ();
	}
}
