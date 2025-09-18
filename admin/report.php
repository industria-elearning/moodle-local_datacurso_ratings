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

/**
 * General ratings report page
 *
 * @package    local_datacurso_ratings
 * @copyright  2025 Industria Elearning <info@industriaelearning.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../../config.php');

require_login();
$context = context_system::instance();
require_capability('moodle/site:config', $context);

$PAGE->set_url(new moodle_url('/local/datacurso_ratings/admin/report.php'));
$PAGE->set_context($context);
$PAGE->set_pagelayout('report');
$PAGE->set_title(get_string('ratingsreport', 'local_datacurso_ratings'));
$PAGE->set_heading(get_string('ratingsreport', 'local_datacurso_ratings'));

// IMPORTANT: Header before content.
echo $OUTPUT->header();

// Container for the report (will be populated by JavaScript).
echo '<div id="general-ratings-report-container"></div>';

$PAGE->requires->js_call_amd('local_datacurso_ratings/ratings_report', 'init', []);
$PAGE->requires->js_call_amd('local_datacurso_ratings/comments_modal', 'init');

echo $OUTPUT->footer();
