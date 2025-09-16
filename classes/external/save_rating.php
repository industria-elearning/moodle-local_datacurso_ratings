<?php
namespace local_datacurso_ratings\external;

use external_api;
use external_function_parameters;
use external_value;
use external_single_structure;
use context_module;
use invalid_parameter_exception;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/externallib.php');

/**
 * External function to save a rating for a course module.
 */
class save_rating extends external_api {

    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'cmid' => new external_value(PARAM_INT, 'Course module id'),
            'rating' => new external_value(PARAM_INT, 'Rating: 1 = like, 0 = dislike'),
            'feedback' => new external_value(PARAM_RAW, 'Optional feedback for negative rating', VALUE_DEFAULT, ''),
        ]);
    }

    public static function execute(int $cmid, int $rating, string $feedback = ''): array {
        global $DB, $USER;

        // Validate params.
        $params = self::validate_parameters(self::execute_parameters(), [
            'cmid' => $cmid,
            'rating' => $rating,
            'feedback' => $feedback,
        ]);

        // Ensure CM exists and user can access.
        $cm = get_coursemodule_from_id(null, $params['cmid'], 0, false, MUST_EXIST);
        $context = context_module::instance($cm->id);
        self::validate_context($context);

        $r = (int)$params['rating'];
        if ($r !== 0 && $r !== 1) {
            throw new invalid_parameter_exception('Invalid rating value. Must be 0 or 1.');
        }

        $now = time();
        $record = $DB->get_record('local_datacurso_ratings',
            ['cmid' => $cm->id, 'userid' => $USER->id]);

        $data = (object)[
            'cmid' => $cm->id,
            'userid' => $USER->id,
            'rating' => $r,
            'feedback' => (string)$params['feedback'],
            'timemodified' => $now,
        ];

        if ($record) {
            $data->id = $record->id;
            $DB->update_record('local_datacurso_ratings', $data);
        } else {
            $data->timecreated = $now;
            $DB->insert_record('local_datacurso_ratings', $data);
        }

        return ['status' => true];
    }

    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'status' => new external_value(PARAM_BOOL, 'Operation status'),
        ]);
    }
}
