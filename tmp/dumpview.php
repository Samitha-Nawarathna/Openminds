<?php
require "c:/wamp64/www/Openminds/app/core/init.php";
$db = new Database();
$res = $db->query("SHOW CREATE VIEW profile_summary");
print_r($res);
