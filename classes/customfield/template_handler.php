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
use core_competency\template;
use local_fieldssuite\customfield\area_handler;

/**
 * Custom field handler for learning plan templates.
 *
 * The instance id is the template id; the values live in the context of the template (system or category).
 *
 * @package    tool_learningplancustomfields
 * @copyright  2026 onwards Kelson da Costa Medeiros
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class template_handler extends area_handler {
    /**
     * Capabilities of the area.
     *
     * @param string $type One of the CAPABILITY_* constants.
     * @return string
     */
    public function get_capability(string $type): string {
        return [
            self::CAPABILITY_CONFIGURE => 'tool/learningplancustomfields:configurecustomfields',
            self::CAPABILITY_EDIT => 'tool/learningplancustomfields:edittemplate',
            self::CAPABILITY_CHANGELOCKED => 'tool/learningplancustomfields:changelockedcustomfields',
        ][$type];
    }

    /**
     * Admin page that manages the fields.
     *
     * @return string
     */
    public function get_admin_section(): string {
        return 'tool_learningplancustomfields_template';
    }

    /**
     * Context of a template.
     *
     * @param int $instanceid Template id.
     * @return \context
     */
    public function get_instance_context(int $instanceid = 0): \context {
        if ($instanceid > 0 && ($template = template::get_record(['id' => $instanceid]))) {
            return $template->get_context();
        }
        return \context_system::instance();
    }

    /**
     * A template the current user can see.
     *
     * @param int $instanceid Template id.
     * @return \stdClass|null
     */
    public function get_instance_info(int $instanceid): ?\stdClass {
        try {
            $template = api::read_template($instanceid);
        } catch (\moodle_exception $e) {
            return null;
        }
        $context = $template->get_context();
        return (object)[
            'name' => format_string($template->get('shortname'), true, ['context' => $context]),
            'url' => new \moodle_url('/admin/tool/lp/templatecompetencies.php', [
                'templateid' => $instanceid,
                'pagecontextid' => $context->id,
            ]),
            'context' => $context,
        ];
    }

    /**
     * The edit page belongs to the template: template navigation of tool_lp.
     *
     * @param int $instanceid Template id.
     * @param \moodle_url $url
     */
    public function set_up_edit_page(int $instanceid, \moodle_url $url): void {
        global $PAGE;
        $template = api::read_template($instanceid);
        \tool_lp\page_helper::setup_for_template($template->get_context()->id, $url, $template);
        $PAGE->set_url($url);
    }
}
