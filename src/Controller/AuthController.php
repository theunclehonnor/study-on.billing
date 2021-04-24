<?php


namespace App\Controller;

use App\Entity\User;
use App\Model\UserDTO;
use JMS\Serializer\SerializerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @Route("/api/v1")
 */
class AuthController extends AbstractController
{
    /**
     *  @Route("/auth", name="api_login_check", methods={"POST"})
     */
    public function auth(): void
    {
        // get jwt token
    }

    /**
     * @Route("/register", name="register", methods={"POST"})
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param ValidatorInterface $validator
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param JWTTokenManagerInterface $JWTManager
     * @return Response
     */
    public function register(
        Request $request,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        UserPasswordEncoderInterface $passwordEncoder,
        JWTTokenManagerInterface $JWTManager
    ): Response {
        // Десериализация
        $userDTO = $serializer->deserialize($request->getContent(), UserDTO::class, 'json');

        $data = [];
        $response = new Response();
        // Проверяем ошибки при валидации
        $validErrors = $validator->validate($userDTO);
        if (count($validErrors)) {
            // Параметры
            $data = [
                'code' => Response::HTTP_BAD_REQUEST,
                'message' => $validErrors,
            ];
            // Статус ответа
            $response->setStatusCode(Response::HTTP_BAD_REQUEST);
            $response->setContent($serializer->serialize($data, 'json'));
            $response->headers->add(['Content-Type' => 'application/json']);
            return $response;
        }
        // Существует ли данный пользовательн в системе
        $entityManager = $this->getDoctrine()->getManager();
        $userRepository = $entityManager->getRepository(User::class);
        if ($userRepository->findOneBy(['email' => $userDTO->email])) {
            $data = [
                'code' => Response::HTTP_FORBIDDEN,
                'message' => 'Пользователь с данным email уже существует',
            ];
            // Устанавливаем статус ответа
            $response->setStatusCode(Response::HTTP_FORBIDDEN);
        } else {
            // Создаем пользователя
            $user = User::fromDto($userDTO);
            $user->setPassword($passwordEncoder->encodePassword(
                $user,
                $user->getPassword()
            ));
            $entityManager->persist($user);
            $entityManager->flush();

            $data = [
                // JWT token
                'token' => $JWTManager->create($user),
            ];
            // Устанавливаем статус ответа
            $response->setStatusCode(Response::HTTP_CREATED);
        }
        $response->setContent($serializer->serialize($data, 'json'));
        $response->headers->add(['Content-Type' => 'application/json']);
        return $response;
    }
}
