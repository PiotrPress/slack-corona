<?php

namespace PiotrPress\Slack\Corona;

class File {
    protected $path = null;

    public function __construct( string $path ) {
        $this->path = $path;
    }

    public function get() {
        if ( $content = file_get_contents( $this->path ) ) return json_decode( $content, true );
        return false;
    }

    public function put( array $content ) {
        return (bool)file_put_contents( $this->path, json_encode( $content ) );
    }
}