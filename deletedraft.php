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
 * View for deleting a draft video which has still the status readytoupload
 *
 * @package    block_opencast
 * @category   view
 * @copyright  2020 Educational Technologies, Graz, University of Technology
 * @author     Behnam Taraghi
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');

use block_opencast\local\upload_helper;

global $PAGE, $OUTPUT, $CFG;

$identifier = required_param('identifier', PARAM_ALPHANUMEXT);
$courseid = required_param('courseid', PARAM_INT);
$action = optional_param('action', '', PARAM_ALPHA);

$baseurl = new moodle_url('/blocks/opencast/deletedraft.php', array('identifier' => $identifier, 'courseid' => $courseid));
$PAGE->set_url($baseurl);

require_login($courseid, false);

$PAGE->set_pagelayout('incourse');
$PAGE->set_title(get_string('pluginname', 'block_opencast'));
$PAGE->set_heading(get_string('pluginname', 'block_opencast'));

$redirecturl = new moodle_url('/blocks/opencast/index.php', array('courseid' => $courseid));
$PAGE->navbar->add(get_string('pluginname', 'block_opencast'), $redirecturl);
$PAGE->navbar->add(get_string('dodeleteevent', 'block_opencast'), $baseurl);

// Capability check.
// the one who is allowed to add the video is also allowed to delete the video before it is uploaded
$coursecontext = context_course::instance($courseid);
require_capability('block/opencast:addvideo', $coursecontext);

$uploadjobs  = upload_helper::get_upload_jobs($courseid);
$jobtodelete = null;
foreach ($uploadjobs as $uploadjob) {
    if($uploadjob->id == $identifier) {
        $jobtodelete = $uploadjob;
        break;
    }
}
if(!$jobtodelete) {
    $message = get_string('videonotfound', 'block_opencast');
    redirect($redirecturl, $message);
}
if($jobtodelete->status != upload_helper::STATUS_READY_TO_UPLOAD) {
    $message = get_string('videodraftnotdeletable', 'block_opencast',
                          upload_helper::get_status_string($jobtodelete->status));
    redirect($redirecturl, $message);
}


if (($action == 'delete') && confirm_sesskey()) {
    $deleted = upload_helper::delete_video_draft($jobtodelete);

    $message = $deleted ? get_string('videodraftdeletionsucceeded', 'block_opencast') :
                          get_string('videodraftnotdeletable',      'block_opencast',
                                  upload_helper::get_status_string($jobtodelete->status));
    redirect($redirecturl, $message);
}


$renderer = $PAGE->get_renderer('block_opencast');
$html = $renderer->render_upload_jobs([$jobtodelete], false);
$label = get_string('dodeleteevent', 'block_opencast');
$params = array(
        'identifier' => $identifier,
        'courseid' => $courseid,
        'action' => 'delete'
);
$urldelete = new \moodle_url('/blocks/opencast/deletedraft.php', $params);
$html .= $OUTPUT->confirm($label, $urldelete, $redirecturl);

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('dodeleteevent', 'block_opencast'));
echo $html;
echo $OUTPUT->footer();