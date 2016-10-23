<?php
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
 * Required includes.
 */

$ROOT= dirname(dirname(dirname(dirname(__FILE__))));
$SRC_ROOT= dirname(dirname(dirname(__FILE__)));

require_once($SRC_ROOT.'/lib/defs.php');
require_once($SRC_ROOT.'/lib/setup.php');
require_once($SRC_ROOT.'/lib/lib.php');

require_once($VIEW_PATH.'/lib/setup.php');

/// PF module absolute path.
$PF_PATH= $VIEW_PATH.'/pf';

// Include these before session start in /lib/libauth.php
// because we save instances of these in the session
require_once($PF_PATH.'/lib/RuleSet.php');
require_once($PF_PATH.'/lib/Rule.php');
require_once($PF_PATH.'/lib/Timeout.php');
require_once($PF_PATH.'/lib/State.php');
require_once($PF_PATH.'/lib/FilterBase.php');
require_once($PF_PATH.'/lib/Filter.php');
require_once($PF_PATH.'/lib/Antispoof.php');
require_once($PF_PATH.'/lib/Anchor.php');
require_once($PF_PATH.'/lib/NatBase.php');
require_once($PF_PATH.'/lib/NatTo.php');
require_once($PF_PATH.'/lib/BinatTo.php');
require_once($PF_PATH.'/lib/RdrTo.php');
require_once($PF_PATH.'/lib/AfTo.php');
require_once($PF_PATH.'/lib/DivertTo.php');
require_once($PF_PATH.'/lib/DivertPacket.php');
require_once($PF_PATH.'/lib/Route.php');
require_once($PF_PATH.'/lib/Macro.php');
require_once($PF_PATH.'/lib/Table.php');
require_once($PF_PATH.'/lib/Queue.php');
require_once($PF_PATH.'/lib/Scrub.php');
require_once($PF_PATH.'/lib/Option.php');
require_once($PF_PATH.'/lib/Limit.php');
require_once($PF_PATH.'/lib/LoadAnchor.php');
require_once($PF_PATH.'/lib/Include.php');
require_once($PF_PATH.'/lib/Comment.php');
require_once($PF_PATH.'/lib/Blank.php');
?>
