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

Yii::import('application.modules.accounts.models.*');
Yii::import('application.modules.actions.models.*');
Yii::import('application.modules.contacts.models.*');
Yii::import('application.modules.opportunities.models.*');
Yii::import('application.modules.quotes.models.*');

/**
 * Test certain features specific to {@link X2Model}.
 *
 * Note, this will use subclasses. This is a shortcut that should probably in the
 * future be replaced with mocks, fake tables and special fixtures in order to
 * generally test {@link X2Model} without relying on ephemeral data.
 *
 * @package application.tests.unit.models
 * @author Demitri Morgan <demitri@x2engine.com>
 */
class X2ModelTest extends X2DbTestCase {

    public $fixtures = array(
        'contact' => 'Contacts',
        'account' => 'Accounts',
        'modules' => 'Modules',
    );

    private $_nameFields;

    public static function setUpBeforeClass () {
        $leadSourceDropdown = Dropdowns::model ()->findByPk (103);
        $leadSourceDropdown->multi = 0;
        if (!$leadSourceDropdown->save ()) throw new CException ('failed to restore dropdown');
        return parent::setUpBeforeClass ();
    }

    public static function tearDownAfterClass () {
        $leadSourceDropdown = Dropdowns::model ()->findByPk (103);
        $leadSourceDropdown->multi = 0;
        $leadSourceDropdown->save ();
        return parent::tearDownAfterClass ();
    }

    public function nameFields() {
        if (!isset($this->_nameFields)) {
            $this->_nameFields = array();
            $this->_nameFields[] = Fields::model()->findByAttributes(array('fieldName' => 'firstName', 'modelName' => 'Contacts'));
            $this->_nameFields[] = Fields::model()->findByAttributes(array('fieldName' => 'lastName', 'modelName' => 'Contacts'));
        }
        return $this->_nameFields;
    }

    public function setDefaultName() {
        list($firstName, $lastName) = $this->nameFields();
        $firstName->defaultValue = 'Gustavo';
        $lastName->defaultValue = 'Fring';
        $firstName->save();
        $lastName->save();
        Yii::app()->cache->flush();
    }

    public function resetNameFields() {
        list($firstName, $lastName) = $this->nameFields();
        $firstName->defaultValue = '';
        $lastName->defaultValue = '';
        $firstName->save();
        $lastName->save();
        Yii::app()->cache->flush();
    }

    public function setUp() {
        parent::setUp();
        $this->setDefaultName();
    }

    public function tearDown() {
        $this->resetNameFields();
        parent::tearDown();
    }
    
    public function testModel(){
        //Test lookup by class name
        $this->assertInstanceOf('Contacts',X2Model::model('Contacts'));
        
        // Test lookup with case insensitive class name
        $this->assertInstanceOf('Actions', X2Model::model('actions'));
        
        //Test Quote weird pluralization rules
        $this->assertInstanceOf('Quote',X2Model::model('quotes'));
        $this->assertInstanceOf('Quote',X2Model::model('Quote'));
        
        //Test Product weird pluralization rules
        $this->assertInstanceOf('Product',X2Model::model('products'));
        $this->assertInstanceOf('Product',X2Model::model('Product'));
        
        //Test Opportunity weird pluralization rules
        $this->assertInstanceOf('Opportunity', X2Model::model('opportunities'));
        $this->assertInstanceOf('Opportunity', X2Model::model('Opportunity'));
        
        $this->setExpectedException('CHttpException');
        X2Model::model('obviously bad model name');
    }
    
    public function testModel2(){
        //Test lookup by class name
        $this->assertInstanceOf('Contacts',X2Model::model2('Contacts'));
        
        // Test lookup with case insensitive class name
        $this->assertInstanceOf('Actions', X2Model::model2('actions'));
        
        //Test Quote weird pluralization rules
        $this->assertInstanceOf('Quote',X2Model::model2('quotes'));
        $this->assertInstanceOf('Quote',X2Model::model2('Quote'));
        
        //Test Product weird pluralization rules
        $this->assertInstanceOf('Product',X2Model::model2('products'));
        $this->assertInstanceOf('Product',X2Model::model2('Product'));
        
        //Test Opportunity weird pluralization rules
        $this->assertInstanceOf('Opportunity', X2Model::model2('opportunities'));
        $this->assertInstanceOf('Opportunity', X2Model::model2('Opportunity'));
        
        //Model2 does not throw exceptions
        $this->assertFalse(X2Model::model2('obviously bad model name'));
    }
    
