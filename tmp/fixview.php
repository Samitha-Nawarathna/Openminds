<?php
$conn = new mysqli('localhost', 'root', '1234', 'openminds');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$sql = "CREATE OR REPLACE VIEW `profile_summary` AS 
SELECT 
  `u`.`id` AS `profile_id`,
  `us`.`subject_id` AS `subject_id`,
  `u`.`username` AS `username`,
  `u`.`display_name` AS `display_name`,
  `r`.`name` AS `role_name`,
  `u`.`created_at` AS `created_at`,
  `s`.`name` AS `subject_name`,
  COALESCE(NULLIF(`u`.`profile_picture`, ''), 'uploads\\\\0\\\\profile.avif') AS `profile_picture`,
  `u`.`banned` AS `banned` 
FROM `user` `u` 
LEFT JOIN `roles` `r` ON `u`.`role` = `r`.`role_id`
LEFT JOIN `experts` `us` ON `u`.`id` = `us`.`user_id`
LEFT JOIN `subjects` `s` ON `us`.`subject_id` = `s`.`id`;";

if ($conn->query($sql) === TRUE) {
    echo "View replaced successfully.";
} else {
    echo "Error replacing view: " . $conn->error;
}
