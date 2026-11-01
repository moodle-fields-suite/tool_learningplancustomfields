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

/**
 * Admin pages of tool_learningplancustomfields.
 *
 * @package    tool_learningplancustomfields
 * @copyright  2026 onwards Kelson da Costa Medeiros
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

foreach (['template', 'plan'] as $area) {
    $ADMIN->add('competencies', new admin_externalpage(
        'tool_learningplancustomfields_' . $area,
        new lang_string($area . 'customfields', 'tool_learningplancustomfields'),
        new moodle_url('/local/fieldssuite/manage.php', ['component' => 'tool_learningplancustomfields', 'area' => $area]),
        'tool/learningplancustomfields:configurecustomfields'
    ));
}