    public function testGetRecordName(){
        $this->assertEquals('action', X2Model::getRecordName('Actions'));
        $this->assertEquals('actions', X2Model::getRecordName('Actions', true));
        
        $this->assertEquals('case',X2Model::getRecordName('Services'));
        $this->assertEquals('cases',X2Model::getRecordName('Services', true));
        
        $this->assertEquals('opportunity', X2Model::getRecordName('Opportunity'));
        $this->assertEquals('opportunities', X2Model::getRecordName('Opportunity', true));
        
        $this->assertEquals('list item', X2Model::getRecordName('X2List'));
        $this->assertEquals('list items', X2Model::getRecordName('X2List', true));
        
        $this->assertEquals('nonexistent', X2Model::getRecordName('nonexistent'));
        $this->assertEquals('nonexistent', X2Model::getRecordName('nonexistent', true));
    }

    /**
     * Test setting default values in new records
     */
    public function testDefaultValues() {
        foreach (X2Model::model('Contacts')->getFields() as $field) {
            // Retrieve new values:
            $field->refresh();
        }

        // Setting default values in the constructor
        $contact = new Contacts;
        $this->assertEquals('Gustavo', $contact->firstName);
        $this->assertEquals('Fring', $contact->lastName);

        // Setting default values in setX2Fields
        $contact->firstName = '';
        $contact->lastName = '';
        $input = array();
        $contact->setX2Fields($input);
        $this->assertEquals('Gustavo', $contact->firstName);
        $this->assertEquals('Fring', $contact->lastName);
    }

    public function testFindByEmail() {
        $c = Contacts::model()->findByEmail($this->contact('testAnyone')->email);
        $this->assertTrue((bool) $c);
        $this->assertEquals($this->contact('testAnyone')->id, $c->id);
    }
    
    public function testFindByAttributes(){
        $c = Contacts::model()->findByAttributes(array('email'=>$this->contact('launchedEmailCampaign1')->email));
        $this->assertTrue((bool) $c);
        $this->assertEquals($this->contact('launchedEmailCampaign1')->id, $c->id);
        
        $c->markAsDuplicate();
        $c->refresh();
        $this->assertEquals($this->contact('launchedEmailCampaign1')->id, $c->id);
        
        $c2 = Contacts::model()->findByAttributes(array('email'=>$this->contact('launchedEmailCampaign1')->email));
        $this->assertTrue((bool) $c2);
        $this->assertEquals($this->contact('launchedEmailCampaign3')->id, $c2->id);
        
        $c3 = Contacts::model()->findByAttributes(array('id'=>$this->contact('launchedEmailCampaign1')->id));
        $this->assertFalse((bool) $c3);
    }

    /**
     * A cursory test of the auto-ref update for the link-type fields refactor.
     */
    public function testUpdateNameIdRefs() {
        $account = $this->account('testQuote');
        $contact = $this->contact('testAnyone');
        // Test name change:
        $account->refresh();
        $account->name = 'A smouldering crater left behind by the G-man';
        $account->save();
        $contact->refresh();
        $this->assertEquals(Fields::nameId($account->name, $account->id), $contact->company);
        // Test deletion:
        $account->delete();
        $contact->refresh();
        $this->assertEquals($account->name, $contact->company);
    }

