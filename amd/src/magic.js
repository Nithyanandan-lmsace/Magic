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
 * Pro magic authentication define js.
 * @module   auth_magic
 * @copyright  2022 bdecent gmbh <https://bdecent.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

 define(['jquery', 'core/fragment', 'core/modal_factory', 'core/modal_events',
    'core/notification', 'core/str', 'core/templates', 'core/ajax'],
 function($, Fragment, ModalFactory, ModalEvents, notification, Str, Templates, Ajax) {

    var MAGIC;

    /**
     * Controls Custom styles tool action.
     * @param {object} params
     */
    var Magic = function(params) {
        var self = this;
        MAGIC = this;

        if (params.enrolstatus !== undefined) {
            self.displayAuthInfoBox(params);
        }

        if (params.cancopylink !== undefined) {
            self.copyuserLoginlink(params.cancopylink);
        }

        if (params.hascourseregister) {
            self.courseQuickRegistration(params);
        }

        if (params.copycampaignlink !== undefined) {
            self.copyCampaignLink(params, 'table .action-copy');
            self.copyCampaignLink(params, 'table .action-couponlink');
        }

        if (params.campaignformfield !== undefined) {
            self.vaildateCampaignFormFields();
            self.changeFormField(params);
            self.campaignHandler();
        }

        self.campaignCourse();
        return true;
    };

    Magic.prototype.campaignCourse = function() {
        var self = this;
        var campaignCourse = document.querySelector("#fitem_id_campaigncourse select#id_campaigncourse");
        if (campaignCourse) {
            campaignCourse.addEventListener("change", function(e) {
                self.setCampaigncourseGroupings(e.target.value, campaignCourse);
                self.enrolmentKeyFormFieldHandler(e.target.value);
            });
            self.setCampaigncourseGroupings(campaignCourse.value, campaignCourse);
            self.enrolmentKeyFormFieldHandler(campaignCourse.value);
        }

        var campaignGroup = document.querySelector("#fitem_id_campaigngroups select#id_campaigngroups");
        if (campaignGroup) {
            campaignGroup.addEventListener("change", function() {
                self.setCampaigncourseGroupings(campaignCourse.value, campaignCourse);
            });
        }
    };

    Magic.prototype.enrolmentKeyFormFieldHandler = function(courseid) {
        if (courseid == '0') {
            if ($("#id_courseenrolmentkey option[value='strict']").length > 0)  {
                $("#id_courseenrolmentkey option[value='strict']").remove();
            }
        } else {
            if ($("#id_courseenrolmentkey option[value='strict']").length == 0)  {
                var strictStr = Str.get_string('campaigns:strict', 'auth_magic');
                strictStr.done(function(localizedEditString) {
                    $("#id_courseenrolmentkey").append("<option value='strict'>" +  localizedEditString + "</option>");
                });
            }
        }
    };

    Magic.prototype.setCampaigncourseGroupings = function(courseid) {
        var self = this;
        var groupingElement = document.querySelector("#fitem_id_campaigngrouping select#id_campaigngrouping");
        $("#id_campaigncourseheadingcontainer .alert-info").remove();

        if (courseid == '0') {
            return false;
        }

        groupingElement.innerHTML = '';
        var disabledStr = Str.get_string('disabled', 'auth_magic');
        disabledStr.done(function(localizedEditString) {
            self.insertOptionSelectbox('0', localizedEditString, groupingElement);
        });


        Ajax.call([{
            methodname: 'auth_magic_get_course_groupings',
            args: {courseid: courseid, campaignid : $('input[name="campaignid"]').attr('value')},
            done: function(data) {
                data.groupings.forEach((list) => {
                    self.insertOptionSelectbox(list.id, list.name, groupingElement);
                });
                // Set the selected grouping.
                if (data.courseinfo.campaign_grouping && $("select#id_campaigngrouping option[value=" +
                    data.courseinfo.campaign_grouping + "]").length) {
                    groupingElement.value = data.courseinfo.campaign_grouping;
                }

                // Show separtegroup.
                var campaignCourseHandler = document.querySelector("#fitem_id_campaigncourse");
                if (campaignCourseHandler) {
                    if (!data.courseinfo.is_separtegroup) {
                        campaignCourseHandler.insertAdjacentHTML("afterend", data.courseinfo.separtegroupinfo);
                    }

                    if (!data.courseinfo.is_available_selfenrol) {
                        campaignCourseHandler.insertAdjacentHTML("afterend", data.courseinfo.selfenrolinfo);
                        self.hideGrouping();
                    } else {
                        self.showGrouping();
                    }
                }
            }
        }]);
    };

    Magic.prototype.hideGrouping = function() {
        $("#fitem_id_groupcapacity").hide();
        $("#fitem_id_campaigngrouping").hide();
    };

    Magic.prototype.showGrouping = function() {
        $("#fitem_id_groupcapacity").show();
        $("#fitem_id_campaigngrouping").show();
    };

    Magic.prototype.insertOptionSelectbox = function(value, text, element) {
        var option = document.createElement("option");
        option.value = value;
        option.text = text;
        element.add(option);
    };

    Magic.prototype.campaignHandler = function() {
        var self = this;
        var fieldHandler = document.querySelector("#id_securitysectioncontainer #fitem_id_emailconfirm select");
        if (fieldHandler) {
            fieldHandler.addEventListener("change", function(e) {
                self.campaignSubmissionHandler(e.target.value);
            });
            self.campaignSubmissionHandler(fieldHandler.value);
        }
    };

    Magic.prototype.campaignSubmissionHandler = function(value) {
        var submissonHandler = document.querySelector("#fitem_id_redirectaftersubmisson select#id_redirectaftersubmisson");
        var submissonHandlerOptions = Array.from(submissonHandler.options).map((opt) => opt.value);
        var addOption = document.createElement("option");
        addOption.value = "redirecturl";
        var redirecturlStr = Str.get_string('campaigns:redirecturl', 'auth_magic');
        redirecturlStr.done(function(localizedEditString) {
            addOption.text = localizedEditString;
        });

        if (value == '1') {
            if (submissonHandlerOptions.includes("redirecturl")) {
                submissonHandler.options.remove(submissonHandler.options.length - 1);
            }
        } else {
            if (!submissonHandlerOptions.includes("redirecturl")) {
                submissonHandler.options.add(addOption);
            }
        }
    };

    Magic.prototype.changeFormField = function(params) {
        var self = this;
        var fieldHandler = document.querySelector("#id_formfieldsection #fitem_id_linkfields select");
        if (fieldHandler) {
            fieldHandler.addEventListener("change", function(e) {
                params.relateduser = e.target.value;
                params.currentcampaignid = $('input[name="campaignid"]').attr('value');
                self.getUserFormField(params);
            });
        }
    };

    Magic.prototype.getUserFormField = function(params) {
        var self = this;
        var fragment =  Fragment.loadFragment('auth_magic', 'get_user_formfield', params.contextid, params);
        document.querySelectorAll("body")[0].classList.add("load-icon");
        fragment.done(function(html, js) {
            // Remove the mform based unique id.
            var currentMformID = $("#page-auth-magic-campaigns-edit .mform").attr("id");
            js = js.replace(/mform1_[A-Za-z0-9]+/g, currentMformID);
            var tempElement = document.createElement('div');
            tempElement.innerHTML = html;

            // Remove the collaspse section js.
            var collapseSectionsId = $("#" + currentMformID + " .collapsible-actions a").attr("id");
            if (collapseSectionsId) {
                js = js.replace(/#collapsesections[A-Za-z0-9]+/g, "#" + collapseSectionsId);
            }

            // Remove the filemanager js.
            var formfields = tempElement.querySelector('#id_formfieldsection');
            $("#page-auth-magic-campaigns-edit .mform #id_formfieldsection").replaceWith(formfields);
            // Implement the show expand formfield section .
            $("#id_formfieldsection a[data-toggle='collapse']").attr("aria-expanded", "true");
            $("#id_formfieldsection a[data-toggle='collapse']").removeClass("collapsed");
            $("#id_formfieldsection #id_formfieldsectioncontainer").addClass("show");
            Templates.runTemplateJS(js);
            $("#id_formfieldsection select#id_linkfields").val(params.relateduser);
            document.querySelectorAll("body")[0].classList.remove("load-icon");
        }.bind(self)).fail(notification.exception);
    };

    Magic.prototype.vaildateCampaignFormFields = function() {
        var self = this;
        var otherFiedlsHandler = document.querySelectorAll("#id_formfieldsection .campaign_otherform_fields select");
        if (otherFiedlsHandler) {
            otherFiedlsHandler.forEach((item) => {
                // When change the field handler element remove the others boxes.
                item.addEventListener("change", function(e) {
                    self.rearrangeFormFields(e.target);
                });
                self.disableEmptyField(item);
                self.rearrangeFormFields(item);
            });
        }
    };

    Magic.prototype.disableEmptyField = function(currentElement) {
        if (currentElement.options.length == 0) {
            setTimeout(function() {
                var option = document.createElement("option");
                option.value = "none";
                var none = Str.get_string('none', 'auth_magic');
                none.done(function(localizedEditString) {
                    option.text = localizedEditString;
                });
                option.selected = true;
                currentElement.options.add(option);
                currentElement.disabled = true;
            }, 500);
            var currentField = currentElement.getAttribute('data-field'); // Last name
            var currentFieldOption = document.querySelector("select[name='" + currentField + "_option']");
            var optionLabels = Array.from(currentFieldOption.options).map((opt) => opt.value);
            if (optionLabels.includes("50")) {
                currentFieldOption.options.remove(currentFieldOption.options.length - 1);
            }
        }
    };

    Magic.prototype.rearrangeFormFields = function(currentElement) {
        var self = this;
        var currentSelectval = currentElement.value;
        if (currentSelectval) {
            var currentField = currentElement.getAttribute('data-field'); // Get current form field.
            // Get form field select element.
            var changeSelectField = document.querySelector("#id_formfieldsection .campaign_otherform_fields #id_"
                + currentSelectval + "_otherfield");
            if (changeSelectField) {
                // Get form field all options.
                var changeSelectFieldValues = self.getFieldDefaultValues(currentSelectval);
                var changeSelectFieldDefault = changeSelectField.value;
                // Remove the form field all options.
                for (var i = changeSelectField.options.length; i >= 0; i--) {
                    changeSelectField.options.remove(i);
                }
                // Add the form field options (Removing the related one).
                // (one or more same element selected to the field, that field remove the them. )
                // Example (lastname & city field select to "username option")
                // then (Username field remove the lastname & city both options).
                for(var j = 0; j < changeSelectFieldValues.length; j++) {
                    let optionfield = document.querySelector("select[name='" +
                        Object.keys(changeSelectFieldValues[j]) + "_otherfield']");
                    if (optionfield != undefined && optionfield.value != currentSelectval) {
                        var option = document.createElement("option");
                        option.value = Object.keys(changeSelectFieldValues[j]);
                        option.text = Object.values(changeSelectFieldValues[j]);
                        if (changeSelectFieldDefault == option.value) {
                            option.selected = true;
                        }
                        changeSelectField.options.add(option);
                    }
                }
            }


            // Below set of codes to the insert the option. When modifiy to select.
            // Example (above (lastname & city field select to "username option"))
            // User changed to the  lastname field to other one.
            // Result: "Username" field add the lastname option.

            // Add the provious select value.
            var currentFieldPreValue = document.querySelector("input[name='" + currentField + "_otherfield_prevalue']");
            // Check provious value exit add the value.
            if (currentFieldPreValue != undefined && currentFieldPreValue.value != "") {
                var fieldPreValues = self.getFieldDefaultValues(currentFieldPreValue.value);
                var index = fieldPreValues.findIndex(function(obj) {
                    return obj.hasOwnProperty(currentField);
                });
                if (index) {
                    var changePreSelectField = document.querySelector("#id_formfieldsection .campaign_otherform_fields #id_" +
                        currentFieldPreValue.value + "_otherfield");
                    if (changePreSelectField) {
                        var option = document.createElement("option");
                        option.value = Object.keys(fieldPreValues[index]);
                        option.text = Object.values(fieldPreValues[index]);
                        changePreSelectField.insertBefore(option, changePreSelectField.options[index]);
                    }
                }
            }

            if (currentFieldPreValue) {
                currentFieldPreValue.value = currentSelectval;
            }

        }
    };

    Magic.prototype.getFieldDefaultValues = function(currentSelectval) {
        var changeSelectFieldValues = [];
        var changeSelectFieldOptions = document.querySelectorAll("input[name='" + currentSelectval + "_otherfield_value[]']");
        changeSelectFieldOptions.forEach(function(inputElement) {
            var val = {};
            val[inputElement.value] = inputElement.getAttribute('data-value');
            changeSelectFieldValues.push(val);
        });
        return changeSelectFieldValues;
    };

    Magic.prototype.courseQuickRegistration = function(params) {
        var uniqueid = "user-index-participants-" + params.courseid;
        var handleSelector = ".pagelayout-incourse [data-table-uniqueid=" + uniqueid + "]";
        var addHandler = document.querySelectorAll(handleSelector);
        if (addHandler) {
            var singleButton = document.createElement("div");
            singleButton.setAttribute("class", "singlebutton quickregister-button");

            // Create a form.
            var form = document.createElement("form");
            form.setAttribute("method", "get");
            form.setAttribute("action", params.url);
            form.setAttribute("id", "quickregister-button");

            // Create an input element for Full Name
            var courseblock = document.createElement("input");
            courseblock.setAttribute("type", "hidden");
            courseblock.setAttribute("name", "courseid");
            courseblock.setAttribute("value", params.courseid);

            var submit = document.createElement("input");
            submit.setAttribute("type", "submit");
            submit.setAttribute("value", params.strquickregister);
            submit.setAttribute("class", "btn btn-secondary my-1");

            form.appendChild(courseblock);
            form.appendChild(submit);
            singleButton.appendChild(form);
            addHandler[0].appendChild(singleButton);
        }
    };

    Magic.prototype.copyuserLoginlink = function(cancopylink) {
        var self = this;
        if (cancopylink) {
            var invitationlink = document.querySelectorAll("table.magicinvitationlink .magic-invitationlink");
            if (invitationlink) {
                invitationlink.forEach(function(items) {
                    items.addEventListener('click', function(e) {
                        e.preventDefault();
                        var userlogin = e.currentTarget.getAttribute("data-invitationlink");
                        self.copyText(userlogin);
                    });
                });
            }
        }
    };

    Magic.prototype.copyTextCliboard = function() {
        var self = this;
        var copyTextBlock = document.querySelectorAll(".auth-magic-block #copy-text")[0];
        if (copyTextBlock) {
            self.copyText(copyTextBlock.value, true);
            copyTextBlock.select();
        }
    };

    Magic.prototype.copyText = function(copytext, modal = false) {
        if (typeof (navigator.clipboard) == 'undefined') {
            var textArea = document.createElement("textarea");
            textArea.value = copytext;
            // Avoid scrolling to bottom
            textArea.style.top = "0";
            textArea.style.left = "0";
            textArea.style.position = "fixed";
            if (!modal) {
                document.body.appendChild(textArea);
            } else {
                document.querySelectorAll(".modal .modal-content")[0].appendChild(textArea);
            }
            textArea.focus();
            textArea.select();
            try {
                document.execCommand('copy');
            } catch (err) {
                return false;
            }
            if (!modal) {
                document.body.removeChild(textArea);
            } else {
                document.querySelectorAll(".modal .modal-content")[0].removeChild(textArea);
            }
        } else {
            navigator.clipboard.writeText(copytext);
        }
        return true;
    };

    Magic.prototype.copyCampaignLink = function(params, element) {
        var copyCampaign = document.querySelectorAll(element);
        copyCampaign.forEach((campaign) => {
            campaign.addEventListener('click', (e) => {
                e.preventDefault();

                var target = e.currentTarget;
                var link = target.dataset.campaignlink;
                params.campaignlink = link;
                params.campaign = true;

                ModalFactory.create({
                    title: params.campaigntitle,
                    type: ModalFactory.types.CANCEL,
                    body: Templates.render('auth_magic/modalbox', params),
                    large: true
                }).then(function(modal) {
                    modal.show();
                    modal.getRoot().on(ModalEvents.bodyRendered, function() {
                        var copyBoardButton = document.querySelectorAll(".auth-magic-block .copy-link-block #copy-cliboard")[0];
                        if (copyBoardButton) {
                            copyBoardButton.addEventListener("click", () => {
                                MAGIC.copyText(link);
                                var copyBoardElement = document.querySelectorAll(".auth-magic-block #copy-campaign-link")[0];
                                if (copyBoardElement) {
                                    copyBoardElement.select();
                                }
                            });
                        }
                    });
                    modal.getRoot().on(ModalEvents.hidden, function() {
                        modal.destroy();
                    });
                    return modal;
                }).catch(notification.exception);

            });
        });
    };

    Magic.prototype.getAuthMagicBody = function(params) {
        return Fragment.loadFragment('auth_magic', 'display_box_content', params.contextid, params);
    };

    Magic.prototype.getAuthMagicCampaignBody = function(params) {
        return Fragment.loadFragment('auth_magic', 'get_campaign_body', params.contextid, params);
    };


    Magic.prototype.displayAuthInfoBox = function(params) {
        var self = this;
        ModalFactory.create({
            title: params.strconfirm,
            type: ModalFactory.types.CANCEL,
            body: self.getAuthMagicBody(params),
            large: true
        }).then(function(modal) {
            modal.show();
            modal.getRoot().on(ModalEvents.bodyRendered, function() {
                var copyBoardButton = document.querySelectorAll(".auth-magic-block .copy-link-block #copy-cliboard")[0];
                if (copyBoardButton) {
                    copyBoardButton.addEventListener("click", self.copyTextCliboard.bind(self));
                }
            });
            modal.getRoot().on(ModalEvents.hidden, function() {
                modal.destroy();
                window.open(params.returnurl, '_self');
            });
            return modal;
        }).catch(notification.exception);
    };

    Magic.prototype.getAuthMagicBody = function(params) {
        return Fragment.loadFragment('auth_magic', 'display_box_content', params.contextid, params);
    };

    return {
        init: function(params) {
            return new Magic(params);
        }
    };

 });
