<?php

$config = require_once dirname(dirname(dirname(__DIR__))) . '/config.php';

session_name($config['sessionName']);
session_start();

$_SESSION['rewritesEnabled'] = true;

echo json_encode(array('result' => true));
