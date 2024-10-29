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
 * @copyright 2023 bdecent gmbh <https://bdecent.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace auth_magic\reportbuilder\local\entities;

use core_reportbuilder\local\entities\base;
use core_reportbuilder\local\report\{column, filter};
use lang_string;
use core_reportbuilder\local\helpers\format;

/**
 * Payment entity class implementation.
 *
 * @package   auth_magic
 * @copyright 2023 Medical Access Uganda Limited
 * @author    Renaat Debleu <info@eWallah.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class campaign_statistics extends base {


    /**
     * Database tables that this entity uses
     *
     * @return array
     */
    protected function get_default_tables(): array {
        return [
            'auth_magic_campaigns',
        ];
    }

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
        return new lang_string('entities:campaign_statistics', 'auth_magic');
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
        $campaigntablealias = "amc";
        $name = $this->get_entity_name();

        // Confirmed users.
        $columns[] = (new column('confirmedusers', new lang_string('campaignsource:field_confirmedusers', 'auth_magic'), $name))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_INTEGER)
            ->set_is_sortable(true)
            ->add_field("{$campaigntablealias}.id")
            ->add_callback(static function(?int $value) use ($DB): int {
                return $DB->count_records_sql("
                    SELECT count(u.id) FROM {user} u
                    LEFT JOIN {auth_magic_campaigns_users} amcu ON amcu.userid = u.id
                    WHERE amcu.campaignid = ? AND u.confirmed = 1
                ", [$value]);
            });

        $columns[] = (new column('unconfirmedusers', new lang_string('campaignsource:field_unconfirmedusers', 'auth_magic'), $name))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_INTEGER)
            ->set_is_sortable(true)
            ->add_field("{$campaigntablealias}.id")
            ->add_callback(static function(?int $value) use ($DB): int {
                return $DB->count_records_sql("
                    SELECT count(u.id) FROM {user} u
                    LEFT JOIN {auth_magic_campaigns_users} amcu ON amcu.userid = u.id
                    WHERE amcu.campaignid = ? AND u.confirmed = 0
                ", [$value]);
            });

        $columns[] = (new column('availableseats', new lang_string('campaignsource:field_campaignavailableseats',
            'auth_magic'), $name))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_INTEGER)
            ->set_is_sortable(true)
            ->add_field("{$campaigntablealias}.id")
            ->add_fields("{$campaigntablealias}.capacity")
            ->add_callback(static function(?int $value, \stdClass $row) use ($DB): String {
                $capacity = $row->capacity;
                if ($capacity) {
                    $campaignusers = $DB->count_records_sql("
                            SELECT count(amcu.id) FROM {auth_magic_campaigns_users} amcu
                            WHERE amcu.campaignid = ?
                        ", [$value]);
                    $availableseats = $capacity - $campaignusers;
                    return get_string("countavailableseats", "auth_magic", $availableseats);
                }
                return get_string('campaigns:unlimited', 'auth_magic');
            });

        $columns[] = (new column('totalrevenue', new lang_string('campaignsource:field_totalrevenue', 'auth_magic'), $name))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_INTEGER)
            ->set_is_sortable(true)
            ->add_field("{$campaigntablealias}.id")
            ->add_callback(static function(?int $value) use ($DB): int {
                $val = $DB->get_record_sql("
                    SELECT
                        SUM(ampl.count_id * p.amount) AS amount
                    FROM (
                        SELECT
                            ampl.paymentid,
                            COUNT(ampl.id) AS count_id
                        FROM {auth_magic_payment_logs} ampl
                        LEFT JOIN {user} u ON ampl.userid = u.id
                        WHERE ampl.campaignid = ?
                        GROUP BY ampl.paymentid
                    ) ampl
                    LEFT JOIN {payments} p ON p.id = ampl.paymentid;
                ", [$value]);
                return !isset($val->amount) ? 0 : $val->amount;
            });

        $columns[] = (new column('firstsignup', new lang_string('campaignsource:field_firstsignup', 'auth_magic'), $name))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_INTEGER)
            ->set_is_sortable(true)
            ->add_field("{$campaigntablealias}.id")
            ->add_callback(static function(?int $value, \stdClass $row) use ($DB): String {
                $record = $DB->get_record_sql("SELECT timecreated FROM {auth_magic_campaigns_users} WHERE campaignid = ? ORDER BY timecreated ASC LIMIT 1", [$value]);
                if (isset($record->timecreated)) {
                    return format::userdate($record->timecreated, $row);
                }
                return "";
            });


        $columns[] = (new column('recentsignup', new lang_string('campaignsource:field_recentsignup', 'auth_magic'), $name))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_INTEGER)
            ->set_is_sortable(true)
            ->add_field("{$campaigntablealias}.id")
            ->add_callback(static function(?int $value, \stdClass $row) use ($DB): String {
                $record = $DB->get_record_sql("SELECT timecreated FROM {auth_magic_campaigns_users}
                    WHERE campaignid = ? ORDER BY timecreated DESC LIMIT 1", [$value]);
                if (isset($record->timecreated)) {
                    return format::userdate($record->timecreated, $row);
                }
                return "";
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