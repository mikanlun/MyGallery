<?php

// Load our autoloader
require_once './../../twig/vendor/autoload.php';

// Specify our Twig templates location
$loader = new Twig_Loader_Filesystem('../templates');

 // Instantiate our Twig
//$twig = new Twig_Environment($loader, ['cache' => '../cache/compilation_cache', 'debug' => true]);
$twig = new Twig_Environment($loader, ['debug' => true]);

