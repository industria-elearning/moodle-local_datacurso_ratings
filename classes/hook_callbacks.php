<?php

namespace local_datacurso_ratings;

defined('MOODLE_INTERNAL') || die();

use core\hook\output\before_footer_html_generation;
use context_module;

/**
 * Hook callbacks for local_datacurso_ratings.
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