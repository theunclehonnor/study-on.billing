<?php


namespace App\Controller;

use App\Entity\Course;
use App\Entity\User;
use App\Model\CourseDTO;
use App\Model\PayDTO;
use App\Service\PaymentService;
use JMS\Serializer\SerializerInterface;
use OpenApi\Annotations as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/v1/courses")
 */
class CourseController extends AbstractController
{
    /**
     * @OA\Get(
     *     path="/api/v1/courses",
     *     tags={"Courses"},
     *     summary="Получение всех курсов",
     *     description="Получение всех курсов",
     *     operationId="courses.index",
     *     @OA\Response(
     *          response="200",
     *          description="Успешное получение курсов",
     *          @OA\JsonContent(
     *              type="array",
     *              @OA\Items(
     *                  @OA\Property(
     *                      property="code",
     *                      type="string",
     *                      example="AREND199230SKLADS"
     *                  ),
     *                  @OA\Property(
     *                      property="type",
     *                      type="string",
     *                      example="rent"
     *                  ),
     *                  @OA\Property(
     *                      property="price",
     *                      type="number",
     *                      format="float",
     *                      example="2021"
     *                  ),
     *              )
     *          )
     *     )
     * )
     *
     * @Route("", name="courses_index", methods={"GET"})
     */
    public function index(SerializerInterface $serializer): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $courseRepository = $entityManager->getRepository(Course::class);

        // Получаем все курсы
        $courses = $courseRepository->findAll();

        $coursesDto = [];
        foreach ($courses as $course) {
            $coursesDTO[] = new CourseDTO(
                $course->getCode(),
                $course->getTypeFormatString(),
                $course->getPrice(),
                $course->getTitle()
            );
        }

        $response = new Response();
        // Статус ответа
        $response->setStatusCode(Response::HTTP_OK);
        // Передаем наши курсы
        $response->setContent($serializer->serialize($coursesDTO, 'json'));
        // Устанавливаем заголовок ( формат json )
        $response->headers->add(['Content-Type' => 'application/json']);
        return $response;
    }

    /**
     * @OA\Get(
     *     path="/api/v1/courses/{code}",
     *     tags={"Courses"},
     *     summary="Получение данного курса",
     *     description="Получение данного курса",
     *     operationId="courses.show",
     *     @OA\Response(
     *         response=200,
     *         description="Курс получен",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="code",
     *                     type="string",
     *                     example="AREND199230SKLADS",
     *                 ),
     *                 @OA\Property(
     *                     property="type",
     *                     type="string",
     *                     example="rent",
     *                 ),
     *                 @OA\Property(
     *                     property="price",
     *                     type="number",
     *                     example="2021",
     *                 ),
     *             ),
     *        )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Данный курс не найден",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="code",
     *                     type="string",
     *                     example="404"
     *                 ),
     *                 @OA\Property(
     *                     property="message",
     *                     type="string",
     *                     example="Данный курс не найден"
     *                 ),
     *             ),
     *        )
     *     ),
     * )
     * @Route("/{code}", name="course_show", methods={"GET"})
     */
    public function show(string $code, SerializerInterface $serializer): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $courseRepository = $entityManager->getRepository(Course::class);

        $course = $courseRepository->findOneBy(['code' => $code]);

        $statusCode = '';
        $dataResponse = [];
        if (!isset($course)) {
            $dataResponse = [
                'code' => Response::HTTP_NOT_FOUND,
                'message' => 'Данный курс не найден',
            ];
            $statusCode = Response::HTTP_NOT_FOUND;
        } else {
            $dataResponse = new CourseDto(
                $course->getCode(),
                $course->getTypeFormatString(),
                $course->getPrice(),
                $course->getTitle()
            );
            $statusCode = Response::HTTP_OK;
        }

        $response = new Response();
        // Статус ответа
        $response->setStatusCode($statusCode);
        // Передаем данные
        $response->setContent($serializer->serialize($dataResponse, 'json'));
        // Устанавливаем заголовок ( формат json )
        $response->headers->add(['Content-Type' => 'application/json']);
        return $response;
    }

    /**
     * @OA\Post(
     *     tags={"Courses"},
     *     path="/api/v1/courses/{code}/pay",
     *     summary="Оплата курса",
     *     description="Оплата курса",
     *     security={
     *         { "Bearer":{} },
     *     },
     *     @OA\Response(
     *         response=200,
     *         description="Курс куплен",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="success",
     *                     type="boolean",
     *                 ),
     *                 @OA\Property(
     *                     property="course_type",
     *                     type="string",
     *                 ),
     *                 @OA\Property(
     *                     property="expires_at",
     *                     type="string",
     *                 ),
     *             ),
     *        )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Данный курс не найден",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="code",
     *                     type="string",
     *                     example="404",
     *                 ),
     *                 @OA\Property(
     *                     property="message",
     *                     type="string",
     *                     example="Данный курс не найден",
     *                 ),
     *             ),
     *        )
     *     ),
     *     @OA\Response(
     *         response=406,
     *         description="У вас недостаточно средств",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="code",
     *                     type="string",
     *                     example="406",
     *                 ),
     *                 @OA\Property(
     *                     property="message",
     *                     type="string",
     *                     example="На вашем счету недостаточно средств",
     *                 ),
     *             ),
     *        )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Invalid JWT Token",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="code",
     *                     type="string",
     *                     example="401",
     *                 ),
     *                 @OA\Property(
     *                     property="message",
     *                     type="string",
     *                     example="Invalid JWT Token",
     *                 ),
     *             ),
     *        )
     *     )
     * )
     * @Route("/{code}/pay", name="course_pay", methods={"POST"})
     */
    public function pay(string $code, PaymentService $paymentService, SerializerInterface $serializer): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $courseRepository = $entityManager->getRepository(Course::class);

        $course = $courseRepository->findOneBy(['code' => $code]);

        $dataResponse = [];
        $response = new Response();
        if (!$course) {
            $dataResponse = [
                'code' => Response::HTTP_NOT_FOUND,
                'message' => 'Данный курс не найден',
            ];
            // Статус ответа
            $response->setStatusCode($dataResponse['code']);
            // Передаем данные
            $response->setContent($serializer->serialize($dataResponse, 'json'));
            // Устанавливаем заголовок ( формат json )
            $response->headers->add(['Content-Type' => 'application/json']);
            return $response;
        }

        /* @var User $user */
        $user = $this->getUser();
        try {
            $transaction = $paymentService->paymentCourses($user, $course);
            $expiresAt = $transaction->getExpiresAt();
            $payDto = new PayDTO(
                true,
                $course->getTypeFormatString(),
                $expiresAt ? $expiresAt->format('Y-m-d T H:i:s') : null
            );

            // Статус ответа
            $response->setStatusCode(Response::HTTP_OK);
            // Передаем данные
            $response->setContent($serializer->serialize($payDto, 'json'));
            // Устанавливаем заголовок ( формат json )
            $response->headers->add(['Content-Type' => 'application/json']);
            return $response;
        } catch (\Exception $e) {
            $dataResponse = [
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
            ];
            // Статус ответа
            $response->setStatusCode($dataResponse['code']);
            // Передаем данные
            $response->setContent($serializer->serialize($dataResponse, 'json'));
            // Устанавливаем заголовок ( формат json )
            $response->headers->add(['Content-Type' => 'application/json']);
            return $response;
        }
    }
}
