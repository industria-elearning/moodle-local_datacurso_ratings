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

namespace local_datacurso_ratings;

use core\hook\output\before_footer_html_generation;
use context_module;

/**
 * Hook callbacks for local_datacurso_ratings.
 *
 * @package    local_datacurso_ratings
 * @category   hook
 * @copyright  2025 Industria Elearning <info@industriaelearning.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class hook_callbacks {

    /**
     * Inject the rating UI just before the footer is generated.
     *
     * @param before_footer_html_generation $hook
     * @return void
     */
    public static function before_footer_html_generation(before_footer_html_generation $hook): void {
        global $PAGE, $OUTPUT;

        // No durante instalación inicial.
        if (during_initial_install()) {
            return;
        }

        // Ignorar ciertos layouts (mantenimiento, impresión, redirección, etc.).
        if (in_array($PAGE->pagelayout, ['maintenance', 'print', 'redirect', 'embedded'])) {
            return;
        }

        // Validar que estamos en un contexto de módulo.
        if ($PAGE->context->contextlevel != CONTEXT_MODULE) {
            return;
        }

        // Validar que hay un cmid, que el usuario está logueado y que no es invitado.
        if (!$PAGE->cm || !isloggedin() || isguestuser()) {
            return;
        }

        $cm = $PAGE->cm;

        // Check if the plugin is enabled in global settings.
        if (!get_config('local_datacurso_ratings', 'enabled')) {
            return; // If disabled, do not inject anything.
        }

        // Exportar datos para el template.
        $feedbackpage = new \local_datacurso_ratings\output\feedback_page();
        $feedbackdata = $feedbackpage->export_for_template($OUTPUT);

        // Renderizar el template Mustache con los botones.
        $html = $OUTPUT->render_from_template('local_datacurso_ratings/rate_button', [
            'cmid' => $cm->id,
            'likeItems' => [
                ['feedbacktext' => 'Muy útil'],
                ['feedbacktext' => 'Bien explicado'],
                ['feedbacktext' => 'Interesante'],
            ],
            'dislikeItems' => $feedbackdata['items'],
        ]);

        // Inyectar el HTML antes del footer.
        $hook->add_html($html);

        // Cargar JS (AMD/ES6) con el cmid.
        $PAGE->requires->js_call_amd('local_datacurso_ratings/rate', 'init', [$cm->id]);
    }
}
