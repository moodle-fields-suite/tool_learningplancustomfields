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

namespace tool_learningplancustomfields\customfield;

use core_competency\api;
use core_competency\plan;
use local_fieldssuite\customfield\area_handler;

/**
 * Custom field handler for learning plans.
 *
 * The instance id is the plan id; the values live in the user context of the owner of the plan,
 * so they are personal data of that user (see the privacy provider).
 *
 * @package    tool_learningplancustomfields
 * @copyright  2026 onwards Kelson da Costa Medeiros
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class plan_handler extends area_handler {
    /**
     * Capabilities of the area.
     *
     * @param string $type One of the CAPABILITY_* constants.
     * @return string
     */
    public function get_capability(string $type): string {
        return [
            self::CAPABILITY_CONFIGURE => 'tool/learningplancustomfields:configurecustomfields',
            self::CAPABILITY_EDIT => 'tool/learningplancustomfields:editplan',
            self::CAPABILITY_CHANGELOCKED => 'tool/learningplancustomfields:changelockedcustomfields',
        ][$type];
    }

    /**
     * Admin page that manages the fields.
     *
     * @return string
     */
    public function get_admin_section(): string {
        return 'tool_learningplancustomfields_plan';
    }

    /**
     * User context of the owner of a plan.
     *
     * @param int $instanceid Plan id.
     * @return \context
     */
    public function get_instance_context(int $instanceid = 0): \context {
        if ($instanceid > 0 && ($plan = plan::get_record(['id' => $instanceid]))) {
            return $plan->get_context();
        }
        return \context_system::instance();
    }

    /**
     * A plan the current user can see.
     *
     * @param int $instanceid Plan id.
     * @return \stdClass|null
     */
    public function get_instance_info(int $instanceid): ?\stdClass {
        try {
            $plan = api::read_plan($instanceid);
        } catch (\moodle_exception $e) {
            return null;
        }
        $context = $plan->get_context();
        return (object)[
            'name' => format_string($plan->get('name'), true, ['context' => $context]),
            'url' => new \moodle_url('/admin/tool/lp/plan.php', ['id' => $instanceid]),
            'context' => $context,
        ];
    }

    /**
     * The edit page belongs to the plan: plan navigation of tool_lp.
     *
     * @param int $instanceid Plan id.
     * @param \moodle_url $url
     */
    public function set_up_edit_page(int $instanceid, \moodle_url $url): void {
        global $PAGE;
        $plan = api::read_plan($instanceid);
        \tool_lp\page_helper::setup_for_plan($plan->get('userid'), $url, $plan);
        $PAGE->set_url($url);
    }
}
