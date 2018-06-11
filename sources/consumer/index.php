<?php

require_once "config.php";

require_once "Consumer.class.php";

$data = $_POST['payload'];

$auth_token = filter_input(INPUT_POST, 'auth_key');

$action = filter_input(INPUT_POST, 'action');

$consumer = new Consumer($config, $auth_token);

$consumer->processRequest($action, $data);