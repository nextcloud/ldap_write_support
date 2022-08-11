const path = require('path')
const webpackConfig = require('@nextcloud/webpack-vue-config')
const webpackRules = require('@nextcloud/webpack-vue-config/rules')

webpackConfig.entry = {
	'admin-settings': path.join(__dirname, 'src/main-settings.js'),
}

webpackConfig.resolve.modules = [
	path.resolve(__dirname, 'node_modules'),
	'node_modules',
]

const rules = {
	...webpackRules,
	RULE_SVG: {
		test: /\.(svg)$/i,
		use: [
			{
				loader: 'url-loader',
			},
		],
	},
}
rules.RULE_ASSETS = {
	test: /\.(png|jpg|gif)$/,
	loader: 'file-loader',
	options: {
		name: '[name].[ext]?[hash]',
	},
}

webpackConfig.module.rules = Object.values(rules)

module.exports = webpackConfig
