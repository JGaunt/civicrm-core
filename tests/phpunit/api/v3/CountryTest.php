<?php
/*
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC. All rights reserved.                        |
 |                                                                    |
 | This work is published under the GNU AGPLv3 license with some      |
 | permitted exceptions and without any warranty. For full license    |
 | and copyright information, see https://civicrm.org/licensing       |
 +--------------------------------------------------------------------+
 */

/**
 *  Test APIv3 civicrm_country* functions
 *
 * @package CiviCRM_APIv3
 * @subpackage API_Contact
 * @group headless
 */
class api_v3_CountryTest extends CiviUnitTestCase {
  protected $_apiversion;
  protected $_params;

  public function setUp() {
    $this->_apiversion = 3;
    parent::setUp();
    $this->useTransaction(TRUE);
    $this->_params = [
      'name' => 'Made Up Land',
      'iso_code' => 'ZZ',
      'region_id' => 1,
    ];
  }

  public function testCreateCountry() {

    $result = $this->callAPIAndDocument('country', 'create', $this->_params, __FUNCTION__, __FILE__);
    $this->assertEquals(1, $result['count']);
    $this->assertNotNull($result['values'][$result['id']]['id']);

    $this->callAPISuccess('country', 'delete', ['id' => $result['id']]);
  }

  public function testDeleteCountry() {
    //create one
    $create = $this->callAPISuccess('country', 'create', $this->_params);

    $result = $this->callAPIAndDocument('country', 'delete', ['id' => $create['id']], __FUNCTION__, __FILE__);
    $this->assertEquals(1, $result['count']);
    $get = $this->callAPISuccess('country', 'get', [
      'id' => $create['id'],
    ]);
    $this->assertEquals(0, $get['count'], 'Country not successfully deleted');
  }

  /**
   * Test civicrm_phone_get with empty params.
   */
  public function testGetEmptyParams() {
    $result = $this->callAPISuccess('Country', 'Get', []);
  }

  /**
   * Test civicrm_phone_get with wrong params.
   */
  public function testGetWrongParams() {
    $this->callAPIFailure('Country', 'Get', ['id' => 'abc']);
  }

  /**
   * Test civicrm_phone_get - success expected.
   */
  public function testGet() {
    $country = $this->callAPISuccess('Country', 'create', $this->_params);
    $params = [
      'iso_code' => $this->_params['iso_code'],
    ];
    $result = $this->callAPIAndDocument('Country', 'Get', $params, __FUNCTION__, __FILE__);
    $this->assertEquals($country['values'][$country['id']]['name'], $result['values'][$country['id']]['name']);
    $this->assertEquals($country['values'][$country['id']]['iso_code'], $result['values'][$country['id']]['iso_code']);
  }

  ///////////////// civicrm_country_create methods

  /**
   * If a new country is created and it is created again it should not create a second one.
   * We check on the iso code (there should be only one iso code
   */
  public function testCreateDuplicateFail() {
    $params = $this->_params;
    unset($params['id']);
    $this->callAPISuccess('country', 'create', $params);
    $this->callAPIFailure('country', 'create', $params);
    $check = $this->callAPISuccess('country', 'getcount', [
      'iso_code' => $params['iso_code'],
    ]);
    $this->assertEquals(1, $check);
  }

}
