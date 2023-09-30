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
 * This is the model class for table "x2_sessions".
 *
 * @package application.models
 * @property integer $id
 * @property string $user
 * @property integer $lastUpdated
 * @property string $IP
 * @property integer $status
 */
class SessionLog extends CActiveRecord {
	/**
	 * Returns the static model of the specified AR class.
	 * @return Session the static model class
	 */
	public static function model($className=__CLASS__) {
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName() {
		return 'x2_session_log';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules() {
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('timestamp', 'numerical', 'integerOnly'=>true),
			array('user', 'length', 'max'=>40),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, user, timestamp', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations() {
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array();
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels() {
		return array(
			'id' => Yii::t('admin','Sesesion ID'),
			'user' => Yii::t('admin','User'),
			'timestamp' => Yii::t('admin','Timestamp'),
			'status' => Yii::t('admin','Session Event'),
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search() {
		// Warning: Please modify the following code to remove attributes that
		// should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('id',$this->id);
        $criteria->compare('sessionId',$this->id);
		$criteria->compare('user',$this->user,true);
		$criteria->compare('timestamp',$this->lastUpdated);
		$criteria->compare('status',$this->status);

		return new CActiveDataProvider(get_class($this), array(
			'criteria'=>$criteria,
		));
    }

    public static function logSession($user, $sessionId, $status){
        $sessionLog=Yii::app()->db->createCommand()
                ->select('sessionLog')
                ->from('x2_admin')
                ->where('id=1')
                ->queryScalar();
        if($sessionLog){
            $model=new SessionLog;
            $model->user=$user;
            $model->sessionId=$sessionId;
            $model->status=$status;
            $model->timestamp=time();
            $model->save();
        }
    }

    public static function parseStatus($status){
        $ret=$status;
        switch($status){
            case 'login':
                $ret='Logged In';
                break;
            case 'invisible':
                $ret="Went Invisible";
                break;
            case 'visible':
                $ret="Went Visible";
                break;
            case 'passiveTimeout':
                $ret='Timeout On Session Cleanup';
                break;
            case 'activeTimeout':
                $ret='Timeout On User Activity';
                break;
            case 'logout':
                $ret="Logged Out";
                break;
        }
        return $ret;
    }
}