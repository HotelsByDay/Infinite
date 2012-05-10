<?php defined('SYSPATH') or die('No direct script access.');

//-- Environment setup --------------------------------------------------------

/**
 * Set the default time zone.
 *
 * @see  http://kohanaframework.org/guide/using.configuration
 * @see  http://php.net/timezones
 */
date_default_timezone_set('Europe/Prague');

/**
 * Set the default locale.
 *
 * @see  http://kohanaframework.org/guide/using.configuration
 * @see  http://php.net/setlocale
 */
setlocale(LC_ALL, 'cs_CZ.utf-8');

/**
 * Oddelovac desetine casti u cisel.
 */
setlocale(LC_NUMERIC, 'POSIX');

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

//-- Configuration and initialization -----------------------------------------

/**
 * Set Kohana::$environment if a 'KOHANA_ENV' environment variable has been supplied.
 */
if (isset($_SERVER['KOHANA_ENV']))
{
	Kohana::$environment = $_SERVER['KOHANA_ENV'];
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
	'base_url'   => '/infinite_devel1/trunk/',
        'index_file' => FALSE,
        'caching'    => FALSE,
));

/**
 * Attach the file write to logging. Multiple writers are supported.
 */
Kohana::$log->attach(new Kohana_Log_File(APPPATH.'logs'));

/**
 * Attach a file reader to config. Multiple readers are supported.
 */
Kohana::$config->attach(new Kohana_Config_File);

/**
 * Enable modules. Modules are referenced by a relative or absolute path.
 */
Kohana::modules(array(
    'core'          => MODPATH.'core',       // Jadro aplikace
    'auth'          => MODPATH.'auth',       // Basic authentication
    'cache'         => MODPATH.'cache',      // Caching with multiple backends
    'cron'          => MODPATH.'cron',       // Spousteni uloh napojenych na cron
    'event'         => MODPATH.'event',      // Modul pro spousteni globalnich udalosti, nahrada za Event z K2
    'form'          => MODPATH.'form',       // Modul poskytujici praci s formulari
    'database'      => MODPATH.'database',   // Database access
    'image'         => MODPATH.'image',      // Image manipulation
    'emailq'        => MODPATH.'emailq',     // E-mail queue - odesila emaily, ktere jsou ve fronte
    'comment'       => MODPATH.'comment',
    'multilang'     => MODPATH.'multilang', //prepinani jazyku
    'orm'           => MODPATH.'orm',        // Object Relationship Mapping
    'emailq'        => MODPATH.'emailq',     //emaily
    'comment'       => MODPATH.'comment',
    'userguide'     => MODPATH.'userguide',  // User guide and API documentation

));

/**
 * Inicializuji tridu, ktera slouzi k pristupu k hlavnimu konfiguracnimu souboru
 * aplikace.
 */
AppConfig::instance()->init(DOCROOT.'config.ini');

/**
 * Aktivace defaultniho jazyku systemu
 */
I18n::lang(AppConfig::instance()->get('lang', 'application'));

/**
 * Nastavim defaultni Cache driver.
 * @TODO: Nastavit na memcache, nainstalovat memcache, zajisti memcache na serveru
 */
Cache::$default = 'file';

/**
 * Pokud uzivatel pristupuje pres nekompatibilni prohlizec, tak tahle routa
 * jej nasmeruje na akci, ktera vygeneruje stranku s chybovou strankou.
 */
if ( ! Core_Browser::compatible())
{
    Route::set('incompatible_browser', '<uri>',
        array(
            'uri'   => '.*'
        ))
        ->defaults(array(
            'controller' => 'error',
            'action'     => 'incombatible_browser'
        ));
}

/**
 * Defaultni adresa - domaci stranka
 */
Route::set('default', '<controller>',
          array(
                'controller' => '(dashboard|)',
          ))
          ->defaults(array(
                'controller' => 'dashboard'
          ));

/**
 * Autentikace uzivatele - prihlaseni, odhlaseni
 */
