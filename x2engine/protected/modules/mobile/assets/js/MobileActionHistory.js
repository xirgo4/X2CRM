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

x2.MobileActionHistory = (function () {

function MobileActionHistory (argsDict) {
    var argsDict = typeof argsDict === 'undefined' ? {} : argsDict;
    var defaultArgs = {
        DEBUG: x2.DEBUG && false
    };
    auxlib.applyArgs (this, defaultArgs, argsDict);
    x2.Widget.call (this, argsDict);
    this.init ();
}

MobileActionHistory.prototype = auxlib.create (x2.Widget.prototype);
 
MobileActionHistory.prototype.setUpCommentPublish = function () {
    var that = this;
    var form$ = $.mobile.activePage.find ('.publisher-comment-form');
    var togglePublisher$ = $.mobile.activePage.find ('#publisher-menu-button');
    this.form$ = form$;
    that.form$.off ('change.setUpCommentPublish').on ('change.setUpCommentPublish', function () {
        $.mobile.loading ('show');
        x2.mobileForm.submitWithFiles (
            that.form$, 
            function (data) {
                $.mobile.activePage.append ($(data).find ('.refresh-content'));
                x2.main.refreshContent ();
                that.form$.find ('input[type="text"]').val ('');
                $.mobile.loading ('hide');
            }, function (jqXHR, textStatus, errorThrown) {
                $.mobile.loading ('hide');
                x2.main.alert (textStatus, 'Error');
            });
        
    });
        
    that.form$.on('submit',function(e){
        e.preventDefault();
    });
    
};

MobileActionHistory.prototype.setUpPublisher = function () {
    var that = this;
    var publisher$ = $.mobile.activePage.find ('.publisher-menu');
    var buttons$ = publisher$.find ('ul li');
    var togglePublisher$ = $.mobile.activePage.find ('.publisher-menu-button');

    // set up open/close behavior of publisher
    this.publisherIsActive = false;
    var clickOutEvt;
    togglePublisher$.click (function () {
        // add classes which trigger animation
        publisher$.toggleClass ('active', !that.publisherIsActive);
        $(this).toggleClass ('inactive', that.publisherIsActive);
        $(this).toggleClass ('active', !that.publisherIsActive);
        if (that.publisherIsActive)
            $('.ui-content').trigger ('scroll.footerFix')
        that.publisherIsActive = !that.publisherIsActive;

        // allow click outside event to trigger menu close
        if (clickOutEvt) publisher$.unbind (clickOutEvt);
        clickOutEvt = auxlib.onClickOutside (publisher$, function () {
            if (that.publisherIsActive)
                togglePublisher$.click (); 
        }, true);
        return false;
    });
         
    that.setUpCommentPublish ();
};

MobileActionHistory.prototype.init = function () {
    var that = this;
    x2.main.onPageShow (function(){       
        that.setUpPublisher ();
    });
};

return MobileActionHistory;

}) ();
