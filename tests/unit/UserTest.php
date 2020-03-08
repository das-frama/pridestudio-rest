<?php

declare(strict_types=1);

namespace tests\unit;

use app\Entities\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testVerifyPassword()
    {
        $user = new User;
        $user->setPassword('moryachok815');
        $this->assertTrue($user->verifyPassword('moryachok815'));
    }
}
