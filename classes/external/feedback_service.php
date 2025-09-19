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

use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_value;
use core_external\external_single_structure;
use context_system;

/**
 * Class feedback_service
 *
 * Servicios externos para gestionar feedback en el plugin.
 *
 * @package    local_datacurso_ratings
 * @copyright  2025 Industria Elearning <info@industriaelearning.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class feedback_service extends external_api {

    /**
     * Define los parámetros de entrada para add_feedback.
     *
     * @return external_function_parameters
     */
    public static function add_feedback_parameters() {
        return new external_function_parameters([
            'feedbacktext' => new external_value(PARAM_TEXT, 'Texto del feedback', VALUE_REQUIRED),
        ]);
    }

    /**
     * Agregar un feedback.
     *
     * @param string $feedbacktext Texto del feedback
     * @return array Array con:
     *  - int id: ID del feedback creado
     *  - string message: Mensaje de confirmación
     */
    public static function add_feedback($feedbacktext) {
        global $DB;

        self::validate_context(context_system::instance());
        $params = self::validate_parameters(self::add_feedback_parameters(), ['feedbacktext' => $feedbacktext]);

        $record = (object)[
            'feedbacktext' => $params['feedbacktext'],
            'timecreated' => time(),
            'timemodified' => time(),
        ];
        $id = $DB->insert_record('local_datacurso_ratings_feedback', $record);

        return ['id' => $id, 'message' => get_string('feedbacksaved', 'local_datacurso_ratings')];
    }

    /**
     * Define la estructura de retorno para add_feedback.
     *
     * @return external_single_structure
     */
    public static function add_feedback_returns() {
        return new external_single_structure([
            'id' => new external_value(PARAM_INT, 'ID del feedback creado'),
            'message' => new external_value(PARAM_TEXT, 'Mensaje de confirmación'),
        ]);
    }

    /**
     * Define los parámetros de entrada para delete_feedback.
     *
     * @return external_function_parameters
     */
    public static function delete_feedback_parameters() {
        return new external_function_parameters([
            'id' => new external_value(PARAM_INT, 'ID del feedback a eliminar', VALUE_REQUIRED),
        ]);
    }

    /**
     * Eliminar un feedback.
     *
     * @param int $id ID del feedback a eliminar
     * @return array Array con:
     *  - string message: Mensaje de confirmación
     */
    public static function delete_feedback($id) {
        global $DB;

        self::validate_context(context_system::instance());
        $params = self::validate_parameters(self::delete_feedback_parameters(), ['id' => $id]);

        $DB->delete_records('local_datacurso_ratings_feedback', ['id' => $params['id']]);

        return ['message' => get_string('feedbackdeleted', 'local_datacurso_ratings')];
    }

    /**
     * Define la estructura de retorno para delete_feedback.
     *
     * @return external_single_structure
     */
    public static function delete_feedback_returns() {
        return new external_single_structure([
            'message' => new external_value(PARAM_TEXT, 'Mensaje de confirmación'),
        ]);
    }
}
