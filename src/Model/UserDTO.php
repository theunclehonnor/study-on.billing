<?php


namespace App\Model;

use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

class UserDTO
{
    /**
     * @Serializer\Type("string")
     * @Assert\Email(message="Email address {{ value }} is not valid")
     */
    private $email;

    /**
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
