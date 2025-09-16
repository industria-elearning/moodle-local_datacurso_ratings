<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

namespace local_datacurso_ratings\external;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/externallib.php');

use external_function_parameters;
use external_value;
use external_single_structure;
use external_multiple_structure;
use external_api;
use context_course;

class get_ratings_report_course extends external_api {

    /**
     * Parámetros de entrada de la función.
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'courseid' => new external_value(PARAM_INT, 'ID del curso')
        ]);
    }

    /**
     * Lógica principal: consulta y devuelve la información.
     */
    public static function execute($courseid) {
        global $DB;

        $params = self::validate_parameters(self::execute_parameters(), [
            'courseid' => $courseid
        ]);

        $context = context_course::instance($params['courseid']);
        self::validate_context($context);

        // SQL para traer solo las actividades de ese curso.
        $sql = "
            SELECT c.fullname AS curso,
                   inst.name AS actividad,
                   m.name AS tipo_modulo,
                   cm.id AS cmid,
                   SUM(CASE WHEN r.rating = 1 THEN 1 ELSE 0 END) AS likes,
                   SUM(CASE WHEN r.rating = 0 THEN 1 ELSE 0 END) AS dislikes,
                   ROUND(SUM(CASE WHEN r.rating = 1 THEN 1 ELSE 0 END) * 100.0 / COUNT(r.id), 1) AS porcentaje_aprobacion,
                   GROUP_CONCAT(DISTINCT r.feedback SEPARATOR ' / ') AS comentarios
            FROM {local_datacurso_ratings} r
            JOIN {course_modules} cm ON cm.id = r.cmid
            JOIN {course} c ON c.id = cm.course
            JOIN {modules} m ON m.id = cm.module
            JOIN (
                SELECT id, name, 'assign' AS modulename FROM {assign}
                UNION ALL
                SELECT id, name, 'quiz' AS modulename FROM {quiz}
                UNION ALL
                SELECT id, name, 'page' AS modulename FROM {page}
                UNION ALL
                SELECT id, name, 'forum' AS modulename FROM {forum}
                UNION ALL
                SELECT id, name, 'book' AS modulename FROM {book}
                UNION ALL
                SELECT id, name, 'url' AS modulename FROM {url}
                UNION ALL
                SELECT id, name, 'choice' AS modulename FROM {choice}
                UNION ALL
                SELECT id, name, 'glossary' AS modulename FROM {glossary}
                UNION ALL
                SELECT id, name, 'lesson' AS modulename FROM {lesson}
                UNION ALL
                SELECT id, name, 'scorm' AS modulename FROM {scorm}
                UNION ALL
                SELECT id, name, 'survey' AS modulename FROM {survey}
                UNION ALL
                SELECT id, name, 'wiki' AS modulename FROM {wiki}
                UNION ALL
                SELECT id, name, 'workshop' AS modulename FROM {workshop}
                UNION ALL
                SELECT id, name, 'label' AS modulename FROM {label}
            ) inst ON inst.id = cm.instance AND inst.modulename = m.name
            WHERE c.id = :courseid
            GROUP BY c.fullname, inst.name, m.name, cm.id
            ORDER BY inst.name ASC
        ";

        $records = $DB->get_records_sql($sql, ['courseid' => $params['courseid']]);

        $result = [];
        foreach ($records as $r) {
            $result[] = [
                'curso' => $r->curso,
                'actividad' => $r->actividad,
                'modulo' => $r->tipo_modulo,
                'cmid' => (int)$r->cmid,
                'likes' => (int)$r->likes,
                'dislikes' => (int)$r->dislikes,
                'porcentaje' => (float)$r->porcentaje_aprobacion,
                'comentarios' => $r->comentarios ?? '',
            ];
        }

        return $result;
    }

    /**
     * Estructura de salida.
     */
    public static function execute_returns() {
        return new external_multiple_structure(
            new external_single_structure([
                'curso' => new external_value(PARAM_TEXT, 'Nombre del curso'),
                'actividad' => new external_value(PARAM_TEXT, 'Nombre de la actividad'),
                'modulo' => new external_value(PARAM_TEXT, 'Tipo de módulo'),
                'cmid' => new external_value(PARAM_INT, 'Course module ID'),
                'likes' => new external_value(PARAM_INT, 'Número de me gusta'),
                'dislikes' => new external_value(PARAM_INT, 'Número de no me gusta'),
                'porcentaje' => new external_value(PARAM_FLOAT, '% de aprobación'),
                'comentarios' => new external_value(PARAM_RAW, 'Comentarios concatenados'),
            ])
        );
    }
}