<?php
// This file is part of Moodle - https://moodle.org/
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
 * The enrol plugin gescof is defined here.
 *
 * @package     enrol_gescof
 * @copyright   2020 CALL Learning <laurent@call-learning.fr>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// The base class 'enrol_plugin' can be found at lib/enrollib.php. Override
// methods as necessary.

/**
 * Class enrol_gescof_plugin.
 */
class enrol_gescof_plugin extends enrol_plugin {

    /**
     * Returns optional enrolment information icons.
     *
     * This is used in course list for quick overview of enrolment options.
     *
     * We are not using single instance parameter because sometimes
     * we might want to prevent icon repetition when multiple instances
     * of one type exist. One instance may also produce several icons.
     *
     * @param array $instances all enrol instances of this type in one course
     * @return array of pix_icon
     */
    public function get_info_icons(array $instances) {
        // TODO : ask Gescof for logo.
        return array(new pix_icon('icon', get_string('pluginname', 'enrol_gescof'), 'enrol_gescof'));
    }

    /**
     * Returns optional enrolment instance description text.
     *
     * This is used in detailed course information.
     *
     *
     * @param object $instance
     * @return string short html text
     */
    public function get_description_text($instance) {
        return get_string('description', 'enrol_gescof');
    }

    /**
     * Use the standard interface for adding/editing the form.
     *
     * @return bool.
     * @since Moodle 3.1.
     */
    public function use_standard_editing_ui() {
        return true;
    }

    /**
     * Adds form elements to add/edit instance form.
     *
     * @param object $instance Enrol instance or null if does not exist yet.
     * @param MoodleQuickForm $mform .
     * @param context $context .
     * @return void
     * @since Moodle 3.1.
     */
    public function edit_instance_form($instance, MoodleQuickForm $mform, $context) {
    }

    /**
     * Perform custom validation of the data used to edit the instance.
     *
     * @param array $data Array of ("fieldname"=>value) of submitted data.
     * @param array $files Array of uploaded files "element_name"=>tmp_file_path.
     * @param object $instance The instance data loaded from the DB.
     * @param context $context The context of the instance we are editing.
     * @return array Array of "element_name"=>"error_description" if there are errors, empty otherwise.
     * @since Moodle 3.1.
     */
    public function edit_instance_validation($data, $files, $instance, $context) {
        return array();
    }

    /**
     * Return whether or not, given the current state, it is possible to add a new instance
     * of this enrolment plugin to the course.
     *
     * @param int $courseid .
     * @return bool.
     */
    public function can_add_instance($courseid) {
        return true;
    }

    /**
     * Is it possible to hide/show enrol instance via standard UI?
     *
     * @param stdClass $instance
     * @return bool
     */
    public function can_hide_show_instance($instance) {
        return true;
    }

    /**
     * Is it possible to delete enrol instance via standard UI?
     *
     * @param stdClass $instance
     * @return bool
     */
    public function can_delete_instance($instance) {
        return true;
    }

    /**
     * Creates course enrol form, checks if form submitted
     * and enrols user if necessary. It can also redirect.
     *
     * @param stdClass $instance
     * @return string html text, usually a form in a text box
     */
    public function enrol_page_hook(stdClass $instance) {
        global $OUTPUT, $USER; //
        $migalbaseurl = get_config('enrol_gescof', 'migalurl');
        $catalogpageurl = null;
        if ($instance->customchar1 && $instance->customchar2) {
            $basegescofurl = trim($migalbaseurl, '/');
            $catalogpageurl = new moodle_url($basegescofurl . '/' . $instance->customchar1 . '/' . $instance->customchar2);
            if (!is_primary_admin($USER) // No redirection for admins.
                && $catalogpageurl
                && get_headers($catalogpageurl)) {
                redirect($catalogpageurl);
            }
        }
        $content = get_string('enrol:description', 'enrol_gescof');
        if ($catalogpageurl) {
            $viewurl = $catalogpageurl->out(false);
            $content .= \html_writer::link($viewurl, get_string('view'),
                array('class' => 'btn btn-primary'));
        }
        return $OUTPUT->box($content);
    }

    /**
     * Returns a button to manually enrol users through the manual enrolment plugin.
     *
     * By default the first manual enrolment plugin instance available in the course is used.
     * If no manual enrolment instances exist within the course then false is returned.
     *
     * This function also adds a quickenrolment JS ui to the page so that users can be enrolled
     * via AJAX.
     *
     * @param course_enrolment_manager $manager
     * @return enrol_user_button
     */
    public function get_manual_enrol_button(course_enrolment_manager $manager) {
        global $CFG, $PAGE;

        $instance = null;
        foreach ($manager->get_enrolment_instances() as $tempinstance) {
            if ($tempinstance->enrol == 'gescof') {
                if ($instance === null) {
                    $instance = $tempinstance;
                }
            }
        }
        if (empty($instance)) {
            return false;
        }

        $link = $this->get_manual_enrol_link($instance);
        if (!$link) {
            return false;
        }

        $button = new enrol_user_button($link, get_string('enrolusers', 'enrol_gescof'), 'get');
        $button->class .= ' enrol_gescof_plugin';

        //$context = context_course::instance($instance->courseid);
        //$arguments = array('contextid' => $context->id);
        //
        //if (!$called) {
        //    $called = true;
        //    // Calling the following more than once will cause unexpected results.
        //    $PAGE->requires->js_call_amd('enrol_gescof/quickenrolment', 'init', array($arguments));
        //}

        return $button;
    }


    /**
     * Returns link to manual enrol UI if exists.
     * Does the access control tests automatically.
     *
     * @param stdClass $instance
     * @return moodle_url
     */
    public function get_manual_enrol_link($instance) {
        $name = $this->get_name();
        if ($instance->enrol !== $name) {
            throw new coding_exception('invalid enrol instance!');
        }

        if (!enrol_is_enabled($name)) {
            return NULL;
        }

        $context = context_course::instance($instance->courseid, MUST_EXIST);

        if (!has_capability('enrol/gescof:enrol', $context)) {
            // Note: manage capability not used here because it is used for editing
            // of existing enrolments which is not possible here.
            return NULL;
        }
        return new moodle_url('/enrol/gescof/manage.php', array('enrolid'=>$instance->id, 'id'=>$instance->courseid));
    }

}
