<?php
/**
 * COPS (Calibre OPDS PHP Server) main script
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 *
 */

    require_once 'config.php';
    require_once 'base.php';
    
    ini_set("log_errors", 1);                                           // do this before instantiating ... php logging prepared !
    ini_set("error_reporting", E_ALL);
    ini_set("error_log", "/share/MD0_DATA/Web/copsgit/phplog.txt");     // TODO: change for production!

    $_SESSION['apppath'] = dirname(__FILE__);
	    
    /**
     * This function more or less works, but the additions checking for definition of classes
     * are more or less useless.
     * No idea what causes the require_once of Author class to fail.
     */
    /*
	*/
    spl_autoload_register(function ($class_name)
    {
    	try 
    	{
    		if (substr($class_name, 0, 16) !== 'VirtualLibraries')
    		{
    			return false;
    		}
    		
    		$fn = str_replace('\\', '/', $class_name) . '.php';
    		$path = $_SESSION['apppath'] . '/' . $fn;
    		trigger_error("Trying to autoload class $class_name at $path", E_USER_NOTICE);
    		if (class_exists($class_name))
    		{
    			trigger_error("Class '$class_name' already exists", E_USER_NOTICE);
    			return false;
    		}
    		if (!file_exists($path))
    		{
    			trigger_error("File '$path' does not exist", E_USER_NOTICE);
    			return false;
    		}

    		trigger_error("Autoloading class $class_name at $path", E_USER_NOTICE);
    		require_once $path;
    		return true;
    	}
    	catch (Exception $e) 
    	{
    		trigger_error($e, E_USER_ERROR);
    		return false;
    	}
    	
    });
    
    \Logger::configure('config.xml');
    
    header('Content-Type:application/xml');
    $page = getURLParam('page', Base::PAGE_INDEX);
    $query = getURLParam('query');
    $n = getURLParam('n', '1');
    if ($query) {
        $page = Base::PAGE_OPENSEARCH_QUERY;
    }
    $qid = getURLParam('id');

    if ($config ['cops_fetch_protect'] == '1') {
        session_start();
        if (!isset($_SESSION['connected'])) {
            $_SESSION['connected'] = 0;
        }
    }

    $OPDSRender = new OPDSRenderer();

    switch ($page) {
        case Base::PAGE_OPENSEARCH :
            echo $OPDSRender->getOpenSearch();
            return;
        default:
            $currentPage = Page::getPage($page, $qid, $query, $n);
            $currentPage->InitializeContent();
            echo $OPDSRender->render($currentPage);
            return;
    }
