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

/**
 * Class get_ratings_report
 *
 * @package    local_datacurso_ratings
 * @copyright  2025 Industria Elearning <info@industriaelearning.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/externallib.php");

use external_function_parameters;
use external_value;
use external_single_structure;
use external_multiple_structure;
use external_api;
use context_system;
use moodle_exception;

class get_ratings_report extends external_api {

    /**
     * Parámetros de entrada de la función.
     */
    public static function execute_parameters() {
        return new external_function_parameters([]);
    }

    /**
     * Lógica principal: consulta y devuelve la información.
     */
    public static function execute() {
        global $DB;

        $params = self::validate_parameters(self::execute_parameters(), []);

$sql = "
    SELECT c.fullname AS curso,
           inst.name AS actividad,
           m.name AS tipo_modulo,
           SUM(CASE WHEN r.rating = 1 THEN 1 ELSE 0 END) AS likes,
           SUM(CASE WHEN r.rating = 0 THEN 1 ELSE 0 END) AS dislikes,
           ROUND(SUM(CASE WHEN r.rating = 1 THEN 1 ELSE 0 END) * 100.0 / COUNT(r.id), 1) AS porcentaje_aprobacion,
           GROUP_CONCAT(DISTINCT r.feedback SEPARATOR ' / ') AS comentarios
    FROM {local_datacurso_ratings} r
    JOIN {course_modules} cm ON cm.id = r.cmid
    JOIN {course} c ON c.id = cm.course
    JOIN {modules} m ON m.id = cm.module
    LEFT JOIN (
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
    GROUP BY c.fullname, inst.name, m.name
    ORDER BY c.fullname ASC
";
        
        $records = $DB->get_records_sql($sql);

        $result = [];
        foreach ($records as $r) {
            $commentsArray = [];
            if (!empty($r->comentarios)) {
                $commentsArray = explode(' / ', $r->comentarios);
            }
        
            $result[] = [
                'course' => $r->curso,
                'activity' => $r->actividad,
                'likes' => (int) $r->likes,
                'dislikes' => (int) $r->dislikes,
                'approvalpercent' => (float) $r->porcentaje_aprobacion,
                'comments' => $commentsArray,
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
                'course' => new external_value(PARAM_TEXT, 'Nombre del curso'),
                'activity' => new external_value(PARAM_TEXT, 'Nombre de la actividad'),
                'likes' => new external_value(PARAM_INT, 'Número de me gusta'),
                'dislikes' => new external_value(PARAM_INT, 'Número de no me gusta'),
                'approvalpercent' => new external_value(PARAM_FLOAT, '% de aprobación'),
                'comments' => new external_multiple_structure(
                    new external_value(PARAM_RAW, 'Comentario individual'),
                    'Lista de comentarios', VALUE_OPTIONAL
                ),
            ])
        );
    }
}