    public function testMassUpdateNameId() {
        $contact = $this->contact('testAnyone');
        // First, need to break all the nameIds...
        Contacts::model()->updateAll(array('nameId' => null));
        // Try with the mass update method, one ID:
        X2Model::massUpdateNameId('Contacts', array($contact->id));
        $contact->refresh();
        $this->assertEquals(Fields::nameId($contact->name, $contact->id), $contact->nameId);
        // Again, but with the "ids" parameter an int instead of an array
        X2Model::massUpdateNameId('Contacts', $contact->id);
        $contact->refresh();
        $this->assertEquals(Fields::nameId($contact->name, $contact->id), $contact->nameId);
        // Try again, multiple records:
        $contact2 = $this->contact('testUser');
        Contacts::model()->updateAll(array('nameId' => null));
        X2Model::massUpdateNameId('Contacts', array($contact->id, $contact2->id));
        $contact->refresh();
        $contact2->refresh();
        $this->assertEquals(Fields::nameId($contact->name, $contact->id), $contact->nameId);
        $this->assertEquals(Fields::nameId($contact2->name, $contact2->id), $contact2->nameId);
        // Try one last time, all records:
        Contacts::model()->updateAll(array('nameId' => null));
        X2Model::massUpdateNameId('Contacts');
        $contact->refresh();
        $contact2->refresh();
        $this->assertEquals(Fields::nameId($contact->name, $contact->id), $contact->nameId);
        $this->assertEquals(Fields::nameId($contact2->name, $contact2->id), $contact2->nameId);
    }

    public function testCompareAttribute () {
        $contact1 = $this->contact ('testAnyone'); 
        $contact2 = $this->contact ('testUser'); 
        $contact1->leadSource = 'Google';
        $this->assertSaves ($contact1);
        $contact2->leadSource = 'Facebook';
        $this->assertSaves ($contact2);

        $criteria = new CDbCriteria;
        $searchModel = new Contacts;
        $searchModel->leadSource = 'Google';
        $compareAttribute = TestingAuxLib::setPublic (
            $searchModel, 'compareAttribute', false, function ($method, $class) {

                return function () use ($method, $class) {
                    $args = func_get_args ();
                    $args = array (
                        &$args[0],
                        $args[1],
                    );
                    return $method->invokeArgs ($class, $args);
                };
            });

        $compareAttribute ($criteria, $searchModel->getField ('leadSource'));
        $contacts = Contacts::model()->findAll ($criteria);
        $this->assertModelArrayEquality (array ($contact1), $contacts);

        $criteria = new CDbCriteria;
        $searchModel = new Contacts;
        $searchModel->leadSource = 'Facebook';
        $compareAttribute = TestingAuxLib::setPublic (
            $searchModel, 'compareAttribute', false, function ($method, $class) {

                return function () use ($method, $class) {
                    $args = func_get_args ();
                    $args = array (
                        &$args[0],
                        $args[1],
                    );
                    return $method->invokeArgs ($class, $args);
                };
            });
        $compareAttribute ($criteria, $searchModel->getField ('leadSource'));
        $contacts = Contacts::model()->findAll ($criteria);
        $this->assertModelArrayEquality (array ($contact2), $contacts);

        $contact1->leadSource = CJSON::encode (array ('Google', 'Facebook'));
        $this->assertSaves ($contact1);

        $criteria = new CDbCriteria;
        $searchModel = new Contacts;
        $searchModel->leadSource = array ('Google');
        $compareAttribute = TestingAuxLib::setPublic (
            $searchModel, 'compareAttribute', false, function ($method, $class) {

                return function () use ($method, $class) {
                    $args = func_get_args ();
                    $args = array (
                        &$args[0],
                        $args[1],
                    );
                    return $method->invokeArgs ($class, $args);
                };
            });
        $compareAttribute ($criteria, $searchModel->getField ('leadSource'));
        $contacts = Contacts::model()->findAll ($criteria);
        $this->assertModelArrayEquality (array ($contact1), $contacts);

        $criteria = new CDbCriteria;
        $searchModel = new Contacts;
        $searchModel->leadSource = array ('Facebook');
        $compareAttribute = TestingAuxLib::setPublic (
            $searchModel, 'compareAttribute', false, function ($method, $class) {

                return function () use ($method, $class) {
                    $args = func_get_args ();
                    $args = array (
                        &$args[0],
                        $args[1],
                    );
                    return $method->invokeArgs ($class, $args);
                };
            });
        $compareAttribute ($criteria, $searchModel->getField ('leadSource'));
        $contacts = Contacts::model()->findAll ($criteria);
        $this->assertModelArrayEquality (array ($contact1, $contact2), $contacts);
    }

