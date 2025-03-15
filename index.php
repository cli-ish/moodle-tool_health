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

/**
 * Test page for problems.
 *
 * @package    tool_health
 * @copyright  1999 onwards Martin Dougiamas (http://dougiamas.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// @codingStandardsIgnoreLine
ob_start(); // Used for the whitespace test.
require('../../../config.php');
$extraws = ob_get_clean();

require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->dirroot . '/' . $CFG->admin . '/tool/health/locallib.php');

admin_externalpage_setup('toolhealth');

$solution = optional_param('solution', 0, PARAM_PLUGIN);

$inspector = new tool_health\local\problem_inspector();
$inspector->handle($solution);

/*
TODO: detect unsupported characters in $CFG->wwwroot - see bug Bug #6091 - relative vs absolute path during backup/restore process
*/
