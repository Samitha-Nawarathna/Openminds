<?php

// Define the absolute physical path to the directory *above* 'app'.
// Assuming config.php is in Openminds/, and 'app' is inside Openminds/.
define('SERVER_ROOT', dirname(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR);// Note: If config.php is in the same folder as the .htaccess/index.php 
// entry point, use: define('SERVER_ROOT', __DIR__ . DIRECTORY_SEPARATOR);


if ($_SERVER["SERVER_NAME"] == "localhost")
{
    /** database config **/
    // define('DBNAME', 'openminds');
    // define('DBHOST', 'localhost');
    // define('DBUSER', 'root');
    // define('DBPASS', '1234');
    // define('DBDRIVER', '');

    define('DBNAME', 'samitha_openminds');
    define('DBHOST', 'mysql-samitha.alwaysdata.net');
    define('DBUSER', 'samitha');
    define('DBPASS', 'sam2008itha0522');
    define('DBDRIVER', '');

    /** file structure config**/
    // ROOT (HTTP path) is used for browser assets/links
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
    define('APP_ROOT', "https://www.openminds.org/app/"); // Updated for consistency
    define('HOST', "https://www.openminds.org");
}

define('APP_PASSWORD', "yqyw rzsi iklf boyd");

// Use SERVER_ROOT for internal server paths:
define('HEADER_PATH', SERVER_ROOT . "app/views/partials/header.view.php");
define('FOOTER_PATH', SERVER_ROOT . "app/views/partials/footer.view.php");

// FIX: Use forward slashes for the URL to avoid backslash issues.
define('DEFAULT_PROFILE_PICTURE', ROOT."uploads/0/profile.avif"); 

/** sessions config**/
ini_set('session.use_only_cookies', 1);
// Corrected typo in setting name
ini_set('session.use_strict_mode', 1); 

$lifetime = 100 * 365 * 24 * 60 * 60; // 100 years in seconds

session_set_cookie_params([
    'lifetime' => $lifetime,
    'domain' => HOST, // Should be the TLD/subdomain, but HOST is used here
    'path' => '/',
    // Use an expression for 'secure' to ensure it's 1 (true) only on HTTPS
    'secure' => ($_SERVER['REQUEST_SCHEME'] ?? 'http') === 'https', 
    'httponly' => 1
]);

ini_set('session.gc_maxlifetime', $lifetime);


session_start();
regenerate_session_id();