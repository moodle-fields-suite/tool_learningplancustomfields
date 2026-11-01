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

use core_customfield\field_controller;
use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\userlist;
use core_privacy\local\request\writer;
use core_privacy\tests\provider_testcase;

/**
 * Tests for the privacy provider of tool_learningplancustomfields.
 *
 * @package    tool_learningplancustomfields
 * @copyright  2026 onwards Kelson da Costa Medeiros
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \tool_learningplancustomfields\privacy\provider
 */
final class provider_test extends provider_testcase {
    /** @var \stdClass Owner of the plans with values. */
    private $user;

    /** @var \stdClass Another user with a plan with values. */
    private $other;

    /** @var \core_competency\plan Plan of the user. */
    private $plan;

    /** @var \core_competency\plan Plan of the other user. */
    private $otherplan;

    /** @var field_controller Plan field. */
    private $mentor;

    /** @var field_controller Template field. */
    private $area;

    /**
     * Two users with a plan each, both with values, and a template with a value.
     */
    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();
        $this->setAdminUser();
        /** @var \core_competency_generator $lpg */
        $lpg = $this->getDataGenerator()->get_plugin_generator('core_competency');
        /** @var \core_customfield_generator $cfg */
        $cfg = $this->getDataGenerator()->get_plugin_generator('core_customfield');
        $this->user = $this->getDataGenerator()->create_user();
        $this->other = $this->getDataGenerator()->create_user();
        $this->plan = $lpg->create_plan(['userid' => $this->user->id, 'name' => 'Leadership path']);
        $this->otherplan = $lpg->create_plan(['userid' => $this->other->id]);
        $template = $lpg->create_template();

        $category = $cfg->create_category(['component' => 'tool_learningplancustomfields', 'area' => 'plan']);
        $this->mentor = $cfg->create_field(['categoryid' => $category->get('id'), 'shortname' => 'mentor', 'type' => 'text']);
        $category = $cfg->create_category(['component' => 'tool_learningplancustomfields', 'area' => 'template']);
        $this->area = $cfg->create_field(['categoryid' => $category->get('id'), 'shortname' => 'area', 'type' => 'text']);

        $cfg->add_instance_data($this->mentor, $this->plan->get('id'), 'Carla');
        $cfg->add_instance_data($this->mentor, $this->otherplan->get('id'), 'Bruno');
        $cfg->add_instance_data($this->area, $template->get('id'), 'HR');
    }

    /**
     * Number of values of a plan.
     *
     * @param \core_competency\plan $plan
     * @return int
     */
    private function count_values(\core_competency\plan $plan): int {
        global $DB;
        return $DB->count_records('customfield_data', ['fieldid' => $this->mentor->get('id'), 'instanceid' => $plan->get('id')]);
    }

    /**
     * The plugin links to the custom fields subsystem.
     */
    public function test_get_metadata(): void {
        $items = provider::get_metadata(new collection('tool_learningplancustomfields'))->get_collection();
        $this->assertCount(1, $items);
        $this->assertSame('core_customfield', $items[0]->get_name());
    }

    /**
     * Only the user context of a user with plan values is returned.
     */
    public function test_get_contexts_for_userid(): void {
        $usercontext = \context_user::instance($this->user->id);
        $this->assertEquals([$usercontext->id], provider::get_contexts_for_userid($this->user->id)->get_contextids());

        $nobody = $this->getDataGenerator()->create_user();
        $this->assertEmpty(provider::get_contexts_for_userid($nobody->id)->get_contextids());
    }

    /**
     * The owner of a user context is the only user found in it.
     */
    public function test_get_users_in_context(): void {
        $userlist = new userlist(\context_user::instance($this->user->id), 'tool_learningplancustomfields');
        provider::get_users_in_context($userlist);
        $this->assertEquals([$this->user->id], $userlist->get_userids());

        $userlist = new userlist(\context_system::instance(), 'tool_learningplancustomfields');
        provider::get_users_in_context($userlist);
        $this->assertEmpty($userlist->get_userids());
    }

    /**
     * The values are exported under the plan, next to the plans exported by core_competency.
     */
    public function test_export_user_data(): void {
        $usercontext = \context_user::instance($this->user->id);
        $this->export_context_data_for_user($this->user->id, $usercontext, 'tool_learningplancustomfields');
        $writer = writer::with_context($usercontext);
        $this->assertTrue($writer->has_any_data());

        $path = [
            get_string('competencies', 'core_competency'),
            get_string('privacy:path:plans', 'core_competency'),
            'Leadership path (' . $this->plan->get('id') . ')',
            get_string('customfielddata', 'core_customfield'),
        ];
        $exported = $writer->get_data(array_merge($path, [$this->data_id($this->plan)]));
        $this->assertSame('Carla', $exported->value);
    }

    /**
     * Id of the value of a plan.
     *
     * @param \core_competency\plan $plan
     * @return int
     */
    private function data_id(\core_competency\plan $plan): int {
        global $DB;
        return (int)$DB->get_field('customfield_data', 'id', [
            'fieldid' => $this->mentor->get('id'),
            'instanceid' => $plan->get('id'),
        ]);
    }

    /**
     * Deleting a user context deletes only the plan values in it.
     */
    public function test_delete_data_for_all_users_in_context(): void {
        global $DB;
        provider::delete_data_for_all_users_in_context(\context_user::instance($this->user->id));
        $this->assertSame(0, $this->count_values($this->plan));
        $this->assertSame(1, $this->count_values($this->otherplan));
        $this->assertTrue($DB->record_exists('customfield_data', ['fieldid' => $this->area->get('id')]));

        provider::delete_data_for_all_users_in_context(\context_system::instance());
        $this->assertTrue($DB->record_exists('customfield_data', ['fieldid' => $this->area->get('id')]));
    }

    /**
     * Deleting the data of a user deletes only the values of their plans.
     */
    public function test_delete_data_for_user(): void {
        $contextlist = new approved_contextlist($this->user, 'tool_learningplancustomfields', [
            \context_user::instance($this->user->id)->id,
            \context_user::instance($this->other->id)->id,
        ]);
        provider::delete_data_for_user($contextlist);
        $this->assertSame(0, $this->count_values($this->plan));
        $this->assertSame(1, $this->count_values($this->otherplan));
    }

    /**
     * Deleting a list of users deletes the values only when the owner of the context is in the list.
     */
    public function test_delete_data_for_users(): void {
        $context = \context_user::instance($this->user->id);
        provider::delete_data_for_users(new approved_userlist($context, 'tool_learningplancustomfields', [$this->other->id]));
        $this->assertSame(1, $this->count_values($this->plan));

        provider::delete_data_for_users(new approved_userlist($context, 'tool_learningplancustomfields', [$this->user->id]));
        $this->assertSame(0, $this->count_values($this->plan));
        $this->assertSame(1, $this->count_values($this->otherplan));
    }
}
