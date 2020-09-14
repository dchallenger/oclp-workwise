<?php
/**
 * Handle cron jobs by passing cli parameters to crontask controller as POST request.
 *
 * Usage:
 * (Windows)
 * C:\xampp\php\php.exe <HRIS_DIR>cron.php -u <USERNAME> -p <PASSWORD> -m <METHOD>
 *
 * @author jconsador
 */

// ------------------------------------------------------------------------

/** 
 * -u Username (required)
 * -p Password (required)
 * -m Method   (required)
 * 
 * @var $fields array
 */
$fields	= getopt('u:p:m:');

foreach ($fields as $field) {
	if (trim($field) == '' || count($fields) < 3) {
		die('Missing options.' . "\n");
	}
}

/**
* Initialize the cURL session
*/
$ch = curl_init();

/**
* Set the URL of the page or file to download.
*/
curl_setopt($ch, CURLOPT_URL, 'http://192.168.7.14/crontask');

//set the url, number of POST vars, POST data
curl_setopt($ch,CURLOPT_POST,count($fields));
curl_setopt($ch,CURLOPT_POSTFIELDS, http_build_query($fields, '', '&'));

/**
* Execute the cURL session
*/
curl_exec ($ch);

/**
* Close cURL session
*/
curl_close ($ch);