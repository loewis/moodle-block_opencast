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
 *
 * * @package    block_opencast
 * @copyright  2017 Andreas Wagner, SYNERGY LEARNING
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once('../../config.php');

use block_opencast\local\upload_helper;

global $PAGE, $OUTPUT, $CFG;

require_once($CFG->dirroot . '/repository/lib.php');

$courseid = required_param('courseid', PARAM_INT);

$baseurl = new moodle_url('/blocks/opencast/addvideo.php', array('courseid' => $courseid));
$PAGE->set_url($baseurl);

$redirecturl = new moodle_url('/blocks/opencast/index.php', array('courseid' => $courseid));

require_login($courseid, false);

// Use block context for this page to ignore course file upload limit.
$pagecontext = upload_helper::get_opencast_upload_context($courseid);
$PAGE->set_context($pagecontext);

$PAGE->set_pagelayout('incourse');
$PAGE->set_title(get_string('pluginname', 'block_opencast'));
$PAGE->set_heading(get_string('pluginname', 'block_opencast'));
$PAGE->navbar->add(get_string('pluginname', 'block_opencast'), $redirecturl);
$PAGE->navbar->add(get_string('edituploadjobs', 'block_opencast'), $baseurl);

// Capability check.
$coursecontext = context_course::instance($courseid);
require_capability('block/opencast:addvideo', $coursecontext);

$data = new stdClass();
$options = array('subdirs' => 0,
                 'maxfiles' => -1,
                 'accepted_types' => 'video', //TODO
                 'return_types' => FILE_INTERNAL);

// Record the user draft area in this context.
//XXX obsolete \block_opencast\local\file_deletionmanager::track_draftitemid($coursecontext->id, $data->videos_filemanager);

if (isset($_FILES['uploaded'])) {
    $name = $_FILES['uploaded']['name'];
    $type = $_FILES['uploaded']['type'];
    $tmp_name = $_FILES['uploaded']['tmp_name'];
    $error = $_FILES['uploaded']['error'];
    if (!$error) {
        $fs = get_file_storage();
        $filerecord = [
            'component' => 'block_opencast',
            'filearea' => upload_helper::OC_FILEAREA,
            'contextid' => $coursecontext->id,
            'filename' => $name,
            'itemid' => 0,
            'filepath' => '/'
            ];
        $fs->create_file_from_pathname($filerecord, $tmp_name);
    }
    // Update all upload jobs.
    \block_opencast\local\upload_helper::save_upload_jobs($courseid, $coursecontext);
    redirect($redirecturl, get_string('uploadjobssaved', 'block_opencast'));
}

$renderer = $PAGE->get_renderer('block_opencast');

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('edituploadjobs', 'block_opencast'));
echo "<form method='POST' enctype='multipart/form-data'>";
echo "<input type='hidden' name='MAX_FILE_SIZE' value='1000000'/>";
echo "<input type='hidden' name='courseid' value='${courseid}'/>";
echo "<input name='uploaded' type='file'/>";
echo "<input type='submit'>";
echo "</form>";
echo $OUTPUT->footer();
