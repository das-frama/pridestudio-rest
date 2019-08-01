<?php

declare(strict_types=1);

namespace app\entity;

use MongoDB\BSON\ObjectId;
use MongoDB\BSON\Persistable;

class User implements Persistable
{
    public $id;
    public $email;
    public $auth_key;
    public $access_token;
    public $password_hash;
    public $password_reset_token;
    public $role;
    public $status;
    public $created_at;
    public $updated_at;
    public $created_by;
    public $updated_by;

    public function __construct()
    {
        settype($this->id, 'int');
        settype($this->role, 'int');
        settype($this->status, 'int');
        settype($this->created_at, 'int');
        settype($this->updated_at, 'int');
        settype($this->created_by, 'int');
        settype($this->updated_by, 'int');
    }

    /**
     * @param string $password
     */
    public function setPassword(string $password): void
    {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        if ($hash === false) {
            return;
        }
        $this->password_hash = $hash;
    }

    /**
     * Verify password.
     * @param string $password
     * @return bool
     */
    public function verifyPassword(string $password): bool
    {
        return password_verify($password, $this->password_hash);
    }

    /**
     * Provides an array or document to serialize as BSON
     * Called during serialization of the object to BSON. The method must return an array or stdClass.
     * Root documents (e.g. a MongoDB\BSON\Serializable passed to MongoDB\BSON\fromPHP()) will always be serialized as a BSON document.
     * For field values, associative arrays and stdClass instances will be serialized as a BSON document and sequential arrays (i.e. sequential, numeric indexes starting at 0) will be serialized as a BSON array.
     * @link https://php.net/manual/en/mongodb-bson-serializable.bsonserialize.php
     * @return array|object An array or stdClass to be serialized as a BSON array or document.
     */
    public function bsonSerialize(): array
    {
        return [
            '_id' => $this->id,
            'email' => $this->email,
            'status' => $this->status,
        ];
    }

    /**
     * Constructs the object from a BSON array or document
     * Called during unserialization of the object from BSON.
     * The properties of the BSON array or document will be passed to the method as an array.
     * @link https://php.net/manual/en/mongodb-bson-unserializable.bsonunserialize.php
     * @param array $data Properties within the BSON array or document.
     */
    public function bsonUnserialize(array $data)
    {
        if ($data['_id'] instanceof ObjectId) {
            $this->id = $data['_id']->__toString();
        }
        $this->email = $data['email'];
        $this->status = $data['status'];
    }
}
