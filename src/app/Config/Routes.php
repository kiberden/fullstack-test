<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index', ['filter' => 'identify']);

$routes->get('auth/me', 'AuthController::me');
$routes->post('auth/identify', 'AuthController::identify');

$routes->post('comments', 'CommentController::create');
$routes->delete('comments/(:num)', 'CommentController::delete/$1');
