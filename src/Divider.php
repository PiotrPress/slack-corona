<?php

namespace PiotrPress\Slack\Corona;

class Divider implements BlockInterface {
    public function render() {
        return [
            'type' => 'divider'
        ];
    }
}