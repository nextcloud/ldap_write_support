<?php

/**
 * SPDX-FileCopyrightText: 2019 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

use Behat\Config\Config;
use Behat\Config\Formatter\JUnitFormatter;
use Behat\Config\Formatter\PrettyFormatter;
use Behat\Config\Profile;
use Behat\Config\Suite;

return (new Config())
	->withProfile((new Profile('default'))
		->withFormatter((new JUnitFormatter())
			->withOutputPath('%paths.base%/output'))
		->withFormatter((new PrettyFormatter())
			->withOutputStyles([
				'comment' => [
					'bright-blue',
				],
			]))
		->withSuite((new Suite('test'))
			->addContext(
				FeatureContext::class,
				[
					'baseUrl' => 'http://localhost:8080/index.php/ocs/',
					'admin' => [
						'admin',
						'admin',
					],
					'regular_user_password' => 123456,
				]
			)
			->withPaths('%paths.base%/features/')));
