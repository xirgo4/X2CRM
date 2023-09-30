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

Yii::import('application.modules.users.models.*');

/**
 * 
 * @package application.tests.unit.models
 * @author Demitri Morgan <demitri@x2engine.com>
 * @coauthor Karol Josef Bustamante <josef@x2engine.com>
 */
class SessionTokenTest extends X2DbTestCase {

    public $fixtures = array(
        'session' => 'SessionToken',
    );

    public static function referenceFixtures() {
        return array(
            'user' => 'User',
            'role' => 'Roles',
            'roleToUser' => 'RoleToUser'
        );
    }

    public function testCleanUpSessions() {
        Yii::app()->cache->flush();
        // Prepare expected data:
        $sessionCounts = array(
            'testWorkingUser' => 1,
            'testDeactivatedUser' => 1,
            'testNotExistsUser' => 0,
        );
        foreach(array_keys($sessionCounts) as $alias) {
            $sessionIds[$alias] = $this->session($alias)->id;
        }
        
        $defaultTimeout = 518400; //60*60*24*6 6 days;
        Yii::app()->settings->timeout = $defaultTimeout;
        
        SessionToken::cleanUpSessions();
        // Sessions 1 and 2 should still be there
        // Session 3 should be gone
        foreach($sessionCounts as $alias => $count){
            $this->assertEquals((integer)$count, SessionToken::model()->countByAttributes(array('id'=>$sessionIds[$alias])),"$alias did not get deleted");
        }
    }
}

?>
