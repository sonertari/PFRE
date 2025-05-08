<?php
/*
 * Copyright (C) 2004-2025 Soner Tari
 *
 * This file is part of PFRE.
 *
 * PFRE is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * PFRE is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with PFRE.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace ModelTest;

/// @todo Check why $MODEL_PATH is not defined in global space, and including bootstrap.php here does not define $MODEL_PATH either
$SRC_ROOT= dirname(dirname(dirname(dirname(__FILE__)))) . '/src';
$MODEL_PATH= $SRC_ROOT . '/Model';
require_once($MODEL_PATH.'/model.php');

class modelTest extends \PHPUnit_Framework_TestCase
{
	function testSetLogLevel()
	{
		global $TEST_DIR_PATH;

		$file= $TEST_DIR_PATH . '/var/www/htdocs/pfre/lib/setup.php';

		$model= new \Model();

		$expected= 'LOG_DEBUG';
		$result= $model->SetLogLevel($expected);
		
		$this->assertTrue($result);

		$actual= $model->GetNVP($file, '\$LOG_LEVEL', 0, ';');

		$this->assertEquals($expected, $actual);

		$expected= 'LOG_INFO';
		$result= $model->SetLogLevel($expected);

		$this->assertTrue($result);

		$actual= $model->GetNVP($file, '\$LOG_LEVEL', 0, ';');

		$this->assertEquals($expected, $actual);

		unlink($file . '.bak');
	}

	function testSetHelpBox()
	{
		global $TEST_DIR_PATH;

		$file= $TEST_DIR_PATH . '/var/www/htdocs/pfre/View/lib/setup.php';

		$model= new \Model();

		$expected= 'FALSE';
		$result= $model->SetHelpBox($expected);
		
		$this->assertTrue($result);

		$actual= $model->GetNVP($file, '\$ShowHelpBox', 0, ';');

		$this->assertEquals($expected, $actual);

		$expected= 'TRUE';
		$result= $model->SetHelpBox($expected);

		$this->assertTrue($result);

		$actual= $model->GetNVP($file, '\$ShowHelpBox', 0, ';');

		$this->assertEquals($expected, $actual);

		unlink($file . '.bak');
	}

	function testSetSessionTimeout()
	{
		global $TEST_DIR_PATH;

		$file= $TEST_DIR_PATH . '/var/www/htdocs/pfre/View/lib/setup.php';

		$model= new \Model();

		$expected= '123';
		$result= $model->SetSessionTimeout($expected);
		
		$this->assertTrue($result);

		$actual= $model->GetNVP($file, '\$SessionTimeout', 0, ';');

		$this->assertEquals($expected, $actual);

		$expected= '300';
		$result= $model->SetSessionTimeout($expected);

		$this->assertTrue($result);

		$actual= $model->GetNVP($file, '\$SessionTimeout', 0, ';');

		$this->assertEquals($expected, $actual);

		unlink($file . '.bak');
	}

	function testSetSessionTimeoutInvalidFixed()
	{
		global $TEST_DIR_PATH;

		$file= $TEST_DIR_PATH . '/var/www/htdocs/pfre/View/lib/setup.php';

		$model= new \Model();

		$result= $model->SetSessionTimeout('0');
		
		$this->assertTrue($result);

		$expected= '10';
		$actual= $model->GetNVP($file, '\$SessionTimeout', 0, ';');

		$this->assertEquals($expected, $actual);

		$expected= '300';
		$result= $model->SetSessionTimeout($expected);

		$this->assertTrue($result);

		$actual= $model->GetNVP($file, '\$SessionTimeout', 0, ';');

		$this->assertEquals($expected, $actual);

		unlink($file . '.bak');
	}

	function testSetForceHTTPs()
	{
		global $TEST_DIR_PATH;

		$file= $TEST_DIR_PATH . '/var/www/htdocs/pfre/lib/setup.php';

		$model= new \Model();

		$expected= 'TRUE';
		$result= $model->SetForceHTTPs($expected);
		
		$this->assertTrue($result);

		$actual= $model->GetNVP($file, '\$ForceHTTPs', 0, ';');

		$this->assertEquals($expected, $actual);

		$expected= 'FALSE';
		$result= $model->SetForceHTTPs($expected);

		$this->assertTrue($result);

		$actual= $model->GetNVP($file, '\$ForceHTTPs', 0, ';');

		$this->assertEquals($expected, $actual);

		unlink($file . '.bak');
	}

	function testSetMaxAnchorNesting()
	{
		global $TEST_DIR_PATH;

		$file= $TEST_DIR_PATH . '/var/www/htdocs/pfre/lib/setup.php';

		$model= new \Model();

		$expected= '10';
		$result= $model->SetMaxAnchorNesting($expected);
		
		$this->assertTrue($result);

		$actual= $model->GetNVP($file, '\$MaxAnchorNesting', 0, ';');

		$this->assertEquals($expected, $actual);

		$expected= '2';
		$result= $model->SetMaxAnchorNesting($expected);

		$this->assertTrue($result);

		$actual= $model->GetNVP($file, '\$MaxAnchorNesting', 0, ';');

		$this->assertEquals($expected, $actual);

		unlink($file . '.bak');
	}

	function testSetPfctlTimeout()
	{
		global $TEST_DIR_PATH;

		$file= $TEST_DIR_PATH . '/var/www/htdocs/pfre/lib/setup.php';

		$model= new \Model();

		$expected= '10';
		$result= $model->SetPfctlTimeout($expected);
		
		$this->assertTrue($result);

		$actual= $model->GetNVP($file, '\$PfctlTimeout', 0, ';');

		$this->assertEquals($expected, $actual);

		$expected= '5';
		$result= $model->SetPfctlTimeout($expected);

		$this->assertTrue($result);

		$actual= $model->GetNVP($file, '\$PfctlTimeout', 0, ';');

		$this->assertEquals($expected, $actual);

		unlink($file . '.bak');
	}
}
?>
