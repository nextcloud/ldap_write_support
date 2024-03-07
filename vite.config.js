import { createAppConfig } from '@nextcloud/vite-config'
import { join } from 'node:path'

export default createAppConfig({
	'admin-settings': join(__dirname, 'src/main-settings.js'),
})
