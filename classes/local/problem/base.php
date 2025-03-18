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
 * Base problem, will be used by other problems.
 *
 * @package     tool_health
 * @copyright   2025 Vincent Schneider (cli-ish)
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class base {
    /**
     * This problem can be critical for security or usage of moodle.
     *
     * @var string SEVERITY_CRITICAL
     */
    const SEVERITY_CRITICAL = 'critical';
    /**
     * This problem can have a significant impact on moodle.
     *
     * @var string SEVERITY_SIGNIFICANT
     */
    const SEVERITY_SIGNIFICANT = 'significant';
    /**
     * This problem may annoy the users of moodle.
     *
     * @var string SEVERITY_ANNOYANCE
     */
    const SEVERITY_ANNOYANCE = 'annoyance';
    /**
     * This problem exist but may not be needed to fix.
     *
     * @var string SEVERITY_NOTICE
     */
    const SEVERITY_NOTICE = 'notice';


    /**
     * Check if the problem exists.
     *
     * @return bool
     */
    public function exists(): bool {
        return false;
    }

    /**
     * Generate title for this problem.
     *
     * @return string
     */
    public function title(): string {
        return '???';
    }

    /**
     * Get severity of the problem.
     *
     * @return string
     */
    public function severity(): string {
        return self::SEVERITY_NOTICE;
    }

    /**
     * Get problem description.
     *
     * @return string
     */
    public function description(): string {
        return '';
    }

    /**
     * Generate solution text.
     *
     * @return string
     */
    public function solution(): string {
        return '';
    }

    /**
     * Returns a list of urls which could be helpful.
     * where the key is the title for the link.
     *
     * @return string[]
     */
    public function links(): array {
        return [];
    }
}
