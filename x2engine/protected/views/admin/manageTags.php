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
?>
<div class="page-title"><h2><?php echo Yii::t('admin', 'Tag Manager'); ?></h2></div>
<div class="form">
    <div style="width:600px;">
        <?php echo Yii::t('admin', "This is a list of all tags currently used within the app."); ?><br />
        <?php echo Yii::t('admin', "To delete a tag, click the delete link in the grid below.  This will remove any relationship between that tag and records, but textual references to the tag will be preserved.") ?><br /><br />
        <?php echo Yii::t('admin', 'To delete all tags, use the "Delete All" button at the bottom of the grid.'); ?>
    </div>
</div>
<?php
$this->widget('zii.widgets.grid.CGridView', array(
    'id' => 'tags-grid',
    'baseScriptUrl' => Yii::app()->request->baseUrl.'/themes/'.Yii::app()->theme->name.'/css/gridview',
    'template' => '<div class="page-title"><h2>'.Yii::t('admin', 'Tags').'</h2><div class="title-bar">'
    .'{summary}</div></div>{items}{pager}',
    'dataProvider' => $dataProvider,
    'columns' => array(
        array(
            'header' => Yii::t('admin','Tag'),
            'name' => 'tag',
            'type' => 'raw',
            'value' => "CHtml::link(
                CHtml::encode(\$data->tag), 
                array(
                    '/search/search',
                    'term'=>CHtml::encode (\$data->tag)
                ), 
                array('class'=>'x2-link x2-tag')
            )"
        ),
        array(
            'header' => Yii::t('admin','# of Records'),
            'type' => 'raw',
            'value' => "X2Model::model('Tags')->countByAttributes(array('tag'=>\$data->tag))"
        ),
        array(
            'header' => Yii::t('admin','Delete Tag'),
            'type' => 'raw',
            'value' => "CHtml::link(Yii::t('admin','Delete Tag'),'#',array('class'=>'x2-button', 'csrf'=>true,'submit'=>'deleteTag?tag='.urlencode (\$data->tag),'confirm'=>Yii::t('admin','Are you sure you want to delete this tag?')))"
        ),
    ),
));
?><br>
<?php 
echo CHtml::link(
    Yii::t('admin', 'Delete All'), '#',
    array(
        'class' => 'x2-button',
        'submit' => 'deleteTag?tag=all', 
        'confirm' => Yii::t('admin','Are you sure you want to delete all tags?'),
        'csrf' => true
    )); ?>
