<!--
  - @copyright 2019 Christoph Wurst <christoph@winzerhof-wurst.at>
  -
  - @author 2019 Christoph Wurst <christoph@winzerhof-wurst.at>
  -
  - @license GNU AGPL version 3 or any later version
  -
  - This program is free software: you can redistribute it and/or modify
  - it under the terms of the GNU Affero General Public License as
  - published by the Free Software Foundation, either version 3 of the
  - License, or (at your option) any later version.
  -
  - This program is distributed in the hope that it will be useful,
  - but WITHOUT ANY WARRANTY; without even the implied warranty of
  - MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  - GNU Affero General Public License for more details.
  -
  - You should have received a copy of the GNU Affero General Public License
  - along with this program.  If not, see <http://www.gnu.org/licenses/>.
  -->

<template>
	<div id="ldap-write-support-admin-settings" class="section">
		<h2>{{ t('ldap_write_support', 'Writing') }}</h2>
		<h3>{{ t('ldap_write_support', 'User template') }}</h3>
		<p>{{ t('ldap_write_support', 'LDIF template for creating users. Following placeholders may be used') }}</p>
		<ul>
			<li><span class="mono">{UID}</span> – {{ t('ldap_write_support', 'the user id provided by the (sub)admin') }}</li>
			<li><span class="mono">{PWD}</span> – {{ t('ldap_write_support', 'the password provided by the (sub)admin') }}</li>
			<li><span class="mono">{BASE}</span> – {{ t('ldap_write_support', 'the LDAP node of the acting (sub)admin or the configured user base') }}</li>
		</ul>
		<textarea class="mono" v-on:change="setUserTemplate" v-model="templates.user">{{templates.user}}</textarea>
	</div>
</template>

<script>
	export default {
		name: 'AdminSettings',
		props: {
			templates: {
				user: Object,
				userDefault: Object,
			},
		},
		components: {
		},
		methods: {
			setUserTemplate() {
				if(this.templates.user === "") {
					let self = this;
					OCP.AppConfig.deleteKey('ldap_write_support', 'template.user', {
						success: function() {
							self.templates.user = self.templates.userDefault;
						}
					});
					return;
				}
				OCP.AppConfig.setValue('ldap_write_support', 'template.user', this.templates.user);
			}
		}
	}
</script>
