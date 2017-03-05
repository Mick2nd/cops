<?php
    if (!isset($config))
        $config = array();
  
    /*
     * The directory containing calibre's metadata.db file, with sub-directories
     * containing all the formats.
     * BEWARE : it has to end with a /
     */
    $config['calibre_directory'] = '/share/MD0_DATA/Multimedia/eBooks/';
    
    /*
     * Catalog's title
     */
    $config['cops_title_default'] = "Jürgens Calibre Bibliothek";
    
    /*
     * use URL rewriting for downloading of ebook in HTML catalog
     * See README for more information
     *  1 : enable
     *  0 : disable
     */
    $config['cops_use_url_rewriting'] = "0";

    /*
     * Force a certain language
     */
    $config['cops_language'] = 'de';
    
    /*
     * Icon for both OPDS and HTML catalog
     * Note that this has to be a real icon (.ico)
     */
    $config['cops_icon'] = "http://192.168.178.4/cops/favicon.png";
    
    /*
     * Default timezone
     * Check following link for other timezones :
     * http://www.php.net/manual/en/timezones.php
     */
    $config['default_timezone'] = "Europe/Berlin";

    /*
     * Custom Columns for the index page
     * to add as an array containing the lookup names configured in Calibre
     *
     * For example : array ("genre", "mycolumn");
     *
     * Note that the composite custom columns are not supported
     */
    $config['cops_calibre_custom_column'] = array ("genre");
    
    /*
     * Custom Columns for the list representation
     * to add as an array containing the lookup names configured in Calibre
     *
     * For example : array ("genre", "mycolumn");
     *
     * Note that the composite custom columns are not supported
     */
    $config['cops_calibre_custom_column_list'] = array ("genre");
    
    /*
     * Custom Columns for the book preview panel
     * to add as an array containing the lookup names configured in Calibre
     *
     * For example : array ("genre", "mycolumn");
     *
     * Note that the composite custom columns are not supported
    */
    $config['cops_calibre_custom_column_preview'] = array ("genre");

    /*
     * Filter on tags to book list
     * Only works with the OPDS catalog
     * Usage : array ("I only want to see books using the tag : Tag1"     => "Tag1",
     *                "I only want to see books not using the tag : Tag1" => "!Tag1",
     *                "I want to see every books"                         => "",
     *
     * Example : array ("All" => "", "Unread" => "!Read", "Read" => "Read")
     */
    $config['cops_books_filter'] = array 
    (
    		"Alle" => "",
    		"Mathematik" => "Mathematik",
    		"Matlab" => "Matlab",
    		"Mathematik ohne Matblab" => "Mathematik and !Matblab"
    );

    /*
     * Use filter in HTML catalog
     * 1 : Yes (enable)
     * 0 : No
     */
    $config['cops_html_tag_filter'] = "1";

    /*
     * Use Virtual Libraries setting (preferred) instead of Tag Filter
     * "0" : do not used
     * "1" : use instead of "cops_books_filter"
     */
    $config['cops_use_virtual_libraries'] = "1";
    