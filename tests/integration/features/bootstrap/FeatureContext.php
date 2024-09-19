<?php

declare(strict_types=1);
/**
 * @copyright Copyright (c) 2019 Arthur Schiwon <blizzz@arthur-schiwon.de>
 *
 * @author Arthur Schiwon <blizzz@arthur-schiwon.de>
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
