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

namespace tool_learningplancustomfields;

use tool_learningplancustomfields\customfield\plan_handler;
use tool_learningplancustomfields\customfield\template_handler;

/**
 * Event observers of tool_learningplancustomfields.
 *
 * @package    tool_learningplancustomfields
 * @copyright  2026 onwards Kelson da Costa Medeiros
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class observer {
    /**
     * Deletes the custom field values of a deleted template.
     *
     * @param \core\event\competency_template_deleted $event
     */
    public static function competency_template_deleted(\core\event\competency_template_deleted $event): void {
        template_handler::create()->delete_instance((int)$event->objectid);
    }

    /**
     * Deletes the custom field values of a deleted plan.
     *
     * @param \core\event\competency_plan_deleted $event
     */
    public static function competency_plan_deleted(\core\event\competency_plan_deleted $event): void {
        plan_handler::create()->delete_instance((int)$event->objectid);
    }

    /**
     * Deletes the plan values of a deleted user.
     *
     * Core deletes the plans of the user without the plan deleted event, and the user context
     * is already gone, so the values are found by the id of that context.
     *
     * @param \core\event\user_deleted $event
     */
    public static function user_deleted(\core\event\user_deleted $event): void {
        global $DB;
        $DB->delete_records_select('customfield_data', 'contextid = :contextid AND fieldid IN (
                SELECT f.id
                  FROM {customfield_field} f
                  JOIN {customfield_category} c ON c.id = f.categoryid
                 WHERE c.component = :component AND c.area = :area)', [
            'contextid' => $event->contextid,
            'component' => 'tool_learningplancustomfields',
            'area' => 'plan',
        ]);
    }
}
