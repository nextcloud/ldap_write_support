<?php
/**

 *
 * @author Bjoern Schiessle <bjoern@schiessle.org>
 * @author Jan-Christoph Borchardt <hey@jancborchardt.net>
 * @author Lukas Reschke <lukas@statuscode.ch>
 *
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */
script('ldapusermanagement', 'settings-admin');
// script('theming', '3rdparty/jscolor/jscolor');
style('ldapusermanagement', 'settings-admin');
?>
<div id="ldapusermanagement" class="section">
	<h2 class="inlineblock"><?php p($l->t('Ldap User Management')); ?></h2>
		<div id="ldapusermanagement_settings_msg" class="msg success inlineblock" style="display: none;">Saved</div>
	<div>
		<label>
			<span><?php p($l->t('Host')) ?></span>
			<input id="ldapusermanagement-host" type="text" placeholder="<?php p($l->t('Host')); ?>" value="<?php p($_['host']) ?>" maxlength="250" />
			<div data-setting="host" data-toggle="tooltip" data-original-title="<?php p($l->t('reset to default')); ?>" class="theme-undo icon icon-history"></div>
		</label>
	</div>
	<div>
		<label>
			<span><?php p($l->t('Port')) ?></span>
			<input id="ldapusermanagement-port" type="text" placeholder="<?php p($l->t('Port')); ?>" value="<?php p($_['port']) ?>" maxlength="500" />
			<div data-setting="port" data-toggle="tooltip" data-original-title="<?php p($l->t('reset to default')); ?>" class="theme-undo icon icon-history"></div>
		</label>
	</div>
	<div>
		<label>
			<span><?php p($l->t('DN')) ?></span>
			<input id="ldapusermanagement-dn" type="text" placeholder="<?php p($l->t('DN')); ?>" value="<?php p($_['dn']) ?>" maxlength="500" />
			<div data-setting="dn" data-toggle="tooltip" data-original-title="<?php p($l->t('reset to default')); ?>" class="theme-undo icon icon-history"></div>
		</label>
	</div>
	<div>
		<label>
			<span><?php p($l->t('Password')) ?></span>
			<input id="ldapusermanagement-password" type="text" placeholder="<?php p($l->t('Password')); ?>" value="<?php p($_['password']) ?>" maxlength="500" />
			<div data-setting="password" data-toggle="tooltip" data-original-title="<?php p($l->t('reset to default')); ?>" class="theme-undo icon icon-history"></div>
		</label>
	</div>
	<div>
		<label>
			<span><?php p($l->t('User Base')) ?></span>
			<input id="ldapusermanagement-userbase" type="text" placeholder="<?php p($l->t('User Base')); ?>" value="<?php p($_['userbase']) ?>" maxlength="500" />
			<div data-setting="userbase" data-toggle="tooltip" data-original-title="<?php p($l->t('reset to default')); ?>" class="theme-undo icon icon-history"></div>
		</label>
	</div>
	<div>
		<label>
			<span><?php p($l->t('Group Base')) ?></span>
			<input id="ldapusermanagement-groupbase" type="text" placeholder="<?php p($l->t('Group Base')); ?>" value="<?php p($_['groupbase']) ?>" maxlength="500" />
			<div data-setting="groupbase" data-toggle="tooltip" data-original-title="<?php p($l->t('reset to default')); ?>" class="theme-undo icon icon-history"></div>
		</label>
	</div>
</div>
