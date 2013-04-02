<?php defined('SYSPATH') or die('No direct script access.');

// -- Environment setup --------------------------------------------------------
// Load the core Kohana class
require SYSPATH . 'classes/kohana/core' . EXT;

if (is_file(APPPATH . 'classes/kohana' . EXT)) {
    // Application extends the core
    require APPPATH . 'classes/kohana' . EXT;
} else {
    // Load empty core extension
    require SYSPATH . 'classes/kohana' . EXT;
}

/**
 * Set the default time zone.
 *
 * @see  http://kohanaframework.org/guide/using.configuration
 * @see  http://php.net/timezones
 */
date_default_timezone_set('Europe/Berlin');

/**
 * Set the default locale.
 *
 * @see  http://kohanaframework.org/guide/using.configuration
 * @see  http://php.net/setlocale
 */
setlocale(LC_ALL, 'de_DE.utf-8');

/**
 * Enable the Kohana auto-loader.
 *
 * @see  http://kohanaframework.org/guide/using.autoloading
 * @see  http://php.net/spl_autoload_register
 */
spl_autoload_register(array('Kohana', 'auto_load'));

/**
 * Enable the Kohana auto-loader for unserialization.
 *
 * @see  http://php.net/spl_autoload_call
 * @see  http://php.net/manual/var.configuration.php#unserialize-callback-func
 */
ini_set('unserialize_callback_func', 'spl_autoload_call');

// -- Configuration and initialization -----------------------------------------

/**
 * Set the default language
 */
I18n::lang('de');

/**
 * Set Kohana::$environment if a 'KOHANA_ENV' environment variable has been supplied.
 *
 * Note: If you supply an invalid environment name, a PHP warning will be thrown
 * saying "Couldn't find constant Kohana::<INVALID_ENV_NAME>"
 */
if (isset($_SERVER['KOHANA_ENV'])) {
    Kohana::$environment = constant('Kohana::' . strtoupper($_SERVER['KOHANA_ENV']));
} else {
    Kohana::$environment = ($_SERVER['REMOTE_ADDR'] == '127.0.0.1' ? Kohana::DEVELOPMENT : Kohana::PRODUCTION);
}

/**
 * Initialize Kohana, setting the default options.
 *
 * The following options are available:
 *
 * - string   base_url    path, and optionally domain, of your application   NULL
 * - string   index_file  name of your index file, usually "index.php"       index.php
 * - string   charset     internal character set used for input and output   utf-8
 * - string   cache_dir   set the internal cache directory                   APPPATH/cache
 * - boolean  errors      enable or disable error handling                   TRUE
 * - boolean  profile     enable or disable internal profiling               TRUE
 * - boolean  caching     enable or disable internal caching                 FALSE
 */
Kohana::init(array(
    'base_url' =>dirname($_SERVER['SCRIPT_NAME']).'/',
    'index_file' => FALSE, // SEO (avoid index.php/mycontroller/action)
    'profile' => (Kohana::$environment !== Kohana::PRODUCTION), //see how good you are
    'caching' => TRUE//(Kohana::$environment === Kohana::PRODUCTION),
));

/**
 * Attach the file write to logging. Multiple writers are supported.
 */
Kohana::$log->attach(new Log_File(APPPATH . 'logs'));

/**
 * Attach a file reader to config. Multiple readers are supported.
 */
Kohana::$config->attach(new Config_File);

/**
 * Enable modules. Modules are referenced by a relative or absolute path.
 */
Kohana::modules(array(
    'auth' => MODPATH . 'auth', // Basic authentication
    'cache' => MODPATH . 'cache', // Caching with multiple backends
     //'codebench'  => MODPATH.'codebench',  // Benchmarking tool
    'database' => MODPATH . 'database', // Database access
    'image' => MODPATH . 'image', // Image manipulation
    'orm' => MODPATH . 'orm', // Object Relationship Mapping
    // 'unittest'   => MODPATH.'unittest',   // Unit testing
     'userguide'  => MODPATH.'userguide',  // User guide and API documentation
    'swiftmailer' => MODPATH . 'swift', //Swiftmailer Module
    'pchart' => MODPATH . 'pchart', //pChart Module
    'phpexcel' => MODPATH . 'phpexcel' //PHPExcel Module
));

$langs = implode('|', Kohana::$config->load('config')->get('avaliable_languages', array()));



Route::set('details_byza','(<lang>/)za<id>',array('lang' => '(' . $langs . ')','id'=>'[0-9]{4}'))
        ->defaults(array(
            'controller'=>'project',
            'action'=>'za_details'
        ));
/**
 * Route to show table details 
 */
Route::set('details', '(<lang>/)table/details/<id>/(<filter>)', array('lang' => '(' . $langs . ')', 'id' => '.+', 'filter' => '.{32}'))
        ->defaults(array(
            'controller' => 'table',
            'action' => 'details'));

Route::set('edit_details', '(<lang>/)table/edit_details/<id>/(<filter>)', array('lang' => '(' . $langs . ')', 'id' => '.+', 'filter' => '.{32}'))
        ->defaults(array(
            'controller' => 'table',
            'action' => 'edit_details'));
/**
 * Route to track download 
 */
Route::set('download_redirect', '(<lang>/)table/download/<type>/<id>/<filter>', array('lang' => '(' . $langs . ')', 'type' => '(xls|xlsx|csv)', 'id' => '.+', 'filter' => '.{32}'))
        ->defaults(array(
            'controller' => 'table',
            'action' => 'download'));

/*
 * Route to create data file and download it
 */
Route::set('download_real', '(<lang>/)table/<action>/<id>/<filter>', array('lang' => '(' . $langs . ')', 'action' => '(xls|xlsx|csv)', 'id' => '.+', 'filter' => '.{32}'))
        ->defaults(array(
            'controller' => 'table'));



/**
 * Route to show download dialog 
 */
Route::set('download_message', '(<lang>/)download/<action>/<id>/<filter>', array('lang' => '(' . $langs . ')', 'action' => '(xls|xlsx|csv)', 'id' => '.+', 'filter' => '.{32}'))
        ->defaults(array(
            'controller' => 'download'));

/**
 * Route to delete Cart filters 
 */
Route::set('delete_cart', '(<lang>/)cart/delete/<id>/(<filter>)', array('lang' => '(' . $langs . ')', 'id' => '.+', 'filter' => '.{32}'))
        ->defaults(array(
            'controller' => 'cart',
            'action' => 'delete'));

// API Browser, if enabled
if (Kohana::$config->load('userguide.api_browser') === TRUE)
{
	Route::set('docs/api', '(<lang>/)guide/api(/<class>)', array('class' => '[a-zA-Z0-9_]+'))
		->defaults(array(
			'controller' => 'userguide',
			'action'     => 'api',
			'class'      => NULL,
		));
}

// User guide pages, in modules
Route::set('docs/guide', '(<lang>/)guide(/<module>(/<page>))', array(
		'page' => '.+',
	))
	->defaults(array(
		'controller' => 'userguide',
		'action'     => 'docs',
		'module'     => '',
	));
/**
 * Set the routes. Each route must have a minimum of a name, a URI and a set of
 * defaults for the URI.
 */
Route::set('default_lang', '(<lang>/)(<controller>(/<action>(/<id>)))', array('lang' => '(' . $langs . ')', 'id' => '.+'))
        ->defaults(array(
            'controller' => 'index',
            'action' => 'index'));

Route::set('default', '(<controller>(/<action>(/<id>)))', array('id' => '.+'))
        ->defaults(array(
            'controller' => 'index',
            'action' => 'index'));



