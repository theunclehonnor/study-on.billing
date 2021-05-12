<?php


namespace App\Tests;

use App\DataFixtures\AppFixtures;
use App\DataFixtures\CourseFixtures;
use App\Service\PaymentService;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Response;

class CourseControllerTest extends AbstractTest
{
    /**
     * @var string
     */
    private $startingPath = '/api/v1/courses';

    /**
     * @var SerializerInterface
     */
    private $serializer;

    public function getFixtures(): array
    {
        return [
            new AppFixtures(
                self::$kernel->getContainer()->get('security.password_encoder'),
                self::$kernel->getContainer()->get(PaymentService::class)
            ),
            new CourseFixtures(),
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->serializer = self::$kernel->getContainer()->get('jms_serializer');
    }

    public function auth($user): array
    {
        // Создание запроса
        $client = self::getClient();
        $client->request(
            'POST',
            '/api/v1/auth',
            [],
            [],
            [ 'CONTENT_TYPE' => 'application/json' ],
            $this->serializer->serialize($user, 'json')
        );


        // Проверка содержимого ответа (В ответе должен быть представлен token)
        return json_decode($client->getResponse()->getContent(), true);
    }

    // Тест получения всех курсов
    public function testGetAllCourses(): void
    {
        // Авторизация
        $user = [
            'username' => 'admin@yandex.ru',
            'password' => 'admin123',
        ];
        $userData = $this->auth($user);

        $client = self::getClient();
        // Создание запроса на получение всех курсов
        $client->request(
            'GET',
            $this->startingPath,
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer ' . $userData['token']
            ]
        );

        // Проверка статуса ответа
        $this->assertResponseCode(Response::HTTP_OK, $client->getResponse());

        // Проверка заголовка ответа (ответ в виде json)
        self::assertTrue($client->getResponse()->headers->contains(
            'Content-Type',
            'application/json'
        ));

        // Проверка содержимого ответа (8 курсов)
        $response = json_decode($client->getResponse()->getContent(), true);
        self::assertCount(8, $response);
    }

    // Тест получения информации о курсе
    public function testGetCourse(): void
    {
        //__________Проверка получения курса c валидными значениями__________
        // Авторизация
        $user = [
            'username' => 'admin@yandex.ru',
            'password' => 'admin123',
        ];
        $userData = $this->auth($user);

        $client = self::getClient();
        // Создание запроса на получения курса
        $codeCourse = 'AREND199230SKLADS';
        $client->request(
            'GET',
            $this->startingPath . '/' . $codeCourse,
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer ' . $userData['token'],
            ]
        );

        // Проверка статуса ответа
        $this->assertResponseCode(Response::HTTP_OK, $client->getResponse());

        // Проверка заголовка ответа (ответ в виде json?)
        self::assertTrue($client->getResponse()->headers->contains(
            'Content-Type',
            'application/json'
        ));

        // Проверка содержимого ответа (тип курса - арендуемый)
        $response = json_decode($client->getResponse()->getContent(), true);
        self::assertEquals('rent', $response['type']);

        //__________Проверка получения курса c неверным jwt токеном__________
        $token = '123';
        // Создание запроса на получения курса
        $codeCourse = 'AREND199230SKLADS';
        $client->request(
            'GET',
            $this->startingPath . '/' . $codeCourse,
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
            ]
        );
        // Проверка статуса ответа
        $this->assertResponseCode(Response::HTTP_UNAUTHORIZED, $client->getResponse());

        //__________Проверка получения несуществующего курса__________
        // Создание запроса на получения курса
        $codeCourse = '333';
        $client->request(
            'GET',
            $this->startingPath . '/' . $codeCourse,
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer ' . $userData['token'],
            ]
        );
        // Проверка статуса ответа
        $this->assertResponseCode(Response::HTTP_NOT_FOUND, $client->getResponse());
    }

    // Тест покупки курса
    public function testPayCourse(): void
    {
        //__________Проверка покупки курса c валидными значениями__________
        // Авторизация
        $user = [
            'username' => 'admin@yandex.ru',
            'password' => 'admin123',
        ];
        $userData = $this->auth($user);

        $client = self::getClient();
        // Создание запроса для оплаты курса
        $codeCourse = 'QNDIQJWDALSDASDJGLSAD';
        $client->request(
            'POST',
            $this->startingPath . '/' . $codeCourse . '/pay',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer ' . $userData['token'],
            ]
        );
        // Проверка статуса ответа
        $this->assertResponseCode(Response::HTTP_OK, $client->getResponse());

        // Проверка заголовка ответа (ответ в виде json?)
        self::assertTrue($client->getResponse()->headers->contains(
            'Content-Type',
            'application/json'
        ));

        // Проверка содержимого ответа (успешная операция)
        $response = json_decode($client->getResponse()->getContent(), true);
        self::assertEquals(true, $response['success']);

        //__________Проверка покупки курса c недостаточным балансом__________
        // Создание запроса для оплаты курса
        // Покупаеп курс за 65000 (после покупки курса тестом выше, у нас осталось 50000, а значит недостаточно средств)
        $codeCourse = 'MSALDLGSALDFJASLDDASODP';
        $client->request(
            'POST',
            $this->startingPath . '/' . $codeCourse . '/pay',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer ' . $userData['token'],
            ]
        );
        // Проверка статуса ответа
        $this->assertResponseCode(Response::HTTP_NOT_ACCEPTABLE, $client->getResponse());

        //__________Проверка покупки курса c невалидным jwt токеном__________
        $token = '123';
        $client->request(
            'POST',
            $this->startingPath . '/' . $codeCourse . '/pay',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
            ]
        );
        // Проверка статуса ответа
        $this->assertResponseCode(Response::HTTP_UNAUTHORIZED, $client->getResponse());
    }
}
