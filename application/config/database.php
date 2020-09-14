<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
| -------------------------------------------------------------------
| DATABASE CONNECTIVITY SETTINGS
| -------------------------------------------------------------------
| This file will contain the settings needed to access your database.
|
| For complete instructions please consult the 'Database Connection'
| page of the User Guide.
|
| -------------------------------------------------------------------
| EXPLANATION OF VARIABLES
| -------------------------------------------------------------------
|
|	['hostname'] The hostname of your database server.
|	['username'] The username used to connect to the database
|	['password'] The password used to connect to the database
|	['database'] The name of the database you want to connect to
|	['dbdriver'] The database type. ie: mysql.  Currently supported:
				 mysql, mysqli, postgre, odbc, mssql, sqlite, oci8
|	['dbprefix'] You can add an optional prefix, which will be added
|				 to the table name when using the  Active Record class
|	['pconnect'] TRUE/FALSE - Whether to use a persistent connection
|	['db_debug'] TRUE/FALSE - Whether database errors should be displayed.
|	['cache_on'] TRUE/FALSE - Enables/disables query caching
|	['cachedir'] The path to the folder where cache files should be stored
|	['char_set'] The character set used in communicating with the database
|	['dbcollat'] The character collation used in communicating with the database
|				 NOTE: For MySQL and MySQLi databases, this setting is only used
| 				 as a backup if your server is running PHP < 5.2.3 or MySQL < 5.0.7
|				 (and in table creation queries made with DB Forge).
| 				 There is an incompatibility in PHP with mysql_real_escape_string() which
| 				 can make your site vulnerable to SQL injection if you are using a
| 				 multi-byte character set and are running versions lower than these.
| 				 Sites using Latin-1 or UTF-8 database character set and collation are unaffected.
|	['swap_pre'] A default table prefix that should be swapped with the dbprefix
|	['autoinit'] Whether or not to automatically initialize the database.
|	['stricton'] TRUE/FALSE - forces 'Strict Mode' connections
|							- good for ensuring strict SQL while developing
|
| The $active_group variable lets you choose which connection group to
| make active.  By default there is only one group (the 'default' group).
|
| The $active_record variables lets you determine whether or not to load
| the active record class
*/

$url = "http://".$_SERVER['HTTP_HOST'];

$parsedUrl = parse_url($url);

$host = explode('.', $parsedUrl['host']);

$subdomains = array_slice($host, 0, count($host) - 2 );

$active_group = 'default';
$active_record = TRUE;

if (isset($subdomains[0]) && $subdomains[0] != '') {
	//$active_group = $subdomains[0];	
}

// Name of install sql.
$db['default']['install_file'] = $db['local']['install_file'] = 'data-mysql/hdi.resource.sql';
// Change depending on hosting.
// Windows 
$db['default']['mysql_path'] = $db['local']['mysql_path'] = 'D:\xampplite\mysql\bin\mysql.exe';

$db['default']['hostname'] = 'localhost';
$db['default']['username'] = 'root';
$db['default']['password'] = '';
$db['default']['database'] = 'hr.oclp07072020';
$db['default']['dbdriver'] = 'mysql';
$db['default']['dbprefix'] = 'hr_';
$db['default']['pconnect'] = FALSE;
$db['default']['db_debug'] = FALSE;
$db['default']['cache_on'] = FALSE;
$db['default']['cachedir'] = '';
$db['default']['char_set'] = 'utf8';
$db['default']['dbcollat'] = 'utf8_general_ci';
$db['default']['swap_pre'] = '';
$db['default']['autoinit'] = TRUE;
$db['default']['stricton'] = FALSE;	

$db['ms_sql']['hostname'] = "192.168.0.146\SQLEXPRESS";
$db['ms_sql']['username'] = "HDIUser";
$db['ms_sql']['password'] = 'Pr0@ctiv3';
$db['ms_sql']['database'] = "NitgenAccessManager";
$db['ms_sql']['dbdriver'] = "mssql";
$db['ms_sql']['dbprefix'] = "";
$db['ms_sql']['pconnect'] = FALSE;
$db['ms_sql']['db_debug'] = TRUE;
$db['ms_sql']['cache_on'] = FALSE;
$db['ms_sql']['cachedir'] = "";
$db['ms_sql']['char_set'] = "utf8";
$db['ms_sql']['dbcollat'] = "utf8_general_ci";

/* End of file database.php */