<?php
namespace TYPO3\Neos\Command;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Neos".            *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License, either version 3 of the   *
 * License, or (at your option) any later version.                        *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;

/**
 * The User Command Controller Service
 *
 * @Flow\Scope("singleton")
 */
class UserCommandController extends \TYPO3\Flow\Cli\CommandController {

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Security\AccountRepository
	 */
	protected $accountRepository;

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Party\Domain\Repository\PartyRepository
	 */
	protected $partyRepository;

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Security\Cryptography\HashService
	 */
	protected $hashService;

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Neos\Domain\Factory\UserFactory
	 */
	protected $userFactory;

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Security\Policy\PolicyService
	 */
	protected $policyService;

	/**
	 * Create a new user
	 *
	 * This command creates a new user which has access to the backend user interface.
	 * It is recommended to user the email address as a username.
	 *
	 * @param string $username The username of the user to be created.
	 * @param string $password Password of the user to be created
	 * @param string $firstName First name of the user to be created
	 * @param string $lastName Last name of the user to be created
	 * @param string $roles A comma separated list of roles to assign
	 * @Flow\Validate(argumentName="username", type="EmailAddress")
	 * @return void
	 */
	public function createCommand($username, $password, $firstName, $lastName, $roles = NULL) {
		$account = $this->accountRepository->findByAccountIdentifierAndAuthenticationProviderName($username, 'Typo3BackendProvider');
		if ($account instanceof \TYPO3\Flow\Security\Account) {
			$this->outputLine('The username "%s" is already in use', array($username));
			$this->quit(1);
		}

		if (empty($roles)) {
			$roleIdentifiers = array('TYPO3.Neos:Editor');
		} else {
			$roleIdentifiers = \TYPO3\Flow\Utility\Arrays::trimExplode(',', $roles);
			foreach ($roleIdentifiers as &$role) {
				if (strpos($role, '.') === FALSE) {
					$role = 'TYPO3.Neos:' . $role;
				}
			}
		}

		try {
			$user = $this->userFactory->create($username, $password, $firstName, $lastName, $roleIdentifiers);
			$this->partyRepository->add($user);
			$accounts = $user->getAccounts();
			foreach ($accounts as $account) {
				$this->accountRepository->add($account);
			}

			$this->outputLine('Created user "%s".', array($username));
		} catch (\TYPO3\Flow\Security\Exception\NoSuchRoleException $exception) {
			$this->outputLine($exception->getMessage());
			$this->quit(1);
		}

	}

	/**
	 * Set a new password for the given user
	 *
	 * This allows for setting a new password for an existing user account.
	 *
	 * @param string $username Username of the account to modify
	 * @param string $password The new password
	 * @return void
	 */
	public function setPasswordCommand($username, $password) {
		$account = $this->accountRepository->findByAccountIdentifierAndAuthenticationProviderName($username, 'Typo3BackendProvider');
		if (!$account instanceof \TYPO3\Flow\Security\Account) {
			$this->outputLine('User "%s" does not exists.', array($username));
			$this->quit(1);
		}
		$account->setCredentialsSource($this->hashService->hashPassword($password, 'default'));
		$this->accountRepository->update($account);

		$this->outputLine('The new password for user "%s" was set.', array($username));
	}

	/**
	 * Add a role to a user
	 *
	 * This command allows for adding a specific role to an existing user.
	 * Currently supported roles: "TYPO3.Neos:Editor", "TYPO3.Neos:Administrator"
	 *
	 * @param string $username The username
	 * @param string $role Role ot be added to the user
	 * @return void
	 */
	public function addRoleCommand($username, $role) {
		$account = $this->accountRepository->findByAccountIdentifierAndAuthenticationProviderName($username, 'Typo3BackendProvider');
		if (!$account instanceof \TYPO3\Flow\Security\Account) {
			$this->outputLine('User "%s" does not exists.', array($username));
			$this->quit(1);
		}

		if (strpos($role, '.') === FALSE) {
			$role = 'TYPO3.Neos:' . $role;
		}
		$roleObject = $this->policyService->getRole($role);
		if ($roleObject === NULL) {
			$this->outputLine('Role "%s" does not exist.', array($role));
			$this->quit(1);
		}

		if ($account->hasRole($roleObject)) {
			$this->outputLine('User "%s" already has the role "%s" assigned.', array($username, $role));
			$this->quit(1);
		}

		$account->addRole($roleObject);
		$this->accountRepository->update($account);
		$this->outputLine('Added role "%s" to user "%s".', array($role, $username));
	}

	/**
	 * Remove a role from a user
	 *
	 * @param string $username Email address of the user
	 * @param string $role Role ot be removed from the user
	 * @return void
	 */
	public function removeRoleCommand($username, $role) {
		$account = $this->accountRepository->findByAccountIdentifierAndAuthenticationProviderName($username, 'Typo3BackendProvider');
		if (!$account instanceof \TYPO3\Flow\Security\Account) {
			$this->outputLine('User "%s" does not exists.', array($username));
			$this->quit(1);
		}

		if (strpos($role, '.') === FALSE) {
			$role = 'TYPO3.Neos:' . $role;
		}

		$roleObject = $this->policyService->getRole($role);
		if ($roleObject === NULL) {
			$this->outputLine('Role "%s" does not exist.', array($role));
			$this->quit(1);
		}

		if (!$account->hasRole($roleObject)) {
			$this->outputLine('User "%s" does not have the role "%s" assigned.', array($username, $role));
			$this->quit(1);
		}

		$account->removeRole($roleObject);
		$this->accountRepository->update($account);
		$this->outputLine('Removed role "%s" from user "%s".', array($role, $username));
	}

}
?>