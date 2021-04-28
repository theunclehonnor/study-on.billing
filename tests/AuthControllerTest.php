<?php

namespace App\Tests;

use App\DataFixtures\AppFixtures;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Response;

class AuthControllerTest extends AbstractTest
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

    // Тесты авторизации в системе
    public function testAuth(): void
    {
        //_____________Проверка успешной авторизации_____________
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

        // Проверка статуса ответа
        $this->assertResponseCode(Response::HTTP_OK, $client->getResponse());

        // Проверка заголовка ответа, что он действительно в формате json
        self::assertTrue($client->getResponse()->headers->contains(
            'Content-Type',
            'application/json'
        ));

        // Проверка содержимого ответа (token)
        $json = json_decode($client->getResponse()->getContent(), true);
        self::assertNotEmpty($json['token']);

        //_____________Проверка неуспешной авторизации_____________
        // Авторизируемся существующим пользователем, но не с верным паролем
        $user = [
            'username' => 'user@yandex.ru',
            'password' => 'user911',
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

        // Проверка статуса ответа, 401
        $this->assertResponseCode(Response::HTTP_UNAUTHORIZED, $client->getResponse());

        // Проверка заголовка ответа, что он действительно в формате json
        self::assertTrue($client->getResponse()->headers->contains(
            'Content-Type',
            'application/json'
        ));

        // Проверка содержимого ответа (Сообщение об оишбке)
        $json = json_decode($client->getResponse()->getContent(), true);
        self::assertNotEmpty($json['message']);
    }

    // Тест успешной регистрации
    public function testRegisterSuccessful(): void
    {
        // Передадим данные о новом пользователе
        $user = [
            'email' => 'testUser11@yandex.ru',
            'password' => 'testUser11',
        ];

        // Формируем запрос
        $client = self::getClient();
        $client->request(
            'POST',
            $this->startingPath.'/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            $this->serializer->serialize($user, 'json')
        );

        // Проверка статуса ответа, 201
        $this->assertResponseCode(Response::HTTP_CREATED, $client->getResponse());

        // Проверка заголовка ответа, что он действительно в формате json
        self::assertTrue($client->getResponse()->headers->contains(
            'Content-Type',
            'application/json'
        ));

        // Проверка содержимого ответа (token)
        $json = json_decode($client->getResponse()->getContent(), true);
        self::assertNotEmpty($json['token']);
    }

    // Тест для неуспешной регистрации
    public function testExistUserRegister(): void
    {
        //_____________Проверка на уже существующего пользователя_____________
        // Данные пользователя
        $user = [
            'email' => 'user@yandex.ru',
            'password' => 'user123',
        ];

        // Формируем запрос
        $client = self::getClient();
        $client->request(
            'POST',
            $this->startingPath.'/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            $this->serializer->serialize($user, 'json')
        );

        // Проверка статуса ответа, 403
        $this->assertResponseCode(Response::HTTP_FORBIDDEN, $client->getResponse());

        // Проверка заголовка ответа, что он действительно в формате json
        self::assertTrue($client->getResponse()->headers->contains(
            'Content-Type',
            'application/json'
        ));

        // Проверка содержимого ответа (Сообщение об ошибке)
        $json = json_decode($client->getResponse()->getContent(), true);
        self::assertEquals('Пользователь с данным email уже существует', $json['message']);

        //_____________Проверка валидации полей_____________
        // Данные пользователя, где пароль состоит менее чем из 6-и символов
        $user = [
            'email' => 'test999123@yandex.ru',
            'password' => 'test',
        ];

        // Формируем запрос
        $client = self::getClient();
        $client->request(
            'POST',
            $this->startingPath.'/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            $this->serializer->serialize($user, 'json')
        );

        // Проверка статуса ответа, 400
        $this->assertResponseCode(Response::HTTP_BAD_REQUEST, $client->getResponse());

        // Проверка заголовка ответа, что он действительно в формате json
        self::assertTrue($client->getResponse()->headers->contains(
            'Content-Type',
            'application/json'
        ));

        // Проверка содержимого ответа (Сообщение об ошибке)
        $json = json_decode($client->getResponse()->getContent(), true);
        self::assertNotEmpty($json['message']);
    }
}
