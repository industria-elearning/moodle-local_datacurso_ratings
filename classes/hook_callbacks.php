<?php

namespace local_datacurso_ratings;

defined('MOODLE_INTERNAL') || die();

use core\hook\output\after_http_headers;
use context_module;

/**
 * Hook callbacks for local_datacurso_ratings.
 */
class hook_callbacks {
    /**
     * Bootstrap the rating UI after HTTP headers.
     *
     * @param after_http_headers $hook
     * @return void
     */
    public static function after_http_headers(after_http_headers $hook): void {
        global $PAGE, $OUTPUT, $USER;
        

        global $PAGE;
        if (during_initial_install()) {
            return;
        }
        if (in_array($PAGE->pagelayout, ['maintenance', 'print', 'redirect', 'embedded'])) {
            // Do not try to show assist UI inside iframe, in maintenance mode,
            // when printing, or during redirects.
            return;
        }
        // Check we are in the right context, exit if not activity.
        if ($PAGE->context->contextlevel != CONTEXT_MODULE) {
            return;
        }

        // Only on module pages, for logged-in (non-guest) users.
        if (!$PAGE->cm || !isloggedin() || isguestuser()) {
            return;
        }

        $cm = $PAGE->cm;

        // Render the button + container via mustache.
        $html = $OUTPUT->render_from_template('local_datacurso_ratings/rate_button', [
            'cmid' => $cm->id,
        ]);
        $hook->add_html($html);

        // Require JS (AMD/ES6) for this cmid.
        $PAGE->requires->js_call_amd('local_datacurso_ratings/rate', 'init', [$cm->id]);
    }
}
