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

/**
 * @file 1382469641-actionDescription-field-permission.php
 *
 * Migration script that adds field-level permissions to Actions.actionDescription
 */


$run = function() {
$roleIDs = Yii::app()->db->createCommand()
                    ->select('id')
                    ->from('x2_roles')
                    ->queryColumn();

            if(is_array($roleIDs) && !empty($roleIDs)){
                $fieldId = Yii::app()->db->createCommand()
                        ->select('id')
                        ->from('x2_fields')
                        ->where('modelName=:modelName AND fieldName=:fieldName', array(
                            ':modelName' => 'Actions',
                            ':fieldName' => 'actionDescription'
                                )
                        )
                        ->queryScalar();

                $params = array();
                $records = array();
                $paramCount = 0;
                $cols = array('roleId', 'fieldId', 'permission');

                $permission = '2';
                $fieldId = (string) $fieldId;


                foreach($roleIDs as $roleId){
                    $record = array();
                    foreach($cols as $col){
                        $param = ":$col$paramCount";
                        $params[$param] = ${$col};
                        $record[] = $param;
                    }
                    $records[] = '('.implode(',', $record).')';
                    $roleIdParam = ":roleId$paramCount";
                    $paramCount++;
                }

                $sql = 'INSERT INTO `x2_role_to_permission` (`'.implode('`,`',$cols).'`) VALUES '.implode(',',$records);
                $command = Yii::app()->db->createCommand($sql);
                $command->execute($params);
            }
        };
$run();


?>
