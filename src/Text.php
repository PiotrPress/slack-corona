<?php

namespace PiotrPress\Slack\Corona;

class Text implements BlockInterface, ElementInterface {
    protected $text = '';

    public function __construct( string $text ) {
        $this->text = $text;
    }

    public function render() {
        return [
            'type' => 'mrkdwn',
            'text' => $this->text
        ];
    }
}