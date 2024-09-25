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
 * User statistics entity class implementation.
 *
 * @package   auth_magic
 * @copyright 2023 Medical Access Uganda Limited
 * @author    Renaat Debleu <info@eWallah.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace auth_magic\reportbuilder\local\entities;

use core_reportbuilder\local\entities\base;
use core_reportbuilder\local\report\{column, filter};
use lang_string;
use core_reportbuilder\local\helpers\format;

/**
 * User statistics entity class implementation.
 *
 * @package   auth_magic
 * @copyright 2023 Medical Access Uganda Limited
 * @author    Renaat Debleu <info@eWallah.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class user_statistics extends base {

    /**
     * Database tables that this entity uses and their default aliases
     *
     * @return array
     */
    protected function get_default_table_aliases(): array {
        return ['auth_magic_campaigns' => 'amc'];
    }

    /**
     * The default title for this entity in the list of columns/conditions/filters in the report builder
     *
     * @return lang_string
     */
    protected function get_default_entity_title(): lang_string {
        return new lang_string('entities:user_statistics', 'auth_magic');
    }

    /**
     * Initialise the entity
     *
     * @return base
     */
    public function initialise(): base {
        $columns = $this->get_all_columns();
        foreach ($columns as $column) {
            $this->add_column($column);
        }
        return $this;
    }


    /**
     * Returns list of all available columns
     *
     * @return column[]
     */
    protected function get_all_columns(): array {
        global $DB;
        $name = $this->get_entity_name();

        // No of Logins.
        $columns[] = (new column('logins', new lang_string('campaignsource:field_logins', 'auth_magic'), $name))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_INTEGER)
            ->set_is_sortable(true)
            ->add_field("amcu.userid")
            ->add_callback(static function(?int $value) use ($DB): int {
                return $DB->count_records_sql("
                    SELECT count(id) FROM {logstore_standard_log}
                    WHERE action = 'loggedin' AND target = 'user' AND userid = ?
                ", [$value]);
        });

        // No of Badges awarded.
        $columns[] = (new column('badgesawarded', new lang_string('campaignsource:field_badgesawarded', 'auth_magic'), $name))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_INTEGER)
            ->set_is_sortable(true)
            ->add_field("amcu.userid")
            ->add_callback(static function(?int $value) use ($DB): int {
                return $DB->count_records_sql("
                    SELECT count(id) FROM {badge_issued}
                    WHERE userid = ?
                ", [$value]);
        });

        // No of enrolled courses.
        $columns[] = (new column('enrolledcourses', new lang_string('campaignsource:field_enrolledcourses', 'auth_magic'), $name))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_INTEGER)
            ->set_is_sortable(true)
            ->add_field("amcu.userid")
            ->add_callback(static function(?int $value) use ($DB): int {
                $params = ['userid' => $value];
                $roles = get_archetype_roles('student');
                list($roleids, $inparams) = $DB->get_in_or_equal(array_keys($roles), SQL_PARAMS_NAMED, 'r');
                $params += $inparams;
                return $DB->count_records_sql("
                    SELECT count(ue.id) FROM {user_enrolments} ue
                    JOIN {enrol} e ON e.id = ue.enrolid
                    JOIN {course} c ON c.id = e.courseid
                    JOIN {context} cx ON cx.instanceid = c.id
                    JOIN {role_assignments} ra ON ra.userid = ue.userid AND ra.contextid = cx.id
                    WHERE ue.userid = :userid AND ra.roleid $roleids
                ", $params);
        });


        // No of inprogress courses.
        $columns[] = (new column('inprogresscourse', new lang_string('campaignsource:field_inprogresscourses', 'auth_magic'), $name))
         ->add_joins($this->get_joins())
         ->set_type(column::TYPE_INTEGER)
         ->set_is_sortable(true)
         ->add_field("amcu.userid")
         ->add_callback(static function(?int $value) use ($DB): int {
            $params = ['userid' => $value];
            $roles = get_archetype_roles('student');
            list($roleids, $inparams) = $DB->get_in_or_equal(array_keys($roles), SQL_PARAMS_NAMED, 'r');
            $params += $inparams;
            $enrolledcourses = $DB->count_records_sql("
                SELECT count(ue.id) FROM {user_enrolments} ue
                JOIN {enrol} e ON e.id = ue.enrolid
                JOIN {course} c ON c.id = e.courseid
                JOIN {context} cx ON cx.instanceid = c.id
                JOIN {role_assignments} ra ON ra.userid = ue.userid AND ra.contextid = cx.id
                WHERE ue.userid = :userid AND ra.roleid $roleids
            ", $params);

            $completedcourses = $DB->count_records_sql("
                SELECT count(ue.id) FROM {user_enrolments} ue
                JOIN {enrol} e ON e.id = ue.enrolid
                JOIN {course} c ON c.id = e.courseid
                JOIN {context} cx ON cx.instanceid = c.id
                JOIN {role_assignments} ra ON ra.userid = ue.userid AND ra.contextid = cx.id
                JOIN {course_completions} cp ON cp.userid = ra.userid AND cp.course = c.id
                WHERE ue.userid = :userid AND ra.roleid $roleids
            ", $params);

            return $enrolledcourses - $completedcourses;
        });


        $columns[] = (new column('completedcourse', new lang_string('campaignsource:field_completedcourses', 'auth_magic'), $name))
         ->add_joins($this->get_joins())
         ->set_type(column::TYPE_INTEGER)
         ->set_is_sortable(true)
         ->add_field("amcu.userid")
         ->add_callback(static function(?int $value) use ($DB): int {
            $params = ['userid' => $value];
            $roles = get_archetype_roles('student');
            list($roleids, $inparams) = $DB->get_in_or_equal(array_keys($roles), SQL_PARAMS_NAMED, 'r');
            $params += $inparams;
            return $DB->count_records_sql("
                SELECT count(ue.id) FROM {user_enrolments} ue
                JOIN {enrol} e ON e.id = ue.enrolid
                JOIN {course} c ON c.id = e.courseid
                JOIN {context} cx ON cx.instanceid = c.id
                JOIN {role_assignments} ra ON ra.userid = ue.userid AND ra.contextid = cx.id
                JOIN {course_completions} cp ON cp.userid = ra.userid AND cp.course = c.id
                WHERE ue.userid = :userid AND ra.roleid $roleids
            ", $params);
        });


        $columns[] = (new column('activitycompletion', new lang_string('campaignsource:field_activitiescompleted', 'auth_magic'), $name))
        ->add_joins($this->get_joins())
        ->set_type(column::TYPE_INTEGER)
        ->set_is_sortable(true)
        ->add_field("amcu.userid")
        ->add_callback(static function(?int $value) use ($DB): int {
            $params = ['userid' => $value];
            return $DB->count_records_sql("SELECT count(cm.id) FROM {course_modules_completion} cm WHERE cm.userid = :userid", $params);
        });

        return $columns;
    }


    /**
     * Return list of all available filters
     *
     * @return filter[]
     */
    protected function get_all_filters(): array {
        return [];
    }

}