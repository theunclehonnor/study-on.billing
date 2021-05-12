<?php


namespace App\Model;

use JMS\Serializer\Annotation as Serializer;

class PayDTO
{
    /**
     * @Serializer\Type("bool")
     */
    private $success;

    /**
     * @Serializer\Type("string")
     */
    private $courseType;

    /**
     * @Serializer\Type("string")
     */
    private $expiresAt;

    public function __construct(bool $success, string $courseType, ?string $expiresAt)
    {
        $this->success = $success;
        $this->courseType = $courseType;
        $this->expiresAt = $expiresAt;
    }

    public function getSuccess(): bool
    {
        return $this->success;
    }

    public function setSuccess(string $success): void
    {
        $this->success = $success;
    }

    public function getCourseType(): string
    {
        return $this->courseType;
    }

    public function setCourseType(string $courseType): void
    {
        $this->courseType = $courseType;
    }

    public function getExpiresAt(): ?string
    {
        return $this->expiresAt;
    }

    public function setExpiresAt(?string $expiresAt): void
    {
        $this->expiresAt = $expiresAt;
    }
}