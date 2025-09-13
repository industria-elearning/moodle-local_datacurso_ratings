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
    ],
     'local_datacurso_ratings_add_feedback' => [
        'classname'   => 'local_datacurso_ratings\external\feedback_service',
        'methodname'  => 'add_feedback',
        'classpath'   => '',
        'description' => 'Agrega un feedback.',
        'type'        => 'write',
        'ajax'        => true,
        'capabilities'=> 'moodle/site:config'
    ],
    'local_datacurso_ratings_delete_feedback' => [
        'classname'   => 'local_datacurso_ratings\external\feedback_service',
        'methodname'  => 'delete_feedback',
        'classpath'   => '',
        'description' => 'Elimina un feedback.',
        'type'        => 'write',
        'ajax'        => true,
        'capabilities'=> 'moodle/site:config'
    ],
        'local_datacurso_ratings_get_ratings_report' => [
        'classname'   => 'local_datacurso_ratings\external\get_ratings_report',
        'methodname'  => 'execute',
        'description' => 'Obtiene un reporte resumido de calificaciones y feedbacks por curso/actividad',
        'type'        => 'read',
        'ajax'        => true,
    ],
];
