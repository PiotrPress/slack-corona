<?php

/*
    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>
 */

namespace PiotrPress\Slack\Corona;

use Exception;

use Monolog\Logger;
use Monolog\ErrorHandler;
use Monolog\Handler\StreamHandler;

use Symfony\Component\HttpFoundation\Response;
use Twig\Loader\FilesystemLoader;
use Twig\Environment;

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\HttpFoundation\Request;

require_once __DIR__ . '/vendor/autoload.php';

$logger = new Logger( 'Corona', [ new StreamHandler( __DIR__ . '/error.log', Logger::DEBUG ) ] );
$handler = new ErrorHandler( $logger );
$handler->registerErrorHandler( [], false );
$handler->registerExceptionHandler();
$handler->registerFatalHandler();

$request = Request::createFromGlobals();

try {
    $processor = new Processor();
    $config = $processor->processConfiguration( new Config(), Yaml::parse( file_get_contents( __DIR__ . '/config.yaml' ) ) );

    $template = new Environment( new FilesystemLoader( [ __DIR__ . '/tmp' ] ) );

    $command = new Command( $config, $template, $request, $logger );
    $response = $command->response();
} catch( Exception $exception ) {
    $logger->error( $exception->getMessage(), $exception->getTrace() );
    $response = new Response( '', Response::HTTP_INTERNAL_SERVER_ERROR );
}

$response->send();