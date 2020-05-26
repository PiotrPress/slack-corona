<?php

namespace PiotrPress\Slack\Corona;

class Context implements BlockInterface {
    protected $elements = [];

    public function add( ElementInterface $element ) {
        $this->elements[] = $element;
    }

    public function render() {
        $elements = [];

        if ( $this->elements ) $elements = array_map( function( $element ) {
            return $element->render();
        }, $this->elements );

        return [
            'type' => 'context',
            'elements' => $elements
        ];
    }
}