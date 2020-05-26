<?php

namespace PiotrPress\Slack\Corona;

class Message {
    protected $blocks = [];

    public function add( BlockInterface $block ) {
        $this->blocks[] = $block;
    }

    public function render() {
        $message = [ 'response_type' => 'in_channel' ];

        if ( $this->blocks ) $message['blocks'] = array_map( function( $block ) {
            return $block->render();
        }, $this->blocks );

        return $message;
    }
}