    public function testGetAllRecordNames() {
        $recordNames = X2Model::getAllRecordNames();
        $this->assertTrue(is_array($recordNames) && !empty($recordNames));
        $this->assertTrue(in_array('contact', $recordNames));
        $this->assertTrue(in_array('Contacts', array_keys($recordNames)));
    }

    public function testGetField() {
        $contact = $this->contact('testAnyone');
        $field = $contact->getField('name');
        $this->assertTrue($field instanceof Fields);
        $this->assertEquals('name', $field->fieldName);

        $field = $contact->getField('nonexistent');
        $this->assertNull($field);
    }

    public function testGetFieldComparisonOptions() {
        $expected = array(
            '=' => 'equals',
            '>' => 'greater than',
            '<' => 'less than',
            '>=' => 'greater than or equal to',
            '<=' => 'less than or equal to',
            '<>' => 'not equal to',
            'list' => 'in list',
            'notList' => 'not in list',
            'empty' => 'empty',
            'notEmpty' => 'not empty',
            'contains' => 'contains',
            'noContains' => 'does not contain',
            'before' => 'before',
            'after' => 'after',
        );
        $this->assertEquals($expected, X2Model::getFieldComparisonOptions());
    }

    public function testGetModelsFromNames() {
        $models = X2Model::getModelsFromNames(array('Contacts', 'Accounts'));
        $this->assertTrue(is_array($models));
        $this->assertTrue($models[0] instanceof Contacts);
        $this->assertTrue($models[1] instanceof Accounts);
    }

    public function testGetTableNames() {
        $models = X2Model::getModelsFromNames(array('Contacts', 'Accounts'));
        $tableNames = X2Model::getTableNames($models);
        $this->assertTrue(is_array($tableNames));
        $this->assertEquals('x2_contacts', $tableNames[0]);
        $this->assertEquals('x2_accounts', $tableNames[1]);
    }

    public function testGetModelNames() {
        $criteria = new CDbCriteria;
        $modelNames = X2Model::getModelNames($criteria);
        $this->assertTrue(is_array($modelNames) && !empty($modelNames));
        $this->assertTrue(in_array('Product', array_keys($modelNames)));
        $this->assertTrue(in_array('Products', $modelNames));
        $this->assertTrue(in_array('Campaign', array_keys($modelNames)));
        $this->assertTrue(in_array('Marketing', $modelNames));
        $this->assertTrue(in_array('X2Leads', array_keys($modelNames)));
        $this->assertTrue(in_array('Leads', $modelNames));
    }

    public function testIsModuleModelName() {
        $this->assertTrue(X2Model::isModuleModelName('Contacts'));
        $this->assertTrue(X2Model::isModuleModelName('Accounts'));
        $this->assertTrue(X2Model::isModuleModelName('Campaign'));
        $this->assertTrue(X2Model::isModuleModelName('X2Leads'));
        $this->assertFalse(X2Model::isModuleModelName('NonExistent'));
    }

    public function testGetModuleModelNames() {
        $modelNames = X2Model::getModuleModelNames();
        $this->assertTrue(is_array($modelNames));
        $this->assertTrue(in_array('Contacts', $modelNames));
        $this->assertTrue(in_array('Accounts', $modelNames));
        $this->assertTrue(in_array('Product', $modelNames));
        $this->assertTrue(in_array('Campaign', $modelNames));
        $this->assertTrue(in_array('X2Leads', $modelNames));
    }

    public function testGetModules() {
        TestingAuxLib::setPrivateProperty('X2Model', '_modules', null);
        $modules = X2Model::getModules();
        $this->assertTrue(is_array($modules) && !empty($modules));
        foreach ($modules as $module) {
            $this->assertTrue($module instanceof Modules);
        }
    }

    public function testGetModuleModelsByName() {
        $modules = X2Model::getModuleModelsByName();
        $this->assertTrue(is_array($modules) && !empty($modules));
        $this->assertTrue(array_key_exists('Contacts', $modules));
        $this->assertTrue($modules['Contacts'] instanceof Contacts);
        $this->assertTrue(array_key_exists('Accounts', $modules));
        $this->assertTrue($modules['Accounts'] instanceof Accounts);
        $this->assertTrue(array_key_exists('Campaign', $modules));
        $this->assertTrue($modules['Campaign'] instanceof Campaign);
    }

