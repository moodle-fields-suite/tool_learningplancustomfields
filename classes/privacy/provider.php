<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

namespace tool_learningplancustomfields\privacy;

use core_customfield\privacy\provider as customfield_provider;
use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\userlist;

/**
 * Privacy provider of tool_learningplancustomfields.
 *
 * The values of the plan area belong to the owner of the plan and live in their user context.
 * The values of the template area describe templates and are not personal data.
 *
 * @package    tool_learningplancustomfields
 * @copyright  2026 onwards Kelson da Costa Medeiros
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\core_userlist_provider,
    \core_privacy\local\request\plugin\provider {
    /** @var string Component of the custom field areas. */
    const COMPONENT = 'tool_learningplancustomfields';

    /** @var string Area whose values are personal data. */
    const AREA = 'plan';

    /**
     * Personal data stored by the plugin.
     *
     * @param collection $collection
     * @return collection
     */
    public static function get_metadata(collection $collection): collection {
        $collection->add_subsystem_link('core_customfield', [], 'privacy:metadata:core_customfield');
        return $collection;
    }

    /**
     * User context of a user who has plans with custom field values.
     *
     * @param int $userid
     * @return contextlist
     */
    public static function get_contexts_for_userid(int $userid): contextlist {
        return customfield_provider::get_customfields_data_contexts(
            self::COMPONENT,
            self::AREA,
            '= 0',
            'IN (SELECT p.id FROM {competency_plan} p WHERE p.userid = :userid)',
            ['userid' => $userid]
        );
    }

    /**
     * The owner of the user context, when their plans have custom field values.
     *
     * @param userlist $userlist
     */
    public static function get_users_in_context(userlist $userlist) {
        $context = $userlist->get_context();
        if (!$context instanceof \context_user) {
            return;
        }
        $sql = "SELECT p.userid
                  FROM {competency_plan} p
                  JOIN {customfield_data} d ON d.instanceid = p.id AND d.contextid = :contextid
                  JOIN {customfield_field} f ON f.id = d.fieldid
                  JOIN {customfield_category} c ON c.id = f.categoryid
                 WHERE p.userid = :userid AND c.component = :component AND c.area = :area";
        $userlist->add_from_sql('userid', $sql, [
            'contextid' => $context->id,
            'userid' => $context->instanceid,
            'component' => self::COMPONENT,
            'area' => self::AREA,
        ]);
    }

    /**
     * Exports the custom field values of the plans of the user, next to the plans exported by core_competency.
     *
     * @param approved_contextlist $contextlist
     */
    public static function export_user_data(approved_contextlist $contextlist) {
        global $DB;
        $user = $contextlist->get_user();
        $usercontext = \context_user::instance($user->id, IGNORE_MISSING);
        if (!$usercontext || !in_array($usercontext->id, $contextlist->get_contextids())) {
            return;
        }
        $approved = new approved_contextlist($user, $contextlist->get_component(), [$usercontext->id]);
        $plans = $DB->get_records('competency_plan', ['userid' => $user->id], 'id', 'id, name');
        foreach ($plans as $plan) {
            customfield_provider::export_customfields_data(
                $approved,
                self::COMPONENT,
                self::AREA,
                '= 0',
                '= :planid',
                ['planid' => $plan->id],
                [
                    get_string('competencies', 'core_competency'),
                    get_string('privacy:path:plans', 'core_competency'),
                    format_string($plan->name, true, ['context' => $usercontext]) . ' (' . $plan->id . ')',
                    get_string('customfielddata', 'core_customfield'),
                ]
            );
        }
    }

    /**
     * Deletes the plan values in a user context.
     *
     * @param \context $context
     */
    public static function delete_data_for_all_users_in_context(\context $context) {
        if ($context instanceof \context_user) {
            customfield_provider::delete_customfields_data_for_context(self::COMPONENT, self::AREA, $context);
        }
    }

    /**
     * Deletes the plan values of the user.
     *
     * @param approved_contextlist $contextlist
     */
    public static function delete_data_for_user(approved_contextlist $contextlist) {
        $userid = $contextlist->get_user()->id;
        foreach ($contextlist->get_contexts() as $context) {
            if ($context instanceof \context_user && $context->instanceid == $userid) {
                customfield_provider::delete_customfields_data_for_context(self::COMPONENT, self::AREA, $context);
            }
        }
    }

    /**
     * Deletes the plan values of the users in the list, in their own user context.
     *
     * @param approved_userlist $userlist
     */
    public static function delete_data_for_users(approved_userlist $userlist) {
        $context = $userlist->get_context();
        if ($context instanceof \context_user && in_array($context->instanceid, $userlist->get_userids())) {
            customfield_provider::delete_customfields_data_for_context(self::COMPONENT, self::AREA, $context);
        }
    }
}
