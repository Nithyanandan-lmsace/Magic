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
 * Magic campaign welcome and follow up message placeholders define js.
 *
 * @module   auth_magic
 * @copyright  2023 bdecent gmbh <https://bdecent.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define([], function() {

    return {

        /**
         * Setup the classes to editors works with placeholders.
         */
        init: function() {
            var messageVars = this;
            var notificationheader = document.getElementById('id_welcomemessagesection_editor');
            if (notificationheader !== null) {
                notificationheader.addEventListener('click', function() {
                    var EditorInput = document.getElementById('id_welcomemessagecontent_editoreditable');
                    if (EditorInput !== null) {
                        messageVars.insertCaretActive(EditorInput);
                    }
                });
            }

            var notificationheader = document.getElementById('id_followupmessagesection');
            if (notificationheader !== null) {
                notificationheader.addEventListener('click', function() {
                    var EditorInput = document.getElementById('id_followupmessagecontent_editoreditable');
                    if (EditorInput !== null) {
                        messageVars.insertCaretActive(EditorInput);
                    }
                });
            }

            var notificationheader = document.getElementById('id_aftersubmisson');
            if (notificationheader !== null) {
                notificationheader.addEventListener('click', function() {
                    var EditorInput = document.getElementById('id_submissioncontent_editoreditable');
                    if (EditorInput !== null) {
                        messageVars.insertCaretActive(EditorInput);
                    }
                });
            }



            var targetNode = document.querySelector('textarea[id$=_editor]');
            if (targetNode !== null) {
                var observer = new MutationObserver(function() {
                    if (targetNode.style.display == 'none') {
                        setTimeout(initIframeListeners, 100);
                    }
                });
                observer.observe(targetNode, {attributes: true, childList: true});
            }

            const initIframeListeners = () => {
                var iframes = document.querySelectorAll('[data-fieldtype="editor"] iframe');
                if (iframes === null || !iframes.length) {
                    return false;
                }
                iframes.forEach((iframe) => {
                    iframe.contentDocument.addEventListener('click', function(e) {
                        var currentFrame = e.target;
                        iframes.forEach((frame) => {
                            var frameElem = frame.contentDocument.querySelector(".insertatcaretactive");
                            if (frameElem !== null) {
                                frameElem.classList.remove("insertatcaretactive");
                            }
                        });

                        var contentBody = currentFrame.querySelector('body');
                        if (contentBody !== null) {
                            contentBody.classList.add("insertatcaretactive");
                        }
                    });
                });

                return true;
            };


            var clickforword = document.getElementsByClassName('campaign-message-clickforword');
            for (var i = 0; i < clickforword.length; i++) {
                clickforword[i].addEventListener('click', function(e) {
                    e.preventDefault(); // To prevent the default behaviour of a tag.
                    var tinyEditor = false;
                    var content = "{" + this.getAttribute('data-text') + "}";
                    var iframes = document.querySelectorAll('[data-fieldtype="editor"] iframe');
                    if (iframes === null || !iframes.length) {
                        messageVars.insertAtCaret(content);
                        return true;
                    }
                    iframes.forEach(function(frame) {
                        var frameElem = frame.contentDocument.querySelector(".insertatcaretactive");
                        if (frameElem !== null) {
                            var contentBody = frame.contentDocument.querySelector('body');
                            if (contentBody !== null) {
                                contentBody.classList.add("insertatcaretactive");
                                var id = contentBody.dataset.id;
                                var editor = window.tinyMCE.get(id);
                                tinyEditor = editor;
                            }
                        }
                    });

                    if (tinyEditor) {
                        tinyEditor.selection.setContent(content);
                    } else {
                        messageVars.insertAtCaret(content);
                    }

                    return true;
                });
            }
        },

        insertCaretActive: function(EditorInput) {
            var caret = document.getElementsByClassName("insertatcaretactive");
            for (var j = 0; j < caret.length; j++) {
                caret[j].classList.remove("insertatcaretactive");
            }
            EditorInput.classList.add("insertatcaretactive");
        },

        /**
         * Insert the placeholder in selected caret place.
         * @param  {string} myValue
         */
        insertAtCaret: function(myValue) {
            var caretelements = document.getElementsByClassName("insertatcaretactive");
            var sel, range;
            for (var n = 0; n < caretelements.length; n++) {
                var thiselem = caretelements[n];

                if (typeof thiselem.value === 'undefined' && window.getSelection) {
                    sel = window.getSelection();
                    if (sel.getRangeAt && sel.rangeCount) {
                        range = sel.getRangeAt(0);
                        range.deleteContents();
                        range.insertNode(document.createTextNode(myValue));

                        for (let position = 0; position != (myValue.length + 1); position++) {
                            sel.modify("move", "right", "character");
                        }
                    }
                } else if (typeof thiselem.value === 'undefined' && document.selection && document.selection.createRange) {
                    range = document.selection.createRange();
                    range.text = myValue;
                }

                if (typeof thiselem.value !== 'undefined') {
                    if (document.selection) {
                        // For browsers like Internet Explorer.
                        thiselem.focus();
                        sel = document.selection.createRange();
                        sel.text = myValue;
                        thiselem.focus();
                    } else if (thiselem.selectionStart || thiselem.selectionStart == '0') {
                        // For browsers like Firefox and Webkit based.
                        var startPos = thiselem.selectionStart;
                        var endPos = thiselem.selectionEnd;
                        thiselem.value = thiselem.value.substring(0, startPos)
                            + myValue + thiselem.value.substring(endPos, thiselem.value.length);
                        thiselem.focus();
                        thiselem.selectionStart = startPos + myValue.length;
                        thiselem.selectionEnd = startPos + myValue.length;
                        thiselem.focus();
                    } else {
                        thiselem.value += myValue;
                        thiselem.focus();
                    }
                }
            }
        },
    };
});
