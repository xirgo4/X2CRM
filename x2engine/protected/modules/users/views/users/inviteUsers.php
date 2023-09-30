<?php
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

Yii::app()->clientScript->registerCss('inviteUsersCss',"

#invitation-email-list {
    width: 80%;
    max-width: 600px;
    height: 150px;
}

");

$menuOptions = array(
    'feed', 'admin', 'create', 'invite',
);
$this->insertMenu($menuOptions);

?>
<div class="page-title icon users"><h2>
    <?php echo Yii::t('users','Invite {users} to X2Engine', array(
        '{users}' => Modules::displayName(),
    )); ?>
</h2></div>

<form method="POST">
<div class="form">
<h2><?php echo Yii::t('users','Instructions'); ?></h2>
<?php echo Yii::t('users','Please enter a list of e-mails separated by commas.'); ?>
	<div class="row"><textarea id='invitation-email-list' name="emails"></textarea></div>
	<div class="row"><input type="submit" value="<?php echo Yii::t('app','Submit');?>" class="x2-button"></div>
</div>
<?php echo X2Html::csrfToken(); ?>
</form>
