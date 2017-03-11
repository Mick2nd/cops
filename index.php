<?php
/**
 * COPS (Calibre OPDS PHP Server) HTML main script
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 *
 */

    require_once dirname(__FILE__) . '/config.php';
    require_once dirname(__FILE__) . '/base.php';

    ini_set("log_errors", 1);                                           // do this before instantiating ... php logging prepared !
    ini_set("error_reporting", E_ALL);
    ini_set("error_log", "/share/MD0_DATA/Web/copsgit/phplog.txt");     // TODO: change for production!
    
    $_SESSION['apppath'] = dirname(__FILE__);
    
    spl_autoload_register(function ($class_name)
    {
    	$fn = str_replace('\\', '/', $class_name) . '.php';
    	$path = $_SESSION['apppath'] . '/' . $fn;
    	trigger_error("Trying to autoload class $class_name at $path", E_USER_NOTICE);
    	 if (file_exists($path))
    	{
    		trigger_error("Autoloading class $class_name at $path", E_USER_NOTICE);
    		require_once $path;
    		return true;
    	}
    	return false;
    });
    
    
    // If we detect that an OPDS reader try to connect try to redirect to feed.php
    if (preg_match('/(MantanoReader|FBReader|Stanza|Marvin|Aldiko|Moon\+ Reader|Chunky|AlReader|EBookDroid|BookReader|CoolReader|PageTurner|books\.ebook\.pdf\.reader|com\.hiwapps\.ebookreader|OpenBook)/', $_SERVER['HTTP_USER_AGENT'])) {
        header('location: feed.php');
        exit();
    }
    
    $page     = getURLParam('page', Base::PAGE_INDEX);
    $query    = getURLParam('query');
    $qid      = getURLParam('id');
    $n        = getURLParam('n', '1');
    $database = GetUrlParam(DB);


    // Access the database ASAP to be sure it's readable, redirect if that's not the case.
    // It has to be done before any header is sent.
    Base::checkDatabaseAvailability();

    if ($config ['cops_fetch_protect'] == '1') {
        session_start();
        if (!isset($_SESSION['connected'])) {
            $_SESSION['connected'] = 0;
        }
    }

    header('Content-Type:text/html;charset=utf-8');

    $data = array('title'                 => $config['cops_title_default'],
                  'version'               => VERSION,
                  'opds_url'              => $config['cops_full_url'] . 'feed.php',
                  'customHeader'          => '',
                  'template'              => getCurrentTemplate(),
                  'server_side_rendering' => useServerSideRendering(),
                  'current_css'           => getCurrentCss(),
                  'favico'                => $config['cops_icon'],
                  'getjson_url'           => 'getJSON.php?' . addURLParameter(getQueryString(), 'complete', 1));
    if (preg_match('/Kindle/', $_SERVER['HTTP_USER_AGENT'])) {
        $data['customHeader'] = '<style media="screen" type="text/css"> html { font-size: 75%; -webkit-text-size-adjust: 75%; -ms-text-size-adjust: 75%; }</style>';
    }
    $headcontent = file_get_contents('templates/' . getCurrentTemplate() . '/file.html');
    $template = new doT();
    $dot = $template->template($headcontent, NULL);
    echo($dot($data));
?><body>
<?php
if (useServerSideRendering()) {
    // Get the data
    $data = JSONRenderer::getJson(true);

    echo serverSideRender($data);
}
?>
</body>
</html>
