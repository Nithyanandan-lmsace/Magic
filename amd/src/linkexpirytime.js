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
 * Magic link expiration form define js.
 * @module   auth_magic
 * @copyright  2023 bdecent gmbh <https://bdecent.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(["core/fragment","core/modal_factory","core/modal_events","core/notification","core/str", 'core/ajax'],
(function(Fragment,ModalFactory,ModalEvents,notification,String, Ajax) {

    /**
     * Set Magic login link expiration time setting show in modal.
     * @param {object} params
     */
    function showModal (params) {
        const seletor = document.querySelectorAll(".magic-loginlink_expiry");
        seletor.forEach((button) => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                var userid = e.currentTarget.getAttribute("data-id");
                params.userid = userid;
                ModalFactory.create({
                    type: ModalFactory.types.SAVE_CANCEL,
                    title: String.get_string('linkexpirytime', 'auth_magic'),
                    body: getBodyContent(params),
                    large: true
                })
                .then(function(modal) {

                    modal.getRoot().on(ModalEvents.save, e => {
                        e.preventDefault();
                        modal.getRoot().find('form').submit();
                    });

                    modal.getRoot().on('submit', 'form', e => {
                        e.preventDefault();
                        submitFormData(userid);
                        modal.hide();
                    });

                    modal.getRoot().on(ModalEvents.hidden, function() {
                        modal.destroy();
                    });
                    modal.show();
                    return modal;
                }).catch(notification.exception);
            });
        });
    }

    /**
     * Submit form data.
     * @param {int} userid
     */
    function submitFormData(userid) {
        var modalform = document.querySelectorAll('#linkexpirytime form')[0];
        var formData = new URLSearchParams(new FormData(modalform)).toString();
        Ajax.call([{
            methodname: 'auth_magic_update_link_expiry_time',
            args: {userid: userid, formdata: formData},
            done: function(response) {
                if (response.message) {
                    notification.addNotification({
                        message: response.message,
                        type: "success"
                    });
                }
            }
        }]);
    }

    /**
     * Returns submit form data in load fragment.
     * @param {object} params
     * @returns {Promise}
     */
    function getBodyContent(params) {
        return Fragment.loadFragment('auth_magic', 'link_expiration_form', params.contextid, params);
    }

    return {
        init: function(params) {
            showModal(params);
        }
    };
}));

//# sourceMappingURL=linkexpirytime.min.js.map