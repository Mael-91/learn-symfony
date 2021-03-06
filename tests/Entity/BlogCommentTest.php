<?php

namespace App\Tests;

use App\Entity\Blog;
use App\Entity\BlogComment;
use App\Entity\User;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\ConstraintViolation;

class BlogCommentTest extends KernelTestCase {

    use AssertTrait;

    public function testValidEntity() {
        $this->assertValidatorErrors($this->getEntity(), 0);
    }

    public function testBlankContent() {
        $this->assertValidatorErrors($this->getEntity()->setContent(''), 1);
    }

    private function getEntity(): BlogComment {
        return (new BlogComment())
            ->setPost($this->makePost())
            ->setAuthor($this->makeUser())
            ->setContent('my content');
    }

    private function makePost(): Blog {
        $post = new Blog();
        $post->setAuthor($this->makeUser())
            ->setContent('my post content')
            ->setTitle('my title')
            ->setPictureFilename('test.jpg');
        return $post;
    }

    private function makeUser(): User {
        $user = new User();
        $password = '$argon2id$v=19$m=65536,t=4,p=1$F3JG29Imj3W9Nz0paHcxHA$OpYmPkCOhiA3/r7ilr00SOYLbfP9ZfcUCz60GLxVmH0';
        $user->setUsername('Mael')
            ->setRoles(['ROLE_SUPER_ADMIN'])
            ->setPassword($password)
            ->setEmail('mael.constantin@gmail.com')
            ->setFirstName('Mael')
            ->setLastName('Constantin')
            ->setBirthday(new \DateTime('24-09-2003'))
            ->setSexe(1)
            ->setEnabled(true)
            ->setAvatarFilename('avatar.png')
            ->setBannerFilename('test_banner.jpg');
        return $user;
    }
}
