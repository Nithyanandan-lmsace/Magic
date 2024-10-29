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

use core_reportbuilder\local\entities\base;
use core_reportbuilder\local\report\{column, filter};
use lang_string;
use core_reportbuilder\local\helpers\format;
use auth_magic\campaign;

/**
 * Payment entity class implementation.
 *
 * @package   auth_magic
 * @copyright 2023 Medical Access Uganda Limited
 * @author    Renaat Debleu <info@eWallah.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class campaign_approval_staus extends base {

    /**
     * $reportid
     * @var int
     */
    public $reportid;

    /**
     * Define construct.
     * @param int $reportid
     */
    public function __construct(int $reportid) {
        $this->reportid = $reportid;
    }

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
        return new lang_string('entities:campaign_approval_staus', 'auth_magic');
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
        global $DB, $PAGE;

        $reportid = $this->reportid;
        $campaigntablealias = "amc";
        $name = $this->get_entity_name();

        // Open.
        $columns[] = (new column('open', new lang_string('campaignsource:field_open', 'auth_magic'), $name))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TEXT)
            ->set_is_sortable(true)
            ->add_field("{$campaigntablealias}.approvaltype")
            ->add_fields("amcu.userid, {$campaigntablealias}.emailconfirm, {$campaigntablealias}.id,
                {$campaigntablealias}.approvalroles")
            ->add_callback(static function(?string $value, $record) use ($DB, $reportid, $PAGE): string {
                if ($PAGE->pagetype == 'admin-reportbuilder-view') {
                    $returnurl = new \moodle_url('/reportbuilder/view.php', ['id' => $reportid]);
                } else {
                    $returnurl = new \moodle_url('/reportbuilder/edit.php', ['id' => $reportid]);
                }
                $returnurl = $returnurl->out();
                if ($value != 'disabled' && $record->userid && $record->emailconfirm != campaign::DISABLE) {
                    $user = $DB->get_record('user', ['id' => $record->userid]);
                    if ($value == 'information' || $value == 'optionalin') {
                        $confirmurl = new \moodle_url('/auth/magic/confirm.php', ['data' => $user->secret .'/'.$user->username,
                        'relateduserlogin' => 0, 'redirecturl' => urlencode($returnurl), 'campaignid' => $record->id]);
                        if ($user && !$DB->record_exists('auth_magic_confirmation_logs', ['userid' => $user->id,
                            'campaignid' => $record->id])) {
                            if ($value == 'information') {
                                return get_string('waitingforconfirmation', 'auth_magic', $confirmurl->out(false));
                            }
                            if (empty($record->approvalroles)) {
                                return get_string('norolewaitingforoptin', 'auth_magic', $confirmurl->out(false));
                            }
                            return get_string('waitingforoptin', 'auth_magic', $confirmurl->out(false));
                        }
                    } else if ($value == 'optionalout' || $value == 'fulloptionout') {
                        if ($user && !$DB->record_exists('auth_magic_revocation_logs', ['userid' => $user->id,
                            'campaignid' => $record->id])) {
                            $revocationurl = new \moodle_url('/auth/magic/campaigns/revocation.php', ['userid' => $user->id,
                            'redirecturl' => urlencode($returnurl), 'campaignid' => $record->id]);

                            if (empty($record->approvalroles)) {
                                return get_string('norolenotopted-out', 'auth_magic', $revocationurl->out(false));
                            }
                            return get_string('notopted-out', 'auth_magic', $revocationurl->out(false));
                        }
                    }
                }
                return "";
            });

        // Approved.
        $columns[] = (new column('approved', new lang_string('campaignsource:field_approved', 'auth_magic'), $name))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TEXT)
            ->set_is_sortable(true)
            ->add_field("amc.approvaltype")
            ->add_fields("amcu.userid, {$campaigntablealias}.emailconfirm, amcu.id, {$campaigntablealias}.id")
            ->add_callback(callable: static function(?string $value, $record) use ($DB): string {
                if ($value != 'disabled' && $record->userid && $record->emailconfirm != campaign::DISABLE) {
                    $user = $DB->get_record('user', ['id' => $record->userid]);
                    if ($user && $DB->record_exists('auth_magic_confirmation_logs', ['userid' => $user->id,
                        'campaignid' => $record->id])) {
                        return get_string('approved', 'auth_magic');
                    }
                }
                return "";
            });

        // Rejected.
        $columns[] = (new column('rejected', new lang_string('campaignsource:field_rejected', 'auth_magic'), $name))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TEXT)
            ->set_is_sortable(true)
            ->add_field("amc.approvaltype")
            ->add_fields("amcu.userid, {$campaigntablealias}.emailconfirm, amcu.id, {$campaigntablealias}.id")
            ->add_callback(callable: static function(?string $value, $record) use ($DB): string {
                if (($value == 'optionalout' || $value == 'fulloptionout') && $record->userid &&
                    $record->emailconfirm != campaign::DISABLE) {
                    $user = $DB->get_record('user', ['id' => $record->userid]);
                    if ($user) {
                        if ($DB->record_exists('auth_magic_revocation_logs', ['userid' => $user->id, 'campaignid' => $record->id])) {
                            return get_string('rejected', 'auth_magic');
                        }
                    }
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