    public function testGetAssociationTypeOptions() {
        $associationTypes = X2Model::getAssociationTypeOptions();
        $this->assertTrue(is_array($associationTypes) && !empty($associationTypes));
        $this->assertTrue(array_key_exists('contacts', $associationTypes));
        $this->assertEquals('Contacts', $associationTypes['contacts']);
        $this->assertTrue(array_key_exists('accounts', $associationTypes));
        $this->assertEquals('Accounts', $associationTypes['accounts']);
        $this->assertTrue(array_key_exists('x2Leads', $associationTypes));
        $this->assertEquals('Leads', $associationTypes['x2Leads']);
    }

    public function testGetModelTitle() {
        $this->assertEquals('Calendar', X2Model::getModelTitle('Calendar'));
        $this->assertEquals('Calendar', X2Model::getModelTitle('X2Calendar'));
        $this->assertEquals('Contacts', X2Model::getModelTitle('Contacts'));
        $this->assertEquals('Contact', X2Model::getModelTitle('Contacts', true));
        $this->assertEquals('Accounts', X2Model::getModelTitle('Accounts'));
        $this->assertEquals('Account', X2Model::getModelTitle('Accounts', true));
        $this->assertEquals('Products', X2Model::getModelTitle('Products'));
        $this->assertEquals('Product', X2Model::getModelTitle('Products', true));
    }

    public function testGetTranslatedModelTitles() {
        $modelTitles = X2Model::getTranslatedModelTitles();
        $this->assertTrue(is_array($modelTitles) && !empty($modelTitles));
        $this->assertTrue(array_key_exists('Contacts', $modelTitles));
        $this->assertEquals('Contacts', $modelTitles['Contacts']);
        $this->assertTrue(array_key_exists('AnonContact', $modelTitles));
        $this->assertEquals('Anonymous Contacts', $modelTitles['AnonContact']);
        $this->assertTrue(array_key_exists('Fingerprint', $modelTitles));
        $this->assertEquals('Fingerprints', $modelTitles['Fingerprint']);
    }


    public function testGetMediaLookupFields() {
        $contact = new Contacts;
        $lookupFields = $contact->getMediaLookupFields();
        $this->assertTrue(is_array($lookupFields) && empty($lookupFields));

        $field = new Fields;
        $field->modelName = 'Contacts';
        $field->fieldName = 'c_mediaLookup';
        $field->attributeLabel = 'Media Lookup';
        $field->type = 'link';
        $field->linkType = 'Media';
        $this->assertTrue($field->save());
        $lookupFields = $contact->getMediaLookupFields();
        $this->assertTrue(is_array($lookupFields) && !empty($lookupFields));
        $this->assertTrue($lookupFields[0] instanceof Fields);
        $this->assertEquals('c_mediaLookup', $lookupFields[0]->fieldName);
        $field->delete();
    }

    public function testGetLinkedAttribute() {
        $contact = $this->contact('testAnyone');
        $type = $contact->getLinkedAttribute('company', 'type');
        $this->assertEquals('Manufacturing', $type);
        $phone = $contact->getLinkedAttribute('company', 'phone');
        $this->assertEquals('831-555-5555', $phone);

        $contact = $this->contact('testUser');
        $type = $contact->getLinkedAttribute('company', 'type');
        $this->assertNull($type);
    }

    public function testRenderErroneousField() {
        $expected = '<span class="erroneous-field">Field could not be found</span>';
        $contact = new Contacts;
        $this->assertEquals($expected, $contact->renderErroneousField());
    }

    public function testRenderLinkedAttribute() {
        $contact = $this->contact('testAnyone');
        $type = $contact->renderLinkedAttribute('company', 'type');
        $this->assertEquals('Manufacturing', $type);
        $phone = $contact->renderLinkedAttribute('company', 'phone');
        $this->assertEquals('<a href="tel:831-555-5555">831-555-5555</a>', $phone);

        $contact = $this->contact('testUser');
        $type = $contact->renderLinkedAttribute('company', 'type');
        $this->assertNull($type);
    }

