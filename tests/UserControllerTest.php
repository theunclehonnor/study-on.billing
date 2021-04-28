<?php

namespace App\Tests;

use App\DataFixtures\AppFixtures;
use App\Entity\User;
use App\Model\UserDTO;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Response;

class UserControllerTest extends AbstractTest
{
    /**
     * @var string
     */
    private $startingPath = '/api/v1';

    /**
     * @var SerializerInterface
     */
    private $serializer;

    public function getFixtures(): array
    {
        return [new AppFixtures(self::$kernel->getContainer()->get('security.password_encoder'))];
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->serializer = self::$kernel->getContainer()->get('jms_serializer');
    }

    public function testCurrent(): void
    {
        $client = self::getClient();

        // Авторизируемся существующим пользователем
        $user = [
            'username' => 'user@yandex.ru',
            'password' => 'user123',
        ];

        // Формируем запрос
        $client = self::getClient();
        $client->request(
            'POST',
            $this->startingPath.'/auth',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            $this->serializer->serialize($user, 'json')
        );
        $json = json_decode($client->getResponse()->getContent(), true);
        // Получаем токен клиента
        $token = $json['token'];

        //_____________Проверка успешной операции получения данных_____________
        // Формирование верного запроса
        $contentHeaders = [
            'HTTP_AUTHORIZATION' => 'Bearer '.$token,
            'CONTENT_TYPE' => 'application/json',
        ];

        $client->request(
            'GET',
            $this->startingPath.'/users/current',
            [],
            [],
            $contentHeaders
        );
        // Проверка статуса ответа, 200
        $this->assertResponseCode(Response::HTTP_OK, $client->getResponse());

        // Проверка заголовка ответа, что он действительно в формате json
        self::assertTrue($client->getResponse()->headers->contains(
            'Content-Type',
            'application/json'
        ));

        /** @var UserDTO $responseUserDTO */
        $responseUserDTO = $this->serializer->deserialize($client->getResponse()->getContent(), UserDTO::class, 'json');

        // Получим данные о пользователе из бд и сравним
        $em = self::getEntityManager();
        $user = $em->getRepository(User::class)->findOneBy(['email' => $responseUserDTO->getUsername()]);
        // Сравнение данных
        self::assertEquals($responseUserDTO->getUsername(), $user->getEmail());
        self::assertEquals($responseUserDTO->getRoles()[0], $user->getRoles()[0]);
        self::assertEquals($responseUserDTO->getBalance(), $user->getBalance());

        //_____________Проверка неуспешной операции (jwt токен неверный)_____________
        $token = 'шишль мышль';
        // Передаем неверный токен
        $contentHeaders = [
            'HTTP_AUTHORIZATION' => 'Bearer '.$token,
            'CONTENT_TYPE' => 'application/json',
        ];

        $client->request(
            'GET',
            $this->startingPath.'/users/current',
            [],
            [],
            $contentHeaders
        );
        // Проверка статуса ответа, 401
        $this->assertResponseCode(Response::HTTP_UNAUTHORIZED, $client->getResponse());
    }
}
