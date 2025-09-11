<?php
defined('MOODLE_INTERNAL') || die();

$functions = [
    'local_datacurso_ratings_save_rating' => [
        'classname'   => 'local_datacurso_ratings\external\save_rating',
        'methodname'  => 'execute',
        'classpath'   => '',
        'description' => 'Save user rating (like/dislike) and optional feedback for a course module.',
        'type'        => 'write',
        'ajax'        => true,
        'capabilities'=> 'local/datacurso_ratings:rate',
    ],
];
