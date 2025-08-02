<?php

if ($_SERVER["SERVER_NAME"] == "localhost")
{
    /** database config **/
    define('DBNAME', 'openminds');
    define('DBHOST', 'localhost');
    define('DBUSER', 'root');
    define('DBPASS', '1234');
    define('DBDRIVER', '');

    /** file structure config**/
    define('ROOT', "http://localhost/Openminds/public/");
    define('APP_ROOT', "http://localhost/Openminds/app/");
    define('HOST', "localhost");

}else
{
    /** database config **/
    define('DBNAME', '');
    define('DBHOST', 'localhost');
    define('DBUSER', 'root');
    define('DBPASS', '');
    define('DBDRIVER', '');

    /** file structure config**/
    define('ROOT', "https://www.openminds.org");
    define('APP_ROOT', "http://localhost/Openminds/app/");
    define('HOST', "https://www.openminds.org");
}

/** sessions config**/
ini_set('session.use_only_cookies', 1);
ini_set('session.use_use_strict_mode', 1);

session_set_cookie_params([
    'lifetime' => 1800,
    'domain' => HOST,
    'path' => '/',
    'secure' => 1,
    'httponly' => 1
]);

session_start();
regenerate_session_id();