    public function testGetStaticLinkedModels() {
        $contact = $this->contact('testAnyone');
        $models = $contact->getStaticLinkedModels();
        $this->assertTrue(is_array($models) && !empty($models));
        $this->assertTrue(array_key_exists('company', $models));
        $this->assertTrue($models['company'] instanceof Accounts);
    }

    public function testGetSummaryFields() {
        $contact = new Contacts;
        $fields = $contact->getSummaryFields();
        $this->assertTrue(is_array($fields));
        $this->assertTrue(in_array('assignedTo', $fields));
        $this->assertFalse(in_array('name', $fields));
        $this->assertFalse(in_array('email', $fields));
        $this->assertFalse(in_array('phone', $fields));

        $contact->name = 'test';
        $fields = $contact->getSummaryFields();
        $this->assertTrue(is_array($fields));
        $this->assertTrue(in_array('name', $fields));
        $this->assertFalse(in_array('email', $fields));
        $this->assertFalse(in_array('phone', $fields));

        $contact->email = 'test@example.com';
        $fields = $contact->getSummaryFields();
        $this->assertTrue(is_array($fields));
        $this->assertTrue(in_array('name', $fields));
        $this->assertTrue(in_array('email', $fields));
        $this->assertFalse(in_array('phone', $fields));

        $contact->phone = '831-555-1234';
        $fields = $contact->getSummaryFields();
        $this->assertTrue(is_array($fields));
        $this->assertTrue(in_array('name', $fields));
        $this->assertTrue(in_array('email', $fields));
        $this->assertTrue(in_array('phone', $fields));
    }

    public function testGetDefaultFormLayout() {
        $layout = X2Model::getDefaultFormLayout('Product');
        $expected = array('sections' => array(array(
            'collapsible' => false,
            'title' => 'Product Info',
            'rows' => array(array(
                'cols' => array(array(
                    'items' => array(
                        array(
                            'name' => 'formItem_adjustment',
                            'labelType' => 'left', 'readOnly' => '0',
                            'height' => 30, 'width' => 155,
                        ),
                        array(
                            'name' => 'formItem_currency',
                            'labelType' => 'left', 'readOnly' => '0',
                            'height' => 30, 'width' => 155,
                        ),
                        array(
                            'name' => 'formItem_description',
                            'labelType' => 'left', 'readOnly' => '0',
                            'height' => 30, 'width' => 155,
                        ),
                        array(
                            'name' => 'formItem_id',
                            'labelType' => 'left', 'readOnly' => '0',
                            'height' => 30, 'width' => 155,
                        ),
                        array(
                            'name' => 'formItem_inventory',
                            'labelType' => 'left', 'readOnly' => '0',
                            'height' => 30, 'width' => 155,
                        ),
                        array(
                            'name' => 'formItem_name',
                            'labelType' => 'left', 'readOnly' => '0',
                            'height' => 30, 'width' => 155,
                        ),
                        array(
                            'name' => 'formItem_price',
                            'labelType' => 'left', 'readOnly' => '0',
                            'height' => 30, 'width' => 155,
                        ),
                        array(
                            'name' => 'formItem_status',
                            'labelType' => 'left', 'readOnly' => '0',
                            'height' => 30, 'width' => 155,
                        ),
                        array(
                            'name' => 'formItem_type',
                            'labelType' => 'left', 'readOnly' => '0',
                            'height' => 30, 'width' => 155,
                        ),
                    ),
                )),
            )),
        )));
        $this->assertEquals($expected, json_decode($layout, true));
    }

    public function testGetModelOfTypeWithId() {
        $this->assertNull(X2Model::getModelOfTypeWithId('Nonexistent', 0));
        $model = X2Model::getModelOfTypeWithId('contacts', 12345, true);
        $this->assertTrue($model instanceof Contacts);
        $this->assertEquals('Testfirstname Testlastname', $model->name);
    }

    public function testGetModelOfTypeWithName() {
        $this->assertNull(X2Model::getModelOfTypeWithName('Nonexistent', 'missing'));
        $model = X2Model::getModelOfTypeWithName('Contacts', 'Testfirstname Testlastname');
        $this->assertTrue($model instanceof Contacts);
        $this->assertEquals('Testfirstname Testlastname', $model->name);
    }
}

?>
