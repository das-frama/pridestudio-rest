<?php declare(strict_types=1);

namespace app\enitites\order;

/**
 * Client class.
 */
class Client 
{
    /** @var int */
    public $id;

    /** @var string */
    public $name;

    /** @var string */
    public $email;

    /** @var string */
    public $phone;

    /** @var int */
    public $sex;

    /** @var string */
    public $comment;
}