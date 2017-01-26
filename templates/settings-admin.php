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
<div id="theming" class="section">
	<h2 class="inlineblock"><?php p($l->t('Ldap User Management')); ?></h2>
		<div id="theming_settings_msg" class="msg success inlineblock" style="display: none;">Saved</div>
	<?php if ($_['themable'] === false) { ?>
	<p>
		<?php p($_['errorMessage']) ?>
	</p>
	<?php } else { ?>
	<div>
		<label>
			<span><?php p($l->t('Host')) ?></span>
			<input id="theming-name" type="text" placeholder="<?php p($l->t('Host')); ?>" value="<?php p($_['host']) ?>" maxlength="250" />
			<div data-setting="name" data-toggle="tooltip" data-original-title="<?php p($l->t('reset to default')); ?>" class="theme-undo icon icon-history"></div>
		</label>
	</div>
	<div>
		<label>
			<span><?php p($l->t('Port')) ?></span>
			<input id="theming-url" type="text" placeholder="<?php p($l->t('Port')); ?>" value="<?php p($_['port']) ?>" maxlength="500" />
			<div data-setting="url" data-toggle="tooltip" data-original-title="<?php p($l->t('reset to default')); ?>" class="theme-undo icon icon-history"></div>
		</label>
	</div>
	<div>
		<label>
			<span><?php p($l->t('DN')) ?></span>
			<input id="theming-slogan" type="text" placeholder="<?php p($l->t('DN')); ?>" value="<?php p($_['dn']) ?>" maxlength="500" />
			<div data-setting="slogan" data-toggle="tooltip" data-original-title="<?php p($l->t('reset to default')); ?>" class="theme-undo icon icon-history"></div>
		</label>
	</div>
	<div>
		<label>
			<span><?php p($l->t('Password')) ?></span>
			<input id="theming-slogan" type="text" placeholder="<?php p($l->t('Password')); ?>" value="<?php p($_['password']) ?>" maxlength="500" />
			<div data-setting="slogan" data-toggle="tooltip" data-original-title="<?php p($l->t('reset to default')); ?>" class="theme-undo icon icon-history"></div>
		</label>
	</div>

	<?php } ?>
</div>
