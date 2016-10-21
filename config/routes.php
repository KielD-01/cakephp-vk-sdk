<?php
use Cake\Core\Plugin;
use Cake\Routing\Route\DashedRoute;
use Cake\Routing\RouteBuilder;
use Cake\Routing\Router;

Router::defaultRouteClass(DashedRoute::class);

Router::scope('/', function (RouteBuilder $routes) {
    $routes->connect('/', ['controller' => 'Pages', 'action' => 'index']);
    $routes->connect('/login', ['controller' => 'Client', 'action' => 'authorize']);
    $routes->connect('/check-auth', ['controller' => 'Client', 'action' => 'checkAuth']);
});

Plugin::routes();