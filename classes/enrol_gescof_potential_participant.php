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
 * Manual user enrolment UI for Gescof.
 *
 * Work in progress. Similar routine than the manual enrol plugin.
 *
 * @package     enrol_gescof
 * @copyright   2020 CALL Learning <contact@call-learning.fr>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace enrol_gescof;
defined('MOODLE_INTERNAL') || die();
use user_selector_base;
global $CFG;
require_once($CFG->dirroot.'/enrol/manual/locallib.php');

/**
 * Class enrol_manual_potential_participant
 *
 * @package local_vetagropro\importer
 */
class enrol_gescof_potential_participant extends \enrol_manual_potential_participant {
    protected function get_options() {
        $options = user_selector_base::get_options();
        $options['enrolid'] = $this->enrolid;
        return $options;
    }
}