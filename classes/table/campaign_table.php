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
 *  Table that lists campaigns.
 * @package    auth_magic
 * @copyright  2019 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace auth_magic\table;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/tablelib.php');
require_once($CFG->dirroot . '/auth/magic/lib.php');

use moodle_url;
use html_writer;

/**
 * Table that lists campaigns.
 *
 * @package auth_magic
 */
class campaigns_table extends \table_sql {

    /**
     * Setup and Render the campaigns table.
     *
     * @param int $pagesize Size of page for paginated displayed table.
     * @param bool $useinitialsbar Whether to use the initials bar which will only be used if there is a fullname column defined.
     * @param string $downloadhelpbutton
     */
    public function out($pagesize, $useinitialsbar, $downloadhelpbutton = '') {

        // Define table headers and columns.
        $columns = ['title', 'campaignowner', 'availability', 'capacity', 'comments', 'action'];
        $headers = [
            get_string('campaigns:title', 'auth_magic'),
            get_string('campaigns:campaignowner', 'auth_magic'),
            get_string('campaigns:availability', 'auth_magic'),
            get_string('campaigns:capacity', 'auth_magic'),
            get_string('campaigns:comments', 'auth_magic'),
            get_string('action'),
        ];

        $this->define_columns($columns);
        $this->define_headers($headers);

        // Remove sorting for some fields.
        $this->sortable(false);

        $this->guess_base_url();

        parent::out($pagesize, $useinitialsbar, $downloadhelpbutton);
    }

    /**
     * Guess the base url for the campaign items table.
     */
    public function guess_base_url(): void {
        $this->baseurl = new moodle_url('/auth/magic/campaigns/manage.php');
    }

    /**
     * Set the sql query to fetch campaigns list.
     *
     * @param int $pagesize Size of page for paginated displayed table.
     * @param boolean $useinitialsbar Whether to use the initials bar which will only be used if there is a fullname column defined.
     * @return void
     */
    public function query_db($pagesize, $useinitialsbar = true) {
        // Fetch all avialable records from auth_magic_campaigns table.
        global $USER;
        $select = "*";
        $from = "{auth_magic_campaigns}";
        $where = 'id > 0';
        $param = [];
        if (!has_capability("auth/magic:viewloginlinks", \context_system::instance())) {
            $where .= ' AND campaignowner = :campaignowner AND status <> 1';
            $param = ['campaignowner' => $USER->id];
        }
        $this->set_sql($select, $from, $where, $param);

        parent::query_db($pagesize, $useinitialsbar);
    }

    /**
     * Show the campaign title in the list.
     *
     * @param object $row
     * @return void
     */
    public function col_title($row) {
        global $OUTPUT;
        $title = html_writer::tag('h6', $row->title, ['class' => 'badge-title']);
        return $title;
    }

    /**
     * Display the "Availablity" of column for a row in the item table.
     *
     * @param object $row The database row representing the campaign item.
     * @return string The HTML representation of the "Availablity" column for the given row.
     */
    public function col_availability($row) {
        $status = ($row->status == 0 ) ? get_string('campaigns:available', 'auth_magic')
            : get_string('campaigns:archived', 'auth_magic');
        $campaignclass = ($row->status == 0) ? 'badge-success' : 'badge-secondary';
        $avaiablefrom  = (!empty($row->startdate)) ? ' from '. userdate($row->startdate,
            $format = '%d/%m/%Y', $timezone = '', $fixday = false) : '';
        $avaiableuntil = (!empty($row->enddate)) ? ' until '. userdate($row->enddate,
            $format = '%d/%m/%Y', $timezone = '', $fixday = false) : '';
        $statustext = $status.$avaiablefrom.$avaiableuntil;
        return html_writer::tag('span', $statustext, ['class' => 'mr-1 badge '.$campaignclass]);
    }

    /**
     * Show the campaign capacity in the list.
     *
     * @param object $row
     * @return void
     */
    public function col_capacity($row) {
        $capacity = ($row->capacity == 0) ? get_string('campaigns:unlimited', 'auth_magic') : $row->capacity.' available';
        $title = html_writer::tag('p', $capacity, ['class' => 'campaign-capaity']);
        return $title;
    }


