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
 * @package application.components.sortableWidget
 */
class QuotesWidget extends TransactionalViewWidget {
    protected $historyType = 'quotes';

     
    public $sortableWidgetJSClass = 'QuotesWidget';

    public function getCreateButtonTitle () {
        return Yii::t('app', 'New quote');
    }

    public function getIcon () {
        return X2Html::x2icon ('quotes');
    }

    public function getPackages () {
        if (!isset ($this->_packages)) {
            $this->_packages = array_merge (parent::getPackages (), array (
                'QuotesWidgetJS' => array(
                    'baseUrl' => Yii::app()->request->baseUrl,
                    'js' => array(
                        'js/sortableWidgets/QuotesWidget.js',
                    ),
                    'depends' => array ('TransactionalViewWidgetJS')
                ),
            ));
        }
        return $this->_packages;
    }

    private static $_JSONPropertiesStructure;
    public static function getJSONPropertiesStructure () {
        if (!isset (self::$_JSONPropertiesStructure)) {
            self::$_JSONPropertiesStructure = array_merge (
                parent::getJSONPropertiesStructure (),
                array (
                    'label' => 'Quotes',
                )
            );
        }
        return self::$_JSONPropertiesStructure;
    }

    public function getGridViewConfig () {
        if (!isset ($this->_gridViewConfig)) {
            $this->_gridViewConfig = array_merge (
                parent::getGridViewConfig (),
                array (
                    'columnOverrides' => array (
                        'assignedTo' => array (
                            'header' => Yii::t('app', 'Created By'),
                        ),
                    ),
                )
            );
            $this->_gridViewConfig['specialColumns'] = array_merge (
                $this->_gridViewConfig['specialColumns'],
                array (
                    'actionDescription'=>array(
                        'header'=>$this->getActionDescriptionHeader (),
                        'name'=>'actionDescription',
                        // get the actions model so that the action description gets loaded
                        'value'=> '
                            ($data = Actions::model ()->findByPk ($data->id)) ?
                                ($data->type === "quotesDeleted" ? 
                                    $data->actionDescription :
                                    $data->renderInlineViewLink ()) : null',

                        'type'=>'raw',
                        'filter' => false,
                    ),
                )
            );
        }
        return $this->_gridViewConfig;
    }

    protected function getActionDescriptionHeader () {
        return Yii::t('actions', 'Quote');
    }

}

?>
