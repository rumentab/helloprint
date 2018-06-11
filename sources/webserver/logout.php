<?php
/**
 * Logout the user
 */

session_start();

if ($_SEESION['loggedin'])
{
    session_destroy();
}

header("Location: login.php");
die();