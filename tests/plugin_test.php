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

use core_competency\api;
use core_customfield\field_controller;
use local_fieldssuite\external\get_values;
use local_fieldssuite\local\display;
use tool_learningplancustomfields\customfield\plan_handler;
use tool_learningplancustomfields\customfield\template_handler;

/**
 * Tests for the template and plan custom field areas.
 *
 * @package    tool_learningplancustomfields
 * @copyright  2026 onwards Kelson da Costa Medeiros
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \tool_learningplancustomfields\customfield\template_handler
 * @covers     \tool_learningplancustomfields\customfield\plan_handler
 * @covers     \tool_learningplancustomfields\observer
 */
final class plugin_test extends \advanced_testcase {
    /**
     * Creates a text field of an area.
     *
     * @param string $area 'template' or 'plan'.
     * @param string $shortname
     * @return field_controller
     */
    private function create_field(string $area, string $shortname): field_controller {
        /** @var \core_customfield_generator $generator */
        $generator = $this->getDataGenerator()->get_plugin_generator('core_customfield');
        $category = $generator->create_category(['component' => 'tool_learningplancustomfields', 'area' => $area]);
        return $generator->create_field([
            'categoryid' => $category->get('id'),
            'shortname' => $shortname,
            'name' => ucfirst($shortname),
            'type' => 'text',
        ]);
    }

    /**
     * Templates live in their own context and plans in the user context of their owner.
     */
    public function test_instance_info(): void {
        $this->resetAfterTest();
        $this->setAdminUser();
        /** @var \core_competency_generator $lpg */
        $lpg = $this->getDataGenerator()->get_plugin_generator('core_competency');
        $category = $this->getDataGenerator()->create_category();
        $catcontext = \context_coursecat::instance($category->id);
        $user = $this->getDataGenerator()->create_user();
        $template = $lpg->create_template(['contextid' => $catcontext->id, 'shortname' => 'Onboarding']);
        $plan = $lpg->create_plan(['userid' => $user->id, 'name' => 'Leadership path']);

        $info = template_handler::create()->get_instance_info($template->get('id'));
        $this->assertSame('Onboarding', $info->name);
        $this->assertEquals($catcontext->id, $info->context->id);
        $this->assertEquals($template->get('id'), $info->url->get_param('templateid'));
        $this->assertEquals($catcontext->id, template_handler::create()->get_instance_context($template->get('id'))->id);

        $usercontext = \context_user::instance($user->id);
        $info = plan_handler::create()->get_instance_info($plan->get('id'));
        $this->assertSame('Leadership path', $info->name);
        $this->assertEquals($usercontext->id, $info->context->id);
        $this->assertEquals($plan->get('id'), $info->url->get_param('id'));
        $this->assertEquals($usercontext->id, plan_handler::create()->get_instance_context($plan->get('id'))->id);

        $this->assertNull(template_handler::create()->get_instance_info(999999));
        $this->assertNull(plan_handler::create()->get_instance_info(999999));
        $this->assertEquals(\context_system::instance()->id, plan_handler::create()->get_instance_context(999999)->id);
    }

    /**
     * Only users who can read the plan get it: the owner can, another user cannot.
     */
    public function test_plan_access(): void {
        $this->resetAfterTest();
        /** @var \core_competency_generator $lpg */
        $lpg = $this->getDataGenerator()->get_plugin_generator('core_competency');
        $owner = $this->getDataGenerator()->create_user();
        $other = $this->getDataGenerator()->create_user();
        $this->setAdminUser();
        $plan = $lpg->create_plan(['userid' => $owner->id, 'status' => \core_competency\plan::STATUS_ACTIVE]);

        $this->setUser($owner);
        $this->assertNotNull(plan_handler::create()->get_instance_info($plan->get('id')));
        $this->setUser($other);
        $this->assertNull(plan_handler::create()->get_instance_info($plan->get('id')));
    }

    /**
     * Values are saved in the instance context, shown with the edit link and returned by the web service.
     */
    public function test_values(): void {
        global $DB;
        $this->resetAfterTest();
        $this->setAdminUser();
        /** @var \core_competency_generator $lpg */
        $lpg = $this->getDataGenerator()->get_plugin_generator('core_competency');
        $user = $this->getDataGenerator()->create_user();
        $template = $lpg->create_template();
        $plan = $lpg->create_plan(['userid' => $user->id]);
        $this->create_field('template', 'area');
        $mentor = $this->create_field('plan', 'mentor');

        template_handler::create()->instance_form_save((object)['id' => $template->get('id'), 'customfield_area' => 'HR']);
        plan_handler::create()->instance_form_save((object)['id' => $plan->get('id'), 'customfield_mentor' => 'Carla']);

        $this->assertEquals(\context_user::instance($user->id)->id, $DB->get_field('customfield_data', 'contextid', [
            'fieldid' => $mentor->get('id'),
            'instanceid' => $plan->get('id'),
        ]));

        $html = display::render_values(plan_handler::create(), $plan->get('id'), true);
        $this->assertStringContainsString('Carla', $html);
        $this->assertStringContainsString('area=plan', $html);

        $result = get_values::get_instances_values(template_handler::create(), [$template->get('id')]);
        $this->assertSame('HR', $result['instances'][0]['fields'][0]['value']);
    }

    /**
     * Deleting a template, a plan or a user deletes the values.
     */
    public function test_deletion(): void {
        global $DB;
        $this->resetAfterTest();
        $this->setAdminUser();
        /** @var \core_competency_generator $lpg */
        $lpg = $this->getDataGenerator()->get_plugin_generator('core_competency');
        $user = $this->getDataGenerator()->create_user();
        $other = $this->getDataGenerator()->create_user();
        $template = $lpg->create_template();
        $first = $lpg->create_plan(['userid' => $user->id]);
        $second = $lpg->create_plan(['userid' => $user->id]);
        $kept = $lpg->create_plan(['userid' => $other->id]);
        $area = $this->create_field('template', 'area');
        $mentor = $this->create_field('plan', 'mentor');
        /** @var \core_customfield_generator $generator */
        $generator = $this->getDataGenerator()->get_plugin_generator('core_customfield');
        $generator->add_instance_data($area, $template->get('id'), 'HR');
        foreach ([$first, $second, $kept] as $plan) {
            $generator->add_instance_data($mentor, $plan->get('id'), 'Carla');
        }

        api::delete_template($template->get('id'));
        $this->assertFalse($DB->record_exists('customfield_data', ['fieldid' => $area->get('id')]));

        api::delete_plan($first->get('id'));
        $this->assertFalse($DB->record_exists('customfield_data', ['instanceid' => $first->get('id')]));
        $this->assertTrue($DB->record_exists('customfield_data', ['instanceid' => $second->get('id')]));

        delete_user($user);
        $this->assertFalse($DB->record_exists('customfield_data', ['instanceid' => $second->get('id')]));
        $this->assertTrue($DB->record_exists('customfield_data', ['instanceid' => $kept->get('id')]));
    }
}
