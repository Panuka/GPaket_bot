<?php

namespace GpaketBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use FOS\UserBundle\Model\GroupableInterface;
use FOS\UserBundle\Model\GroupInterface;
use FOS\UserBundle\Model\UserInterface;
use Symfony\Component\Security\Core\User\EquatableInterface;

/**
 * User
 */
class User implements UserInterface, EquatableInterface  {
	/**
	 * @var integer
	 */
	private $user_id;

	/**
	 * @var string
	 */
	private $first_name;

	/**
	 * @var string
	 */
	private $last_name;

	/**
	 * @var string
	 */
	protected $username;

	/**
	 * @var \Doctrine\Common\Collections\Collection
	 */
	private $messages;

	/**
	 * @var string
	 */
	protected $usernameCanonical;

	/**
	 * @var string
	 */
	protected $email;

	/**
	 * @var string
	 */
	protected $emailCanonical;

	/**
	 * @var boolean
	 */
	protected $enabled;

	/**
	 * The salt to use for hashing
	 *
	 * @var string
	 */
	protected $salt;

	/**
	 * Encrypted password. Must be persisted.
	 *
	 * @var string
	 */
	protected $password;

	/**
	 * Plain password. Used for model validation. Must not be persisted.
	 *
	 * @var string
	 */
	protected $plainPassword;

	/**
	 * @var \DateTime
	 */
	protected $lastLogin;

	/**
	 * Random string sent to the user email address in order to verify it
	 *
	 * @var string
	 */
	protected $confirmationToken;

	/**
	 * @var \DateTime
	 */
	protected $passwordRequestedAt;

	/**
	 * @var Collection
	 */
	protected $groups;

	/**
	 * @var boolean
	 */
	protected $locked;

	/**
	 * @var boolean
	 */
	protected $expired;

	/**
	 * @var \DateTime
	 */
	protected $expiresAt;

	/**
	 * @var array
	 */
	protected $roles;

	/**
	 * @var boolean
	 */
	protected $credentialsExpired;

	/**
	 * @var \DateTime
	 */
	protected $credentialsExpireAt;


	/**
	 * Set firstName
	 *
	 * @param string $firstName
	 *
	 * @return User
	 */
	public function setFirstName($firstName) {
		$this->first_name = $firstName;

		return $this;
	}

	/**
	 * Get firstName
	 *
	 * @return string
	 */
	public function getFirstName() {
		return $this->first_name;
	}

	/**
	 * Set lastName
	 *
	 * @param string $lastName
	 *
	 * @return User
	 */
	public function setLastName($lastName) {
		$this->last_name = $lastName;

		return $this;
	}

	/**
	 * Get lastName
	 *
	 * @return string
	 */
	public function getLastName() {
		return $this->last_name;
	}


	/**
	 * Constructor
	 */
	public function __construct() {
		$this->messages = new ArrayCollection();
		$this->salt = base_convert(sha1(uniqid(mt_rand(), true)), 16, 36);
		$this->enabled = false;
		$this->locked = false;
		$this->expired = false;
		$this->roles = array();
		$this->credentialsExpired = false;
	}

	/**
	 * Set userId
	 *
	 * @param integer $userId
	 *
	 * @return User
	 */
	public function setUserId($userId) {
		$this->user_id = $userId;

		return $this;
	}

	/**
	 * Get userId
	 *
	 * @return integer
	 */
	public function getUserId() {
		return $this->user_id;
	}


	/**
	 * Add message
	 *
	 * @param Message $message
	 *
	 * @return User
	 */
	public function addMessage(Message $message) {
		$this->messages[] = $message;

		return $this;
	}

	/**
	 * Remove message
	 *
	 * @param Message $message
	 */
	public function removeMessage(Message $message) {
		$this->messages->removeElement($message);
	}

	/**
	 * Get messages
	 *
	 * @return \Doctrine\Common\Collections\Collection
	 */
	public function getMessages() {
		return $this->messages;
	}

	/**
	 * @var \Doctrine\Common\Collections\Collection
	 */
	private $chats;


	/**
	 * Add chat
	 *
	 * @param Chat $chat
	 *
	 * @return User
	 */
	public function addChat(Chat $chat) {
		$this->chats[$chat->getChatId()] = $chat;

		return $this;
	}

	/**
	 * Remove chat
	 *
	 * @param Chat $chat
	 */
	public function removeChat(Chat $chat) {
		$this->chats->removeElement($chat);
	}

	/**
	 * Get chats
	 *
	 * @return \Doctrine\Common\Collections\Collection
	 */
	public function getChats() {
		return $this->chats;
	}


	public function addRole($role) {
		$role = strtoupper($role);
		if ($role === static::ROLE_DEFAULT) {
			return $this;
		}

		if (!in_array($role, $this->roles, true)) {
			$this->roles[] = $role;
		}

		return $this;
	}

