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

namespace local_datacurso_ratings\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\userlist;
use core_privacy\local\request\writer;

/**
 * Privacy Subsystem implementation for local_datacurso_ratings.
 *
 * This plugin stores user ratings linked to course modules.
 *
 * @package   local_datacurso_ratings
 * @category  privacy
 * @copyright 2025
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements \core_privacy\local\metadata\provider, \core_privacy\local\request\plugin\provider {

    /**
     * Describe the types of personal data stored by this plugin.
     *
     * @param collection $collection
     * @return collection
     */
    public static function get_metadata(collection $collection): collection {
        $collection->add_database_table(
            'local_datacurso_ratings',
            [
                'userid'       => 'privacy:metadata:local_datacurso_ratings:userid',
                'cmid'         => 'privacy:metadata:local_datacurso_ratings:cmid',
                'rating'       => 'privacy:metadata:local_datacurso_ratings:rating',
                'feedback'     => 'privacy:metadata:local_datacurso_ratings:feedback',
                'timecreated'  => 'privacy:metadata:local_datacurso_ratings:timecreated',
                'timemodified' => 'privacy:metadata:local_datacurso_ratings:timemodified',
            ],
            'privacy:metadata:local_datacurso_ratings',
        );

        return $collection;
    }

    /**
     * Get the list of contexts that contain user information for the specified user.
     *
     * @param int $userid The user ID.
     * @return contextlist
     */
    public static function get_contexts_for_userid(int $userid): contextlist {
        $contextlist = new contextlist();

        $sql = "SELECT ctx.id
                  FROM {local_datacurso_ratings} r
                  JOIN {context} ctx ON ctx.contextlevel = :contextlevel
                 WHERE r.userid = :userid";
        $params = [
            'contextlevel' => CONTEXT_SYSTEM,
            'userid' => $userid,
        ];

        $contextlist->add_from_sql($sql, $params);
        return $contextlist;
    }

    /**
     * Export all user data for the specified context.
     *
     * @param approved_contextlist $contextlist
     */
    public static function export_user_data(approved_contextlist $contextlist) {
        global $DB;

        if (empty($contextlist->get_contextids())) {
            return;
        }

        $userid = $contextlist->get_user()->id;
        $records = $DB->get_records('local_datacurso_ratings', ['userid' => $userid]);

        if (!empty($records)) {
            foreach ($contextlist as $context) {
                writer::with_context($context)->export_data(
                    ['Ratings'],
                    (object)['entries' => $records]
                );
            }
        }
    }

    /**
     * Get the list of users who have data in the given context.
     *
     * @param userlist $userlist The userlist to add the users to.
     */
    public static function get_users_in_context(userlist $userlist) {
        if ($userlist->get_context()->contextlevel != CONTEXT_SYSTEM) {
            return;
        }

        $sql = "SELECT userid
                  FROM {local_datacurso_ratings}";
        $userlist->add_from_sql('userid', $sql, []);
    }

    /**
     * Delete all data for all users in the specified context.
     *
     * @param \context $context
     */
    public static function delete_data_for_all_users_in_context(\context $context) {
        global $DB;

        if ($context->contextlevel == CONTEXT_SYSTEM) {
            $DB->delete_records('local_datacurso_ratings');
        }
    }

    /**
     * Delete all user data for the specified user in the specified context.
     *
     * @param approved_contextlist $contextlist
     */
    public static function delete_data_for_user(approved_contextlist $contextlist) {
        global $DB;

        if (empty($contextlist->get_contextids())) {
            return;
        }

        $userid = $contextlist->get_user()->id;
        $DB->delete_records('local_datacurso_ratings', ['userid' => $userid]);
    }

    /**
     * Delete multiple users within a single context.
     *
     * @param approved_userlist $userlist
     */
    public static function delete_data_for_users(approved_userlist $userlist) {
        global $DB;

        $context = $userlist->get_context();
        if ($context->contextlevel != CONTEXT_SYSTEM) {
            return;
        }

        $userids = $userlist->get_userids();
        if (!empty($userids)) {
            list($insql, $params) = $DB->get_in_or_equal($userids, SQL_PARAMS_NAMED);
            $DB->delete_records_select('local_datacurso_ratings', "userid $insql", $params);
        }
    }
}
