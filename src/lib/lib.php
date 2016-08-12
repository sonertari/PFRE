<?php
/* $pfre: lib.php,v 1.6 2016/08/12 14:18:43 soner Exp $ */

/*
 * Copyright (c) 2016 Soner Tari.  All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 * 1. Redistributions of source code must retain the above copyright
 *    notice, this list of conditions and the following disclaimer.
 * 2. Redistributions in binary form must reproduce the above copyright
 *    notice, this list of conditions and the following disclaimer in the
 *    documentation and/or other materials provided with the distribution.
 * 3. All advertising materials mentioning features or use of this
 *    software must display the following acknowledgement: This
 *    product includes software developed by Soner Tari
 *    and its contributors.
 * 4. Neither the name of Soner Tari nor the names of
 *    its contributors may be used to endorse or promote products
 *    derived from this software without specific prior written
 *    permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE AUTHOR ``AS IS'' AND ANY EXPRESS OR
 * IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES
 * OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED.
 * IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT
 * NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF
 * THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

/** @file
 * Project-wide common functions.
 */

function IsFilePath($str)
{
	global $PF_CONFIG_PATH, $TMP_PATH, $TEST_DIR_PATH;

	// If we are not testing the controller, we should expect regular file paths
	// For example, _Include or LoadAnchor type of rules accept regular paths, not test ones
	/// @attention ? should never appear in regex patterns, this is better than using / or | chars
	return
		// For CVS Tag displayed in the footer
		preg_match("?^($TEST_DIR_PATH|)/var/www/htdocs/pfre/View/\w[\w./\-_]*$?", $str)
		// pf configuration files
		|| preg_match("?^($TEST_DIR_PATH|)$PF_CONFIG_PATH/\w[\w.\-_]*$?", $str)
		|| preg_match("?^($TEST_DIR_PATH|)/etc/\w[\w.\-_]*$?", $str)
		// Uploaded tmp files
		|| preg_match("?^($TEST_DIR_PATH|)$TMP_PATH/\w[\w.\-_]*$?", $str);
}

function FlattenArray(&$array)
{
	if (count($array) == 1) {
		/// @attention Don't use 0 as key to fetch the last value
		$array= $array[key($array)];
	}
}
?>
