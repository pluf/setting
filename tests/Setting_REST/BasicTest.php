<?php
/*
 * This file is part of Pluf Framework, a simple PHP Application Framework.
 * Copyright (C) 2010-2020 Phoinex Scholars Co. (http://dpq.co.ir)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */
use PHPUnit\Framework\TestCase;
require_once 'Pluf.php';

/**
 * @backupGlobals disabled
 * @backupStaticAttributes disabled
 */
class Setting_REST_BasicTest extends TestCase
{

    private static $client = null;

    private static $user = null;

    /**
     * @beforeClass
     */
    public static function createDataBase()
    {
        Pluf::start(__DIR__ . '/../conf/mysql.conf.php');
        $m = new Pluf_Migration(array(
            'Pluf',
            'User',
            'Role',
            'Setting'
        ));
        $m->install();
        $m->init();
        
        // TODO: update user api to get user by login directly
        $user = new User();
        $user = $user->getUser('admin');
        $role = Role::getFromString('Pluf.owner');
        $user->setAssoc($role);
        
        self::$client = new Test_Client(array(
            array(
                'app' => 'Setting',
                'regex' => '#^/api/setting#',
                'base' => '',
                'sub' => include 'Setting/urls.php'
            ),
            array(
                'app' => 'User',
                'regex' => '#^/api/user#',
                'base' => '',
                'sub' => include 'User/urls.php'
            )
        ));
    }

    /**
     * @afterClass
     */
    public static function removeDatabses()
    {
        $m = new Pluf_Migration(array(
            'Pluf',
            'User',
            'Role',
            'Setting'
        ));
        $m->unInstall();
    }

    /**
     * Getting list of properties
     *
     * @test
     */
    public function anonymousCanGetListOfSettings()
    {
        $response = self::$client->get('/api/setting/find');
        Test_Assert::assertResponseNotNull($response, 'Find result is empty');
        Test_Assert::assertResponseStatusCode($response, 200, 'Find status code is not 200');
        Test_Assert::assertResponsePaginateList($response, 'Find result is not JSON paginated list');
    }

    /**
     * Getting list of properties with admin
     *
     * @test
     */
    public function adminCanGetListOfSettings()
    {
        // Login
        $response = self::$client->post('/api/user/login', array(
            'login' => 'admin',
            'password' => 'admin'
        ));
        Test_Assert::assertResponseStatusCode($response, 200, 'Fail to login');
        
        // Getting list
        $response = self::$client->get('/api/setting/find');
        Test_Assert::assertResponseNotNull($response, 'Find result is empty');
        Test_Assert::assertResponseStatusCode($response, 200, 'Find status code is not 200');
        Test_Assert::assertResponsePaginateList($response, 'Find result is not JSON paginated list');
    }

    /**
     * Create a new setting in system
     *
     * @test
     */
    public function adminCanCreateASetting()
    {
        // Login
        $response = self::$client->post('/api/user/login', array(
            'login' => 'admin',
            'password' => 'admin'
        ));
        Test_Assert::assertResponseStatusCode($response, 200, 'Fail to login');
        
        // Getting list
        $values = array(
            'key' => 'KEY-TEST-' . rand(),
            'value' => 'NOT SET',
            'mode' => Setting::MOD_PUBLIC
        );
        $response = self::$client->post('/api/setting/new', $values);
        Test_Assert::assertResponseNotNull($response, 'Find result is empty');
        Test_Assert::assertResponseStatusCode($response, 200, 'Find status code is not 200');
        
        $setting = new Setting();
        $list = $setting->getList();
        Test_Assert::assertTrue(sizeof($list) > 0, 'Setting is not created');
        Test_Assert::assertEquals($values['value'], Setting_Service::get($values['key']), 'Values are not equal.');
    }

    /**
     * Create and update a new setting in system by admin
     *
     * @test
     */
    public function adminCanCreateAndGetSettingByKey()
    {
        // Login
        $response = self::$client->post('/api/user/login', array(
            'login' => 'admin',
            'password' => 'admin'
        ));
        Test_Assert::assertResponseStatusCode($response, 200, 'Fail to login');
        
        // Getting list
        $values = array(
            'key' => 'KEY-TEST-' . rand(),
            'value' => 'NOT SET',
            'mode' => Setting::MOD_PUBLIC
        );
        $response = self::$client->post('/api/setting/new', $values);
        Test_Assert::assertResponseNotNull($response, 'Find result is empty');
        Test_Assert::assertResponseStatusCode($response, 200, 'Find status code is not 200');
        
        $setting = new Setting();
        $list = $setting->getList();
        Test_Assert::assertTrue(sizeof($list) > 0, 'Setting is not created');
        Test_Assert::assertEquals($values['value'], Setting_Service::get($values['key']), 'Values are not equal.');
        
        $response = self::$client->get('/api/setting/' . $values['key']);
        Test_Assert::assertResponseNotNull($response, 'Find result is empty');
        Test_Assert::assertResponseStatusCode($response, 200, 'Find status code is not 200');
    }

