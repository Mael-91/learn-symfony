<?php

namespace App\Event;

use App\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;

class BadPasswordLoginEvent extends Event {

    /**
     * @var User
     */
    private $user;

    public function __construct(User $user) {
        $this->user = $user;
    }

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }
}