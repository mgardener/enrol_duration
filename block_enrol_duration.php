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
 * Main code for Enrolment duration image block.
 *
 * @package   block_enrol_duration
 * @copyright  2013 Michael Gardener (https://github.com/mgardener)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class block_enrol_duration extends block_base {

    function init() {
        $this->title = get_string('pluginname', 'block_enrol_duration');
    }

    function applicable_formats() {
        
        return array('all' => true,
                     'course' => true
                     );
    }

    function specialization() {
        $this->title = isset($this->config->title) ? format_string($this->config->title) : format_string(get_string('pluginname', 'block_enrol_duration'));
    }

    function instance_allow_multiple() {
        return false;
    }

    function get_content() {
        global $CFG, $USER, $DB;
        //require_once('lib.php');
        //require_once($CFG->libdir.'/enrollib.php');

        if ($this->content !== null) {
            return $this->content;
        }
        
        $userid = $USER->id;
        $this->content = new stdClass;
        $this->content->text = '';
        
        if ($this->page->course->id == SITEID){            
            if ($courses = enrol_get_my_courses(NULL, 'visible DESC, fullname ASC')) {
                foreach ($courses as $mycourse) {
                                    
                    $sql = "SELECT ue.timeend 
                              FROM {user_enrolments} ue, {enrol} e 
                             WHERE ue.userid = ? 
                               AND ue.enrolid = e.id 
                               AND e.courseid= ?";

                    $duration = $DB->get_record_sql($sql, array($userid, $mycourse->id));

                    

                    if ($duration && ($duration->timeend > time())) {
                        $days = ceil(($duration->timeend - time())/ 86400);
                        $weeks = $days / 7;
                        $date = getdate($duration->timeend);
                 
                        $fulldate = $date['month'] .' '. $date['mday'] .', '. $date['year'];

                        $this->content->text  .= '<p>'.get_string('enrolmentin', 'block_enrol_duration').' <em><a href="'.$CFG->wwwroot.'/course/view.php?id='.$mycourse->id.'">'.$mycourse->fullname.'</a></em> '.
                                                get_string('expiresin', 'block_enrol_duration').'<br>';
                        $this->content->text .= '<strong>'.$days.' '.get_string('days', 'block_enrol_duration').'</strong>';
                        $this->content->text .= ': '.$fulldate.'.</p>';
                    } else {
                        $this->content->text  .= '<p>'.get_string('enrolmentin', 'block_enrol_duration').' <em><a href="'.$CFG->wwwroot.'/course/view.php?id='.$mycourse->id.'">'.$mycourse->fullname.'</a></em> '.get_string('noexpiration', 'block_enrol_duration').'.</p>';
                    }
                
                }
            }
        }else{            
            $courseid = $this->page->course->id;
            
            $sql = "SELECT ue.timeend 
                      FROM {user_enrolments} ue, {enrol} e 
                     WHERE ue.userid = ? 
                       AND ue.enrolid = e.id 
                       AND e.courseid= ?";

            $duration = $DB->get_record_sql($sql, array($userid, $courseid));


            if ($duration && ($duration->timeend > time())) {
                $days = ceil(($duration->timeend - time())/ 86400);
                $weeks = $days / 7;
                $date = getdate($duration->timeend);
         
                $fulldate = $date['month'] .' '. $date['mday'] .', '. $date['year'];
                $coursename = $this->page->course->fullname;

                $this->content->text  = '<p>'.get_string('enrolmentin', 'block_enrol_duration').' <em>'.$coursename.'</em> '.
                                        get_string('expiresin', 'block_enrol_duration').'<br>';
                $this->content->text .= '<strong>'.$days.' '.get_string('days', 'block_enrol_duration').'</strong>';
                $this->content->text .= ': '.$fulldate.'.</p>';
            } else {
                $this->content->text  = '<p>'.get_string('enrolmentin', 'block_enrol_duration').' <em>'.$this->page->course->fullname.
                                        '</em> '.get_string('noexpiration', 'block_enrol_duration').'.</p>';
            }
            
        }
        $this->content->footer = '';
        
        return $this->content;
    }
}