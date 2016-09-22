<?php

/*
 * This file is part of the FOSUserBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace GpaketBundle\Model;


use FOS\UserBundle\Model\UserInterface;
use FOS\UserBundle\Model\UserManagerInterface;
use GpaketBundle\Entity\User;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class UserProvider implements UserProviderInterface, UserManagerInterface {

	/**
	 * @var EncoderFactoryInterface
	 */
	protected $encoderFactory;

	/**
	 * @param string $username
	 *
	 * @return UserInterface
	 */
	public function loadUserByUsername($username) {
		@trigger_error('Using the UserManager as user provider is deprecated. Use FOS\UserBundle\Security\UserProvider instead.', E_USER_DEPRECATED);

		$user = $this->findUserByUsername($username);

		if (!$user) {
			throw new UsernameNotFoundException(sprintf('No user with name "%s" was found.', $username));
		}

		return $user;
	}

	/**
	 * Finds a user by email
	 *
	 * @param string $email
	 *
	 * @return UserInterface
	 */
	public function findUserByEmail($email) {
		return $this->findUserBy(array('email' => $this->canonicalizeEmail($email)));
	}

	/**
	 * Whether this provider supports the given user class.
	 *
	 * @param string $class
	 *
	 * @return bool
	 */
	public function supportsClass($class) {
		// TODO: Implement supportsClass() method.
	}

	/**
	 * Refreshes the user for the account interface.
	 *
	 * It is up to the implementation to decide if the user data should be
	 * totally reloaded (e.g. from the database), or if the UserInterface
	 * object can just be merged into some internal array of users / identity
	 * map.
	 *
	 * @param \Symfony\Component\Security\Core\User\UserInterface $user
	 *
	 * @return \Symfony\Component\Security\Core\User\UserInterface
	 *
	 * @throws UnsupportedUserException if the account is not supported
	 */
	public function refreshUser(\Symfony\Component\Security\Core\User\UserInterface $user) {
		@trigger_error('Using the UserManager as user provider is deprecated. Use FOS\UserBundle\Security\UserProvider instead.', E_USER_DEPRECATED);

		$class = $this->getClass();
		if (!$user instanceof $class) {
			throw new UnsupportedUserException('Account is not supported.');
		}
		if (!$user instanceof User) {
			throw new UnsupportedUserException(sprintf('Expected an instance of FOS\UserBundle\Model\User, but got "%s".', get_class($user)));
		}

		$refreshedUser = $this->findUserBy(array('user_id' => $user->getId()));
		if (null === $refreshedUser) {
			throw new UsernameNotFoundException(sprintf('User with ID "%d" could not be reloaded.', $user->getId()));
		}

		return $refreshedUser;
	}

	public function findUserByUsername($username) {
		return $this->findUserBy(array('username' => $username));
	}

	/**
	 * Creates an empty user instance.
	 *
	 * @return UserInterface
	 */
	public function createUser() {
		// TODO: Implement createUser() method.
	}

	/**
	 * Deletes a user.
	 *
	 * @param UserInterface $user
	 *
	 * @return void
	 */
	public function deleteUser(UserInterface $user) {
		// TODO: Implement deleteUser() method.
	}

	/**
	 * Finds one user by the given criteria.
	 *
	 * @param array $criteria
	 *
	 * @return UserInterface
	 */
	public function findUserBy(array $criteria) {
		// TODO: Implement findUserBy() method.
	}

	/**
	 * Finds a user by its username or email.
	 *
	 * @param string $usernameOrEmail
	 *
	 * @return UserInterface or null if user does not exist
	 */
	public function findUserByUsernameOrEmail($usernameOrEmail) {
		// TODO: Implement findUserByUsernameOrEmail() method.
	}

	/**
	 * Finds a user by its confirmationToken.
	 *
	 * @param string $token
	 *
	 * @return UserInterface or null if user does not exist
	 */
	public function findUserByConfirmationToken($token) {
		// TODO: Implement findUserByConfirmationToken() method.
	}

	/**
	 * Returns a collection with all user instances.
	 *
	 * @return \Traversable
	 */
	public function findUsers() {
		// TODO: Implement findUsers() method.
	}

	/**
	 * Returns the user's fully qualified class name.
	 *
	 * @return string
	 */
	public function getClass() {
		// TODO: Implement getClass() method.
	}

	/**
	 * Reloads a user.
	 *
	 * @param UserInterface $user
	 *
	 * @return void
	 */
	public function reloadUser(UserInterface $user) {
		// TODO: Implement reloadUser() method.
	}

	/**
	 * Updates a user.
	 *
	 * @param UserInterface $user
	 *
	 * @return void
	 */
	public function updateUser(UserInterface $user) {
		// TODO: Implement updateUser() method.
	}

	/**
	 * Updates the canonical username and email fields for a user.
	 *
	 * @param UserInterface $user
	 *
	 * @return void
	 */
	public function updateCanonicalFields(UserInterface $user) {
		// TODO: Implement updateCanonicalFields() method.
	}

	/**
	 * Updates a user password if a plain password is set.
	 *
	 * @param UserInterface $user
	 *
	 * @return void
	 */
	public function updatePassword(UserInterface $user) {
		// TODO: Implement updatePassword() method.
	}
}