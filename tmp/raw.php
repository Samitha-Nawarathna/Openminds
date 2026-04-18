<?php
$conn = new mysqli('localhost', 'root', '1234', 'openminds');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$res = $conn->query("SHOW CREATE VIEW profile_summary");
if ($res) {
    $row = $res->fetch_assoc();
    file_put_contents(__DIR__ . '/v.txt', $row['Create View']);
} else {
    echo "Query failed";
}
