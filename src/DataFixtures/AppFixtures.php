<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Service\PaymentService;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    private $passwordEncoder;
    private $paymentService;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder, PaymentService $paymentService)
    {
        $this->passwordEncoder = $passwordEncoder;
        $this->paymentService = $paymentService;
    }

    /**
     * @throws \Exception
     */
    public function load(ObjectManager $manager)
    {
        // Обычного пользователя
        $user = new User();
        $user->setEmail('user@yandex.ru');
        $user->setPassword($this->passwordEncoder->encodePassword(
            $user,
            'user123'
        ));
        $user->setRoles(["ROLE_USER"]);
        $user->setBalance(0);
        $manager->persist($user);
        $this->paymentService->deposit($user, $_ENV['START_AMOUNT']);

        // Супер пользователь
        $user = new User();
        $user->setEmail('admin@yandex.ru');
        $user->setPassword($this->passwordEncoder->encodePassword(
            $user,
            'admin123'
        ));
        $user->setRoles(["ROLE_SUPER_ADMIN"]);
        $user->setBalance(0);
        $manager->persist($user);
        $this->paymentService->deposit($user, $_ENV['START_AMOUNT']);

        $manager->flush();
    }
}
