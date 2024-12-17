<?php

declare(strict_types=1);
/**
 * SPDX-FileCopyrightText: 2019 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use PHPUnit\Framework\Assert;

class FeatureContext extends LDAPContext implements Context {
	/** @var string[] */
	private $userIdsToCleanUp = [];
	/** @var string[] */
	private $groupIdsToCleanUp = [];
	/** @var string */
	private $recentlyCreatedUser;

	/**
	 * @AfterScenario
	 */
	public function deleteCreatedObjects() {
		$this->asAn('admin');
		while ($uid = array_shift($this->userIdsToCleanUp)) {
			error_log("deleting user $uid");
			$this->deletingTheUser($uid);
		}

		while ($gid = array_shift($this->groupIdsToCleanUp)) {
			error_log("deleting group $gid");
			$this->sendingTo('DELETE', '/cloud/groups/' . $gid);
		}
	}

	public function resetAppConfigs() {
		$this->modifyServerConfig('core', 'newUser.generateUserID', 'no');
		$this->modifyServerConfig('core', 'newUser.requireEmail', 'no');
	}

	/**
	 * @Then /^it yields "([^"]*)" result$/
	 */
	public function itYieldsResult($count) {
		$users = simplexml_load_string($this->getResponse()->getBody()->getContents())->data->users;
		Assert::assertSame((int)$count, $users->children()->count());
	}

	/**
	 * @When /^creating a user with$/
	 */
	public function creatingAUserWith(TableNode $args) {
		$this->sendingToWith('POST', '/cloud/users', $args);
		$xml = simplexml_load_string($this->getResponse()->getBody()->getContents());
		if ($xml->data && $xml->data->id) {
			$this->userIdsToCleanUp[(string)$xml->data->id] = (string)$xml->data->id;
			$this->recentlyCreatedUser = (string)$xml->data->id;
		}
	}

	/**
	 * @Given /^the created users resides on LDAP$/
	 */
	public function theCreatedUsersResidesOnLDAP() {
		$tableNode = new TableNode([['backend', 'LDAP']]);
		$this->userHasSetting($this->recentlyCreatedUser, $tableNode);
	}

	/**
	 * @Given /^creating a group with gid "([^"]*)"$/
	 */
	public function creatingAGroupWithGid($gid) {
		$args = new TableNode([['groupid', $gid]]);
		$this->sendingToWith('POST', '/cloud/groups', $args);
		$xml = simplexml_load_string($this->getResponse()->getBody()->getContents());
		if ($this->getOCSResponse($this->getResponse()) === 200) {
			$this->groupIdsToCleanUp[$gid] = $gid;
		}
	}

	/**
	 * @Given /^user "([^"]*)" exists on "([^"]*)" backend$/
	 */
	public function userExistsOnBackend($uid, $backendName) {
		$this->assureUserExists($uid);
		$needle = '<backend>' . $backendName . '</backend>';
		Assert::assertNotFalse(strpos($this->getResponse()->getBody()->getContents(), $needle));
	}
}
