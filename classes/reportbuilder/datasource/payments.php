<?php
// This file is part of the tool_certificate plugin for Moodle - http://moodle.org/
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
 * Payments datasource
 *
 * @package    auth_magic
 * @copyright  2023 Medical Access Uganda
 * @author     Renaat Debleu <info@eWallah.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

declare(strict_types=1);

namespace auth_magic\reportbuilder\datasource;

use core_course\reportbuilder\local\entities\enrolment;
use core_reportbuilder\datasource;
use core_reportbuilder\local\entities\user;
use auth_magic\reportbuilder\local\entities\payment;

/**
 * Payments datasource
 *
 * @package    auth_magic
 * @copyright  2023 Medical Access Uganda
 * @author     Renaat Debleu <info@eWallah.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class payments extends datasource {
    /**
     * Initialise report
     */
    protected function initialise(): void {

        $main = new payment();
        $mainalias = $main->get_table_alias('payments');
        $mainname = $main->get_entity_name();

        $this->set_main_table('payments', $mainalias);
        $this->add_entity($main->add_join("JOIN {auth_magic_payment_logs} ampl ON ampl.paymentid = {$mainalias}.id
            JOIN {auth_magic_campaigns} amc ON amc.id = ampl.campaignid
        "));

        $user = new user();
        $useralias = $user->get_table_alias('user');
        $username = $user->get_entity_name();
        $this->add_entity($user->add_join(
            "LEFT JOIN {user} {$useralias} ON {$useralias}.id = {$mainalias}.userid"
        ));

        $this->add_columns_from_entity($mainname);
        $this->add_filters_from_entity($mainname);
        $this->add_conditions_from_entity($mainname);

        $this->add_columns_from_entity($username);
        $this->add_filters_from_entity($username);
        $this->add_conditions_from_entity($username);
    }

    /**
     * Get the visible name of the report.
     *
     * @return string
     */
    public static function get_name(): string {
        return get_string('payments_transactions', 'auth_magic');
    }

    /**
     * Return the columns that will be added to the report once is created
     *
     * @return string[]
     */
    public function get_default_columns(): array {
        return [
            'payment:accountid',
            'payment:origin',
            'payment:originlinked',
            'payment:gateway',
            'user:fullnamewithlink',
            'payment:amount',
            'payment:currency',
            'payment:timecreated',
        ];
    }

    /**
     * Return the filters that will be added to the report once is created
     *
     * @return string[]
     */
    public function get_default_filters(): array {
        return [
            'payment:gateway',
            'payment:origin',
            'payment:status',
        ];
    }

    /**
     * Return the conditions that will be added to the report once is created
     *
     * @return string[]
     */
    public function get_default_conditions(): array {
        return [];
    }
}
