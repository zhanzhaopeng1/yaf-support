<?php

namespace Yaf\Support\Auth;

use Illuminate\Contracts\Auth\Authenticatable as UserContract;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Support\Str;
use Yaf\Support\Database\PdoClient;

class DatabaseUserProvider implements UserProvider
{
    /**
     * @var mixed|\Yaf\Support\Database\PdoClient
     */
    protected $conn;

    /**
     * The hasher implementation.
     *
     * @var \Illuminate\Contracts\Hashing\Hasher
     */
    protected $hasher;

    /**
     * The table containing the users.
     *
     * @var string
     */
    protected $table;

    /**
     * DatabaseUserProvider constructor.
     * @param                $table
     * @throws \Exception
     */
    public function __construct($table)
    {
        $this->conn  = dbConnect();
        $this->table = $table;
    }

    /**
     * Retrieve a user by their unique identifier.
     *
     * @param  mixed $identifier
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveById($identifier)
    {
        $user = $this->conn->table($this->table)->find($identifier);

        return $this->getGenericUser($user);
    }

    /**
     * Retrieve a user by their unique identifier and "remember me" token.
     *
     * @param  mixed  $identifier
     * @param  string $token
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveByToken($identifier, $token)
    {
        $user = $this->getGenericUser(
            $this->conn->table($this->table)->find($identifier)
        );

        return $user && $user->getRememberToken() && hash_equals($user->getRememberToken(), $token)
            ? $user : null;
    }

    /**
     * Update the "remember me" token for the given user in storage.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable $user
     * @param  string                                     $token
     * @return void
     */
    public function updateRememberToken(UserContract $user, $token)
    {
        $this->conn->table($this->table)
            ->where($user->getAuthIdentifierName(), $user->getAuthIdentifier())
            ->update([$user->getRememberTokenName() => $token]);
    }

    /**
     * @param array $credentials
     * @return UserContract|null|GenericUser
     * @throws \Exception
     */
    public function retrieveByCredentials(array $credentials)
    {
        if (empty($credentials) ||
            (count($credentials) === 1 &&
                array_key_exists('password', $credentials))) {
            return null;
        }

        // First we will add each credential element to the query as a where clause.
        // Then we can execute the query and, if we found a user, return it in a
        // generic "user" object that will be utilized by the Guard instances.
        foreach ($credentials as $key => $value) {
            if (Str::contains($key, 'password')) {
                continue;
            }

            $condition = PdoClient::condition("{$key} = ?", $value);
            $user = dbConnect()->getRowByCondition('user', $condition);
        }

        return $this->getGenericUser($user);
    }

    /**
     * @param $user
     * @return GenericUser
     */
    protected function getGenericUser($user)
    {
        if (!is_null($user)) {
            return new GenericUser((array)$user);
        }

        return null;
    }

    /**
     * Validate a user against the given credentials.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable $user
     * @param  array                                      $credentials
     * @return bool
     */
    public function validateCredentials(UserContract $user, array $credentials)
    {
        return $this->hasher->check(
            $credentials['password'], $user->getAuthPassword()
        );
    }
}
