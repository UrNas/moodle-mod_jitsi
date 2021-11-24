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
 * Library of interface functions and constants for module jitsi
 *
 * All the core Moodle functions, neeeded to allow the module to work
 * integrated in Moodle should be placed here.
 *
 * All the jitsi specific functions, needed to implement all the module
 * logic, should go to locallib.php. This will help to save some memory when
 * Moodle is performing actions across all modules.
 *
 * @package    mod_jitsi
 * @copyright  2021 Sergio Comerón Sánchez-Paniagua <sergiocomeron@icloud.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once("$CFG->libdir/formslib.php");
require_once(dirname(__FILE__).'/lib.php');

global $DB;

$deletejitsisourceid = optional_param('deletejitsisourceid', 0, PARAM_INT);
$sesskey = optional_param('sesskey', null, PARAM_TEXT);

$PAGE->set_context(context_system::instance());

$PAGE->set_url('/mod/jitsi/adminrecord.php');
require_login();

if ($deletejitsisourceid && confirm_sesskey($sesskey)) {
    deleterecordyoutube($deletejitsisourceid);
    redirect($PAGE->url, get_string('deleted'));
}

$PAGE->set_title(format_string(get_string('records', 'jitsi')));
$PAGE->set_heading(format_string(get_string('records', 'jitsi')));

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('records', 'jitsi'));
echo $OUTPUT->box(get_string('tablelistjitsis', 'jitsi'));

if (is_siteadmin()) {
    $table = new html_table();
    $table->head = array('Id', 'Link', get_string('acount', 'jitsi'), get_string('date'), get_string('delete'));
    $sources = $DB->get_records('jitsi_source_record', array());
    $acountinuse = $DB->get_record('jitsi_record_acount', array('inuse' => 1));

    foreach ($sources as $source) {
        if (isDeletable($source->id)) {
            if ($source->acount == $acountinuse->id) {
                $deleteurl = new moodle_url('/mod/jitsi/adminrecord.php?&deletejitsisourceid='.
                    $source->id. '&sesskey=' . sesskey());
                $deleteicon = new pix_icon('t/delete', get_string('delete'));
                $acount = $DB->get_record('jitsi_record_acount', array('id' => $source->acount));
                $deleteaction = $OUTPUT->action_icon($deleteurl, $deleteicon,
                    new confirm_action(get_string('deletesource?', 'jitsi')));
                $table->data[] = array($source->id, $source->link, $acount->name, userdate($source->timecreated), $deleteaction);
            } else {
                $acount = $DB->get_record('jitsi_record_acount', array('id' => $source->acount));
                $table->data[] = array($source->id, $source->link, $acount->name, userdate($source->timecreated),
                    get_string('otheracount', 'jitsi'));
            }
        }
    }
    echo html_writer::table($table);
}
echo $OUTPUT->footer();

function isincluded($sources, $sourceelement) {
    foreach ($sources as $source) {
        if ($source->id == $sourceelement->id) {
            return true;
        }
    }
    return false;
}