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

        // 1. Preferencias del usuario por categoría (solo una query).
        $sqlusercats = "
            SELECT r.categoryid,
                   SUM(CASE WHEN r.rating = 1 THEN 1 ELSE 0 END) AS likes,
                   SUM(CASE WHEN r.rating = 0 THEN 1 ELSE 0 END) AS dislikes
              FROM {local_datacurso_ratings} r
             WHERE r.userid = :userid
          GROUP BY r.categoryid
        ";
        $catprefs = $DB->get_records_sql($sqlusercats, ['userid' => $userid]);

        $categorypref = [];
        foreach ($catprefs as $c) {
            $likes = (int)($c->likes ?? 0);
            $dislikes = (int)($c->dislikes ?? 0);
            $total = $likes + $dislikes;
            $categorypref[$c->categoryid] = $total > 0 ? ($likes / $total) : null;
        }

        // 2. Ratio global.
        $global = $DB->get_record_sql("
            SELECT
                SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) AS likes,
                SUM(CASE WHEN rating = 0 THEN 1 ELSE 0 END) AS dislikes
            FROM {local_datacurso_ratings}
        ");
        $globallikes = (int)($global->likes ?? 0);
        $globaldislikes = (int)($global->dislikes ?? 0);
        $globalratio = ($globallikes + $globaldislikes) > 0
            ? ($globallikes / ($globallikes + $globaldislikes))
            : 0.5;

        // 3. Cursos en los que ya está inscrito (para excluir).
        $enrolledids = [];
        if (function_exists('enrol_get_users_courses')) {
            $enrolledids = array_keys(enrol_get_users_courses($userid, true));
        }

        // 4. Obtener todos los cursos visibles con estadísticas agregadas en una sola query.
        $params = ['siteid' => SITEID];
        $sqlcourses = "
            SELECT c.id AS courseid,
                   c.fullname,
                   c.category,
                   COALESCE(SUM(CASE WHEN r.rating = 1 THEN 1 ELSE 0 END), 0) AS likes,
                   COALESCE(SUM(CASE WHEN r.rating = 0 THEN 1 ELSE 0 END), 0) AS dislikes
              FROM {course} c
         LEFT JOIN {local_datacurso_ratings} r ON r.courseid = c.id
             WHERE c.visible = 1
               AND c.id <> :siteid
          GROUP BY c.id, c.fullname, c.category
        ";

        $courses = $DB->get_records_sql($sqlcourses, $params);

        // 5. Calcular score de cada curso.
        $recommendations = [];
        foreach ($courses as $course) {
            $courseid = (int)$course->courseid;
            if (in_array($courseid, $enrolledids, true)) {
                continue;
            }

            $likes = (int)$course->likes;
            $dislikes = (int)$course->dislikes;
            $total = $likes + $dislikes;
            $satisfaction = $total > 0 ? round(($likes / $total) * 100, 2) : 0.0;

            $catid = (int)$course->category;
            $usercatratio = $categorypref[$catid] ?? null;
            $catratio = $usercatratio !== null ? $usercatratio : $globalratio;
            $catpercent = round($catratio * 100, 2);

            $score = (0.6 * $catpercent) + (0.4 * $satisfaction);

            $recommendations[] = [
                'courseid' => $courseid,
                'fullname' => $course->fullname,
                'categoryid' => $catid,
                'course_satisfaction' => $satisfaction,
                'category_preference_pct' => $catpercent,
                'score' => round($score, 2),
            ];
        }

        // 6. Ordenar y filtrar.
        usort($recommendations, static fn($a, $b) => $b['score'] <=> $a['score']);
        $filtered = array_filter($recommendations, static fn($r) => $r['category_preference_pct'] >= 80);

        // 7. Devolver top N.
        return array_slice(array_values($filtered), 0, max(0, $limit));
    }
}
