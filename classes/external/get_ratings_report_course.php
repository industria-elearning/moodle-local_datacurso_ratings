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
use core\context;
use moodle_exception;

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
        global $DB, $USER;

        $params = self::validate_parameters(self::execute_parameters(), [
            'courseid' => $courseid
        ]);

        $context = context_course::instance($params['courseid']);
        self::validate_context($context);

        // Inicializar modinfo del curso.
        $modinfo = get_fast_modinfo($params['courseid']);
        $cms = $modinfo->get_cms();

        $result = [];

        foreach ($cms as $cm) {
            if (!$cm->uservisible) {
                continue; // Saltar si el usuario no puede ver el módulo.
            }

            // Traer las valoraciones de esta actividad.
            $sql = "
                SELECT 
                    SUM(CASE WHEN r.rating = 1 THEN 1 ELSE 0 END) AS likes,
                    SUM(CASE WHEN r.rating = 0 THEN 1 ELSE 0 END) AS dislikes,
                    ROUND(SUM(CASE WHEN r.rating = 1 THEN 1 ELSE 0 END) * 100.0 / NULLIF(COUNT(r.id),0), 1) AS porcentaje_aprobacion,
                    GROUP_CONCAT(DISTINCT r.feedback SEPARATOR ' / ') AS comentarios
                FROM {local_datacurso_ratings} r
                WHERE r.cmid = :cmid
            ";

            $record = $DB->get_record_sql($sql, ['cmid' => $cm->id]);

            // Si no hay votos aún, inicializamos en 0.
            $likes = (int)($record->likes ?? 0);
            $dislikes = (int)($record->dislikes ?? 0);
            $porcentaje = (float)($record->porcentaje_aprobacion ?? 0);
            $comentarios = $record->comentarios ?? '';

            $result[] = [
                'curso' => $modinfo->get_course()->fullname,
                'actividad' => $cm->name,
                'modulo' => $cm->modname,
                'cmid' => (int)$cm->id,
                'likes' => $likes,
                'dislikes' => $dislikes,
                'porcentaje' => $porcentaje,
                'comentarios' => $comentarios,
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
