<?php
session_start();
 $menuPath= "./";

require_once("middleware/auth.php");
verifyAuth();

// Redirect to dashboard initially
header("Location: ./dashboard/");
exit();
