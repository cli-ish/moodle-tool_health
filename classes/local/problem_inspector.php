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

namespace tool_health\local;

use coding_exception;
use tool_health\local\problem\base;

/**
 * Inspector used to check the problems.
 *
 * @package     tool_health
 * @copyright   2025 Vincent Schneider (cli-ish)
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class problem_inspector {

    /**
     * Check user supplied solution or autorun the problem tests.
     *
     * @param string $solution
     * @return void
     * @throws coding_exception
     */
    public function handle(string $solution) {
        global $OUTPUT;
        echo $OUTPUT->header();
        echo $OUTPUT->heading(get_string('pluginname', 'tool_health'));

        $class = '\\tool_health\\local\\problem\\' . $solution;
        if (strpos($solution, 'problem_') === 0 && class_exists($class)) {
            echo $OUTPUT->heading(get_string('healthproblemsolution', 'tool_health'));
            $this->health_print_solution($solution);
        } else {
            $this->health_find_problems();
        }
        echo $OUTPUT->footer();
    }

    /**
     * Find all problems which can be tested.
     *
     * @return void
     * @throws coding_exception
     */
    private function health_find_problems() {
        global $OUTPUT, $CFG;
        $issues = [
            base::SEVERITY_CRITICAL => [],
            base::SEVERITY_SIGNIFICANT => [],
            base::SEVERITY_ANNOYANCE => [],
            base::SEVERITY_NOTICE => [],
        ];
        $problems = 0;
        $path = $CFG->dirroot . '/' . $CFG->admin . '/tool/health/classes/local/problem';

        $re = '/^(problem_[0-9]{6})\.php$/';
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
                    $problems++;
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
            $severities = [
                base::SEVERITY_CRITICAL,
                base::SEVERITY_SIGNIFICANT,
                base::SEVERITY_ANNOYANCE,
                base::SEVERITY_NOTICE];
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
    private function health_print_solution(string $classname) {
        $class = '\\tool_health\\local\\problem\\' . $classname;
        $problem = new $class;
        $data = [
            'title' => $problem->title(),
            'severity' => $problem->severity(),
            'description' => $problem->description(),
            'solution' => $problem->solution(),
            'links' => $problem->links(),
        ];
        $links = '';
        foreach ($data['links'] as $key => $link) {
            $links .= '<a href="' . $link . '" target="_blank">' . $key . '</a><br/>';
        }

        echo '<dl class="healthissues ' . $data['severity'] . '">';
        echo '<dt>' . $data['title'] . '</dt>';
        echo '<dd>' . $data['description'] . '</dd>';
        echo '<dt id="solution" class="solution">' . get_string('healthsolution', 'tool_health') . '</dt>';
        echo '<dd class="solution">' . $data['solution'] . '<br/>' . $links . '</dd></dl>';
        echo '<form id="healthformreturn" action="index.php#' . $classname . '" method="get">';
        echo '<input type="submit" value="' . get_string('healthreturntomain', 'tool_health') . '" />';
        echo '</form>';
    }
}
