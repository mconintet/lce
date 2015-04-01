<?php

error_reporting(E_ALL);

include('../web/Application.php');

\Lce\web\Application::run(array(
    'debugMode' => true
));