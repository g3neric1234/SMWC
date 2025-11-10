<?php
session_start();
session_destroy();
header("Location: /smwc/index");
exit;
?>
