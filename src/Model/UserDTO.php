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
     *     type="string",
     *     title="Username",
     *     description="Username"
     * )
     * @Serializer\Type("string")
     */
    private $username;

    /**
     * @OA\Property(
     *     format="email",
     *     title="Email",
     *     description="Email"
     * )
     * @Serializer\Type("string")
     * @Assert\Email(message="Email address {{ value }} is not valid")
     */
    private $email;

    /**
     * @OA\Property(
     *     type="string",
     *     title="Password",
     *     description="Password"
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
     * @OA\Property(
     *     type="array",
     *     @OA\Items(
     *         type="string"
     *     ),
     *     title="Roles",
     *     description="Roles"
     * )
     * @Serializer\Type("array")
     */
    private $roles = [];

    /**
     * @OA\Property(
     *     type="float",
     *     title="Balance",
     *     description="Balance"
     * )
     * @Serializer\Type("float")
     */
    private $balance;

    public function getEmail()
    {
        return $this->email;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function setEmail($email): void
    {
        $this->email = $email;
    }

    public function setPassword($password): void
    {
        $this->password = $password;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function setRoles(array $roles): void
    {
        $this->roles = $roles;
    }

    public function getBalance()
    {
        return $this->balance;
    }

    public function setBalance(float $balance): void
    {
        $this->balance = $balance;
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function setUsername(string $username): void
    {
        $this->username = $username;
        $this->email = $username;
    }
}
