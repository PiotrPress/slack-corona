<?php

namespace PiotrPress\Slack\Corona;

class Section implements BlockInterface {
    protected $text = null;

    public function __construct( Text $text ) {
        $this->text = $text;
    }

    public function render() {
        return [
            'type' => 'section',
            'text' => $this->text->render()
        ];
    }
}