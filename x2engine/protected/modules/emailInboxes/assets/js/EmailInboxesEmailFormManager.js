/***********************************************************************************
 * X2Engine Open Source Edition is a customer relationship management program developed by
 * X2 Engine, Inc. Copyright (C) 2011-2017 X2 Engine Inc.
 * 
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License version 3 as published by the
 * Free Software Foundation with the addition of the following permission added
 * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
 * IN WHICH THE COPYRIGHT IS OWNED BY X2ENGINE, X2ENGINE DISCLAIMS THE WARRANTY
 * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
 * details.
 * 
 * You should have received a copy of the GNU Affero General Public License along with
 * this program; if not, see http://www.gnu.org/licenses or write to the Free
 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301 USA.
 * 
 * You can contact X2Engine, Inc. P.O. Box 610121, Redwood City,
 * California 94061, USA. or at email address contact@x2engine.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2 Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2 Engine".
 **********************************************************************************/

x2.EmailInboxesEmailFormManager = (function () {

function EmailInboxesEmailFormManager (argsDict) {
    var argsDict = typeof argsDict === 'undefined' ? {} : argsDict;
    var defaultArgs = {
        DEBUG: x2.DEBUG && false,
        enableResizability: false,
        disableHistory: false,
        translations: {
            'Forwarded message': 'Forwarded message',
            'From': 'From',
            'Date': 'Date',
            'Subject': 'Subject',
            'To': 'To'
        },
        signature: ''
    };
    this._emailInboxGridViewManager = $('#email-list').data ('emailInboxesGridSettings');
    argsDict.translations = $.extend (defaultArgs.translations, argsDict.translations);
    auxlib.applyArgs (this, defaultArgs, argsDict);
    x2.InlineEmailEditorManager.call (this, argsDict);
}

EmailInboxesEmailFormManager.prototype = auxlib.create (x2.InlineEmailEditorManager.prototype);

EmailInboxesEmailFormManager.prototype.addForwardingHeader = function (
    message$, from, date, subject, to) {

    var header$ = $('<div>', {
        text: '---------- ' + this.translations['Forwarded message'] + ' ----------'
    })
    header$.append ($('<div>', {
        text: this.translations.From + ': ' + from
    }));
    header$.append ($('<div>', {
        html: this.translations['Date'] + ': ' + date
    }));
    header$.append ($('<div>', {
        text: this.translations['Subject'] + ': ' + subject
    }));
    header$.append ($('<div>', {
        text: this.translations['To'] + ': ' + to
    }));
    header$.append ($('<br><br>'));
    return header$.append (message$);
};

/**
 * Wrap html in styled blockquotes 
 */
EmailInboxesEmailFormManager.prototype.quoteText = function (text, date, author) {
    if (author) {
        var text$ = $('<div>', {
            html: date + ', ' + auxlib.htmlEncode (author) + ': '
        });
        return text$.add ($('<blockquote>').append (text));
    } else {
        return $('<blockquote>').append (text);
    }
};

/**
 * Overrides parent method 
 */
EmailInboxesEmailFormManager.prototype.clearForm = function () {
    x2.InlineEmailEditorManager.prototype.clearForm.call (this);
    $('#reply-form').hide ();
    $('#email-message').val ('');
    $('input[name="InlineEmail[to]"]').val('').blur ();
    $('input[name="InlineEmail[cc]"]').val('').blur ();
    $('input[name="InlineEmail[subject]"]').val('').blur ();
    $('input[name="InlineEmail[bcc]"]').val('').blur ();
};

/**
 * Overrides parent method 
 */
EmailInboxesEmailFormManager.prototype.afterSend = function (data) {
    x2.topFlashes.displayFlash (data.message, 'success');
    this.clearForm ();
    x2.Notifs.updateHistory();
    if (this.disableHistory) {
        this.element.hide ();
    } else {
        history.back ();
    }
};

EmailInboxesEmailFormManager.prototype.closeForm = function () {
    if ($('#reply-form').is (':visible')) {
        this.clearForm ();
        $('#reply-form').hide ();
    }
};

/**
 * Overrrides parent method. Since the reply and compose pages are never accessed on page load, 
 * browser state can be used to access the previously viewed page.
 */
EmailInboxesEmailFormManager.prototype._setUpCloseFunctionality = function () {
    var that = this;
    this.element.find ('.cancel-send-button').click (function () {
        if (that.disableHistory) {
            that.element.hide ();
        } else {
            history.back ();
        }
        that.clearForm ();
        return false;
    });
};


return EmailInboxesEmailFormManager;

}) ();

