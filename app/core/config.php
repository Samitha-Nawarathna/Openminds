<?php

if ($_SERVER["SERVER_NAME"] == "localhost")
{
    /** database config **/
    define('DBNAME', 'openmind_test');
    define('DBHOST', 'localhost');
    define('DBUSER', 'root');
    define('DBPASS', '1234');
    define('DBDRIVER', '');

    define('ROOT', "http://localhost/Openminds/public/");
}else
{
    /** database config **/
    define('DBNAME', '');
    define('DBHOST', 'localhost');
    define('DBUSER', 'root');
    define('DBPASS', '');
    define('DBDRIVER', '');

    define('ROOT', "https://www.openminds.org");
}