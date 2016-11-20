<?php

/**
 * Setup middleware pipeline:
 */

// The error handler should be the first (most outer) middleware to catch all Exceptions.
$app->pipe(Zend\Stratigility\Middleware\ErrorHandler::class);
$app->pipe(Zend\Expressive\Helper\ServerUrlMiddleware::class);

// Pipe more middleware here that you want to execute on every request:
// - bootstrapping
// - pre-conditions
// - modifications to outgoing responses

// Register the routing middleware in the middleware pipeline
$app->pipeRoutingMiddleware();
$app->pipe(Zend\Expressive\Helper\UrlHelperMiddleware::class);

// Add more middleware here that needs to introspect the routing results; this might include:
// - route-based authentication
// - route-based validation
// - etc.
//
// Piped Middleware may be either callables or service names. Middleware may also be passed as an array; each item in
// the array must resolve to middleware eventually (i.e., callable or service name).
//
// Middleware can be attached to specific paths, allowing you to mix and match applications under a common domain.
// The handlers in each middleware attached this way will see a URI with that PATH SEGMENT STRIPPED !!!
// - $app->pipe('/api', $apiMiddleware);
// - $app->pipe('/docs', $apiDocMiddleware);
// - $app->pipe('/files', $filesMiddleware);

// Register the dispatch middleware in the middleware pipeline
$app->pipeDispatchMiddleware();

// At this point, if no Response is return by any middleware, the NotFoundHandler
// kicks in
$app->pipe(Zend\Expressive\Middleware\NotFoundHandler::class);
