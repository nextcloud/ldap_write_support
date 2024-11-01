/**
 * SPDX-FileCopyrightText: 2019 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

import { loadState } from '@nextcloud/initial-state'
import Vue from 'vue'

import AdminSettings from './components/AdminSettings.vue'

// eslint-disable-next-line import/no-unresolved, n/no-missing-import
import 'vite/modulepreload-polyfill'

const View = Vue.extend(AdminSettings)
const AppID = 'ldap_write_support'

new View({
	propsData: {
		templates: loadState(AppID, 'templates'),
		switches: loadState(AppID, 'switches'),
	},
}).$mount('#ldap-write-support-admin-settings')
