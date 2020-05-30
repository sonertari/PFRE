<?php
/*
 * Copyright (C) 2004-2020 Soner Tari
 *
 * This file is part of UTMFW.
 *
 * UTMFW is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * UTMFW is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with UTMFW.  If not, see <http://www.gnu.org/licenses/>.
 */

/** @file
 * Defs and library functions for Controller.
 */

/**
 * Functions and info strings used in shell arg control.
 *
 * @param string func Function to check type
 * @param string desc Info string to use when check failed
 */
$ArgTypes= array(
	FILEPATH	=>	array(
		'func'	=> 'IsFilePath',
		'desc'	=> _('Filepath wrong'),
		),
	NAME		=>	array(
		'func'	=> 'IsName',
		'desc'	=> _('Name wrong'),
		),
	NUM			=>	array(
		'func'	=> 'IsNumber',
		'desc'	=> _('Number wrong'),
		),
	SHA1STR	=>	array(
		'func'	=> 'IsSha1Str',
		'desc'	=> _('Not sha1 encrypted string'),
		),
	BOOL	=>	array(
		'func'	=> 'IsBool',
		'desc'	=> _('Not boolean'),
		),
	SAVEFILEPATH	=>	array(
		'func'	=> 'IsFilePath',
		'desc'	=> _('Filepath wrong'),
		),
	JSON	=>	array(
		'func'	=> 'IsJson',
		'desc'	=> _('Not JSON encoded string'),
		),
);

function IsName($str)
{
	return preg_match('/' . RE_NAME . '/', $str);
}

function IsNumber($str)
{
	return preg_match('/' . RE_NUM . '/', $str);
}

function IsSha1Str($str)
{
	return preg_match('/' . RE_SHA1 . '/', $str);
}

function IsBool($str)
{
	return preg_match('/' . RE_BOOL . '/', $str);
}

function IsJson($str)
{
	return json_decode($str) !== NULL;
}

function IsEmpty($str)
{
	return empty($str);
}

/**
 * Computes and fills arg count variables.
 *
 * @param array $commands Available commands for the current model
 * @param array $argv Argument vector
 * @param string $cmd Method name, key to $commands
 * @param int $actual Given arg count [out]
 * @param int $expected Expected arg count [out]
 * @param int $acceptable Acceptable arg count [out]
 * @param int $check Arg count used while validating [out]
 */
function ComputeArgCounts($commands, $argv, $cmd, &$actual, &$expected, &$acceptable, &$check)
{
	$actual= count($argv);
	$expected= count($commands[$cmd]['argv']);

	$acceptable= $expected;
	for ($argpos= 0; $argpos < $expected; $argpos++) {
		$argtype= $commands[$cmd]['argv'][$argpos];
		if ($argtype & NONE) {
			$acceptable--;
		}
	}
	
	/// @attention There may be extra or missing args, hence min() here
	$check= min($actual, $expected);
}

/**
 * Checks types of the arguments passed.
 *
 * The arguments are checked against the types listed in $commands.
 *
 * @param array $commands Available commands for the current model
 * @param string $command Method name, key to $commands
 * @param array $argv Argument vector
 * @param int $check Arg count used while validating
 * @return bool Validation result
 *
 * @todo There are 2 types of argument checks in this project, which one to choose?
 */
function ValidateArgs($commands, $command, $argv, $check)
{
	global $ArgTypes;

	$helpmsg= $commands[$command]['desc'];
	$logmsg= $commands[$command]['desc'];

	$valid= FALSE;
	// Check each argument in order
	for ($argpos= 0; $argpos < $check; $argpos++) {
		$arg= $argv[$argpos];
		$argtype= $commands[$command]['argv'][$argpos];

		// Multiple types may match for an arg, hence the foreach loop
		foreach ($ArgTypes as $type => $conf) {
			// Acceptable types are bitwise ORed, hence the AND here
			if ($argtype & $type) {
				$validatefunc= $conf['func'];
				if ($validatefunc($arg)) {
					$valid= TRUE;

					if ($type & FILEPATH) {
						// Further check if file really exists
						exec("[ -e $arg ]", $output, $retval);
						if ($retval !== 0) {
							$valid= FALSE;

							$errormsg= "$command: $arg";
							Error(_('No such file').": $errormsg");
							ctlr_syslog(LOG_NOTICE, __FILE__, __FUNCTION__, __LINE__, "No such file: $errormsg");
						}
					}

					if ($valid) {
						// One type succeded, hence do not check for other possible types for this arg
						break;
					}
				}
				else {
					$valid= FALSE;
					
					$helpmsg.= "\n"._($conf['desc']).': '.$arg;
					$logmsg.= "\n".$conf['desc'].': '.$arg;
					// Will keep checking if further types are possible for this arg
				}
			}
		}

		if (!$valid) {
			// One arg failed to check, do not run the func
			break;
		}
	}
	
	if (!$valid) {
		Error(_('Arg type check failed').": $helpmsg");
		ctlr_syslog(LOG_NOTICE, __FILE__, __FUNCTION__, __LINE__, "Arg type check failed: $logmsg");
	}
	return $valid;
}

