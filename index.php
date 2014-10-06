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
 * Book download subplugin
 *
 * @package    booktool_download
 * @copyright  2014 Ivana Skelic, Hrvoje Golcic 
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(dirname(__FILE__).'/../../../../config.php');

require_once($CFG->libdir.'/filelib.php');
require_once($CFG->libdir.'/tcpdf/tcpdf.php');
require_once(dirname(__FILE__).'/lib.php');
require_once(dirname(__FILE__).'/mypdf.php');

global $CFG;

$id = required_param('id', PARAM_INT); // Course Module ID

$cm = get_coursemodule_from_id('book', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id'=>$cm->course), '*', MUST_EXIST);
$bookid = $DB->get_record('book', array('id'=>$cm->instance), 'id', MUST_EXIST);

require_course_login($course, true, $cm);

$context = context_module::instance($cm->id);

require_course_login($course, true, $cm);
require_capability('booktool/download:download', $context);

generate_pdf($bookid->id, $course->id);
