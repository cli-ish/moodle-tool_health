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
ob_start(); // Used for teh whitespace test.
require('../../../config.php');
$extraws = ob_get_clean();

require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->dirroot . '/' . $CFG->admin . '/tool/health/locallib.php');

admin_externalpage_setup('toolhealth');

define('SEVERITY_NOTICE', 'notice');
define('SEVERITY_ANNOYANCE', 'annoyance');
define('SEVERITY_SIGNIFICANT', 'significant');
define('SEVERITY_CRITICAL', 'critical');

$solution = optional_param('solution', 0, PARAM_PLUGIN);

echo $OUTPUT->header();

$class = '\\tool_health\\local\\problem\\' . $solution;
if (strpos($solution, 'problem_') === 0 && class_exists($class)) {
    health_print_solution($class);
} else {
    health_find_problems();
}


echo $OUTPUT->footer();

/**
 * Find all problems which can be tested.
 *
 * @return void
 * @throws coding_exception
 */
function health_find_problems() {
    global $OUTPUT, $CFG;

    echo $OUTPUT->heading(get_string('pluginname', 'tool_health'));

    $issues = [
        SEVERITY_CRITICAL => [],
        SEVERITY_SIGNIFICANT => [],
        SEVERITY_ANNOYANCE => [],
        SEVERITY_NOTICE => [],
    ];
    $problems = 0;
    $path = $CFG->dirroot . '/' . $CFG->admin . '/tool/health/classes/local/problem';

    $re = '/^(problem_[0-9]{6})\.php$/m';
    foreach (scandir($path) as $class) {
        preg_match($re, $class, $result);
        if ($result) {
            $classname = '\\tool_health\\local\\problem\\' . $result[1];
            $problem = new $classname;

            if ($problem->exists()) {
                $severity = $problem->severity();
                $issues[$severity][$result[1]] = [
                    'severity' => $severity,
                    'description' => $problem->description(),
                    'title' => $problem->title(),
                ];
                ++$problems;
            }
            unset($problem);
        }
    }

    if ($problems == 0) {
        echo '<div id="healthnoproblemsfound">';
        echo get_string('healthnoproblemsfound', 'tool_health');
        echo '</div>';
    } else {
        echo $OUTPUT->heading(get_string('healthproblemsdetected', 'tool_health'));
        $severities = [SEVERITY_CRITICAL, SEVERITY_SIGNIFICANT, SEVERITY_ANNOYANCE, SEVERITY_NOTICE];
        foreach ($severities as $severity) {
            if (!empty($issues[$severity])) {
                echo '<dl class="healthissues ' . $severity . '">';
                foreach ($issues[$severity] as $classname => $data) {
                    echo '<dt id="' . $classname . '">' . $data['title'] . '</dt>';
                    echo '<dd>' . $data['description'];
                    echo '<form action="index.php#solution" method="get">';
                    echo '<input type="hidden" name="solution" value="' . $classname .
                        '" /><input type="submit" value="' . get_string('viewsolution') . '" />';
                    echo '</form></dd>';
                }
                echo '</dl>';
            }
        }
    }
}

/**
 * Print the solution html content.
 *
 * @param string $classname
 * @return void
 * @throws coding_exception
 */
function health_print_solution(string $classname) {
    global $OUTPUT;
    $problem = new $classname;
    $data = [
        'title' => $problem->title(),
        'severity' => $problem->severity(),
        'description' => $problem->description(),
        'solution' => $problem->solution(),
    ];

    echo $OUTPUT->heading(get_string('pluginname', 'tool_health'));
    echo $OUTPUT->heading(get_string('healthproblemsolution', 'tool_health'));
    echo '<dl class="healthissues ' . $data['severity'] . '">';
    echo '<dt>' . $data['title'] . '</dt>';
    echo '<dd>' . $data['description'] . '</dd>';
    echo '<dt id="solution" class="solution">' . get_string('healthsolution', 'tool_health') . '</dt>';
    echo '<dd class="solution">' . $data['solution'] . '</dd></dl>';
    echo '<form id="healthformreturn" action="index.php#' . $classname . '" method="get">';
    echo '<input type="submit" value="' . get_string('healthreturntomain', 'tool_health') . '" />';
    echo '</form>';
}

/*
TODO:
    detect unsupported characters in $CFG->wwwroot - see bug Bug #6091 - relative vs absolute path during backup/restore process
*/
