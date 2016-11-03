<?php
// This file is part of The Bootstrap 3 Moodle theme
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
 * Renderers to align Moodle's HTML with that expected by Bootstrap
 *
 * @package    theme_edgy
 * @copyright  2014 Waterford Institute of Technology
 * @authors    Cathal O'Riordan - based on Bootstrap 3 theme by Bas Brands, David Scotson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

include_once($CFG->dirroot . "/blocks/course_overview/renderer.php"); 

class theme_huey_block_course_overview_renderer extends block_course_overview_renderer {
    
    public function course_overview($courses, $overviews) {
      
        global $CFG;
      
        $support_courses = array();
        
        foreach ($courses as $key => $course) {
            if (preg_match("/^SUPPORT|INFO|PROG_/", $course->idnumber)) {
                $support_courses[$course->id] = $course;
                unset($courses[$key]); // remove support course from array
            }    
        }
        
        // display 'jump to course' dropdown menu
        $html = html_writer::start_tag('div', array('class'=>'well well-sm'));
        $html .= html_writer::start_tag('div', array('class'=>'container-fluid', 'id' => 'quickaccess'));
        $html .= html_writer::start_tag('div', array('class'=>'row'));
        $html .= html_writer::start_tag('div', array('class'=>'col-md-6'));
        $html .= html_writer::tag('h4', 'Jump to a Registered module');
        $html .= print_jumpto_course_form($courses, 'go_to_registered_courses', 
                                        get_string("jumptomoduleareaprompt", 'theme_edgy'), true);
        $html .= html_writer::end_tag('div');

        if (count($support_courses) > 0) {
        $html .= html_writer::start_tag('div', array('class' => 'col-md-6'));
        $html .= html_writer::tag('h4', 'Jump to an Information area');
        $html .= print_jumpto_course_form($support_courses, 'go_to_support_courses',
                                        get_string("jumptosupportareaprompt", 'theme_edgy'), true);
        $html .= html_writer::end_tag('div');
        }
        $html .= html_writer::end_tag('div');
        $html .= html_writer::end_tag('div');
        $html .= html_writer::end_tag('div');
  
        $config = get_config('block_course_overview');
        $ismovingcourse = false;
        $courseordernumber = 0;
        $maxcourses = count($courses);
        $userediting = false;
        // Intialise string/icon etc if user is editing and courses > 1
        if ($this->page->user_is_editing() && (count($courses) > 1)) {
          $userediting = true;
          $this->page->requires->js_init_call('M.block_course_overview.add_handles');

          // Check if course is moving
          $ismovingcourse = optional_param('movecourse', FALSE, PARAM_BOOL);
          $movingcourseid = optional_param('courseid', 0, PARAM_INT);
        }

        // Render first movehere icon.
        if ($ismovingcourse) {
          // Remove movecourse param from url.
          $this->page->ensure_param_not_in_url('movecourse');

          // Show moving course notice, so user knows what is being moved.
          $html .= $this->output->box_start('notice');
          $a = new stdClass();
          $a->fullname = $courses[$movingcourseid]->fullname;
          $a->cancellink = html_writer::link($this->page->url, get_string('cancel'));
          $html .= get_string('movingcourse', 'block_course_overview', $a);
          $html .= $this->output->box_end();

          $moveurl = new moodle_url('/blocks/course_overview/move.php',
                      array('sesskey' => sesskey(), 'moveto' => 0, 'courseid' => $movingcourseid));
          // Create move icon, so it can be used.
          $movetofirsticon = html_writer::empty_tag('img',
                  array('src' => $this->output->pix_url('movehere'),
                      'alt' => get_string('movetofirst', 'block_course_overview', $courses[$movingcourseid]->fullname),
                      'title' => get_string('movehere')));
          $moveurl = html_writer::link($moveurl, $movetofirsticon);
          $html .= html_writer::tag('div', $moveurl, array('class' => 'movehere'));
        }

        foreach ($courses as $key => $course) {
            // lets declare common stuff up here,
            $courseurl = new moodle_url('/course/view.php', array('id' => $course->id));
          
          // If moving course, then don't show course which needs to be moved.
          if ($ismovingcourse && ($course->id == $movingcourseid)) {
              continue;
          }
          $html .= $this->output->box_start('coursebox media', "course-{$course->id}");

          if ($course instanceof stdClass) {
              require_once($CFG->libdir. '/coursecatlib.php');
              $course_with_files = new course_in_list($course);
          }
          
          $html .= html_writer::start_tag('div', array('class' => 'course_title pull-left'));
          // If user is editing, then add move icons.
          if ($userediting && !$ismovingcourse) {
              $moveicon = html_writer::empty_tag('img',
                      array('src' => $this->pix_url('t/move')->out(false),
                          'alt' => get_string('movecourse', 'block_course_overview', $course->fullname),
                          'title' => get_string('move')));
              $moveurl = new moodle_url($this->page->url, 
                             array('sesskey' => sesskey(), 'movecourse' => 1, 'courseid' => $course->id));
              $moveurl = html_writer::link($moveurl, $moveicon);
              $html .= html_writer::tag('div', $moveurl, array('class' => 'move'));
          }
          
          // add course title image, if available
          $files = $course_with_files->get_course_overviewfiles();
          $url = "";
          
          if ($files) {
              // work with the first overview file returned
              $course_image_file = array_shift($files);

              $url = file_encode_url($CFG->wwwroot."/pluginfile.php",
                    '/'. $course_image_file->get_contextid(). '/'. $course_image_file->get_component(). '/'.
                    $course_image_file->get_filearea(). $course_image_file->get_filepath(). $course_image_file->get_filename(),                       false);
         
              
          } else {
             $url = $this->output->pix_url('no-course-img', 'format_simple');
          }
          
          $html .= html_writer::link($courseurl, 
                                      html_writer::empty_tag('img', array('src' => $url, 
                                                             'alt' => 'Course Image', 
                                                             'class' => 'media-object course-image course-image--small')),
                                      array('class' => 'pull-left'));
          
          $html .= $this->output->box('', 'flush');
          $html .= html_writer::end_tag('div');
          
          $html .= html_writer::start_tag('article', array('class' => 'media-body'));
         
          // No need to pass title through s() here as it will be done automatically by html_writer.
          $attributes = array('title' => $course->fullname);
          if ($course->id > 0) {
              if (empty($course->visible)) {
                  $attributes['class'] = 'dimmed';
              }
              $coursefullname = format_string(get_course_display_name_for_list($course), true, $course->id);
              $link = html_writer::link($courseurl, $coursefullname, $attributes);
              $html .= $this->output->heading($link, 2, 'title');
          } else {
              $html .= $this->output->heading(html_writer::link(
                  new moodle_url('/auth/mnet/jump.php', array('hostid' => $course->hostid, 'wantsurl' => '/course/view.php?id='.$course->remoteid)),
                  format_string($course->shortname, true), $attributes) . ' (' . format_string($course->hostname) . ')', 2, 'title');
          }
          

          if (!empty($config->showchildren) && ($course->id > 0)) {
              // List children here.
              if ($children = block_course_overview_get_child_shortnames($course->id)) {
                  $html .= html_writer::tag('span', $children, array('class' => 'coursechildren'));
              }
          }

          // If user is moving courses, then down't show overview.
          if (isset($overviews[$course->id]) && !$ismovingcourse) {
              $html .= $this->activity_display($course->id, $overviews[$course->id]);
          }

          $html .= $this->output->box('', 'flush');
          $html .= html_writer::end_tag('article');
          $html .= $this->output->box_end();
          $courseordernumber++;
          if ($ismovingcourse) {
              $moveurl = new moodle_url('/blocks/course_overview/move.php',
                          array('sesskey' => sesskey(), 'moveto' => $courseordernumber, 'courseid' => $movingcourseid));
              $a = new stdClass();
              $a->movingcoursename = $courses[$movingcourseid]->fullname;
              $a->currentcoursename = $course->fullname;
              $movehereicon = html_writer::empty_tag('img',
                      array('src' => $this->output->pix_url('movehere'),
                          'alt' => get_string('moveafterhere', 'block_course_overview', $a),
                          'title' => get_string('movehere')));
              $moveurl = html_writer::link($moveurl, $movehereicon);
              $html .= html_writer::tag('div', $moveurl, array('class' => 'movehere'));
          }
      }
      // Wrap course list in a div and return.
      return html_writer::tag('div', $html, array('class' => 'course_list'));

    }     
}