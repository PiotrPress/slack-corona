<?php

namespace PiotrPress\Slack\Corona;

use Monolog\Logger;

use Twig\Environment;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

class Command {
    protected $config = [];
    protected $template = null;
    protected $request = null;
    protected $logger = null;

	public function __construct( array $config, Environment $template, Request $request, Logger $logger ) {
	    $this->config = $config;
	    $this->template = $template;
        $this->request = $request;
        $this->logger = $logger;
	}

	public function response() {
	    if ( strtoupper( $this->request->getMethod() ) !== 'POST' ) {
            $this->logger->error( 'Invalid method', debug_backtrace() );
            return new Response( 'Invalid method', Response::HTTP_BAD_REQUEST );
        }

        if ( strtolower( $this->request->headers->get('content-type') ) !== 'application/x-www-form-urlencoded' ) {
            $this->logger->error( 'Invalid content type', debug_backtrace() );
            return new Response( 'Invalid content type', Response::HTTP_BAD_REQUEST );
        }

        if ( $this->request->request->get( 'token' ) !== $this->config['slack']['token'] ) {
            $this->logger->error( 'Invalid token', debug_backtrace() );
            return new Response( 'Invalid token', Response::HTTP_BAD_REQUEST );
        }

        if (  $this->request->request->getInt( 'ssl_check' ) === 1 ) {
            $this->logger->info( 'SSL check', debug_backtrace() );
            return new Response();
        }

        $command = '/' . $this->config['slack']['command'];
        if ( $this->request->request->get( 'command' ) !== $command ) {
            $this->logger->error( 'Invalid command', debug_backtrace() );
            return new Response( 'Invalid command', Response::HTTP_BAD_REQUEST );
        }

        $file = new File( dirname( __DIR__ ) . '/summary.json' );
        $fileSummary = $file->get();

        $api = new Api();
        $apiSummary = $api->summary();

        if ( $apiSummary and $apiSummary !== $fileSummary ) $file->put( $summary = $apiSummary );
        else $summary = $fileSummary;

        $countries = [
            'global' => array_merge( [
                'Country' => 'Global',
                'CountryCode' => 'global',
                'Slug' => 'global',
                'Date' => date( "Y-m-d H:i:s", time() )
            ], $summary['Global'] )
        ];
        foreach ( $summary['Countries'] as $country ) {
            $countries[$country['Slug']] = $country;
        }

        $text = trim( strtolower( $this->request->request->get( 'text' ) ) ) ?: 'global';
        if ( 'global' === $text ) $country = $countries['global'];
        elseif ( in_array( $text, array_keys( $countries ) ) ) $country = $countries[$text];
        else {
            $message = new Message();
            $message->add( new Section( new Text( $this->template->render( 'Help.twig', [
                'countries' => implode( ', ', array_keys( $countries ) ) ] ) ) ) );
            return new JsonResponse( $message->render() );
        }

        $message = new Message();
        $context = new Context();
        $context->add( new Text( $this->template->render( 'Command.twig', [
            'command' => $command,
            'text' => $text
        ] ) ) );
        $message->add( $context );
        $message->add( new Section( new Text( $this->template->render( 'Header.twig', [
            'code' => strtolower( $country['CountryCode'] ),
            'name' => $country['Country'],
        ] ) ) ) );
        foreach ( [ 'Confirmed' => 'warning', 'Recovered' => 'recycle', 'Deaths' => 'no_entry' ] as $section => $icon ) {
            $message->add( new Divider() );
            $message->add( new Section( new Text( $this->template->render( 'Section.twig', [
                'icon' => $icon,
                'section' => $section,
                'total' => $country["Total$section"],
                'new' => $country["New$section"]
            ] ) ) ) );
        }
        $message->add( new Divider() );
        $context = new Context();
        $context->add( new Text( $this->template->render( 'Footer.twig', [
            'date' => str_replace( [ 'T', 'Z' ], ' ', $country['Date'] )
        ] ) ) );
        $message->add( $context );

        return new JsonResponse( $message->render() );
    }
}
