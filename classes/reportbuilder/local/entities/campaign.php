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
 * Payment entity class implementation.
 *
 * @package   auth_magic
 * @copyright 2023 Medical Access Uganda Limited
 * @author    Renaat Debleu <info@eWallah.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace auth_magic\reportbuilder\local\entities;

use core_reportbuilder\local\filters\{date, select, text, autocomplete};
use auth_magic\reportbuilder\local\filters\json_autocomplete;
use core_reportbuilder\local\helpers\format;
use core_reportbuilder\local\entities\base;
use core_reportbuilder\local\report\{column, filter};
use lang_string;
use stdClass;
use moodle_url;
use html_writer;
use context_helper;
use context_system;
use core_user;
use auth_magic\campaign as campaignmagic;
use core_reportbuilder\local\filters\boolean_select;

require_once($CFG->dirroot . "/cohort/lib.php");


/**
 * Payment entity class implementation.
 *
 * @package   auth_magic
 * @copyright 2023 Medical Access Uganda Limited
 * @author    Renaat Debleu <info@eWallah.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class campaign extends base {

    /**
     * Database tables that this entity uses and their default aliases
     *
     * @return array
     */
    protected function get_default_table_aliases(): array {
        return ['auth_magic_campaigns' => 'amc', 'auth_magic_campaigns_payment' => 'amcp', 'auth_magic_campaigns_users' => 'amcu'];
    }

    /**
     * The default title for this entity in the list of columns/conditions/filters in the report builder
     *
     * @return lang_string
     */
    protected function get_default_entity_title(): lang_string {
        return new lang_string('strcampaigns', 'auth_magic');
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

        // All the filters defined by the entity can also be used as conditions.
        $filters = $this->get_all_filters();
        foreach ($filters as $filter) {
            $this
                ->add_filter($filter)
                ->add_condition($filter);
        }

        $conditions = $this->get_all_conditions();
        foreach ($conditions as $condition) {
            $this->add_condition($condition);
        }

        return $this;
    }

     /**
     * Returns list of all available columns
     *
     * @return column[]
     */
    protected function get_all_conditions(): array {
        global $USER;

        $campaigntablealias = $this->get_table_alias('auth_magic_campaigns');
        $conditions[] = (new filter(
            boolean_select::class,
            'usercohort',
            new lang_string('onlymycampaign', 'auth_magic'),
            $this->get_entity_name(),
            "{$campaigntablealias}.campaignowner = :userid",
            ['userid' => $USER->id]
        ))
        ->set_options([boolean_select::CHECKED => new lang_string('yes')])
        ->add_joins($this->get_joins());

        return $conditions;
    }

    /**
     * Returns list of all available columns
     *
     * @return column[]
     */
    protected function get_all_columns(): array {
        global $DB;
        $campaigntablealias = $this->get_table_alias('auth_magic_campaigns');

        $columns[] = (new column(
            'campaigntitlelink',
            new lang_string('campaignsource:field_campaigntitlelink', 'auth_magic'),
            $this->get_entity_name(),
        ))
            ->set_type(column::TYPE_TEXT)
            ->add_field("{$campaigntablealias}.title", 'campaigntitlelink')
            ->add_fields("{$campaigntablealias}.id")
            ->set_is_sortable($this->is_sortable('fee'))
            ->add_callback(static function(?string $value, stdClass $row) : string {
                return html_writer::link(new moodle_url('/auth/magic/campaigns/edit.php', ['id' => $row->id, 'sesskey' => sesskey()]),
                $value);
            });

        $campaignfields = $this->get_campaign_fields();
        foreach ($campaignfields as $campaignfield => $campaignfieldlang) {
            $columntype = $this->get_campaign_field_type($campaignfield);

            $columnfieldsql = "{$campaigntablealias}.{$campaignfield}";
            if ($columntype === column::TYPE_LONGTEXT && $DB->get_dbfamily() === 'oracle') {
                $columnfieldsql = $DB->sql_order_by_text($columnfieldsql, 1024);
            }

            $column = (new column(
                $campaignfield,
                $campaignfieldlang,
                $this->get_entity_name(),
            ))
                ->add_joins($this->get_joins())
                ->set_type($columntype)
                ->add_field($columnfieldsql, $campaignfield)
                ->set_is_sortable($this->is_sortable($campaignfield))
                ->add_callback([$this, 'format'], $campaignfield);

            $viewfullnames = has_capability('moodle/site:viewfullnames', context_system::instance());

            if ($campaignfield === 'description') {
                $column
                    ->add_fields("{$campaigntablealias}.descriptionformat, {$campaigntablealias}.id");
            } else if ($campaignfield === 'comments') {
                $column
                    ->add_fields("{$campaigntablealias}.commentsformat, {$campaigntablealias}.id");
            } else if ($campaignfield === 'status') {
                $column->add_callback(function(?string $value): string {
                    return ($value == 0) ? get_string('campaigns:available', 'auth_magic') : get_string('campaigns:archived', 'auth_magic');
                });
            } else if ($campaignfield === 'visibility') {
                $column->add_callback(function(?string $value): string {
                    return ($value == 0) ? get_string('campaigns:hidden', 'auth_magic') : get_string('visible');
                });
            } else if ($campaignfield === 'campaignowner') {
                $column->add_callback(function(?string $value, stdClass $row) use($viewfullnames) : string {
                    return html_writer::link(new moodle_url('/user/profile.php', ['id' => $row->campaignowner]),
                    fullname(core_user::get_user($row->campaignowner), $viewfullnames));
                });
            } else if ($campaignfield === 'cohorts') {
                $column->add_callback(function(?string $value, stdClass $row) use ($DB): string {
                    if (!empty($row->cohorts)) {
                        $cohortids = json_decode($row->cohorts);
                        $cohortnames = "";
                        foreach ($cohortids as $cohortid) {
                            $cohortname = $DB->get_field('cohort', 'name', array('id' => $cohortid));
                            $cohortnames .= $cohortname;
                            if ($cohortid != end($cohortids)) {
                                $cohortnames .= ", ";
                            }
                        }
                        return $cohortnames;
                    }
                    return "";
                });
            } else if ($campaignfield === 'globalrole') {
                $column->add_callback(function(?string $value) use ($DB): string {
                    if ($value) {
                        return role_get_name($DB->get_record('role', ['id' => $value]));
                    }
                    return "";
                });
            } else if ($campaignfield === 'campaigncourse') {
                $column->add_callback(function(?string $value): string {
                    if ($value) {
                        return format_string(get_course($value)->fullname);
                    }
                    return "";
                });
            } else if ($campaignfield === 'password') {
                $column
                    ->add_field("CASE WHEN {$columnfieldsql} = '' THEN 0 ELSE 1 END", $campaignfield);
            } else if ($campaignfield === 'restrictroles') {

                $column->add_callback(function(?string $value, stdClass $row) use ($DB): string {
                    if (!empty($row->restrictroles)) {
                        $roleids = json_decode($row->restrictroles);
                        $rolenames = "";
                        foreach ($roleids as $roleid) {
                            $rolename = role_get_name($DB->get_record('role', ['id' => $roleid]));
                            $rolenames .= $rolename;
                            if ($roleid != end($roleids)) {
                                $rolenames .= ", ";
                            }
                        }
                        return $rolenames;
                    }
                    return "";
                });

            } else if ($campaignfield === 'restrictcohorts') {
                $column->add_callback(function(?string $value, stdClass $row) use ($DB): string {
                    if (!empty($row->restrictcohorts)) {
                        $cohortids = json_decode($row->restrictcohorts);
                        $cohortnames = "";
                        foreach ($cohortids as $cohortid) {
                            $cohortname = $DB->get_field('cohort', 'name', array('id' => $cohortid));
                            $cohortnames .= $cohortname;
                            if ($cohortid != end($cohortids)) {
                                $cohortnames .= ", ";
                            }
                        }
                        return $cohortnames;
                    }
                    return "";
                });
            }
            $columns[] = $column;
        }
        $campaignpaymenttablealias = $this->get_table_alias('auth_magic_campaigns_payment');


        $feefield = "CASE WHEN {$campaignpaymenttablealias}.fee IS NOT NULL THEN ". $DB->sql_concat("{$campaignpaymenttablealias}.fee", "' '", "{$campaignpaymenttablealias}.currency"). " ELSE '" . get_string('campaigns:strfree', 'auth_magic') . "' END";
        $columns[] = (new column(
            'fee',
            new lang_string('campaignsource:field_fee', 'auth_magic'),
            $this->get_entity_name(),
        ))
            ->add_joins(["LEFT JOIN {auth_magic_campaigns_payment} $campaignpaymenttablealias ON {$campaignpaymenttablealias}.campaignid = {$campaigntablealias}.id"])
            ->set_type(column::TYPE_TEXT)
            ->add_field($feefield, 'fee')
            ->set_is_sortable($this->is_sortable('fee'))
            ->add_callback([$this, 'format'], 'fee');

        return $columns;
    }

    /**
     * Check if this field is sortable
     *
     * @param string $fieldname
     * @return bool
     */
    protected function is_sortable(string $fieldname): bool {
        // Some columns can't be sorted, like longtext or images.
        $nonsortable = [
            'description',
            'comments',
        ];

        return !in_array($fieldname, $nonsortable);
    }

    /**
     * Formats the user field for display.
     *
     * @param mixed $value Current field value.
     * @param stdClass $row Complete row.
     * @param string $fieldname Name of the field to format.
     * @return string
     */
    public function format($value, stdClass $row, string $fieldname): string {
        global $CFG;
        if ($this->get_campaign_field_type($fieldname) === column::TYPE_BOOLEAN) {
            return format::boolean_as_text($value);
        }

        if ($this->get_campaign_field_type($fieldname) === column::TYPE_TIMESTAMP) {
            return format::userdate($value, $row);
        }


        if ($fieldname === 'description' || $fieldname === 'comments') {
            if (empty($row->id)) {
                return '';
            }

            require_once("{$CFG->libdir}/filelib.php");

            context_helper::preload_from_record($row);
            $context = context_system::instance();
            $format = $fieldname . "format";
            $content = file_rewrite_pluginfile_urls($value, 'pluginfile.php', $context->id, 'auth_magic', $fieldname, $row->id);
            return format_text($content, $row->{$format}, ['context' => $context->id]);
        }

        return s($value);
    }

    protected function get_campaign_field_type($campaignfield) {
        switch ($campaignfield) {
            case 'description':
            case 'comments':
                $fieldtype = column::TYPE_LONGTEXT;
                break;
            case 'capacity':
                $fieldtype = column::TYPE_INTEGER;
                break;
            case 'privacypolicy':
            case 'welcomemessage':
            case 'password':
                $fieldtype = column::TYPE_BOOLEAN;
                break;
            case 'startdate':
            case 'enddate':
            case 'timecreated':
            case 'timemodified':
            case 'expirydate':
                $fieldtype = column::TYPE_TIMESTAMP;
                break;
            default:
                $fieldtype = column::TYPE_TEXT;
                break;
        }

        return $fieldtype;
    }

    protected function get_campaign_fields() {
        return [
            'title' => new lang_string('campaignsource:field_name', 'auth_magic'),
            'description' => new lang_string('campaignsource:field_description', 'auth_magic'),
            'comments' => new lang_string('campaignsource:field_comments', 'auth_magic'),
            'timecreated' => new lang_string('campaignsource:field_timecreated', 'auth_magic'),
            'timemodified' => new lang_string('campaignsource:field_timemodified', 'auth_magic'),
            'capacity' => new lang_string('campaignsource:field_capacity', 'auth_magic'),
            'status' => new lang_string('campaignsource:field_status', 'auth_magic'),
            'visibility' => new lang_string('campaignsource:field_visibility', 'auth_magic'),
            'restrictroles' => new lang_string('campaignsource:field_restrictbyrole', 'auth_magic'),
            'restrictcohorts' => new lang_string('campaignsource:field_restrictbycohort', 'auth_magic'),
            'startdate' => new lang_string('campaignsource:field_availablefrom', 'auth_magic'),
            'enddate' => new lang_string('campaignsource:field_enddate', 'auth_magic'),
            'password' => new lang_string('campaignsource:field_password', 'auth_magic'),
            'cohorts' => new lang_string('campaignsource:field_cohorts', 'auth_magic'),
            'globalrole' => new lang_string('campaignsource:field_globalrole', 'auth_magic'),
            'campaignowner' => new lang_string('campaignsource:field_campaignowner', 'auth_magic'),
            'privacypolicy' => new lang_string('campaignsource:field_consentstatement', 'auth_magic'),
            'welcomemessage' => new lang_string('campaignsource:field_welcomemessage', 'auth_magic'),
            'followupmessagedelay' => new lang_string('campaignsource:field_followupmessagedelay', 'auth_magic'),
            'expirydate' => new lang_string('campaignsource:field_expirydate', 'auth_magic'),
            'campaigncourse' => new lang_string('campaignsource:field_campaigncourse', 'auth_magic'),
        ];
    }

    /**
     * Return list of all available filters
     *
     * @return filter[]
     */
    protected function get_all_filters(): array {
        global $DB;
        $filters = [];
        $filterparams = [];
        $tablealias = $this->get_table_alias('auth_magic_campaigns');
        $campaignpaymenttablealias = $this->get_table_alias('auth_magic_campaigns_payment');
        $campaignfields = $this->get_campaign_fields();
        $autocompletefields = ['restrictroles', 'restrictcohorts', 'cohorts'];
        foreach ($campaignfields as $field => $name) {
            $filterfieldsql = "{$tablealias}.{$field}";
            if ($this->get_campaign_field_type($field) === column::TYPE_LONGTEXT) {
                $filterfieldsql = $DB->sql_cast_to_char($filterfieldsql);
            }
            $optionscallback = [static::class, 'get_options_for_' . $field];
            if (in_array($field, $autocompletefields)) {
                $classname = json_autocomplete::class;
            } else if (is_callable($optionscallback)) {
                $classname = select::class;
            } else if ($this->get_campaign_field_type($field) === column::TYPE_BOOLEAN) {
                $classname = boolean_select::class;
                if ($field == 'password') {
                    $filterfieldsql = "CASE WHEN {$filterfieldsql} = '' THEN 0 ELSE 1 END";
                }
            } else if ($this->get_campaign_field_type($field) === column::TYPE_TIMESTAMP) {
                $classname = date::class;
            } else {
                $classname = text::class;
            }

            $filter = (new filter(
                $classname,
                $field,
                $name,
                $this->get_entity_name(),
                $filterfieldsql
            ))
                ->add_joins($this->get_joins());

            // Populate filter options by callback, if available.
            if (is_callable($optionscallback)) {
                $filter->set_options_callback($optionscallback);
            }
            $filters[] = $filter;
        }

        $feefield = "CASE WHEN {$campaignpaymenttablealias}.fee IS NOT NULL THEN ". $DB->sql_concat("{$campaignpaymenttablealias}.fee", "' '", "{$campaignpaymenttablealias}.currency"). " ELSE '" . get_string('campaigns:strfree', 'auth_magic') . "' END";
        // Fee.
        $filters[] = (new filter(
            text::class,
            'fee',
            new lang_string('campaignsource:field_fee', 'auth_magic'),
            $this->get_entity_name(),
            $feefield,
        ))
            ->add_joins($this->get_joins());

        return $filters;
    }

    public static function get_options_for_restrictroles() : array {
        $rolelist = role_get_names(\context_system::instance());
        $roleoptions = [];
        foreach ($rolelist as $role) {
            $roleoptions[$role->id] = $role->localname;
        }
        return $roleoptions;
    }

    public static function get_options_for_restrictcohorts() : array {
        return self::get_options_for_cohorts();
    }


    public static function get_options_for_cohorts() : array {
        $cohortslist = \cohort_get_all_cohorts();
        $cohorts = $cohortslist['cohorts'];
        if ($cohorts) {
            array_walk($cohorts, function(&$value) {
                $value = $value->name;
            });
        }
        return $cohorts;
    }


    public static function get_options_for_campaignowner() : array {
        global $CFG, $DB;
        require_once($CFG->dirroot . "/auth/magic/lib.php");
        $users = $DB->get_records_sql("SELECT *
                                    FROM {user}
                                    WHERE confirmed = 1 AND deleted = 0 AND id <> ?", [$CFG->siteguest]);
        return auth_magic_get_usernames_choices($users);
    }

    public static function get_options_for_globalrole() : array {
        global $DB;
        $roles = get_roles_for_contextlevels(CONTEXT_SYSTEM);
        list($insql, $inparams) = $DB->get_in_or_equal(array_values($roles));
        $roles = $DB->get_records_sql("SELECT * FROM {role} WHERE id $insql", $inparams);
        return role_fix_names($roles, null, ROLENAME_ALIAS, true);
    }

    /**
     * List of options for the field status.
     *
     * @return string[]
     */
    public static function get_options_for_status(): array {
        return [
            campaignmagic::STATUS_AVAILABLE => get_string('campaigns:available', 'auth_magic'),
            campaignmagic::STATUS_ARCHIVED => get_string('campaigns:archived', 'auth_magic'),
        ];
    }


    /**
     * List of options for the field status.
     *
     * @return string[]
     */
    public static function get_options_for_visibility(): array {
        return [
            campaignmagic::HIDDEN => get_string('campaigns:hidden', 'auth_magic'),
            campaignmagic::VISIBLE => get_string('visible'),
        ];
    }
}
