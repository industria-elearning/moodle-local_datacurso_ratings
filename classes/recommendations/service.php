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

namespace local_datacurso_ratings\recommendations;

/**
 * Recommendation service for local_datacurso_ratings plugin.
 *
 * This service calculates course recommendations based on user preferences,
 * category engagement, and general activity satisfaction ratios.
 *
 * @package    local_datacurso_ratings
 * @copyright  2025 Industria Elearning <info@industriaelearning.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class service {

    /**
     * Get recommended courses for a specific user.
     *
     * @param int $userid The user ID.
     * @param int $limit  Maximum number of recommendations to return.
     * @return array The list of recommended courses.
     */
    public static function get_recommendations_for_user(int $userid, int $limit = 5): array {
        global $DB;

        // 1) User preferences by category.
        $sql = "
            SELECT r.categoryid,
                   SUM(CASE WHEN r.rating = 1 THEN 1 ELSE 0 END) AS likes,
                   SUM(CASE WHEN r.rating = 0 THEN 1 ELSE 0 END) AS dislikes
              FROM {local_datacurso_ratings} r
             WHERE r.userid = :userid
          GROUP BY r.categoryid
        ";
        $catstats = $DB->get_records_sql($sql, ['userid' => $userid]);

        $categorypref = [];
        foreach ($catstats as $row) {
            $likes = (int)($row->likes ?? 0);
            $dislikes = (int)($row->dislikes ?? 0);
            $total = $likes + $dislikes;
            $ratio = $total > 0 ? ($likes / $total) : null;
            $categorypref[(int)$row->categoryid] = $ratio;
        }

        // 2) Global rating statistics.
        $sqlglobal = "
            SELECT SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) AS likes,
                   SUM(CASE WHEN rating = 0 THEN 1 ELSE 0 END) AS dislikes
              FROM {local_datacurso_ratings}
        ";
        $g = $DB->get_record_sql($sqlglobal);
        $globallikes = (int)($g->likes ?? 0);
        $globaldislikes = (int)($g->dislikes ?? 0);
        $globalratio = ($globallikes + $globaldislikes) > 0
            ? ($globallikes / ($globallikes + $globaldislikes))
            : 0.5;

        // 3) Get enrolled courses to exclude them from recommendations.
        $enrolledids = [];
        if (function_exists('enrol_get_users_courses')) {
            $usercourses = enrol_get_users_courses($userid, true);
            $enrolledids = array_keys($usercourses);
        }

        // 4) Get visible candidate courses (exclude site course).
        $sqlcourses = "
            SELECT id, fullname, category
              FROM {course}
             WHERE visible = 1
               AND id <> :siteid
        ";
        $courses = $DB->get_records_sql($sqlcourses, ['siteid' => SITEID]);

        $candidates = [];

        foreach ($courses as $course) {
            $courseid = (int)$course->id;

            // Skip if user already enrolled.
            if (in_array($courseid, $enrolledids, true)) {
                continue;
            }

            // 5) Course satisfaction from ratings table.
            $sqlcourse = "
                SELECT SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) AS likes,
                       SUM(CASE WHEN rating = 0 THEN 1 ELSE 0 END) AS dislikes
                  FROM {local_datacurso_ratings}
                 WHERE courseid = :courseid
            ";
            $cr = $DB->get_record_sql($sqlcourse, ['courseid' => $courseid]);
            $courselikes = (int)($cr->likes ?? 0);
            $coursedislikes = (int)($cr->dislikes ?? 0);
            $coursetotal = $courselikes + $coursedislikes;
            $coursesatisfaction = $coursetotal > 0 ? round(($courselikes / $coursetotal) * 100, 2) : 0.0;

            // 6) User preference for course category (fall back to global).
            $catid = (int)$course->category;
            $usercatratio = $categorypref[$catid] ?? null;
            $catratioused = $usercatratio !== null ? $usercatratio : $globalratio;
            $catpercent = round($catratioused * 100, 2);

            // 7) Combined score (60% category preference + 40% course satisfaction).
            $score = (0.6 * $catpercent) + (0.4 * $coursesatisfaction);

            $candidates[] = [
                'courseid' => $courseid,
                'fullname' => $course->fullname,
                'categoryid' => $catid,
                'course_satisfaction' => $coursesatisfaction,
                'category_preference_pct' => $catpercent,
                'score' => round($score, 2),
            ];
        }

        // 8) Sort by score descending.
        usort($candidates, static function($a, $b) {
            return $b['score'] <=> $a['score'];
        });

        // 9) Filter by minimum category preference (>= 80%).
        $filtered = array_filter($candidates, static function($c) {
            return ($c['category_preference_pct'] ?? 0) >= 80.0;
        });

        // 10) Return top N (limit).
        return array_slice(array_values($filtered), 0, max(0, (int)$limit));
    }
}
