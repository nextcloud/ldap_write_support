<?php

/**
 * SPDX-FileCopyrightText: 2019 Nextcloud GmbH and Nextcloud contributors
 * SPDX-FileCopyrightText: 2019 Cooperativa EITA <eita.org.br>
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\LdapWriteSupport\Command;

use Exception;
use OC\SubAdmin;
use OCA\User_LDAP\Group_Proxy;
use OCA\User_LDAP\Helper;
use OCP\IConfig;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GroupAdminsToLdap extends Command {
	/**
	 * This adds/removes group subadmins as ldap group owners
	 */
	private $simulate = false;
	private $verbose = false;

	/** @var SubAdmin */
	private $subAdmin;
	/** @var IConfig */
	private $ocConfig;
	/** @var Helper */
	private $helper;
	/** @var Group_Proxy */
	private $groupProxy;

	/**
	 * GroupAdminsToLdap constructor.
	 */
	public function __construct(
		SubAdmin $subAdmin,
		IConfig $ocConfig,
		Helper $helper,
		Group_Proxy $groupProxy,
	) {
		parent::__construct();

		$this->subAdmin = $subAdmin;
		$this->ocConfig = $ocConfig;
		$this->helper = $helper;
		$this->groupProxy = $groupProxy;
	}

	protected function configure() {
		$this
			->setName('ldap-ext:sync-group-admins')
			->setDescription('syncs group admin informations to ldap')
			->addOption(
				'sim',
				null,
				InputOption::VALUE_NONE,
				'does not change database; just simulate'
			)
			->addOption(
				'verb',
				null,
				InputOption::VALUE_NONE,
				'verbose'
			)
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output): int {
		if ($input->getOption('sim')) {
			$this->simulate = true;
		}

		if ($input->getOption('verb')) {
			$this->verbose = true;
		}

		try {
			if ($this->simulate) {
				$output->writeln('SIMULATE MODE ON');
			}

			$configPrefixes = $this->helper->getServerConfigurationPrefixes(true);

			if (count($configPrefixes) > 1) {
				throw new Exception('NOT PREPARED TO DEAL WITH MODE THAN 1 LDAP SOURCE, EXITING...');
			}

			$access = $this->groupProxy->getLDAPAccess($configPrefixes[0]);
			$conn = $access->getConnection();

			$allSubAdmins = $this->subAdmin->getAllSubAdmins();

			$currentNCAdmins = [];
			foreach ($allSubAdmins as $subAdmin) {
				$gid = $subAdmin['group']->getGID();
				if (!key_exists($gid, $currentNCAdmins)) {
					$currentNCAdmins[$gid] = [];
				}
				array_push($currentNCAdmins[$gid], $subAdmin['user']->getUID());
			}

			$allLdapGroups = $access->fetchListOfGroups(
				$conn->ldapGroupFilter,
				[$conn->ldapGroupDisplayName, 'dn','member','owner']
			);

			$currentLDAPAdmins = [];
			foreach ($allLdapGroups as $ldapGroup) {
				$gid = $ldapGroup[$conn->ldapGroupDisplayName][0];
				if (key_exists('owner', $ldapGroup)) {
					if (!key_exists($gid, $currentLDAPAdmins)) {
						$currentLDAPAdmins[$gid] = [];
					}
					foreach ($ldapGroup['owner'] as $ownerDN) {
						$uid = $access->getUserMapper()->getNameByDN($ownerDN);
						array_push($currentLDAPAdmins[$gid], $uid);
					}
				}
			}

			function diff_user_arrays($array1, $array2) {
				$difference = [];
				foreach ($array1 as $gid => $users) {
					if (!isset($array2[$gid]) || !is_array($array2[$gid])) {
						$difference[$gid] = $users;
					} else {
						$diff = array_diff($array1[$gid], $array2[$gid]);
						if (count($diff)) {
							$difference[$gid] = array_diff($array1[$gid], $array2[$gid]);
						}
					}
				}
				return $difference;
			}


			$onlyInLDAP = diff_user_arrays($currentLDAPAdmins, $currentNCAdmins);
			$onlyInNC = diff_user_arrays($currentNCAdmins, $currentLDAPAdmins);


			foreach ($onlyInNC as $gid => $users) {
				$groupDN = $access->getGroupMapper()->getDNByName($gid);
				foreach ($users as $uid) {
					$userDN = $access->getUserMapper()->getDNByName($uid);
					$entry = [
						'owner' => $userDN
					];
					if ($this->verbose) {
						$output->writeln("ADD: UID=$uid ($userDN) into GID=$gid ($groupDN)");
					}
					if (!$this->simulate) {
						ldap_mod_add($conn->getConnectionResource(), $groupDN, $entry);
					}
				}
			}

			foreach ($onlyInLDAP as $gid => $users) {
				$groupDN = $access->getGroupMapper()->getDNByName($gid);
				foreach ($users as $uid) {
					$userDN = $access->getUserMapper()->getDNByName($uid);
					$entry = [
						'owner' => $userDN
					];
					if ($this->verbose) {
						$output->writeln("DEL: UID=$uid ($userDN) into GID=$gid ($groupDN)");
					}
					if (!$this->simulate) {
						ldap_mod_del($conn->getConnectionResource(), $groupDN, $entry);
					}
				}
			}

			$output->writeln("As Pink Floyd says: 'This is the end....'");
		} catch (Exception $e) {
			$output->writeln('<error>' . $e->getMessage() . '</error>');
			return 1;
		}

		return 0;
	}
}