	/**
	 * Serializes the user.
	 *
	 * The serialized data have to contain the fields used during check for
	 * changes and the id.
	 *
	 * @return string
	 */
	public function serialize() {
		return serialize(array(
			$this->password,
			$this->salt,
			$this->usernameCanonical,
			$this->username,
			$this->expired,
			$this->locked,
			$this->credentialsExpired,
			$this->enabled,
			$this->user_id,
			$this->expiresAt,
			$this->credentialsExpireAt,
			$this->email,
			$this->emailCanonical,
		));
	}

	/**
	 * Unserializes the user.
	 *
	 * @param string $serialized
	 */
	public function unserialize($serialized) {
		$data = unserialize($serialized);
		// add a few extra elements in the array to ensure that we have enough keys when unserializing
		// older data which does not include all properties.
		$data = array_merge($data, array_fill(0, 2, null));

		list(
			$this->password,
			$this->salt,
			$this->usernameCanonical,
			$this->username,
			$this->expired,
			$this->locked,
			$this->credentialsExpired,
			$this->enabled,
			$this->user_id,
			$this->expiresAt,
			$this->credentialsExpireAt,
			$this->email,
			$this->emailCanonical
			) = $data;
	}

	/**
	 * Removes sensitive data from the user.
	 */
	public function eraseCredentials() {
		$this->plainPassword = null;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getId() {
		return $this->user_id;
	}

	public function getUsernameCanonical() {
		return $this->usernameCanonical;
	}

	public function getSalt() {
		return $this->salt;
	}

	public function getEmail() {
		return $this->email;
	}

	public function getEmailCanonical() {
		return $this->emailCanonical;
	}

	/**
	 * Gets the encrypted password.
	 *
	 * @return string
	 */
	public function getPassword() {
		return $this->password;
	}

	public function getPlainPassword() {
		return $this->plainPassword;
	}

	/**
	 * Gets the last login time.
	 *
	 * @return \DateTime
	 */
	public function getLastLogin() {
		return $this->lastLogin;
	}

	public function getConfirmationToken() {
		return $this->confirmationToken;
	}

	/**
	 * Returns the user roles
	 *
	 * @return array The roles
	 */
	public function getRoles() {
		$roles = $this->roles;

		foreach ($this->getGroups() as $group) {
			$roles = array_merge($roles, $group->getRoles());
		}

		// we need to make sure to have at least one role
		$roles[] = static::ROLE_DEFAULT;

		return array_unique($roles);
	}

	/**
	 * Never use this to check if this user has access to anything!
	 *
	 * Use the SecurityContext, or an implementation of AccessDecisionManager
	 * instead, e.g.
	 *
	 *         $securityContext->isGranted('ROLE_USER');
	 *
	 * @param string $role
	 *
	 * @return boolean
	 */
	public function hasRole($role) {
		return in_array(strtoupper($role), $this->getRoles(), true);
	}

	public function isAccountNonExpired() {
		if (true === $this->expired) {
			return false;
		}

		if (null !== $this->expiresAt && $this->expiresAt->getTimestamp() < time()) {
			return false;
		}

		return true;
	}

	public function isAccountNonLocked() {
		return !$this->locked;
	}

	public function isCredentialsNonExpired() {
		if (true === $this->credentialsExpired) {
			return false;
		}

		if (null !== $this->credentialsExpireAt && $this->credentialsExpireAt->getTimestamp() < time()) {
			return false;
		}

		return true;
	}

	public function isCredentialsExpired() {
		return !$this->isCredentialsNonExpired();
	}

	public function isEnabled() {
		return $this->enabled;
	}

	public function isExpired() {
		return !$this->isAccountNonExpired();
	}

	public function isLocked() {
		return !$this->isAccountNonLocked();
	}

	public function isSuperAdmin() {
		return $this->hasRole(static::ROLE_SUPER_ADMIN);
	}

	public function removeRole($role) {
		if (false !== $key = array_search(strtoupper($role), $this->roles, true)) {
			unset($this->roles[$key]);
			$this->roles = array_values($this->roles);
		}

		return $this;
	}

	public function setUsernameCanonical($usernameCanonical) {
		$this->usernameCanonical = $usernameCanonical;

		return $this;
	}

	/**
	 * @param \DateTime $date
	 *
	 * @return User
	 */
	public function setCredentialsExpireAt(\DateTime $date = null) {
		$this->credentialsExpireAt = $date;

		return $this;
	}

	/**
	 * @param boolean $boolean
	 *
	 * @return User
	 */
	public function setCredentialsExpired($boolean) {
		$this->credentialsExpired = $boolean;

		return $this;
	}

	public function setEmail($email) {
		$this->email = $email;

		return $this;
	}

	public function setEmailCanonical($emailCanonical) {
		$this->emailCanonical = $emailCanonical;

		return $this;
	}

	public function setEnabled($boolean) {
		$this->enabled = (Boolean)$boolean;

		return $this;
	}

	/**
	 * Sets this user to expired.
	 *
	 * @param Boolean $boolean
	 *
	 * @return User
	 */
	public function setExpired($boolean) {
		$this->expired = (Boolean)$boolean;

		return $this;
	}

	/**
	 * @param \DateTime $date
	 *
	 * @return User
	 */
	public function setExpiresAt(\DateTime $date = null) {
		$this->expiresAt = $date;

		return $this;
	}

	public function setPassword($password) {
		$this->password = $password;

		return $this;
	}

	public function setSuperAdmin($boolean) {
		if (true === $boolean) {
			$this->addRole(static::ROLE_SUPER_ADMIN);
		} else {
			$this->removeRole(static::ROLE_SUPER_ADMIN);
		}

		return $this;
	}

	public function setPlainPassword($password) {
		$this->plainPassword = $password;

		return $this;
	}

	public function setLastLogin(\DateTime $time = null) {
		$this->lastLogin = $time;

		return $this;
	}

	public function setLocked($boolean) {
		$this->locked = $boolean;

		return $this;
	}

	public function setConfirmationToken($confirmationToken) {
		$this->confirmationToken = $confirmationToken;

		return $this;
	}

	public function setPasswordRequestedAt(\DateTime $date = null) {
		$this->passwordRequestedAt = $date;

		return $this;
	}

	/**
	 * Gets the timestamp that the user requested a password reset.
	 *
	 * @return null|\DateTime
	 */
	public function getPasswordRequestedAt() {
		return $this->passwordRequestedAt;
	}

	public function isPasswordRequestNonExpired($ttl) {
		return $this->getPasswordRequestedAt() instanceof \DateTime &&
		$this->getPasswordRequestedAt()->getTimestamp() + $ttl > time();
	}

	public function setRoles(array $roles) {
		$this->roles = array();

		foreach ($roles as $role) {
			$this->addRole($role);
		}

		return $this;
	}

	/**
	 * Gets the groups granted to the user.
	 *
	 * @return Collection
	 */
	public function getGroups() {
		return $this->groups ?: $this->groups = new ArrayCollection();
	}

	public function getGroupNames() {
		$names = array();
		foreach ($this->getGroups() as $group) {
			$names[] = $group->getName();
		}

		return $names;
	}

	/**
	 * @param string $name
	 *
	 * @return boolean
	 */
	public function hasGroup($name) {
		return in_array($name, $this->getGroupNames());
	}

	public function addGroup(GroupInterface $group) {
		if (!$this->getGroups()->contains($group)) {
			$this->getGroups()->add($group);
		}

		return $this;
	}

	public function removeGroup(GroupInterface $group) {
		if ($this->getGroups()->contains($group)) {
			$this->getGroups()->removeElement($group);
		}

		return $this;
	}

	/**
	 * Sets the username.
	 *
	 * @param string $username
	 *
	 * @return self
	 */
	public function setUsername($username) {
		$this->username = $username;
		return $this;
	}

	/**
	 * Returns the username used to authenticate the user.
	 *
	 * @return string The username
	 */
	public function getUsername() {
		return $this->username;
	}

    /**
     * Get enabled
     *
     * @return boolean
     */
    public function getEnabled()
    {
        return $this->enabled;
    }

    /**
     * Set salt
     *
     * @param string $salt
     *
     * @return User
     */
    public function setSalt($salt)
    {
        $this->salt = $salt;

        return $this;
    }

    /**
     * Get locked
     *
     * @return boolean
     */
    public function getLocked()
    {
        return $this->locked;
    }

    /**
     * Get expired
     *
     * @return boolean
     */
    public function getExpired()
    {
        return $this->expired;
    }

    /**
     * Get expiresAt
     *
     * @return \DateTime
     */
    public function getExpiresAt()
    {
        return $this->expiresAt;
    }

    /**
     * Get credentialsExpired
     *
     * @return boolean
     */
    public function getCredentialsExpired()
    {
        return $this->credentialsExpired;
    }

    /**
     * Get credentialsExpireAt
     *
     * @return \DateTime
     */
    public function getCredentialsExpireAt()
    {
        return $this->credentialsExpireAt;
    }

	/**
	 * The equality comparison should neither be done by referential equality
	 * nor by comparing identities (i.e. getId() === getId()).
	 *
	 * However, you do not need to compare every attribute, but only those that
	 * are relevant for assessing whether re-authentication is required.
	 *
	 * Also implementation should consider that $user instance may implement
	 * the extended user interface `AdvancedUserInterface`.
	 *
	 * @param \Symfony\Component\Security\Core\User\UserInterface $user
	 *
	 * @return bool
	 */
	public function isEqualTo(\Symfony\Component\Security\Core\User\UserInterface $user) {
		// TODO: Implement isEqualTo() method.
	}
}
