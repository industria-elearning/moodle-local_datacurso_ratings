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
use context_module;

/**
 * Class get_ai_analysis_comments
 *
 * Devuelve un resumen generado por IA (mockeado por ahora) de los comentarios
 * realizados en una actividad.
 *
 * @package    local_datacurso_ratings
 * @copyright  2025 Industria Elearning
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class get_ai_analysis_comments extends external_api {

    /**
     * Parámetros de entrada del WS.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'cmid' => new external_value(PARAM_INT, 'ID del course module (actividad/recurso)', VALUE_REQUIRED),
        ]);
    }

    /**
     * Lógica del WS.
     *
     * @param int $cmid
     * @return array
     */
    public static function execute($cmid) {
        global $USER;

        // Validar contexto del módulo.
        $context = context_module::instance($cmid);
        self::validate_context($context);

        // Validar parámetros.
        $params = self::validate_parameters(self::execute_parameters(), ['cmid' => $cmid]);

        // Aquí iría la lógica de análisis con IA, pero devolvemos un mock.
        $mockresponse = [
            'cmid' => $params['cmid'],
            'ai_analysis_comment' => 'La mayoría de los estudiantes valoran positivamente la actividad,'
                . ' destacando que el contenido fue claro y útil para reforzar el tema.'
                . ' Sin embargo, algunos mencionan que las instrucciones podrían ser más detalladas.',
        ];
        return $mockresponse;
    }

    /**
     * Estructura de retorno del WS.
     *
     * @return external_single_structure
     */
    public static function execute_returns() {
        return new external_single_structure([
            'cmid' => new external_value(PARAM_INT, 'ID del course module'),
            'ai_analysis_comment' => new external_value(PARAM_RAW, 'Resumen analítico generado por IA'),
        ]);
    }
}
