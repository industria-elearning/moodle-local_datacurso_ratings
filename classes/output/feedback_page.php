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
 * Class feedback_page
 *
 * @package    local_datacurso_ratings
 * @copyright  2025 Industria Elearning <info@industriaelearning.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_datacurso_ratings\output;

use renderable;
use templatable;
use renderer_base;
use stdClass;
class feedback_page implements renderable, templatable {
    private $items;
    public function __construct() {
        global $DB;
        $records = $DB->get_records('local_datacurso_ratings_feedback', null, 'id DESC');
        $this->items = array_values($records);
    }
    public function export_for_template(renderer_base $output) {
        $items = [];
        foreach ($this->items as $rec) {
            $items[] = [
                'id' => $rec->id,
                'feedbacktext' => $rec->feedbacktext,
            ];
        }

        return [
            'items' => $items,
            'sesskey' => sesskey()
        ];
    }
}