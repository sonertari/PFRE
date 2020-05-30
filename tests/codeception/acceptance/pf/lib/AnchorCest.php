<?php 
/*
 * Copyright (C) 2004-2020 Soner Tari
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

require_once ('Rule.php');

class AnchorCest extends Rule
{
	protected $type= 'Anchor';
	protected $ruleNumber= 2;
	protected $lineNumber= 2;
	protected $sender= 'anchor';

	protected $origRule= 'anchor "test" in on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 set queue (std, service) rtable 3 max-pkt-rate 100/10 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 ) {
block
anchor out {
pass proto tcp from any to port { 25, 80, 443 }
}
pass in proto tcp to any port 22
} # Test';
	protected $expectedDispOrigRule= '';

	protected $modifiedRule= 'anchor out from { 192.168.0.1, 1.1.1.1 } to port { ssh, 1111 } # Test1';
	protected $expectedDispModifiedRule= '';

	private $inline= ' {
block
anchor out {
pass proto tcp from any to port { 25, 80, 443 }
}
pass in proto tcp to any port 22
}';

	function __construct()
	{
		parent::__construct();

		$this->expectedDispOrigRule= $this->ruleNumber . ' ' . $this->type . ' ' . $this->lineNumber . '
' . ($this->lineNumber + 1) . '
' . ($this->lineNumber + 2) . '
' . ($this->lineNumber + 3) . '
' . ($this->lineNumber + 4) . '
' . ($this->lineNumber + 5) . '
' . ($this->lineNumber + 6) . ' test in
em0
tcp
192.168.0.1 ssh
2222
192.168.0.2 ssh keep
std
service block
anchor out {
pass proto tcp from any to port { 25, 80, 443 }
}
pass in proto tcp to any port 22 Test e u d x';
		$this->expectedDispModifiedRule= $this->ruleNumber . ' ' . $this->type . ' ' . $this->lineNumber . ' out
192.168.0.1
1.1.1.1
ssh
1111
Test1 e u d x';
		$this->revertedRule= 'anchor "test" in on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 max-pkt-rate 100/10 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 ) {
block
anchor out {
pass proto tcp from any to port { 25, 80, 443 }
}
pass in proto tcp to any port 22
} # Test';
	}

	protected function modifyRule(AcceptanceTester $I)
	{
		$I->fillField('#identifier', '');
		$this->clickApplySeeResult($I, 'anchor in on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 max-pkt-rate 100/10 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 )' . $this->inline . ' # Test');

		$I->selectOption('#direction', 'out');
		$this->clickApplySeeResult($I, 'anchor out on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 max-pkt-rate 100/10 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 )' . $this->inline . ' # Test');

		$this->clickDeleteLink($I, 'delInterface', 'em0');
		$this->seeResult($I, 'anchor out inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 max-pkt-rate 100/10 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 )' . $this->inline . ' # Test');

		$I->selectOption('#af', '');
		$this->clickApplySeeResult($I, 'anchor out proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 max-pkt-rate 100/10 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 )' . $this->inline . ' # Test');

		$this->clickDeleteLink($I, 'delProto', 'tcp');
		$this->seeResult($I, 'anchor out from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 max-pkt-rate 100/10 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 )' . $this->inline . ' # Test');

		$I->fillField('#addFrom', '1.1.1.1');
		$this->clickApplySeeResult($I, 'anchor out from { 192.168.0.1, 1.1.1.1 } port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 max-pkt-rate 100/10 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 )' . $this->inline . ' # Test');

		$this->clickDeleteLink($I, 'delFromPort', 'ssh');
		$this->seeResult($I, 'anchor out from { 192.168.0.1, 1.1.1.1 } port 2222 os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 max-pkt-rate 100/10 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 )' . $this->inline . ' # Test');

		$this->clickDeleteLink($I, 'delFromPort', '2222');
		$this->seeResult($I, 'anchor out from { 192.168.0.1, 1.1.1.1 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 max-pkt-rate 100/10 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 )' . $this->inline . ' # Test');

		$this->clickDeleteLink($I, 'delOs', 'openbsd');
		$this->seeResult($I, 'anchor out from { 192.168.0.1, 1.1.1.1 } to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 max-pkt-rate 100/10 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 )' . $this->inline . ' # Test');

		$this->clickDeleteLink($I, 'delTo', '192.168.0.2');
		$this->seeResult($I, 'anchor out from { 192.168.0.1, 1.1.1.1 } to port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 max-pkt-rate 100/10 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 )' . $this->inline . ' # Test');

		$I->fillField('#addToPort', '1111');
		$this->clickApplySeeResult($I, 'anchor out from { 192.168.0.1, 1.1.1.1 } to port { ssh, 1111 } user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 max-pkt-rate 100/10 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 )' . $this->inline . ' # Test');

		$this->clickDeleteLink($I, 'delUser', 'root');
		$this->seeResult($I, 'anchor out from { 192.168.0.1, 1.1.1.1 } to port { ssh, 1111 } group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 max-pkt-rate 100/10 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 )' . $this->inline . ' # Test');

		$this->clickDeleteLink($I, 'delGroup', 'wheel');
		$this->seeResult($I, 'anchor out from { 192.168.0.1, 1.1.1.1 } to port { ssh, 1111 } flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 max-pkt-rate 100/10 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 )' . $this->inline . ' # Test');

		$I->fillField('#flags', '');
		$this->clickApplySeeResult($I, 'anchor out from { 192.168.0.1, 1.1.1.1 } to port { ssh, 1111 } tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 max-pkt-rate 100/10 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 )' . $this->inline . ' # Test');

		$I->fillField('#tos', '');
		$this->clickApplySeeResult($I, 'anchor out from { 192.168.0.1, 1.1.1.1 } to port { ssh, 1111 } allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 max-pkt-rate 100/10 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 )' . $this->inline . ' # Test');

		$I->uncheckOption('#allow-opts');
		$this->clickApplySeeResult($I, 'anchor out from { 192.168.0.1, 1.1.1.1 } to port { ssh, 1111 } once label "test" tag "test" !tagged "test" set prio 2 rtable 3 max-pkt-rate 100/10 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 )' . $this->inline . ' # Test');

		$I->uncheckOption('#once');
		$this->clickApplySeeResult($I, 'anchor out from { 192.168.0.1, 1.1.1.1 } to port { ssh, 1111 } label "test" tag "test" !tagged "test" set prio 2 rtable 3 max-pkt-rate 100/10 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 )' . $this->inline . ' # Test');

		$I->fillField('#label', '');
		$this->clickApplySeeResult($I, 'anchor out from { 192.168.0.1, 1.1.1.1 } to port { ssh, 1111 } tag "test" !tagged "test" set prio 2 rtable 3 max-pkt-rate 100/10 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 )' . $this->inline . ' # Test');

		$I->fillField('#tag', '');
		$this->clickApplySeeResult($I, 'anchor out from { 192.168.0.1, 1.1.1.1 } to port { ssh, 1111 } !tagged "test" set prio 2 rtable 3 max-pkt-rate 100/10 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 )' . $this->inline . ' # Test');

		$I->uncheckOption('#not-tagged');
		$this->clickApplySeeResult($I, 'anchor out from { 192.168.0.1, 1.1.1.1 } to port { ssh, 1111 } tagged "test" set prio 2 rtable 3 max-pkt-rate 100/10 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 )' . $this->inline . ' # Test');

		$I->fillField('#tagged', '');
		$this->clickApplySeeResult($I, 'anchor out from { 192.168.0.1, 1.1.1.1 } to port { ssh, 1111 } set prio 2 rtable 3 max-pkt-rate 100/10 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 )' . $this->inline . ' # Test');

		$this->clickDeleteLink($I, 'delPrio', '2');
		$this->seeResult($I, 'anchor out from { 192.168.0.1, 1.1.1.1 } to port { ssh, 1111 } rtable 3 max-pkt-rate 100/10 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 )' . $this->inline . ' # Test');

//		$I->selectOption('#queueSec', '');
//		$this->clickApplySeeResult($I, 'anchor out from { 192.168.0.1, 1.1.1.1 } to port { ssh, 1111 } set queue std rtable 3 max-pkt-rate 100/10 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 )' . $this->inline . ' # Test');
//
//		$I->selectOption('#queuePri', '');
//		$this->clickApplySeeResult($I, 'anchor out from { 192.168.0.1, 1.1.1.1 } to port { ssh, 1111 } rtable 3 max-pkt-rate 100/10 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 )' . $this->inline . ' # Test');

		$I->fillField('#rtable', '');
		$this->clickApplySeeResult($I, 'anchor out from { 192.168.0.1, 1.1.1.1 } to port { ssh, 1111 } max-pkt-rate 100/10 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 )' . $this->inline . ' # Test');

		$I->fillField('#max-pkt-rate', '');
		$this->clickApplySeeResult($I, 'anchor out from { 192.168.0.1, 1.1.1.1 } to port { ssh, 1111 } probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 )' . $this->inline . ' # Test');

		$I->fillField('#probability', '');
		$this->clickApplySeeResult($I, 'anchor out from { 192.168.0.1, 1.1.1.1 } to port { ssh, 1111 } prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 )' . $this->inline . ' # Test');

		$I->fillField('#prio', '');
		$this->clickApplySeeResult($I, 'anchor out from { 192.168.0.1, 1.1.1.1 } to port { ssh, 1111 } set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 )' . $this->inline . ' # Test');

		$I->fillField('#set-tos', '');
		$this->clickApplySeeResult($I, 'anchor out from { 192.168.0.1, 1.1.1.1 } to port { ssh, 1111 } !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 )' . $this->inline . ' # Test');

		$I->uncheckOption('#not-received-on');
		$this->clickApplySeeResult($I, 'anchor out from { 192.168.0.1, 1.1.1.1 } to port { ssh, 1111 } received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 )' . $this->inline . ' # Test');

		$I->fillField('#received-on', '');
		$this->clickApplySeeResult($I, 'anchor out from { 192.168.0.1, 1.1.1.1 } to port { ssh, 1111 } keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 )' . $this->inline . ' # Test');

		$I->selectOption('#state-filter', '');
		$this->clickApplySeeResult($I, 'anchor out from { 192.168.0.1, 1.1.1.1 } to port { ssh, 1111 }' . $this->inline . ' # Test');

		$I->fillField('#inline', '');
		$this->clickApplySeeResult($I, 'anchor out from { 192.168.0.1, 1.1.1.1 } to port { ssh, 1111 } # Test');

		$I->fillField('#comment', 'Test1');
		$this->clickApplySeeResult($I, $this->modifiedRule);

		// Apply again to delete stale elements in the rule struct, specifically the state options
		$I->click('Apply');
	}

	protected function revertModifications(AcceptanceTester $I)
	{
		$I->fillField('#identifier', 'test');
		$this->clickApplySeeResult($I, 'anchor "test" out from { 192.168.0.1, 1.1.1.1 } to port { ssh, 1111 } # Test1');

		$I->selectOption('#direction', 'in');
		$this->clickApplySeeResult($I, 'anchor "test" in from { 192.168.0.1, 1.1.1.1 } to port { ssh, 1111 } # Test1');

		$I->fillField('#addInterface', 'em0');
		$this->clickApplySeeResult($I, 'anchor "test" in on em0 from { 192.168.0.1, 1.1.1.1 } to port { ssh, 1111 } # Test1');

		$I->selectOption('#af', 'inet');
		$this->clickApplySeeResult($I, 'anchor "test" in on em0 inet from { 192.168.0.1, 1.1.1.1 } to port { ssh, 1111 } # Test1');

		$I->fillField('#addProto', 'tcp');
		$this->clickApplySeeResult($I, 'anchor "test" in on em0 inet proto tcp from { 192.168.0.1, 1.1.1.1 } to port { ssh, 1111 } # Test1');

		$this->clickDeleteLink($I, 'delFrom', '1.1.1.1');
		$this->seeResult($I, 'anchor "test" in on em0 inet proto tcp from 192.168.0.1 to port { ssh, 1111 } # Test1');

		$I->fillField('#addFromPort', 'ssh');
		$this->clickApplySeeResult($I, 'anchor "test" in on em0 inet proto tcp from 192.168.0.1 port ssh to port { ssh, 1111 } # Test1');

		$I->fillField('#addFromPort', '2222');
		$this->clickApplySeeResult($I, 'anchor "test" in on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } to port { ssh, 1111 } # Test1');

		$I->fillField('#addOs', 'openbsd');
		$this->clickApplySeeResult($I, 'anchor "test" in on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to port { ssh, 1111 } # Test1');

		$I->fillField('#addTo', '192.168.0.2');
		$this->clickApplySeeResult($I, 'anchor "test" in on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port { ssh, 1111 } # Test1');

		$this->clickDeleteLink($I, 'delToPort', '1111');
		$this->seeResult($I, 'anchor "test" in on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh # Test1');

		$I->fillField('#addUser', 'root');
		$this->clickApplySeeResult($I, 'anchor "test" in on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root # Test1');

		$I->fillField('#addGroup', 'wheel');
		$this->clickApplySeeResult($I, 'anchor "test" in on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel # Test1');

		$I->fillField('#flags', 'S/SA');
		$this->clickApplySeeResult($I, 'anchor "test" in on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA # Test1');

		$I->fillField('#tos', '1');
		$this->clickApplySeeResult($I, 'anchor "test" in on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 # Test1');

		$I->checkOption('#allow-opts');
		$this->clickApplySeeResult($I, 'anchor "test" in on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts # Test1');

		$I->checkOption('#once');
		$this->clickApplySeeResult($I, 'anchor "test" in on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once # Test1');

		$I->fillField('#label', 'test');
		$this->clickApplySeeResult($I, 'anchor "test" in on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" # Test1');

		$I->fillField('#tag', 'test');
		$this->clickApplySeeResult($I, 'anchor "test" in on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" # Test1');

		$I->fillField('#tagged', 'test');
		$this->clickApplySeeResult($I, 'anchor "test" in on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" tagged "test" # Test1');

		$I->checkOption('#not-tagged');
		$this->clickApplySeeResult($I, 'anchor "test" in on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" # Test1');

		$I->fillField('#addPrio', '2');
		$this->clickApplySeeResult($I, 'anchor "test" in on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 # Test1');

//		$I->selectOption('#queuePri', 'std');
//		$I->click('Apply');
//
//		$I->selectOption('#queueSec', 'service');

		$I->fillField('#rtable', '3');
		$this->clickApplySeeResult($I, 'anchor "test" in on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 # Test1');

		$I->fillField('#max-pkt-rate', '100/10');
		$this->clickApplySeeResult($I, 'anchor "test" in on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 max-pkt-rate 100/10 # Test1');

		$I->fillField('#probability', '10%');
		$this->clickApplySeeResult($I, 'anchor "test" in on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 max-pkt-rate 100/10 probability 10% # Test1');

		$I->fillField('#prio', '4');
		$this->clickApplySeeResult($I, 'anchor "test" in on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 max-pkt-rate 100/10 probability 10% prio 4 # Test1');

		$I->fillField('#set-tos', '5');
		$this->clickApplySeeResult($I, 'anchor "test" in on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 max-pkt-rate 100/10 probability 10% prio 4 set tos 5 # Test1');

		$I->fillField('#received-on', 'em0');
		$this->clickApplySeeResult($I, 'anchor "test" in on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 max-pkt-rate 100/10 probability 10% prio 4 set tos 5 received-on em0 # Test1');

		$I->checkOption('#not-received-on');
		$this->clickApplySeeResult($I, 'anchor "test" in on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 max-pkt-rate 100/10 probability 10% prio 4 set tos 5 !received-on em0 # Test1');

		/// @todo Fix frag and interval? How?
		$I->selectOption('#state-filter', 'Keep State');
		$this->clickApplySeeResult($I, 'anchor "test" in on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 max-pkt-rate 100/10 probability 10% prio 4 set tos 5 !received-on em0 keep state ( frag 1, interval 2 ) # Test1');

		$I->fillField('#max', '1');
		$this->clickApplySeeResult($I, 'anchor "test" in on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 max-pkt-rate 100/10 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, frag 1, interval 2 ) # Test1');

		$I->fillField('#max-src-states', '2');
		$this->clickApplySeeResult($I, 'anchor "test" in on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 max-pkt-rate 100/10 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, frag 1, interval 2 ) # Test1');

		$I->fillField('#max-src-nodes', '3');
		$this->clickApplySeeResult($I, 'anchor "test" in on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 max-pkt-rate 100/10 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, frag 1, interval 2 ) # Test1');

		$I->fillField('#max-src-conn', '4');
		$this->clickApplySeeResult($I, 'anchor "test" in on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 max-pkt-rate 100/10 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, frag 1, interval 2 ) # Test1');

		$I->fillField('#max-src-conn-rate', '5/5');
		$this->clickApplySeeResult($I, 'anchor "test" in on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 max-pkt-rate 100/10 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, frag 1, interval 2 ) # Test1');

		$I->checkOption('#sloppy');
		$this->clickApplySeeResult($I, 'anchor "test" in on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 max-pkt-rate 100/10 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, frag 1, interval 2 ) # Test1');

		$I->checkOption('#no-sync');
		$this->clickApplySeeResult($I, 'anchor "test" in on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 max-pkt-rate 100/10 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, frag 1, interval 2 ) # Test1');

		$I->checkOption('#pflow');
		$this->clickApplySeeResult($I, 'anchor "test" in on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 max-pkt-rate 100/10 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, frag 1, interval 2 ) # Test1');

		$I->checkOption('#if-bound');
		$this->clickApplySeeResult($I, 'anchor "test" in on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 max-pkt-rate 100/10 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, frag 1, interval 2 ) # Test1');

		$I->fillField('#overload', 'over');
		$this->clickApplySeeResult($I, 'anchor "test" in on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 max-pkt-rate 100/10 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over>, frag 1, interval 2 ) # Test1');

		$I->checkOption('#flush');
		$this->clickApplySeeResult($I, 'anchor "test" in on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 max-pkt-rate 100/10 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush, frag 1, interval 2 ) # Test1');

		$I->checkOption('#global');
		$this->clickApplySeeResult($I, 'anchor "test" in on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 max-pkt-rate 100/10 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, frag 1, interval 2 ) # Test1');

		$I->checkOption('#source-track');
		$this->clickApplySeeResult($I, 'anchor "test" in on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 max-pkt-rate 100/10 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track, frag 1, interval 2 ) # Test1');

		$I->selectOption('#source-track-option', 'rule');
		$this->clickApplySeeResult($I, 'anchor "test" in on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 max-pkt-rate 100/10 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2 ) # Test1');

		$I->fillField('#src_track', '3');
		$this->clickApplySeeResult($I, 'anchor "test" in on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 max-pkt-rate 100/10 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3 ) # Test1');

		$I->fillField('#tcp_first', '4');
		$this->clickApplySeeResult($I, 'anchor "test" in on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 max-pkt-rate 100/10 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4 ) # Test1');

		$I->fillField('#tcp_opening', '5');
		$this->clickApplySeeResult($I, 'anchor "test" in on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 max-pkt-rate 100/10 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5 ) # Test1');

		$I->fillField('#tcp_established', '6');
		$this->clickApplySeeResult($I, 'anchor "test" in on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 max-pkt-rate 100/10 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6 ) # Test1');

		$I->fillField('#tcp_closing', '7');
		$this->clickApplySeeResult($I, 'anchor "test" in on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 max-pkt-rate 100/10 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7 ) # Test1');

		$I->fillField('#tcp_finwait', '8');
		$this->clickApplySeeResult($I, 'anchor "test" in on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 max-pkt-rate 100/10 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8 ) # Test1');

		$I->fillField('#tcp_closed', '9');
		$this->clickApplySeeResult($I, 'anchor "test" in on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 max-pkt-rate 100/10 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9 ) # Test1');

		$I->fillField('#udp_first', '10');
		$this->clickApplySeeResult($I, 'anchor "test" in on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 max-pkt-rate 100/10 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10 ) # Test1');

		$I->fillField('#udp_single', '11');
		$this->clickApplySeeResult($I, 'anchor "test" in on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 max-pkt-rate 100/10 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11 ) # Test1');

		$I->fillField('#udp_multiple', '12');
		$this->clickApplySeeResult($I, 'anchor "test" in on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 max-pkt-rate 100/10 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12 ) # Test1');

		$I->fillField('#icmp_first', '13');
		$this->clickApplySeeResult($I, 'anchor "test" in on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 max-pkt-rate 100/10 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13 ) # Test1');

		$I->fillField('#icmp_error', '14');
		$this->clickApplySeeResult($I, 'anchor "test" in on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 max-pkt-rate 100/10 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14 ) # Test1');

		$I->fillField('#other_first', '15');
		$this->clickApplySeeResult($I, 'anchor "test" in on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 max-pkt-rate 100/10 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15 ) # Test1');

		$I->fillField('#other_single', '16');
		$this->clickApplySeeResult($I, 'anchor "test" in on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 max-pkt-rate 100/10 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16 ) # Test1');

		$I->fillField('#other_multiple', '17');
		$this->clickApplySeeResult($I, 'anchor "test" in on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 max-pkt-rate 100/10 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17 ) # Test1');

		$I->fillField('#adaptive_start', '18');
		$this->clickApplySeeResult($I, 'anchor "test" in on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 max-pkt-rate 100/10 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18 ) # Test1');

		$I->fillField('#adaptive_end', '19');
		$this->clickApplySeeResult($I, 'anchor "test" in on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 max-pkt-rate 100/10 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 ) # Test1');

		///@todo Find out how to insert \t chars in textarea, otherwise each \t char advances focus to the next control in tab order
//		$I->fillField('#inline', '	block
//	anchor out {
//		pass proto tcp from any to port { 25, 80, 443 }
//	}
//	pass in proto tcp to any port 22
//');
		$I->fillField('#inline', 'block
anchor out {
pass proto tcp from any to port { 25, 80, 443 }
}
pass in proto tcp to any port 22
');
		$this->clickApplySeeResult($I, 'anchor "test" in on em0 inet proto tcp from 192.168.0.1 port { ssh, 2222 } os openbsd to 192.168.0.2 port ssh user root group wheel flags S/SA tos 1 allow-opts once label "test" tag "test" !tagged "test" set prio 2 rtable 3 max-pkt-rate 100/10 probability 10% prio 4 set tos 5 !received-on em0 keep state ( max 1, max-src-states 2, max-src-nodes 3, max-src-conn 4, max-src-conn-rate 5/5, sloppy, no-sync, pflow, if-bound, overload <over> flush global, source-track rule, frag 1, interval 2, src.track 3, tcp.first 4, tcp.opening 5, tcp.established 6, tcp.closing 7, tcp.finwait 8, tcp.closed 9, udp.first 10, udp.single 11, udp.multiple 12, icmp.first 13, icmp.error 14, other.first 15, other.single 16, other.multiple 17, adaptive.start 18, adaptive.end 19 ) {
block
anchor out {
pass proto tcp from any to port { 25, 80, 443 }
}
pass in proto tcp to any port 22
} # Test1');

		$I->fillField('#comment', 'Test');
		$this->clickApplySeeResult($I, $this->revertedRule);
	}

	protected function modifyRuleQuick(AcceptanceTester $I)
	{
		$I->fillField('#identifier', '');
		$I->selectOption('#direction', 'out');
		$I->click('Apply');

		$this->clickDeleteLink($I, 'delInterface', 'em0');
		$I->selectOption('#af', '');
		$I->click('Apply');

		$this->clickDeleteLink($I, 'delProto', 'tcp');
		$I->fillField('#addFrom', '1.1.1.1');
		$I->click('Apply');

		$this->clickDeleteLink($I, 'delFromPort', 'ssh');
		$this->clickDeleteLink($I, 'delFromPort', '2222');
		$this->clickDeleteLink($I, 'delOs', 'openbsd');
		$this->clickDeleteLink($I, 'delTo', '192.168.0.2');

		$I->fillField('#addToPort', '1111');
		$I->click('Apply');

		$this->clickDeleteLink($I, 'delUser', 'root');
		$this->clickDeleteLink($I, 'delGroup', 'wheel');

		$I->fillField('#flags', '');
		$I->fillField('#tos', '');
		$I->uncheckOption('#allow-opts');
		$I->uncheckOption('#once');
		$I->fillField('#label', '');
		$I->fillField('#tag', '');
		$I->uncheckOption('#not-tagged');
		$I->fillField('#tagged', '');
		$I->click('Apply');

		$this->clickDeleteLink($I, 'delPrio', '2');

		$I->selectOption('#queuePri', '');
		$I->selectOption('#queueSec', '');
		$I->fillField('#rtable', '');
		$I->fillField('#max-pkt-rate', '');
		$I->fillField('#probability', '');
		$I->fillField('#prio', '');
		$I->fillField('#set-tos', '');
		$I->uncheckOption('#not-received-on');
		$I->fillField('#received-on', '');
		$I->selectOption('#state-filter', '');
		$I->fillField('#inline', '');
		$I->fillField('#comment', 'Test1');
		$I->click('Apply');
		
		// Apply again to delete stale elements in the rule struct, specifically the state options
		$I->click('Apply');
	}

	protected function revertModificationsQuick(AcceptanceTester $I)
	{
		$I->fillField('#identifier', 'test');
		$I->selectOption('#direction', 'in');
		$I->fillField('#addInterface', 'em0');
		$I->selectOption('#af', 'inet');
		$I->fillField('#addProto', 'tcp');
		$I->click('Apply');

		$this->clickDeleteLink($I, 'delFrom', '1.1.1.1');

		$I->fillField('#addFromPort', 'ssh');
		$I->click('Apply');

		$I->fillField('#addFromPort', '2222');
		$I->fillField('#addOs', 'openbsd');
		$I->fillField('#addTo', '192.168.0.2');
		$I->click('Apply');

		$this->clickDeleteLink($I, 'delToPort', '1111');

		$I->fillField('#addUser', 'root');
		$I->fillField('#addGroup', 'wheel');
		$I->fillField('#flags', 'S/SA');
		$I->fillField('#tos', '1');
		$I->checkOption('#allow-opts');
		$I->checkOption('#once');
		$I->fillField('#label', 'test');
		$I->fillField('#tag', 'test');
		$I->fillField('#tagged', 'test');
		$I->click('Apply');

		$I->checkOption('#not-tagged');
		$I->fillField('#addPrio', '2');
		$I->fillField('#rtable', '3');
		$I->fillField('#max-pkt-rate', '100/10');
		$I->fillField('#probability', '10%');
		$I->fillField('#prio', '4');
		$I->fillField('#set-tos', '5');
		$I->fillField('#received-on', 'em0');
		$I->click('Apply');

		$I->checkOption('#not-received-on');
		$I->selectOption('#state-filter', 'Keep State');
		$I->click('Apply');

		$I->fillField('#max', '1');
		$I->fillField('#max-src-states', '2');
		$I->fillField('#max-src-nodes', '3');
		$I->fillField('#max-src-conn', '4');
		$I->fillField('#max-src-conn-rate', '5/5');
		$I->checkOption('#sloppy');
		$I->checkOption('#no-sync');
		$I->checkOption('#pflow');
		$I->checkOption('#if-bound');
		$I->fillField('#overload', 'over');
		$I->click('Apply');

		$I->checkOption('#flush');
		$I->click('Apply');

		$I->checkOption('#global');
		$I->checkOption('#source-track');
		$I->click('Apply');

		$I->selectOption('#source-track-option', 'rule');
		$I->fillField('#src_track', '3');
		$I->fillField('#tcp_first', '4');
		$I->fillField('#tcp_opening', '5');
		$I->fillField('#tcp_established', '6');
		$I->fillField('#tcp_closing', '7');
		$I->fillField('#tcp_finwait', '8');
		$I->fillField('#tcp_closed', '9');
		$I->fillField('#udp_first', '10');
		$I->fillField('#udp_single', '11');
		$I->fillField('#udp_multiple', '12');
		$I->fillField('#icmp_first', '13');
		$I->fillField('#icmp_error', '14');
		$I->fillField('#other_first', '15');
		$I->fillField('#other_single', '16');
		$I->fillField('#other_multiple', '17');
		$I->fillField('#adaptive_start', '18');
		$I->fillField('#adaptive_end', '19');
		$I->fillField('#inline', 'block
anchor out {
pass proto tcp from any to port { 25, 80, 443 }
}
pass in proto tcp to any port 22
');
		$I->fillField('#comment', 'Test');
		$I->click('Apply');
	}
}
?>