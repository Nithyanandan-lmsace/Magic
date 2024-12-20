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
 * This file defines observers needed by the plugin.
 *
 * @package    auth_magic
 * @copyright  2023 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

$observers = [
    [
        'eventname' => '\core\event\user_created',
        'callback' => '\auth_magic\event\observer::created_user_data_request',
    ],
    [
        'eventname' => '\core\event\user_deleted',
        'callback' => '\auth_magic\event\observer::create_delete_data_request',
    ],
    [
        'eventname' => '\core\event\user_updated',
        'callback' => '\auth_magic\event\observer::create_update_data_request',
    ],
    [
        'eventname' => '\core\event\user_list_viewed',
        'callback' => '\auth_magic\event\observer::create_user_list_viewed_request',
    ],

    [
        'eventname' => '\core\event\config_log_created',
        'callback' => '\auth_magic\event\observer::config_log_created',
    ],

    [
        'eventname' => 'core\event\group_member_added',
        'callback' => '\auth_magic\event\observer::create_group_member_request',
    ],
];
