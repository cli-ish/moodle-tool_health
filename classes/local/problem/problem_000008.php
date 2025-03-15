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
 * 000008 tests if the memory_limit can be set.
 *
 * @package     tool_health
 * @copyright   2025 Vincent Schneider (cli-ish)
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class problem_000008 extends base {

    /**
     * Generate title for this problem.
     *
     * @return string
     */
    public function title(): string {
        return 'PHP: memory_limit cannot be controlled by Moodle';
    }

    /**
     * Check if the problem exists.
     *
     * @return bool
     */
    public function exists(): bool {
        $oldmemlimit = @ini_get('memory_limit');
        if (empty($oldmemlimit)) {
            // PHP not compiled with memory limits, this means that it's
            // probably limited to 8M or in case of Windows not at all.
            // We can ignore it for now - there is not much to test anyway
            // Todo: add manual test that fills memory??
            return false;
        }
        $oldmemlimit = get_real_size($oldmemlimit);
        // Now lets change the memory limit to something higher.
        $newmemlimit = ($oldmemlimit + 1024 * 1024 * 5);
        raise_memory_limit($newmemlimit);
        $testmemlimit = get_real_size(@ini_get('memory_limit'));
        // Verify the change had any effect at all.
        if ($oldmemlimit == $testmemlimit) {
            // Memory limit can not be changed - is it big enough then?
            if ($oldmemlimit < get_real_size('128M')) {
                return true;
            } else {
                return false;
            }
        }
        reduce_memory_limit($oldmemlimit);
        return false;
    }

    /**
     * Get severity of the problem.
     *
     * @return string
     */
    public function severity(): string {
        return base::SEVERITY_NOTICE;
    }


    /**
     * Get problem description.
     *
     * @return string
     */
    public function description(): string {
        return 'The settings for PHP on your server do not allow a script to request more memory during its execution. ' .
            'This means that there is a hard limit of ' . @ini_get('memory_limit') . ' for each script. ' .
            'It is possible that certain operations within Moodle will require more than this amount in order ' .
            'to complete successfully, especially if there are lots of data to be processed.';
    }

    /**
     * Generate solution text.
     *
     * @return string
     */
    public function solution(): string {
        return 'It is recommended that you contact your web server administrator to address this issue.';
    }
}
