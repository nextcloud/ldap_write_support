<!--
  - SPDX-FileCopyrightText: 2019 Nextcloud GmbH and Nextcloud contributors
  - SPDX-License-Identifier: AGPL-3.0-or-later
-->

<script setup lang="ts">
import { showError } from '@nextcloud/dialogs'
import { t } from '@nextcloud/l10n'
import { ref } from 'vue'
import NcActionCheckbox from '@nextcloud/vue/components/NcActionCheckbox'

import '@nextcloud/dialogs/style.css'

interface Templates {
	user: string
	userDefault: string
}

const props = defineProps<{
	templates: Templates
	switches: Record<string, boolean>
}>()

const userTemplate = ref(props.templates.user.slice())
const checkboxes = ref({ ...props.switches })

/**
 * Persist the user template, or reset to the default when it is empty.
 */
function setUserTemplate(): void {
	if (props.templates.user === '') {
		OCP.AppConfig.deleteKey('ldap_write_support', 'template.user', {
			success: () => {
				userTemplate.value = props.templates.userDefault.slice()
			},
			error: () => showError(t('ldap_write_support', 'Failed to set user template.')),
		})
		return
	}
	OCP.AppConfig.setValue('ldap_write_support', 'template.user', userTemplate.value)
}

/**
 * Toggle a switch and persist it through the app config.
 *
 * @param prefKey - The preference key to write
 * @param state - The new checked state
 * @param appId - The app the preference belongs to
 */
function toggleSwitch(prefKey: string, state: boolean, appId = 'ldap_write_support'): void {
	checkboxes.value[prefKey] = state
	let value = Number(state).toString()
	if (appId === 'core') {
		// the database key has a slighlty different style, need to transform
		prefKey = 'newUser.' + prefKey.charAt(7).toLowerCase() + prefKey.slice(8)
		value = value === '1' ? 'yes' : 'no'
	}

	OCP.AppConfig.setValue(appId, prefKey, value, {
		error: () => showError(t('ldap_write_support', 'Failed to set switch.')),
	})
}
</script>

<template>
	<div id="ldap-write-support-admin-settings" class="section">
		<h2>{{ t('ldap_write_support', 'Writing') }}</h2>
		<h3>{{ t('ldap_write_support', 'Switches') }}</h3>
		<ul>
			<NcActionCheckbox
				:modelValue="checkboxes.createPreventFallback"
				@update:modelValue="toggleSwitch('createPreventFallback', $event)">
				{{ t('ldap_write_support', 'Prevent fallback to other backends when creating users or groups.') }}
			</NcActionCheckbox>
			<NcActionCheckbox
				:modelValue="checkboxes.createRequireActorFromLdap"
				@update:modelValue="toggleSwitch('createRequireActorFromLdap', $event)">
				{{ t('ldap_write_support', 'To create users, the acting (sub)admin has to be provided by LDAP.') }}
			</NcActionCheckbox>
			<NcActionCheckbox
				:modelValue="checkboxes.newUserGenerateUserID"
				@update:modelValue="toggleSwitch('newUserGenerateUserID', $event, 'core')">
				{{ t('ldap_write_support', 'A random user ID has to be generated, i.e. not being provided by the (sub)admin.') }}
			</NcActionCheckbox>
			<NcActionCheckbox
				:modelValue="checkboxes.newUserRequireEmail"
				@update:modelValue="toggleSwitch('newUserRequireEmail', $event, 'core')">
				{{ t('ldap_write_support', 'An LDAP user must have an email address set.') }}
			</NcActionCheckbox>
			<NcActionCheckbox
				:modelValue="checkboxes.hasAvatarPermission"
				@update:modelValue="toggleSwitch('hasAvatarPermission', $event)">
				{{ t('ldap_write_support', 'Allow users to set their avatar') }}
			</NcActionCheckbox>
			<NcActionCheckbox
				:modelValue="checkboxes.hasPasswordPermission"
				@update:modelValue="toggleSwitch('hasPasswordPermission', $event)">
				{{ t('ldap_write_support', 'Allow users to set their password') }}
			</NcActionCheckbox>
			<NcActionCheckbox
				:modelValue="checkboxes.useUnicodePassword"
				:title="t('ldap_write_support', 'If the server does not support the modify password extended operation use the `unicodePwd` instead of the `userPassword` attribute for setting the password')"
				@update:modelValue="toggleSwitch('useUnicodePassword', $event)">
				{{ t('ldap_write_support', 'Use the `unicodePwd` attribute for setting the user password') }}
			</NcActionCheckbox>
		</ul>
		<h3>{{ t('ldap_write_support', 'User template') }}</h3>
		<p>{{ t('ldap_write_support', 'LDIF template for creating users. Following placeholders may be used') }}</p>
		<ul class="disc">
			<li><span class="mono">{UID}</span> – {{ t('ldap_write_support', 'the user id provided by the (sub)admin') }}</li>
			<li><span class="mono">{PWD}</span> – {{ t('ldap_write_support', 'the password provided by the (sub)admin') }}</li>
			<li><span class="mono">{BASE}</span> – {{ t('ldap_write_support', 'the LDAP node of the acting (sub)admin or the configured user base') }}</li>
		</ul>
		<textarea v-model="userTemplate" class="mono" @change="setUserTemplate" />
	</div>
</template>

<style lang="scss">
#ldap-write-support-admin-settings {
	.mono {
		font-family: monospace;
		font-size: larger;
	}

	ul.disc {
		list-style-type: disc;
		list-style-position: inside;
		margin-left: 44px;

		li {
			margin: 5px 0;
		}
	}

	textarea {
		width: 100%;
		height: 150px;
		max-width: 600px;
	}
}
</style>
