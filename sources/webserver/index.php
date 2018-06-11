<?php

session_start();

if ($_SESSION['loggedin'])
{
    $user = $_SESSION['user'];
    die("<h1>Welcome $user</h1><p><a href='logout.php'>Logout</a></p>");
}
else
{
    header("Location: login.php");
    die();
}