    /**
     * Show the campaign comment.
     */

    public function col_comments($row) {
        //pri
        return file_rewrite_pluginfile_urls(
            $row->comments, 'pluginfile.php', \context_system::instance()->id,
            'auth_magic', 'comments', $row->id
        );
    }

    /**
     * Show the campaign owner in the list.
     *
     * @param object $row
     * @return void
     */
    public function col_campaignowner($row) {
        global $DB;
        $campaignownerid = ($row->campaignowner);
        $user = $DB->get_record('user', ['id' => $campaignownerid]);
        $ownername = fullname($user);
        $title = html_writer::tag('p',  $ownername, ['class' => 'campaign-owner']);
        return $title;
    }

    /**
     * Actions Column, which contains the options to update the campaign visibility, Update the campaign, campaigns link, search.
     *
     * @param  \stdclass $row
     * @return string
     */
    public function col_action($row) {
        global $OUTPUT;

        $baseurl = new \moodle_url('/auth/magic/campaigns/manage.php', [
            'id' => $row->id,
            'sesskey' => \sesskey(),
        ]);
        $actions = [];

        // Show/Hide.
        if ($row->visibility) {
            $actions[] = [
                'url' => new \moodle_url($baseurl, ['action' => 'hidecampaign']),
                'icon' => new \pix_icon('t/hide', \get_string('hide')),
                'attributes' => ['data-action' => 'hide', 'class' => 'action-hide'],
            ];
        } else {
            $actions[] = [
                'url' => new \moodle_url($baseurl, ['action' => 'showcampaign']),
                'icon' => new \pix_icon('t/show', \get_string('show')),
                'attributes' => ['data-action' => 'show', 'class' => 'action-show'],
            ];
        }

        // Edit.
        $actions[] = [
            'url' => new moodle_url('/auth/magic/campaigns/edit.php', [
                'id' => $row->id,
                'sesskey' => sesskey(),
            ]),
            'icon' => new \pix_icon('t/edit', \get_string('edit')),
            'attributes' => ['class' => 'action-edit'],
        ];

        // Delete.
        $actions[] = [
            'url' => new \moodle_url($baseurl, ['action' => 'delete']),
            'icon' => new \pix_icon('t/delete', \get_string('delete')),
            'attributes' => ['class' => 'action-delete'],
            'action' => new \confirm_action(get_string('campaigns:deleteconfirmcampaign', 'auth_magic')),
        ];

        // Make the campaign link.
        $params = ['code' => $row->code];
        if (!empty($row->password)) {
            $params['token'] = $row->token;
        }

        $campaignlink = ( new \moodle_url('/auth/magic/campaigns/view.php', $params))->out(false);
        $actions[] = [
            'url' => new \moodle_url($baseurl, ['action' => 'copy']),
            'icon' => new \pix_icon('e/insert_edit_link', \get_string('campaigns:link', 'auth_magic')),
            'attributes' => ['class' => 'action-copy', 'data-campaignlink' => $campaignlink],
        ];

        // Make the campaign preview.
        $actions[] = [
            'url' => $campaignlink,
            'icon' => new \pix_icon('a/search', \get_string('campaigns:preview', 'auth_magic')),
            'attributes' => ['class' => 'action-preview', 'target' => '_blank'],
        ];

        if ($row->courseenrolmentkey != 'disabled') {
            $params['coupon'] = md5($row->coupon);
            $campaigncouponlink = ( new \moodle_url('/auth/magic/campaigns/view.php', $params))->out(false);
            $actions[] = [
                'url' => new \moodle_url($campaigncouponlink, ['action' => 'couponlink']),
                'icon' => new \pix_icon('t/tags', \get_string('campaigns:couponlink', 'auth_magic')),
                'attributes' => ['class' => 'action-couponlink', 'data-campaignlink' => $campaigncouponlink],
            ];
        }

        $actionshtml = [];
        foreach ($actions as $action) {
            $action['attributes']['role'] = 'button';
            $actionshtml[] = $OUTPUT->action_icon(
                $action['url'],
                $action['icon'],
                ($action['action'] ?? null),
                $action['attributes'],
            );
        }
        return html_writer::span(join('', $actionshtml), 'campaign-actions item-actions mr-0');;
    }
}
