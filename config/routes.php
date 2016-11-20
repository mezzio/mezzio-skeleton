<?php

/**
 * Setup routes with a single request method:
 *
 * $app->get('path', MiddlewareAction::class, 'route.name');
 * $app->post('path', MiddlewareAction::class, 'route.name');
 * $app->put('path', MiddlewareAction::class, 'route.name');
 * $app->patch('path', MiddlewareAction::class, 'route.name');
 * $app->delete('path', MiddlewareAction::class, 'route.name');
 *
 * Or with multiple request methods:
 *
 * $app->route('path', MiddlewareAction::class, ['GET', 'POST', ...], 'route.name');
 */

$app->get('/', App\Action\HomePageAction::class, 'home');
$app->get('/api/ping', App\Action\PingAction::class, 'api.ping');
