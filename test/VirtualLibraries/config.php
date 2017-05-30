<?php
namespace VirtualLibraries;

/**
 * To be included in every test case
 */
date_default_timezone_set('Europe/Berlin');

require_once dirname(__DIR__) . '/../vendor/autoload.php';

\Logger::configure(dirname(__DIR__) . '/../config.xml');
