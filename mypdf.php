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

// Extend the TCPDF class to create custom Header and Footer
class MYPDF extends TCPDF {
	
    // Page footer
    public function Footer() {
        // Position at 15 mm from bottom
        $this->SetY(-15);
        // Set font
        $this->SetFont('freeserif', '', 8); //empty string for second parameter gives us regular font
        // Page number
        $this->Cell(0, 
            10, 
            get_string('page', 'booktool_download').' '.$this->getAliasNumPage().' / '.$this->getAliasNbPages(), 
            0, false, 'C', 0, '', 0, false, 'T', 'M');
    }
}