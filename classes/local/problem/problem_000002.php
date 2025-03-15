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
 * 000002 tests for extra characters at the end of the config.php file.
 *
 * @package     tool_health
 * @copyright   2025 Vincent Schneider (cli-ish)
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class problem_000002 extends base {
    /**
     * Generate title for this problem.
     *
     * @return string
     */
    public function title(): string {
        return 'Extra characters at the end of config.php or other library function';
    }

    /**
     * Check if the problem exists.
     *
     * @return bool
     */
    public function exists(): bool {
        global $extraws;

        if ($extraws === '') {
            return false;
        }
        return true;
    }

    /**
     * Get severity of the problem.
     *
     * @return string
     */
    public function severity(): string {
        return SEVERITY_SIGNIFICANT;
    }


    /**
     * Get problem description.
     *
     * @return string
     */
    public function description(): string {
        return 'Your Moodle configuration file config.php or another library file, contains some characters after ' .
            'the closing PHP tag (?>). This causes Moodle to exhibit several kinds of problems ' .
            '(such as broken downloaded files) and must be fixed.';
    }

    /**
     * Generate solution text.
     *
     * @return string
     * @uses $CFG
     */
    public function solution(): string {
        global $CFG;
        return 'You need to edit <strong>' . $CFG->dirroot . '/config.php</strong> and remove all characters ' .
            '(including spaces and returns) after the ending ?> tag. These two characters should be the very ' .
            'last in that file. The extra trailing whitespace may be also present in other PHP files that are ' .
            'included from lib/setup.php.';
    }
}
