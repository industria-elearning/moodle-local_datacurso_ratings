<?php

defined('MOODLE_INTERNAL') || die();

$callbacks = [
    [
        'hook' => \core\hook\output\after_http_headers::class,
        'callback' => [\local_datacurso_ratings\hook_callbacks::class, 'after_http_headers'],
    ],
];
