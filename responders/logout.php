<?php
session_start();
session_unset();
session_destroy();
header("Location: ../responders/admin_login.php");
exit;
?>
