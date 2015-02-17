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
 * Library of functions for the booktool_download module
 *
 * @package    booktool_download
 * @copyright  2014 Ivana Skelic, Hrvoje Golcic 
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

/**
 * Adds module specific settings to the settings block
 *
 * @param settings_navigation $settings The settings navigation object
 * @param navigation_node $node The node to add module settings to
 */
 function booktool_download_extend_settings_navigation(settings_navigation $settings, navigation_node $node) {

 	global $PAGE;

 	$params = $PAGE->url->params();
 	if (empty($params['id'])) {
 		return;
 	}

 	if (has_capability('booktool/download:download', $PAGE->cm->context)) {
 		
 		$url = new moodle_url('/mod/book/tool/download/index.php', 
 			array('id'=>$params['id'])
 			);
	 	$action = new action_link($url, 
	 		get_string('download', 'booktool_download'), 
	 		new popup_action('click', $url)
	 		);
	 	$node->add(get_string('download', 'booktool_download'), 
	 		$url, 
	 		navigation_node::TYPE_SETTING, 
	 		null, 
	 		null, 
	 		new pix_icon('download', '', 'booktool_download', array('class'=>'icon'))
	 		);
 	}
}

/**
 * Generates PDF file for given Book id
 *
 * @param int $bookid id property of Book stdClass
 * @param int $courseid id property of Course stdClass
 * @param int id $contextid id property of Context stdClass
 */
function booktool_download_generate_pdf($bookid, $courseid, $contextid) {
	
	global $DB;

	// create new PDF document
	$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

	//get name value from book table (Book name)
	$name = $DB->get_field(
		'book', 
		'name', 
		array('id' => $bookid), 
		MUST_EXIST
		);

	// get name value from course table (Course Name)
	$coursename = $DB->get_field(
		'course', 
		'fullname', 
		array('id' => $courseid), 
		MUST_EXIST
		);

	// set PDF document information
	$pdf->SetTitle($name);
	$pdf->SetSubject($coursename);

	// remove PDF header
	$pdf->setPrintHeader(false);

	// set PDF header and footer fonts
	$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

	// set default monospaced font
	$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

	// set margins
	$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
	$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
	$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

	// set auto page breaks
	$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

	// set image scale factor
	$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);


	// start generating PDF document
	$pdf->AddPage();
	// freeserif is best for UTF-8 chars
	$pdf->SetFont('freeserif', 'B', 20);
	$pdf->Write(10, $name, '', 0, 'C', true, 0, false, false, 0);
	$pdf->SetFont('freeserif', 'B', 16);
	$pdf->Write(10, $coursename, '', 0, 'C', true, 0, false, false, 0);

	// this two variables are used as counters
	$chaptercnt = 1;
	$subchaptercnt = 1;

	$rs = $DB->get_recordset(
		'book_chapters',
		array('bookid' => $bookid),
		'',
		'id, subchapter, title, content',
		0,
		0
	);

	if ($rs->valid()) {

		foreach ($rs as $record) {
			
			$pdf->AddPage();

			$record->content = file_rewrite_pluginfile_urls(
				$record->content, 
				'pluginfile.php', 
				$contextid, 
				'mod_book', 
				'chapter', 
				$record->id
			);

			if ($record->subchapter == 1) {

				$subchapter_title = $chaptercnt.".".$subchaptercnt." ".$record->title;

				$pdf->SetFont('freeserif', 'B', 14);

				$pdf->Bookmark($subchapter_title, 1, 0, '', '', array(0,0,0));
				$pdf->Cell(0, 10, $subchapter_title, 0, 1, 'L');

				$subchaptercnt ++;

			} else {

				$pdf->SetFont('freeserif', 'B', 16);

				$chapter_title = $chaptercnt." ".$record->title;
				$pdf->Bookmark($chapter_title, 0, 0, '', 'B', array(0,0,0));
				$pdf->Cell(0, 6, $chapter_title, 0, 1, 'L');

				$chaptercnt ++;

			}

			$pdf->SetFont('freeserif', '', 12);

		    // library can't parse too much text at once, breaking output in paragraphs using REGEX
			$content = preg_split(
				'/<p[^>]+>/i', 
				$record->content, 
				NULL, 
				PREG_SPLIT_DELIM_CAPTURE
				);

			foreach ($content as $part) {

				if ($part != '</p>') {

					$pdf->WriteHTML($part, true, 0 , true, 0);
				}
			}
		}
	}

	$pdf->addTOCPage(PDF_PAGE_ORIENTATION, PDF_PAGE_FORMAT, true);
	$pdf->addTOC('2', 'freeserif', ' ', get_string('toc', 'booktool_download'), 'B', array(0,0,0));
	$pdf->endTOCPage();

	$pdf->Output($name.'.pdf', 'I');

	//============================================================+
	// END OF FILE
	//============================================================+
	
}