<?php
$password = 'admin123';
$hashed = password_hash($password, PASSWORD_BCRYPT);
echo $hashed;
?>