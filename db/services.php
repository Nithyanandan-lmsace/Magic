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
 * Magic authentication plugin external functions and service definitions.
 *
 * @package auth_magic
 * @copyright  2023 bdecent gmbh <https://bdecent.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$functions = [
    'auth_magic_get_magiclink_passcheck' => [
        'classname' => 'external',
        'methodname' => 'get_magiclink_passcheck',
        'classpath' => 'auth/magic/classes/external.php',
        'description' => 'Check the get magic button passcheck',
        'type' => 'write',
        'ajax' => true,
        'loginrequired' => false,
    ],
    'auth_magic_get_magic_links' => [
        'classname' => 'external',
        'methodname' => 'get_magic_links',
        'classpath' => 'auth/magic/classes/external.php',
        'description' => 'Get a magic authentication links',
        'type' => 'write',
        'ajax' => true,
    ],
    'auth_magic_update_link_expiry_time' => [
        'classname' => 'external',
        'methodname' => 'update_link_expiry_time',
        'classpath' => 'auth/magic/classes/external.php',
        'description' => 'Manually override a magic link expiration time',
        'capabilities' => 'auth/magic:usersetlinkexpirytime',
        'type' => 'write',
        'ajax' => true,
    ],
    'auth_magic_get_course_groupings' => [
        'classname' => 'external',
        'methodname' => 'get_course_groupings',
        'classpath' => 'auth/magic/classes/external.php',
        'description' => 'Get the course groupings',
        'type' => 'write',
        'ajax' => true,
    ],
    'auth_magic_get_bankgat_amount' => [
        'classname' => 'external',
        'methodname' => 'get_bankgat_amount',
        'classpath' => 'auth/magic/classes/external.php',
        'description' => 'Get the bank gatway amount',
        'type' => 'write',
        'ajax' => true,
    ],
];
