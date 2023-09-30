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

 Yii::import ('application.components.sortableWidget.ProfileGridViewWidget');

/**
 * @package application.components
 */
class NewWebLeadsGridViewProfileWidget extends ProfileGridViewWidget {

    public $canBeDeleted = true;

    public $defaultTitle = 'New Web Leads';

    public $relabelingEnabled = true;

    public $template = '<div class="submenu-title-bar widget-title-bar">{widgetLabel}{closeButton}{minimizeButton}{settingsMenu}</div>{widgetContents}';
 
    private static $_JSONPropertiesStructure;

    protected $_viewFileParams;

    /**
     * @var array the config array passed to widget ()
     */
    private $_gridViewConfig;

	public function init ($skipGridViewInit = false) {
        parent::init ($skipGridViewInit);

        // set default sort order if this widget is new
        $gridId = $this->getWidgetKey ();
        $isNew = $this->getWidgetProperty ('new');
        if ($isNew) {
            $this->asa ('GridViewDbSettingsBehavior')->uid = $gridId;
            $this->asa ('GridViewDbSettingsBehavior')->saveSetting ('sort', 'createDate.desc');
            // We're about to modify the profile again in setWidgetProperty. This prevents change
            // made in saveSetting from being wiped out
            $this->profile->refresh ();
            $this->setWidgetProperty ('new', 0);
        }
    }

    public function behaviors () {
        return array_merge (parent::behaviors (), array (
            'GridViewDbSettingsBehavior' => 'GridViewDbSettingsBehavior',
        ));
    }

    protected function getModel () {
        if (!isset ($this->_model)) {
            $this->_model = new Contacts ('search',
                $this->widgetKey, $this->getWidgetProperty ('dbPersistentGridSettings'));
            $this->afterGetModel ();
        }
        return $this->_model;
    }

    public static function getJSONPropertiesStructure () {
        if (!isset (self::$_JSONPropertiesStructure)) {
            self::$_JSONPropertiesStructure = array_merge (
                parent::getJSONPropertiesStructure (),
                array (
                    'label' => 'New Web Leads',
                    'dbPersistentGridSettings' => 1,
                    'hidden' => 0,
                    'new' => 1
                )
            );
        }
        return self::$_JSONPropertiesStructure;
    }

    public function getDataProvider () {
        if (!isset ($this->_dataProvider)) {
            $criteria = new CDbCriteria;
            $criteria->distinct = true;
            $criteria->join = "
                JOIN x2_events ON x2_events.associationType='Contacts' AND 
                    x2_events.associationId=t.id    
            ";
            $resultsPerPage = self::getJSONProperty (
                $this->profile, 'resultsPerPage', $this->widgetType, $this->widgetUID);
            $this->_dataProvider = $this->model->searchAll ($resultsPerPage, $criteria);
        }
        return $this->_dataProvider;
    }

    /**
     * @return array the config array passed to widget ()
     */
    public function getGridViewConfig () {
        if (!isset ($this->_gridViewConfig)) {
            $this->_gridViewConfig = array_merge (
                parent::getGridViewConfig (),
                array (
                    'enableQtips' => true,
                    'qtipManager' => array (
                        'X2GridViewQtipManager',
                        'loadingText'=> addslashes(Yii::t('app','loading...')),
                        'qtipSelector' => ".contact-name"
                    ),
                    'moduleName' => 'Contacts',
                    'defaultGvSettings'=>array(
                        'gvCheckbox' => 30,
                        'name' => 125,
                        'email' => 165,
                        'leadSource' => 83,
                    ),
                    'specialColumns'=>array(
                        'name'=>array(
                            'name'=>'name',
                            'header'=>Yii::t('contacts','Name'),
                            'value'=>'$data->link',
                            'type'=>'raw',
                        ),
                    ),
                    'massActions'=>array(
                        'MassDelete', 'MassTag', 'MassUpdateFields', 
                        'MassAddToList', 'NewListFromSelection',
                    ),
                    'enableTags'=>true,
                )
            );
        }
        return $this->_gridViewConfig;
    }

}
?>
