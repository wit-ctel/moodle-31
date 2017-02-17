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
 * student fee block renderer
 *
 * @package    block_student_fee
 * @copyright  2016 Cathal O'Riordan, WIT (www.wit.ie)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Student_fee block rendrer
 *
 * @copyright  2016 Cathal O'Riordan, WIT (www.wit.ie)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class block_supportsection_renderer extends plugin_renderer_base {
    
    public function display_search(){
        
        $template = new stdClass();      
        $supportrssurl = "http://staging.elearning.wit.ie/support/moodle-support-section";
        $supportxml = simplexml_load_file($supportrssurl);
        $supportarray = array();


        for($i = 0; $i < 5; $i++){
            $title = (string)$supportxml->channel->item[$i]->title;
            $url = (string)$supportxml->channel->item[$i]->url;
            $supportarray[$i] = array(
                'key' => $i,
                'title' => $title,
                'url' => $url,);
            }

        $template->supportarray = $supportarray;
        return $this->render_from_template('block_supportsection/search', $template);
    }
    
}
    