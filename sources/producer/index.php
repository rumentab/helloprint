<?php

require_once "config.php";

require_once "Dispatcher.class.php";

if (filter_input(INPUT_SERVER, 'REQUEST_METHOD') === 'POST')
{
    $input_type = INPUT_POST;

    $data = $_POST['payload'];
}
else {
    $input_type = INPUT_GET;

    $data = $_GET['payload'];
}

$auth_token = filter_input($input_type, 'authToken');

$action = filter_input($input_type, 'action');


$dispatcher = new Dispatcher($config, $auth_token);

$dispatcher->processRequest($action, $data);