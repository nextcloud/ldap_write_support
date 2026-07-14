/**
 * SPDX-FileCopyrightText: 2019 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

import { loadState } from '@nextcloud/initial-state'
import { createApp } from 'vue'
import AdminSettings from './components/AdminSettings.vue'

import 'vite/modulepreload-polyfill'

const AppID = 'ldap_write_support'

const app = createApp(AdminSettings, {
	templates: loadState(AppID, 'templates'),
	switches: loadState(AppID, 'switches'),
})
app.mount('#ldap-write-support-admin-settings-mount')
