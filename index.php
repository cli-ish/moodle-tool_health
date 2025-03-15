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

if (strpos($solution, 'problem_') === 0 && class_exists($solution)) {
    health_print_solution($solution);
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
    global $OUTPUT;

    echo $OUTPUT->heading(get_string('pluginname', 'tool_health'));

    $issues = [
        SEVERITY_CRITICAL => [],
        SEVERITY_SIGNIFICANT => [],
        SEVERITY_ANNOYANCE => [],
        SEVERITY_NOTICE => [],
    ];
    $problems = 0;

    for ($i = 1; $i < 1000000; ++$i) {
        $classname = sprintf('problem_%06d', $i);
        if (!class_exists($classname)) {
            continue;
        }
        $problem = new $classname;

        if ($problem->exists()) {
            $severity = $problem->severity();
            $issues[$severity][$classname] = [
                'severity' => $severity,
                'description' => $problem->description(),
                'title' => $problem->title(),
            ];
            ++$problems;
        }
        unset($problem);
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

/**
 * Base problem, will be used by other problems.
 */
abstract class problem_base {

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
        return SEVERITY_NOTICE;
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
}

/**
 * 000002 tests for extra characters at the end of the config.php file.
 */
class problem_000002 extends problem_base {
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

/**
 * 000003 tests if the dataroot exist and is writeable.
 */
class problem_000003 extends problem_base {
    /**
     * Generate title for this problem.
     *
     * @return string
     */
    public function title(): string {
        return '$CFG->dataroot does not exist or does not have write permissions';
    }

    /**
     * Check if the problem exists.
     *
     * @return bool
     */
    public function exists(): bool {
        global $CFG;
        if (!is_dir($CFG->dataroot) || !is_writable($CFG->dataroot)) {
            return true;
        }
        return false;
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
        global $CFG;
        return 'Your <strong>config.php</strong> says that your "data root" directory is <strong>' . $CFG->dataroot .
            '</strong>. However, this directory either does not exist or cannot be written to by Moodle. This means ' .
            'that a variety of problems will be present, such as users not being able to log in and not being able ' .
            'to upload any files. It is imperative that you address this problem for Moodle to work correctly.';
    }

    /**
     * Generate solution text.
     *
     * @return string
     * @uses $CFG
     */
    public function solution(): string {
        global $CFG;
        return 'First of all, make sure that the directory <strong>' . $CFG->dataroot .
            '</strong> exists. If the directory does exist, then you must make sure that Moodle is able to write ' .
            'to it. Contact your web server administrator and request that he gives write permissions for that ' .
            'directory to the user that the web server process is running as.';
    }
}

/**
 * 000004 tests if the cron is run on a regular base.
 */
class problem_000004 extends problem_base {
    /**
     * Generate title for this problem.
     *
     * @return string
     */
    public function title(): string {
        return 'cron.php is not set up to run automatically';
    }

    /**
     * Check if the problem exists.
     *
     * @return bool
     * @throws dml_exception
     * @throws dml_exception
     */
    public function exists(): bool {
        $lastcron = get_config('tool_task', 'lastcronstart');
        $expectedfrequency = $CFG->expectedcronfrequency ?? MINSECS;
        $delta = time() - $lastcron;
        return $expectedfrequency && ($delta > $expectedfrequency + MINSECS);
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
        return 'The cron.php mainenance script has not been run in the expected interval, the interval can be ' .
            'defined over $CFG->expectedcronfrequency. This probably means that your ' .
            'server is not configured to automatically run this script in regular time intervals. If this is the ' .
            'case, then Moodle will mostly work as it should but some operations (notably sending email to users) ' .
            'will not be carried out at all.';
    }

    /**
     * Generate solution text.
     *
     * @return string
     * @uses $CFG
     */
    public function solution(): string {
        global $CFG;
        return 'For detailed instructions on how to enable cron, see ' .
            '<a href="' . $CFG->wwwroot . '/doc/?file=install.html#cron">this section</a> of the installation manual.';
    }
}

/**
 * 000005 tests if the session.auto_start is enabled
 */
class problem_000005 extends problem_base {
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

/**
 * 000007 tests the file_upload php variable.
 */
class problem_000007 extends problem_base {
    /**
     * Generate title for this problem.
     *
     * @return string
     */
    public function title(): string {
        return 'PHP: file_uploads is disabled';
    }

    /**
     * Check if the problem exists.
     *
     * @return bool
     */
    public function exists(): bool {
        return !ini_get_bool('file_uploads');
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
        return 'Your PHP configuration includes a disabled setting, file_uploads, that ' .
            '<strong>must be enabled</strong> to let Moodle offer its full functionality. Until this setting is ' .
            'enabled, it will not be possible to upload any files into Moodle. This includes, for example, course ' .
            'content and user pictures.';
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
            '<strong>php.ini</strong> file, then find the line that looks like this: <pre>file_uploads = Off</pre> ' .
            'and change it to <pre>file_uploads = On</pre> and then restart your web server. Be warned that this, as ' .
            'any other PHP setting change, might affect other web applications running on the server.</li><li>' .
            'Finally, you may be able to change this setting just for your site by creating or editing the file ' .
            '<strong>' . $CFG->dirroot . '/.htaccess</strong> to contain this line: ' .
            '<pre>php_value file_uploads "On"</pre></li></ol>';
    }
}

/**
 * 000008 tests if the memory_limit can be set.
 */
class problem_000008 extends problem_base {
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
        return SEVERITY_NOTICE;
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

/**
 * 000009 tests if the sql database is conected without a password.
 */
class problem_000009 extends problem_base {
    /**
     * Generate title for this problem.
     *
     * @return string
     */
    public function title(): string {
        return 'SQL: using account without password';
    }

    /**
     * Check if the problem exists.
     *
     * @return bool
     */
    public function exists(): bool {
        global $CFG;
        return empty($CFG->dbpass);
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
        global $CFG;
        return 'The user account your are connecting to the database server with is set up without a password. ' .
            'This is a very big security risk and is only somewhat lessened if your database is configured to not ' .
            'accept connections from any hosts other than the server Moodle is running on. Unless you use a strong ' .
            'password to connect to the database, you risk unauthorized access to and manipulation of your data.' .
            ($CFG->dbuser != 'root' ? '' : (' <strong>This is especially alarming because such access to the ' .
                'database would be as the superuser (root)!</strong>'));
    }

    /**
     * Generate solution text.
     *
     * @return string
     * @uses $CFG
     */
    public function solution(): string {
        global $CFG;
        return 'You should change the password of the user <strong>' . $CFG->dbuser . '</strong> both in your ' .
            'database and in your Moodle <strong>config.php</strong> immediately!' .
            ($CFG->dbuser != 'root' ? '' : (' It would also be a good idea to change the user account from root' .
                ' to something else, because this would lessen the impact in the event that your ' .
                'database is compromised anyway.'));
    }
}

/**
 * 000012 tests if random questions are consistent.
 */
class problem_000012 extends problem_base {
    /**
     * Generate title for this problem.
     *
     * @return string
     */
    public function title(): string {
        return 'Random questions data consistency';
    }

    /**
     * Check if the problem exists.
     *
     * @return bool
     * @throws dml_exception
     * @throws dml_exception
     */
    public function exists(): bool {
        global $DB;
        return $DB->record_exists_select('question', "qtype = 'random' AND parent <> id", []);
    }

    /**
     * Get severity of the problem.
     *
     * @return string
     */
    public function severity(): string {
        return SEVERITY_ANNOYANCE;
    }


    /**
     * Get problem description.
     *
     * @return string
     */
    public function description(): string {
        return '<p>For random questions, question.parent should equal question.id. ' .
            'There are some questions in your database for which this is not true. ' .
            'One way that this could have happened is for random questions restored from backup before ' .
            '<a href="http://tracker.moodle.org/browse/MDL-5482">MDL-5482</a> was fixed.</p>';
    }

    /**
     * Generate solution text.
     *
     * @return string
     * @uses $CFG
     */
    public function solution(): string {
        global $CFG;
        return '<p>Upgrade to Moodle 1.9.1 or later, or manually execute the SQL</p>' .
            '<pre>UPDATE ' . $CFG->prefix . 'question SET parent = id WHERE qtype = \'random\' and parent &lt;> id;</pre>';
    }
}

/**
 * 000014 tests if any none multianswer/random questions have a question as parent.
 */
class problem_000014 extends problem_base {
    /**
     * Generate title for this problem.
     *
     * @return string
     */
    public function title(): string {
        return 'Only multianswer and random questions should be the parent of another question';
    }

    /**
     * Check if the problem exists.
     *
     * @return bool
     * @throws dml_exception
     * @throws dml_exception
     */
    public function exists(): bool {
        global $DB;
        return $DB->record_exists_sql("
                SELECT * FROM {question} q
                    JOIN {question} parent_q ON parent_q.id = q.parent
                WHERE parent_q.qtype NOT IN ('random', 'multianswer')");
    }

    /**
     * Get severity of the problem.
     *
     * @return string
     */
    public function severity(): string {
        return SEVERITY_ANNOYANCE;
    }


    /**
     * Get problem description.
     *
     * @return string
     */
    public function description(): string {
        return '<p>You have questions that violate this in your databse. ' .
            'You will need to investigate to determine how this happened.</p>';
    }

    /**
     * Generate solution text.
     *
     * @return string
     */
    public function solution(): string {
        return '<p>It is impossible to give a solution without knowing more about ' .
            ' how the problem was caused. You may be able to get help from the ' .
            '<a href="http://moodle.org/mod/forum/view.php?f=121">Quiz forum</a>.</p>';
    }
}

/**
 * 000016 tests if question categories have the same context as their parent.
 */
class problem_000016 extends problem_base {
    /**
     * Generate title for this problem.
     *
     * @return string
     */
    public function title(): string {
        return 'Question categories should belong to the same context as their parent';
    }

    /**
     * Check if the problem exists.
     *
     * @return bool
     * @throws dml_exception
     */
    public function exists(): bool {
        global $DB;
        return $DB->record_exists_sql("
            SELECT parent_qc.id AS parent, child_qc.id AS child, child_qc.contextid
            FROM {question_categories} child_qc
                JOIN {question_categories} parent_qc ON child_qc.parent = parent_qc.id
            WHERE child_qc.contextid <> parent_qc.contextid");
    }

    /**
     * Get severity of the problem.
     *
     * @return string
     */
    public function severity(): string {
        return SEVERITY_ANNOYANCE;
    }


    /**
     * Get problem description.
     *
     * @return string
     */
    public function description(): string {
        global $DB;
        $problemcategories = $DB->get_records_sql("
            SELECT
                parent_qc.id AS parentid, parent_qc.name AS parentname, parent_qc.contextid AS parentcon,
                child_qc.id AS childid, child_qc.name AS childname, child_qc.contextid AS childcon
            FROM {question_categories} child_qc
                JOIN {question_categories} parent_qc ON child_qc.parent = parent_qc.id
            WHERE child_qc.contextid <> parent_qc.contextid");
        $table = '<table><thead><tr><th colspan="3">Child category</th><th colspan="3">Parent category</th></tr><tr>' .
            '<th>Id</th><th>Name</th><th>Context id</th>' .
            '<th>Id</th><th>Name</th><th>Context id</th>' .
            "</tr></thead><tbody>\n";
        foreach ($problemcategories as $cat) {
            $table .= "<tr><td>$cat->childid</td><td>" . s($cat->childname) .
                "</td><td>$cat->childcon</td><td>$cat->parentid</td><td>" . s($cat->parentname) .
                "</td><td>$cat->parentcon</td></tr>\n";
        }
        $table .= '</tbody></table>';
        return '<p>When one question category is the parent of another, then they ' .
            'should both belong to the same context. This is not true for the following categories:</p>' .
            $table;
    }

    /**
     * Generate solution text.
     *
     * @return string
     */
    public function solution(): string {
        return '<p>An automated solution is difficult. It depends whether the ' .
            'parent or child category is in the wrong pace.' .
            'People in the <a href="http://moodle.org/mod/forum/view.php?f=121">Quiz forum</a> may be able to help.</p>';
    }
}

/**
 * 000017 tests the question categories tree structure
 *
 * @link https://tracker.moodle.org/browse/MDL-34684
 */
class problem_000017 extends problem_base {
    /**
     * Generate title for this problem.
     *
     * @return string
     */
    public function title(): string {
        return 'Question categories tree structure';
    }

    /**
     * Find problems (missing parents/loops)
     *
     * @return array|null
     * @throws dml_exception
     */
    private function find_problems() {
        global $DB;
        static $answer = null;

        if (is_null($answer)) {
            $categories = $DB->get_records('question_categories', [], 'id');

            // Look for missing parents.
            $missingparent = tool_health_category_find_missing_parents($categories);

            // Look for loops.
            $loops = tool_health_category_find_loops($categories);

            $answer = [$missingparent, $loops];
        }

        return $answer;
    }

    /**
     * Check if the problem exists.
     *
     * @return bool
     * @throws dml_exception
     */
    public function exists(): bool {
        [$missingparent, $loops] = $this->find_problems();
        return !empty($missingparent) || !empty($loops);
    }

    /**
     * Get severity of the problem.
     *
     * @return string
     */
    public function severity(): string {
        return SEVERITY_ANNOYANCE;
    }


    /**
     * Get problem description.
     *
     * @return string
     * @throws dml_exception
     */
    public function description(): string {
        [$missingparent, $loops] = $this->find_problems();

        $description = '<p>The question categories should be arranged into tree ' .
            ' structures by the question_categories.parent field. Sometimes ' .
            ' this tree structure gets messed up.</p>';

        $description .= tool_health_category_list_missing_parents($missingparent);
        $description .= tool_health_category_list_loops($loops);

        return $description;
    }

    /**
     * Generate solution text.
     *
     * @return string
     * @throws dml_exception
     * @uses $CFG
     */
    public function solution(): string {
        global $CFG;
        [$missingparent, $loops] = $this->find_problems();

        $solution = '<p>Consider executing the following SQL queries. These fix ' .
            'the problem by moving some categories to the top level.</p>';

        if (!empty($missingparent)) {
            $solution .= "<pre>UPDATE " . $CFG->prefix . "question_categories\n" .
                "        SET parent = 0\n" .
                "        WHERE id IN (" . implode(',', array_keys($missingparent)) . ");</pre>\n";
        }

        if (!empty($loops)) {
            $solution .= "<pre>UPDATE " . $CFG->prefix . "question_categories\n" .
                "        SET parent = 0\n" .
                "        WHERE id IN (" . implode(',', array_keys($loops)) . ");</pre>\n";
        }

        return $solution;
    }
}

/**
 * Check course categories tree structure for problems.
 *
 * @copyright  2013 Marko Vidberg
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class problem_000018 extends problem_base {
    /**
     * Generate title for this problem.
     *
     * @return string
     */
    public function title(): string {
        return 'Course categories tree structure';
    }

    /**
     * Search for problems in the course categories.
     *
     * @return array List of categories that contain missing parents or loops.
     * @throws dml_exception
     * @uses $DB
     */
    private function find_problems() {
        global $DB;
        static $answer = null;

        if (is_null($answer)) {
            $categories = $DB->get_records('course_categories', [], 'id');

            // Look for missing parents.
            $missingparent = tool_health_category_find_missing_parents($categories);

            // Look for loops.
            $loops = tool_health_category_find_loops($categories);

            $answer = [$missingparent, $loops];
        }

        return $answer;
    }

    /**
     * Check if the problem exists.
     *
     * @return bool
     * @throws dml_exception
     */
    public function exists(): bool {
        [$missingparent, $loops] = $this->find_problems();
        return !empty($missingparent) || !empty($loops);
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
     * @throws dml_exception
     */
    public function description(): string {
        [$missingparent, $loops] = $this->find_problems();

        $description = '<p>The course categories should be arranged into tree ' .
            ' structures by the course_categories.parent field. Sometimes ' .
            ' this tree structure gets messed up.</p>';

        $description .= tool_health_category_list_missing_parents($missingparent);
        $description .= tool_health_category_list_loops($loops);

        return $description;
    }

    /**
     * Generate solution text.
     *
     * @return string
     * @throws dml_exception
     * @uses $CFG
     */
    public function solution(): string {
        global $CFG;
        [$missingparent, $loops] = $this->find_problems();

        $solution = '<p>Consider executing the following SQL queries. These fix ' .
            'the problem by moving some categories to the top level.</p>';

        if (!empty($missingparent)) {
            $solution .= "<pre>UPDATE " . $CFG->prefix . "course_categories\n" .
                "        SET parent = 0, depth = 1, path = CONCAT('/', id)\n" .
                "        WHERE id IN (" . implode(',', array_keys($missingparent)) . ");</pre>\n";
        }

        if (!empty($loops)) {
            $solution .= "<pre>UPDATE " . $CFG->prefix . "course_categories\n" .
                "        SET parent = 0, depth = 1, path = CONCAT('/', id)\n" .
                "        WHERE id IN (" . implode(',', array_keys($loops)) . ");</pre>\n";
        }

        return $solution;
    }
}

/*

TODO:

    session.save_path -- it doesn't really matter because we are already IN a session, right?
    detect unsupported characters in $CFG->wwwroot - see bug Bug #6091 - relative vs absolute path during backup/restore process

*/
