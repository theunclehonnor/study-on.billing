<?php


namespace App\Controller;

use App\Entity\User;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/v1/users")
 */
class UserController extends AbstractController
{
    /**
     * @Route("/current", name="current_user", methods={"POST"})
     * @param SerializerInterface $serializer
     * @return Response
     */
    public function current(SerializerInterface $serializer): Response
    {
        // Получаем пользователя
        $user = $this->getUser();
        $response = new Response();
        // Проверка пользователя в системе
        if (!$user) {
            // Формируем ответ с ошибкой, если пользователя не существует
            $data = [
                'code' => Response::HTTP_NOT_FOUND,
                'message' => 'Данного пользователя не существует',
            ];
            $response->setStatusCode(Response::HTTP_NOT_FOUND);
        } else {
            // Если всё ок, и пользователь существует, то:
            $entityManager = $this->getDoctrine()->getManager();
            $userRepository = $entityManager->getRepository(User::class);
            // Получаем информацию о пользователе
            $user = $userRepository->findOneBy(['email' => $user->getUsername()]);
            // Формируем ответ с данными пользователя
            $data = [
                'username' => $user->getEmail(),
                'roles' => $user->getRoles(),
                'balance' => $user->getBalance()
            ];
            $response->setStatusCode(Response::HTTP_OK);
        }
        $response->setContent($serializer->serialize($data, 'json'));
        $response->headers->add(['Content-Type' => 'application/json']);
        return $response;
    }
}
