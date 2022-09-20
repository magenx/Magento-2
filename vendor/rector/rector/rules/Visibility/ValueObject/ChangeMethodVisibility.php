<?php

declare (strict_types=1);
namespace Rector\Visibility\ValueObject;

use Rector\Core\Validation\RectorAssert;
final class ChangeMethodVisibility
{
    /**
     * @var class-string
     * @readonly
     */
    private $class;
    /**
     * @readonly
     * @var string
     */
    private $method;
    /**
     * @readonly
     * @var int
     */
    private $visibility;
    /**
     * @param class-string $class
     */
    public function __construct(string $class, string $method, int $visibility)
    {
        $this->class = $class;
        $this->method = $method;
        $this->visibility = $visibility;
        RectorAssert::className($class);
    }
    public function getClass() : string
    {
        return $this->class;
    }
    public function getMethod() : string
    {
        return $this->method;
    }
    public function getVisibility() : int
    {
        return $this->visibility;
    }
}
