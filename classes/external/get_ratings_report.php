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
 * Webservice para traer el reporte general de actividades con ratings
 */
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

        // Validar contexto de sistema (porque es reporte global).
        $context = context_system::instance();
        self::validate_context($context);

        // 1. Traer los ratings agrupados por cmid.
        $sql = "
            SELECT r.cmid,
                   cm.course,
                   SUM(CASE WHEN r.rating = 1 THEN 1 ELSE 0 END) AS likes,
                   SUM(CASE WHEN r.rating = 0 THEN 1 ELSE 0 END) AS dislikes,
                   ROUND(SUM(CASE WHEN r.rating = 1 THEN 1 ELSE 0 END) * 100.0 / COUNT(r.id), 1) AS porcentaje_aprobacion,
                   GROUP_CONCAT(DISTINCT r.feedback SEPARATOR ' / ') AS comentarios
            FROM {local_datacurso_ratings} r
            JOIN {course_modules} cm ON cm.id = r.cmid
            GROUP BY r.cmid, cm.course
            ORDER BY cm.course ASC
        ";
        $records = $DB->get_records_sql($sql);

        $result = [];

        // 2. Agrupar por curso usando get_fast_modinfo.
        $coursecache = []; // Cache local para no recalcular modinfo varias veces.

        foreach ($records as $r) {
            $courseid = $r->course;

            if (!isset($coursecache[$courseid])) {
                $coursecache[$courseid] = get_fast_modinfo($courseid);
            }
            $modinfo = $coursecache[$courseid];

            // Obtener info del módulo.
            if (!$modinfo->cms || !isset($modinfo->cms[$r->cmid])) {
                continue; // Si no existe el cmid, saltar.
            }

            $cm = $modinfo->get_cm($r->cmid);
            if (!$cm->uservisible) {
                continue; // Saltar si el usuario no tiene permiso de verlo.
            }

            $commentsArray = [];
            if (!empty($r->comentarios)) {
                $commentsArray = explode(' / ', $r->comentarios);
            }

            $result[] = [
                'course' => $modinfo->get_course()->fullname,
                'activity' => $cm->name,
                'modname' => $cm->modname,
                'cmid' => $cm->id,
                'url' => $cm->url ? $cm->url->out(false) : '',
                'likes' => (int)$r->likes,
                'dislikes' => (int)$r->dislikes,
                'approvalpercent' => (float)$r->porcentaje_aprobacion,
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
                'modname' => new external_value(PARAM_TEXT, 'Tipo de módulo'),
                'cmid' => new external_value(PARAM_INT, 'Course module ID'),
                'url' => new external_value(PARAM_URL, 'URL de la actividad', VALUE_OPTIONAL),
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