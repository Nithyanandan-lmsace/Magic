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

use core_reportbuilder\local\filters\{date, duration, number, select, text, autocomplete};
use core_reportbuilder\local\helpers\format;
use core_reportbuilder\local\entities\base;
use core_reportbuilder\local\report\{column, filter};
use lang_string;
use stdClass;
use moodle_url;
use html_writer;

/**
 * Payment entity class implementation.
 *
 * @package   auth_magic
 * @copyright 2023 Medical Access Uganda Limited
 * @author    Renaat Debleu <info@eWallah.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class payment extends base {


    /**
     * Database tables that this entity uses
     *
     * @return array
     */
    protected function get_default_tables(): array {
        return [
            'payments',
        ];
    }



    /**
     * Database tables that this entity uses and their default aliases
     *
     * @return array
     */
    protected function get_default_table_aliases(): array {
        return ['payments' => 'pa'];
    }

    /**
     * The default title for this entity in the list of columns/conditions/filters in the report builder
     *
     * @return lang_string
     */
    protected function get_default_entity_title(): lang_string {
        return new lang_string('payments');
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

        return $this;
    }

    /**
     * Returns list of all available columns
     *
     * @return column[]
     */
    protected function get_all_columns(): array {
        $tablealias = $this->get_table_alias('payments');
        $name = $this->get_entity_name();

        // Accountid column.
        $columns[] = (new column('accountid', new lang_string('name'), $name))
            ->add_joins($this->get_joins())
            ->add_join("LEFT JOIN {payment_accounts} pac ON {$tablealias}.accountid = pac.id")
            ->set_type(column::TYPE_TEXT)
            ->add_field("pac.name")
            ->set_is_sortable(true);

        // Origin.
        $columns[] = (new column('origin', new lang_string('origin', 'auth_magic'), $name))
            ->add_joins($this->get_joins())
            ->add_join("LEFT JOIN {auth_magic_payment_logs} opl ON {$tablealias}.id = opl.paymentid
                        LEFT JOIN {auth_magic_campaigns} omc ON omc.id = opl.campaignid")
            ->add_fields('omc.id,omc.campaigncourse,omc.title')
            ->set_type(column::TYPE_TEXT)
            ->set_is_sortable(true)
            ->add_callback(static function ($value, stdClass $row): string {
                return ($row->campaigncourse) ? format_string(get_course($row->campaigncourse)->fullname) : $row->title;
            });

        // Origin Linked.
        $columns[] = (new column('originlinked', new lang_string('originlinked', 'auth_magic'), $name))
            ->add_joins($this->get_joins())
            ->add_join("LEFT JOIN {auth_magic_payment_logs} olpl ON {$tablealias}.id = olpl.paymentid
                        LEFT JOIN {auth_magic_campaigns} olmc ON olmc.id = olpl.campaignid")
            ->set_type(column::TYPE_TEXT)
            ->set_is_sortable(true)
            ->add_fields('olmc.id,olmc.campaigncourse,olmc.title')
            ->add_callback(static function ($value, stdClass $row): string {
                if ($row->campaigncourse) {
                    $course = get_course($row->campaigncourse);
                    return html_writer::link(new moodle_url('/course/view.php', ['id' => $row->campaigncourse]),
                                format_string($course->fullname));
                } else {
                    return html_writer::link(new moodle_url('/auth/magic/campaigns/edit.php', ['id' => $row->id,
                    'sesskey' => sesskey()]), $row->title);
                }
            });

        // Paymeny status.
        $columns[] = (new column('status', new lang_string('paymentstatus', 'auth_magic'), $name))
            ->add_joins($this->get_joins())
            ->add_join("LEFT JOIN {auth_magic_payment_logs} pl ON {$tablealias}.id = pl.paymentid")
            ->set_type(column::TYPE_TEXT)
            ->add_field("pl.status")
            ->set_is_sortable(true);

        // Component column.
        $columns[] = (new column('component', new lang_string('plugin'), $name))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TEXT)
            ->add_field("{$tablealias}.component")
            ->set_is_sortable(true);

        // Gateway column.
        $columns[] = (new column('gateway', new lang_string('type_paygw', 'plugin'), $name))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TEXT)
            ->add_field("{$tablealias}.gateway")
            ->set_is_sortable(true);

        // Amount column.
        $columns[] = (new column('amount', new lang_string('cost'), $name))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TEXT)
            ->add_field("{$tablealias}.amount")
            ->set_is_sortable(true)
            ->add_callback(function(?string $value): string {
                return ($value === '') ? '0' : number_format(floatval($value));
            });

        // Currency column.
        $columns[] = (new column('currency', new lang_string('currency'), $name))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TEXT)
            ->add_field("{$tablealias}.currency")
            ->set_is_sortable(true);

        // Date column.
        $columns[] = (new column('timecreated', new lang_string('date'), $name))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TIMESTAMP)
            ->add_field("{$tablealias}.timecreated")
            ->set_is_sortable(true)
            ->add_attributes(['class' => 'text-right'])
            ->add_callback([format::class, 'userdate'], get_string('strftimedatetimeshortaccurate', 'core_langconfig'));

        return $columns;
    }

    /**
     * Return list of all available filters
     *
     * @return filter[]
     */
    protected function get_all_filters(): array {

        $tablealias = $this->get_table_alias('payments');
        $name = $this->get_entity_name();

        // Gateway filter.
        $filters[] = (new filter(text::class, 'gateway', new lang_string('type_paygw', 'plugin'), $name, "{$tablealias}.gateway"))
            ->add_joins($this->get_joins());

        $filters[] = (new filter(select::class, 'origin', new lang_string('origin', 'auth_magic'), $name, "omc.id"))
            ->add_joins($this->get_joins())
            ->set_options_callback(static function(): array {
                global $DB;
                $sql = "SELECT amc.id,amc.campaigncourse,amc.title FROM {payments} p
                JOIN {auth_magic_payment_logs} ampl ON ampl.paymentid = p.id
                LEFT JOIN {auth_magic_campaigns} amc ON amc.id = ampl.campaignid";
                $records = $DB->get_records_sql($sql);
                $courses = get_string('courses');
                $campaigns = get_string('strcampaigns', 'auth_magic');
                $originoptions = [$courses => [], $campaigns => []];
                foreach ($records as $record) {
                    if ($record->campaigncourse) {
                        $originoptions[$courses][$record->id] = format_string(get_course($record->campaigncourse)->fullname);
                    } else {
                        $originoptions[$campaigns][$record->id] = $record->title;
                    }
                }
                return $originoptions;
            });

        $filters[] = (new filter(text::class, 'status', new lang_string('paymentstatus', 'auth_magic'), $name, "pl.status"))
            ->add_joins($this->get_joins());

        return $filters;
    }
}
