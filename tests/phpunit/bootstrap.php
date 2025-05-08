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

$SRC_ROOT= dirname(dirname(dirname(__FILE__))) . '/src';
require_once($SRC_ROOT . '/lib/defs.php');

require_once($SRC_ROOT . '/Model/include.php');
require_once($SRC_ROOT . '/View/pf/include.php');

$TEST_ROOT= dirname(dirname(dirname(__FILE__)));
$TEST_PATH= $TEST_ROOT . '/tests/phpunit';
$TEST_DIR= '/tests/phpunit/root';
$TEST_DIR_PATH= $TEST_ROOT . $TEST_DIR;
$TEST_DIR_SRC= $TEST_DIR . '/var/www/htdocs/pfre';

/// @todo Check why posix_getlogin() returns empty string
/// @todo Is it better to use exec('whoami')?
$INSTALL_USER= posix_getpwuid(posix_getuid())['name'];

// Forward compatibility
if (class_exists('\PHPUnit\Framework\TestCase') && !class_exists('\PHPUnit_Framework_TestCase')) {
	class_alias('\PHPUnit\Framework\TestCase', '\PHPUnit_Framework_TestCase');
}
?>