    /**
     * Create and update a new setting in system by admin
     *
     * @test
     */
    public function adminCanCreateAndGetSettingById()
    {
        // Login
        $response = self::$client->post('/api/user/login', array(
            'login' => 'admin',
            'password' => 'admin'
        ));
        Test_Assert::assertResponseStatusCode($response, 200, 'Fail to login');
        
        // Getting list
        $values = array(
            'key' => 'KEY-TEST-' . rand(),
            'value' => 'NOT SET',
            'mode' => Setting::MOD_PUBLIC
        );
        $response = self::$client->post('/api/setting/new', $values);
        Test_Assert::assertResponseNotNull($response, 'Find result is empty');
        Test_Assert::assertResponseStatusCode($response, 200, 'Find status code is not 200');
        
        $setting = new Setting();
        $list = $setting->getList();
        Test_Assert::assertTrue(sizeof($list) > 0, 'Setting is not created');
        Test_Assert::assertEquals($values['value'], Setting_Service::get($values['key']), 'Values are not equal.');
        
        $sql = new Pluf_SQL('`key`=%s', array(
            $values['key']
        ));
        $one = $setting->getOne(array(
            'filter' => $sql->gen()
        ));
        Test_Assert::assertNotNull($one, 'Setting not found with key');
        
        $response = self::$client->get('/api/setting/' . $one->id);
        Test_Assert::assertResponseNotNull($response, 'Find result is empty');
        Test_Assert::assertResponseStatusCode($response, 200, 'Find status code is not 200');
    }
    
    
    /**
     * Create and update a new setting in system by admin
     *
     * @test
     */
    public function adminCanCreateAndDeleteSettingById()
    {
        // Login
        $response = self::$client->post('/api/user/login', array(
            'login' => 'admin',
            'password' => 'admin'
        ));
        Test_Assert::assertResponseStatusCode($response, 200, 'Fail to login');
        
        // Getting list
        $values = array(
            'key' => 'KEY-TEST-' . rand(),
            'value' => 'NOT SET',
            'mode' => Setting::MOD_PUBLIC
        );
        $response = self::$client->post('/api/setting/new', $values);
        Test_Assert::assertResponseNotNull($response, 'Find result is empty');
        Test_Assert::assertResponseStatusCode($response, 200, 'Find status code is not 200');
        
        // Get setting form db
        $setting = new Setting();
        $sql = new Pluf_SQL('`key`=%s', array(
            $values['key']
        ));
        $one = $setting->getOne(array(
            'filter' => $sql->gen()
        ));
        Test_Assert::assertNotNull($one, 'Setting not found with key');
        
        // delete by id
        $response = self::$client->delete('/api/setting/' . $one->id);
        Test_Assert::assertResponseNotNull($response, 'Find result is empty');
        Test_Assert::assertResponseStatusCode($response, 200, 'Find status code is not 200');
        
        // Check if deleted
        $one = $setting->getOne(array(
            'filter' => $sql->gen()
        ));
        Test_Assert::assertNull($one, 'Setting is not deleted');
    }
    
    
    /**
     * Create and update a new setting in system by admin
     *
     * @test
     */
    public function adminCanCreateAndUpdateSettingById()
    {
        // Login
        $response = self::$client->post('/api/user/login', array(
            'login' => 'admin',
            'password' => 'admin'
        ));
        Test_Assert::assertResponseStatusCode($response, 200, 'Fail to login');
        
        // Getting list
        $values = array(
            'key' => 'KEY-TEST-' . rand(),
            'value' => 'NOT SET',
            'mode' => Setting::MOD_PUBLIC
        );
        $response = self::$client->post('/api/setting/new', $values);
        Test_Assert::assertResponseNotNull($response, 'Find result is empty');
        Test_Assert::assertResponseStatusCode($response, 200, 'Find status code is not 200');
        
        $setting = new Setting();
        $sql = new Pluf_SQL('`key`=%s', array(
            $values['key']
        ));
        $one = $setting->getOne(array(
            'filter' => $sql->gen()
        ));
        Test_Assert::assertNotNull($one, 'Setting not found with key');
        
        $values['value'] = 'new value' .rand();
        $response = self::$client->post('/api/setting/' . $one->id, $values);
        Test_Assert::assertResponseNotNull($response, 'Find result is empty');
        Test_Assert::assertResponseStatusCode($response, 200, 'Find status code is not 200');
        
        
        $one = $setting->getOne(array(
            'filter' => $sql->gen()
        ));
        Test_Assert::assertNotNull($one, 'Setting not found with key');
        Test_Assert::assertEquals($values['value'], $one->value);
    }
}