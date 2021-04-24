<?php


namespace App\Model;

use JMS\Serializer\Annotation as Serializer;
use OpenApi\Annotations as OA;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @OA\Schema(
 *     title="UserDTO",
 *     description="UserDTO"
 * )
 *
 * Class UserDTO
 * @package App\Model
 */
class UserDTO
{
    /**
     * @OA\Property(
     *     format="email",
     *     title="Email",
     *     description="Email",
     *     example="test@yandex.ru"
     * )
     * @Serializer\Type("string")
     * @Assert\Email(message="Email address {{ value }} is not valid")
     */
    private $email;

    /**
     * @OA\Property(
     *     format="string",
     *     title="Password",
     *     description="Password",
     *     example="test123"
     * )
     * @Serializer\Type("string")
     * @Assert\Length(
     *     min="6",
     *     minMessage="Your password must be at least {{ limit }} characters",
     * )
     * @Assert\NotBlank()
     */
    private $password;

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @return mixed
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param mixed $email
     */
    public function setEmail($email): void
    {
        $this->email = $email;
    }

    /**
     * @param mixed $password
     */
    public function setPassword($password): void
    {
        $this->password = $password;
    }
}
