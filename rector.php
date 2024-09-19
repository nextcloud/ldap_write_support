<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2024 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-only
 */

use Nextcloud\Rector\Set\NextcloudSets;
use Rector\Config\RectorConfig;

return RectorConfig::configure()
	->withPaths([
		__DIR__ . '/lib',
		__DIR__ . '/templates',
	])
	->withPhpSets(php81:true)
	->withTypeCoverageLevel(0)
	->withImportNames(importShortClasses: false)
	->withSets([
		NextcloudSets::NEXTCLOUD_27,
	]);
