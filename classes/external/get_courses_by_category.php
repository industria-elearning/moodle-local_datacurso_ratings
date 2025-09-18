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

require_once("$CFG->libdir/externallib.php");

use external_function_parameters;
use external_value;
use external_single_structure;
use external_multiple_structure;
use external_api;
use context_system;

/**
 * Web service para obtener cursos por categoría.
 *
 * @package    local_datacurso_ratings
 * @copyright  2025 Industria Elearning
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class get_courses_by_category extends external_api {

    /**
     * Parámetros de entrada de la función.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'categoryid' => new external_value(PARAM_INT, 'Category ID to filter courses'),
        ]);
    }

    /**
     * Lógica principal: obtiene cursos de una categoría específica.
     *
     * @param int $categoryid
     * @return array
     */
    public static function execute($categoryid) {
        global $DB;

        $params = self::validate_parameters(self::execute_parameters(), [
            'categoryid' => $categoryid,
        ]);

        // Validar contexto de sistema.
        $context = context_system::instance();
        self::validate_context($context);

        // Verificar que la categoría existe.
        $category = $DB->get_record('course_categories', ['id' => $params['categoryid']], '*', MUST_EXIST);

        // Obtener cursos de la categoría.
        $courses = $DB->get_records('course', [
            'category' => $params['categoryid'],
            'visible' => 1, // Solo cursos visibles.
        ], 'fullname ASC', 'id, fullname, shortname, category');

        // Procesar resultados.
        $result = [];
        foreach ($courses as $course) {
            // Saltar el curso sitio (ID = 1).
            if ($course->id == SITEID) {
                continue;
            }

            // Verificar si el usuario tiene acceso al curso.
            $coursecontext = \context_course::instance($course->id);
            if (
                has_capability('moodle/course:view', $coursecontext) ||
                has_capability('local/datacurso_ratings:viewreports', $coursecontext)
            ) {
                $result[] = [
                    'id' => $course->id,
                    'fullname' => $course->fullname,
                    'shortname' => $course->shortname,
                    'categoryid' => $course->category,
                ];
            }
        }

        return $result;
    }

    /**
     * Estructura de salida.
     *
     * @return external_multiple_structure
     */
    public static function execute_returns() {
        return new external_multiple_structure(
            new external_single_structure([
                'id' => new external_value(PARAM_INT, 'Course ID'),
                'fullname' => new external_value(PARAM_TEXT, 'Course full name'),
                'shortname' => new external_value(PARAM_TEXT, 'Course short name'),
                'categoryid' => new external_value(PARAM_INT, 'Category ID'),
            ])
        );
    }
}
