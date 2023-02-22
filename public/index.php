<?php

use Slim\Factory\AppFactory;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

require __DIR__ . '/../vendor/autoload.php';
require '../src/db/db.php';
require '../src/utils/make_response.php';
require '../src/utils/authentication.php';

$app = AppFactory::create();
$app->addErrorMiddleware(true, true, true);

$app->get('/', function (Request $request, Response $response) {
    return make_response_message(200,"Server running",$response);
});

#Users
require "../src/routes/users.php";
#Chats
require "../src/routes/chats.php";

$app->run();