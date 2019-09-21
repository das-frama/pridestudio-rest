<?php

declare(strict_types=1);

namespace app\tests;

use app\entity\User;
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