Route::set('user_auth', '<controller>',
        array(
            'controller' => '(login|logout)',
        ));

/**
 * Uzivatelsky profil
 */
Route::set('user_profile', 'my_profile')
           ->defaults(array(
               'controller' => 'user',
               'action'     => 'my_profile'
           ));


/**
 * Zakladni URL pro pristup na poradace:
 * "/object/table"
 * "/object/table_data" //ajax volani pro nacteni data na /object/table
 * "/object"
 */
Route::set('object_actions', '<controller>/<action>(/<type>)',
        array(
            'action' => '(table|trash|table_data|table_trash_data|do|undo|export_data|new|remove_filter_item|cb_data|load_filter_item)',
            'type' => '[a-z0-9_]+'
        ))
	->defaults(array(
		'action'     => 'index',
	));

/**
 * Zakladni URL pro pristup na poradace:
 * "/object/edit/1"
 * "/object/overview/4"
 */
Route::set('object_actions_with_id', '<controller>/<action>/<id>',
        array(
            'action' => '(delete|edit|overview|change_attr)',
            'id'     => '[0-9]+'
        ))
	->defaults(array(
		'action'     => 'index',
	));

/**
 * URL pro nacteni ajax formulare, kde je i specifikace typu formulare.
 * Napr.: "/interest/edit_ajax/client_form?ed=1530"
 */
Route::set('object_ajax_form', '<controller>(/<action>(/<form_type>)(/<id>))',
        array(
            'action' => '(edit_ajax)',
            'id'     => '[0-9]+'
        ))
	->defaults(array(
		'action'     => 'index',
	));

/**
 * Ajaxova volani na strance s overview - nacteni obsahu pres submenu
 */
Route::set('object_overview_ajax', '<controller>/<action>/<panel>/<id>',
        array(
            'action' => '(overview_subcontent)',
            'panel'  => '[a-z_]+',
            'id'     => '[0-9]+'
        ));

/**
 * Object data panel ajax volani - nacitan panel (vypis dat) na zaklade filtru
 */
Route::set('object_data_panel_ajax', '<controller>/<action>/<panel>',
        array(
            'action' => '(table_obd_panel)',
        ));

/**
 * URL pro praci s crony
 * Napr.: "/cron/setup", "cron/index"
 */
Route::set('cron', '<controller>/<action>(/<id>)',
        array(
            'controller' => 'cron',
            'action' => '(index|setup)',
        ));

//Pokud je zapnuta funkce fulltextoveho vyhledavani tak vytvorim potrebne pravidla pro routovani
if (AppConfig::instance()->get('fulltext', 'application'))
{
    Route::set('fulltext', '<controller>/<action>',
            array(
                'controller' => 'fulltext',
                'action'     => 'search|search_data'
            ));
}

/**
 * Upload souboru
 * Napr.: "/upload/file/advert.photo/advert_photo_preview"
 */
Route::set('upload', '<controller>/<action>/<config_key>',
        array(
            'controller'     => 'upload',
            'action'         => 'file',
            'config_key'     => '[\.a-z_]+'
        ));

/**
 * Cached JS files
 * Example: "/js/js.2jl4h4l2kj35l24sfd9sdf87asfd"
 */
Route::set('js', 'js/<id>',
        array(
            'id' => 'js\.[a-z0-9]+'
        ))
	->defaults(array(
            'controller' => 'web',
            'action'     => 'js'
	));


//globalni udalost - moduly jsou inicializovany, zakladni routy nastaveny
//v teto udalosti by melo probehnout nastaveni jazyka dle prihlaseneho uzivatele
//apod.
Dispatcher::instance()->trigger_event('system.ready', Dispatcher::event());

if ( ! defined('SUPPRESS_REQUEST'))
{
	/**
	 * Execute the main request. A source of the URI can be passed, eg: $_SERVER['PATH_INFO'].
	 * If no source is specified, the URI will be automatically detected.
	 */
	echo Request::instance()
		->execute()
		->send_headers()
		->response;
}
