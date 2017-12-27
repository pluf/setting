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
class Setting_ServiceTest extends TestCase
{

    /**
     * @beforeClass
     */
    public static function createDataBase()
    {
        Pluf::start(__DIR__ . '/../conf/mysql.conf.php');
        $m = new Pluf_Migration(array(
            'Pluf',
            'Setting'
        ));
        $m->install();
        $m->init();
    }

    /**
     * @afterClass
     */
    public static function removeDatabses()
    {
        $m = new Pluf_Migration(array(
            'Pluf',
            'Setting'
        ));
        $m->unInstall();
    }

    /**
     * Getting list of properties
     *
     * @test
     */
    public function shouldPossibleToGetNotDefinedProperty()
    {
        $result = Setting_Service::get('undefined-key', 'value');
        Test_Assert::assertNotNull($result, 'Failt to get non defined value');
        Test_Assert::assertEquals('value', $result, 'Value is not a defualt one');
    }

    /**
     * @test
     */
    public function shouldUseFirstValueAsInital()
    {
        $key = 'undefined-key';
        $result = Setting_Service::get($key, 'value1');
        Test_Assert::assertNotNull($result, 'Failt to get non defined value');
        Test_Assert::assertEquals('value', $result, 'Value is not a defualt one');
        
        $result2 = Setting_Service::get($key, 'value2');
        Test_Assert::assertEquals($result, $result2, 'Value is not a defualt one');
    }

    /**
     * @test
     */
    public function flushMustPushDataToDB()
    {
        $key = 'undefined-key-' . rand();
        $value = 'value';
        $result = Setting_Service::get($key, $value);
        Test_Assert::assertNotNull($result, 'Failt to get non defined value');
        Test_Assert::assertEquals('value', $result, 'Value is not a defualt one');
        
        Setting_Service::flush();
        
        $setting = new Setting();
        $sql = new Pluf_SQL('`key`=%s', array(
            $key
        ));
        $one = $setting->getOne(array(
            'filter' => $sql->gen()
        ));
        Test_Assert::assertNotNull($one, 'Setting not found with key');
        Test_Assert::assertEquals($value, $one->value, 'value are not the same');
    }

    /**
     * @test
     */
    public function shouldUsePreSavedSetting()
    {
        $key = 'undefined-key-' . rand();
        $value = 'value' . rand();
        
        // Create setting
        $setting = new Setting();
        $setting->key = $key;
        $setting->value = $value;
        $setting->mod = Setting::MOD_PUBLIC;
        Test_Assert::assertTrue($setting->create());
        
        $result = Setting_Service::get($key, 'New value');
        Test_Assert::assertNotNull($result, 'Failt to get non defined value');
        Test_Assert::assertEquals($value, $result, 'Value is not a defualt one');
    }
}