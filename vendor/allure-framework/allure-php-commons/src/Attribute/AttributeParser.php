<?php

declare(strict_types=1);

namespace Qameta\Allure\Attribute;

use Closure;
use Doctrine\Common\Annotations\AnnotationReader;
use Qameta\Allure\Exception\InvalidMethodNameException;
use Qameta\Allure\Model;
use Qameta\Allure\Model\ModelProviderInterface;
use Qameta\Allure\Setup\LinkTemplateCollection;
use Qameta\Allure\Setup\LinkTemplateCollectionInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionProperty;

use function array_merge;
use function is_string;

final class AttributeParser implements ModelProviderInterface
{
    use Model\ModelProviderTrait;

    private ?string $displayName = null;

    private ?string $description = null;

    private ?string $descriptionHtml = null;

    /**
     * @var list<Model\Label>
     */
    private array $labels = [];

    /**
     * @var list<Model\Link>
     */
    private array $links = [];

    /**
     * @var list<Model\Parameter>
     */
    private array $parameters = [];

    /**
     * @param array<AttributeInterface>       $attributes
     * @param LinkTemplateCollectionInterface $linkTemplates
     */
    public function __construct(
        array $attributes,
        private LinkTemplateCollectionInterface $linkTemplates,
    ) {
        $this->processAnnotations(...$attributes);
    }

    /**
     * @param class-string|object|null             $classOrObject
     * @param callable-string|Closure|null         $methodOrFunction
     * @param string|null                          $property
     * @param LinkTemplateCollectionInterface|null $linkTemplates
     * @return list<ModelProviderInterface>
     * @throws ReflectionException
     */
    public static function createForChain(
        string|object|null $classOrObject,
        string|Closure|null $methodOrFunction = null,
        ?string $property = null,
        ?LinkTemplateCollectionInterface $linkTemplates = null,
    ): array {
        $reader = new LegacyAttributeReader(
            new AnnotationReader(),
            new AttributeReader(),
        );
        $annotations = [];

        if (isset($classOrObject)) {
            $annotations[] = $reader->getClassAnnotations(new ReflectionClass($classOrObject));
            if (isset($property)) {
                $annotations[] = $reader->getPropertyAnnotations(new ReflectionProperty($classOrObject, $property));
            }
        }

        if (isset($methodOrFunction)) {
            $annotations[] = isset($classOrObject)
                ? $reader->getMethodAnnotations(
                    new ReflectionMethod(
                        $classOrObject,
                        is_string($methodOrFunction)
                            ? $methodOrFunction
                            : throw new InvalidMethodNameException($methodOrFunction),
                    ),
                )
                : $reader->getFunctionAnnotations(new ReflectionFunction($methodOrFunction));
        }

        return [
            new self(
                array_merge(...$annotations),
                $linkTemplates ?? new LinkTemplateCollection(),
            )
        ];
    }

    private function processAnnotations(AttributeInterface ...$attributes): void
    {
        foreach ($attributes as $attribute) {
            if ($attribute instanceof DisplayNameInterface) {
                $this->displayName = $attribute->getValue();
            }
            if ($attribute instanceof DescriptionInterface) {
                if ($attribute->isHtml()) {
                    $this->descriptionHtml = $attribute->getValue();
                } else {
                    $this->description = $attribute->getValue();
                }
            }
            if ($attribute instanceof LinkInterface) {
                $this->links[] = $this->createLink($attribute);
            }
            if ($attribute instanceof LabelInterface) {
                $this->labels[] = $this->createLabel($attribute);
            }
            if ($attribute instanceof ParameterInterface) {
                $this->parameters[] = $this->createParameter($attribute);
            }
        }
    }

    private function createLink(LinkInterface $link): Model\Link
    {
        $linkType = Model\LinkType::fromOptionalString($link->getType());

        return new Model\Link(
            name: $link->getName(),
            url: $link->getUrl() ?? $this->linkTemplates->get($linkType)?->buildUrl($link->getName()),
            type: $linkType,
        );
    }

    /**
     * @return list<Model\Link>
     */
    public function getLinks(): array
    {
        return $this->links;
    }

    private function createLabel(LabelInterface $label): Model\Label
    {
        return new Model\Label(
            name: $label->getName(),
            value: $label->getValue(),
        );
    }

    /**
     * @return list<Model\Label>
     */
    public function getLabels(): array
    {
        return $this->labels;
    }

    private function createParameter(ParameterInterface $parameter): Model\Parameter
    {
        return new Model\Parameter(
            name: $parameter->getName(),
            value: $parameter->getValue(),
            excluded: $parameter->getExcluded(),
            mode: Model\ParameterMode::fromOptionalString($parameter->getMode()),
        );
    }

    /**
     * @return list<Model\Parameter>
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function getDisplayName(): ?string
    {
        return $this->displayName;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getDescriptionHtml(): ?string
    {
        return $this->descriptionHtml;
    }
}
