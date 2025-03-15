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

namespace tool_health\local\problem;

/**
 * 000005 tests if the session.auto_start is enabled
 *
 * @package     tool_health
 * @copyright   2025 Vincent Schneider (cli-ish)
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class problem_000005 extends base {
    /**
     * Generate title for this problem.
     *
     * @return string
     */
    public function title(): string {
        return 'PHP: session.auto_start is enabled';
    }

    /**
     * Check if the problem exists.
     *
     * @return bool
     */
    public function exists(): bool {
        return ini_get_bool('session.auto_start');
    }

    /**
     * Get severity of the problem.
     *
     * @return string
     */
    public function severity(): string {
        return SEVERITY_CRITICAL;
    }


    /**
     * Get problem description.
     *
     * @return string
     */
    public function description(): string {
        return 'Your PHP configuration includes an enabled setting, session.auto_start, that ' .
            '<strong>must be disabled</strong> in order for Moodle to work correctly. Notable symptoms arising from ' .
            'this misconfiguration include fatal errors and/or blank pages when trying to log in.';
    }

    /**
     * Generate solution text.
     *
     * @return string
     * @uses $CFG
     */
    public function solution(): string {
        global $CFG;
        return '<p>There are two ways you can solve this problem:</p><ol><li>If you have access to your main ' .
            '<strong>php.ini</strong> file, then find the line that looks like this: ' .
            '<pre>session.auto_start = 1</pre> and change it to <pre>session.auto_start = 0</pre> and then restart ' .
            'your web server. Be warned that this, as any other PHP setting change, might affect other web ' .
            'applications running on the server.</li><li>Finally, you may be able to change this setting just ' .
            'for your site by creating or editing the file <strong>' . $CFG->dirroot . '/.htaccess</strong> to ' .
            'contain this line: <pre>php_value session.auto_start "0"</pre></li></ol>';
    }
}