/**
 * Expands command line args.
 *
 * @param array $ArgV Argument vector, modified
 * @param string $Locale The Locale to set
 * @param string $Command Model command to call
 * @return bool Validation result
 */
function ExpandArgs(&$ArgV, &$Locale, &$Command)
{
	$valid= FALSE;

	// Arg 0 contains all of the args as a json encoded string
	if (count($ArgV) < 1) {
		ctlr_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, 'Not enough args: '.print_r($ArgV, TRUE));
		goto out;
	}

	if (count($ArgV) > 1) {
		ctlr_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, 'Too many args: '.print_r($ArgV, TRUE));
		goto out;
	}

	$decoded= json_decode($ArgV[0], TRUE);

	if ($decoded == NULL || !is_array($decoded)) {
		ctlr_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, 'Failed decoding args: '.print_r($ArgV, TRUE));
		goto out;
	}

	$ArgV= $decoded;

	if (count($ArgV) < 2) {
		ctlr_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, 'Not enough args: '.print_r($ArgV, TRUE));
		goto out;
	}

	// Controller runs using the session locale of View
	$Locale= $ArgV[0];
	$Command= $ArgV[1];
	// Discard locale and command before computing arg counts
	$ArgV= array_slice($ArgV, 2);

	$valid= TRUE;
out:
	return $valid;
}

/**
 * Validates command line.
 *
 * @param array $ArgV Argument vector
 * @param string $Locale The Locale to set
 * @param string $Command Model command to call
 * @param bool $validateargs Whether to validate command args
 * @param object $Model Model class created
 * @return bool Validation result
 */
function ValidateCommand($ArgV, $Locale, $Command, $validateargs, &$Model)
{
	global $LOCALES, $VIEW_PATH;

	$Model= new Pf();

	/// @attention Do not set the locale until after the model file is included and the model is created,
	/// otherwise strings recorded into logs are also translated, such as the strings on the Commands array of models.
	/// Strings cannot be detranslated.
	if (!array_key_exists($Locale, $LOCALES)) {
		ctlr_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Locale not in LOCALES: $Locale");
		goto out;
	}

	putenv('LC_ALL='.$Locale);
	putenv('LANG='.$Locale);

	$Domain= 'pfre';
	bindtextdomain($Domain, $VIEW_PATH.'/locale');
	bind_textdomain_codeset($Domain, $LOCALES[$Locale]['Codeset']);
	textdomain($Domain);

	if (!method_exists($Model, $Command)) {
		$ErrorStr= "Pf->$Command()";
		Error(_('Method does not exist').": $ErrorStr");
		ctlr_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Method does not exist: $ErrorStr");
		goto out;
	}

	if (!array_key_exists($Command, $Model->Commands)) {
		Error(_('Unsupported command').": $Command");
		ctlr_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Unsupported command: $Command");
		goto out;
	}

	if (!$validateargs) {
		goto out2;
	}

	ComputeArgCounts($Model->Commands, $ArgV, $Command, $ActualArgC, $ExpectedArgC, $AcceptableArgC, $ArgCheckC);

	if ($ActualArgC < $AcceptableArgC) {
		$ErrorStr= "[$AcceptableArgC]: $ActualArgC";
		Error(_('Not enough args')." $ErrorStr");
		ctlr_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Not enough args $ErrorStr");
		goto out;
	}

	if ($ActualArgC > $ExpectedArgC) {
		$ErrorStr= "[$ExpectedArgC]: $ActualArgC: ".implode(', ', array_slice($ArgV, $ExpectedArgC));
		Error(_('Too many args')." $ErrorStr");
		ctlr_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Too many args $ErrorStr");
		goto out;
	}

	// Check only the relevant args
	if ($ArgCheckC > 0 && !ValidateArgs($Model->Commands, $Command, $ArgV, $ArgCheckC)) {
		Error(_('Not running command').": $Command");
		ctlr_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "Not running command: $Command");
		goto out;
	}
out2:
	$valid= TRUE;
out:
	return $valid;
}
?>
