<?php

namespace App\Model;

use Nette;
use Nette\Security\Passwords;


/**
 * Users management.
 */
class UserManager extends Nette\Object implements Nette\Security\IAuthenticator
{
	const
		TABLE_NAME = 'user',
		COLUMN_ID = 'id',
		COLUMN_NAME = 'name',
		COLUMN_EMAIL = 'email',
		COLUMN_PASSWORD_HASH = 'password',
		COLUMN_ROLE = 'role',

		TABLE_RIGHTS = 'rights',
		COLUMN_USER_ID = 'user_id',
		COLUMN_RIGHT = 'right';


	/** @var Nette\Database\Context */
	private $database;


	public function __construct(Nette\Database\Context $database)
	{
		$this->database = $database;
	}


	/**
	 * Performs an authentication.
	 * @return Nette\Security\Identity
	 * @throws Nette\Security\AuthenticationException
	 */
	public function authenticate(array $credentials)
	{
		list($username, $password) = $credentials;

		$row = $this->database->table(self::TABLE_NAME)->where(self::COLUMN_NAME, $username)->fetch();

		if (!$row) {
			throw new Nette\Security\AuthenticationException('The username is incorrect.', self::IDENTITY_NOT_FOUND);

		} elseif (!Passwords::verify($password, $row[self::COLUMN_PASSWORD_HASH])) {
			throw new Nette\Security\AuthenticationException('The password is incorrect.', self::INVALID_CREDENTIAL);

		} elseif (Passwords::needsRehash($row[self::COLUMN_PASSWORD_HASH])) {
			$row->update(array(
				self::COLUMN_PASSWORD_HASH => Passwords::hash($password),
			));
		}

		$arr = $row->toArray();
		unset($arr[self::COLUMN_PASSWORD_HASH]);
		return new Nette\Security\Identity($row[self::COLUMN_ID], $row[self::COLUMN_ROLE], $arr);
	}


	/**
	 * Adds new user.
	 * @param  string
	 * @param  string
	 * @return void
	 */
	public function add($username, $password)
	{
		try {
			$this->database->table(self::TABLE_NAME)->insert(array(
				self::COLUMN_NAME => $username,
				self::COLUMN_PASSWORD_HASH => Passwords::hash($password),
			));
		} catch (Nette\Database\UniqueConstraintViolationException $e) {
			throw new DuplicateNameException;
		}
	}

	/*Returns all users*/
	public function getUsers()
	{
		return $this->database->table(self::TABLE_NAME);
	}

	public function addUser($values)
	{
		try {
			$this->database->table(self::TABLE_NAME)->insert(array(
				self::COLUMN_NAME => $values->name,
				self::COLUMN_EMAIL => $values->email,
				self::COLUMN_PASSWORD_HASH => Passwords::hash($values->password),
			));
		} catch (Nette\Database\UniqueConstraintViolationException $e) {
			throw new DuplicateNameException;
		}
	}

	/*Get user*/
	public function getUser($userId)
	{
		$user =  $this->database->table(self::TABLE_NAME)->where(self::COLUMN_ID, $userId)->fetch();

		if ( !$user )
		{
			throw new UserDoesNotExistException;
		}

		return $user;
	}

	/*Get user by name*/
	public function getUserByName($userName)
	{
		return $this->database->table(self::TABLE_NAME)->where(self::COLUMN_NAME, $userName)->fetch();
	}

	/*Edit User*/
	public function editUser($userId, $values) 
	{
		$user = $this->database->table(self::TABLE_NAME)->where(self::COLUMN_ID, $userId);

		if ( !$user )
		{
			throw new UserDoesNotExistException;
		}

		$user->update(array(
				self::COLUMN_NAME => $values->name,
				self::COLUMN_EMAIL => $values->email,
				self::COLUMN_PASSWORD_HASH => Passwords::hash($values->password),
			));
	}

	/*Remove User*/
	public function removeUser($userId) 
	{
		$user = $this->database->table(self::TABLE_NAME)->where(self::COLUMN_ID, $userId)->fetch();

		if ( !$user )
		{
			throw new UserDoesNotExistException;
		}

		$user->delete();
	}

}

class UserDoesNotExistException extends \Exception
{}

class DuplicateNameException extends \Exception
{}
