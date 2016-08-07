<?php
/* $pfre: NatBase.php,v 1.8 2016/08/06 23:48:36 soner Exp $ */

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

class NatBase extends Filter
{
	function display($ruleNumber, $count)
	{
		$this->dispHead($ruleNumber);
		$this->dispAction();
		$this->dispValue('direction', 'Direction');
		$this->dispInterface();
		$this->dispLog();
		$this->dispKey('quick', 'Quick');
		$this->dispValue('proto', 'Proto');
		$this->dispSrcDest();
		$this->dispValues('redirhost', 'Redirect Host');
		$this->dispValue('redirport', 'Redirect Port');
		$this->dispTail($ruleNumber, $count);
	}
	
	function input()
	{
		$this->inputAction();

		$this->inputFilterHead();

		$this->inputLog();
		$this->inputBool('quick');

		$this->inputNat();

		$this->inputFilterOpts();

		$this->inputKey('comment');
		$this->inputDelEmpty();
	}

	function inputNat()
	{
		$this->inputDel('redirhost', 'delRedirHost');
		$this->inputAdd('redirhost', 'addRedirHost');
		$this->inputKey('redirport');
		$this->inputPoolType();
	}

	function edit($ruleNumber, $modified, $testResult, $generateResult, $action)
	{
		$this->editIndex= 0;
		$this->ruleNumber= $ruleNumber;

		$this->editHead($modified);

		$this->editAction();

		$this->editFilterHead();

		$this->editLog();
		$this->editCheckbox('quick', 'Quick');

		$this->editNat();

		$this->editFilterOpts();

		$this->editComment();
		$this->editTail($modified, $testResult, $generateResult, $action);
	}

	function editNat()
	{
		$this->editValues('redirhost', 'Redirect Host', 'delRedirHost', 'addRedirHost', 'ip, host, table or macro', 'Nat', NULL);
		$this->editText('redirport', 'Redirect Port', 'Nat', NULL, 'number, name, table or macro');
		$this->editPoolType();
	}
}
?>
