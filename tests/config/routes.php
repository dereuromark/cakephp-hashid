<?php

use Cake\Routing\RouteBuilder;
use Cake\Routing\Router;

Router::scope('/', function(RouteBuilder $routes) {
	$routes->fallbacks();
});
