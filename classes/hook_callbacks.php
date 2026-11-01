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

use core\hook\output\after_standard_main_region_html_generation;
use local_fieldssuite\local\display;
use tool_learningplancustomfields\customfield\plan_handler;
use tool_learningplancustomfields\customfield\template_handler;

/**
 * Hook callbacks of tool_learningplancustomfields.
 *
 * @package    tool_learningplancustomfields
 * @copyright  2026 onwards Kelson da Costa Medeiros
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class hook_callbacks {
    /**
     * Shows the values, and the link to edit them, on the pages of a template and of a plan.
     *
     * - Template page (template competencies) and edit template page: values of the template.
     * - Plan page and edit plan page: values of the plan.
     *
     * @param after_standard_main_region_html_generation $hook
     */
    public static function after_standard_main_region_html_generation(after_standard_main_region_html_generation $hook): void {
        global $PAGE;
        if (!$PAGE->has_set_url()) {
            return;
        }
        switch ($PAGE->pagetype) {
            case 'admin-tool-lp-templatecompetencies':
                $id = (int)$PAGE->url->get_param('templateid');
                if ($id) {
                    display::add_to_page($hook, template_handler::create(), $id, '', true);
                }
                break;
            case 'admin-tool-lp-edittemplate':
                $id = (int)$PAGE->url->get_param('id');
                if ($id) {
                    display::add_to_page($hook, template_handler::create(), $id, 'h2', true);
                }
                break;
            case 'admin-tool-lp-plan':
                $id = (int)$PAGE->url->get_param('id');
                if ($id) {
                    display::add_to_page($hook, plan_handler::create(), $id, '', true);
                }
                break;
            case 'admin-tool-lp-editplan':
                $id = (int)$PAGE->url->get_param('id');
                if ($id) {
                    display::add_to_page($hook, plan_handler::create(), $id, 'h2', true);
                }
                break;
        }
    }
}
