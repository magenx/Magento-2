<?php

declare(strict_types=1);

namespace Laminas\Di\CodeGenerator;

use Laminas\Di\ConfigInterface;
use Laminas\Di\Exception\RuntimeException;
use Laminas\Di\Resolver\DependencyResolverInterface;
use Laminas\Di\Resolver\InjectionInterface;
use Laminas\Di\Resolver\TypeInjection;
use SplFileObject;

use function assert;
use function dirname;
use function file_get_contents;
use function implode;
use function is_string;
use function preg_replace;
use function sprintf;
use function str_repeat;
use function str_replace;
use function strrpos;
use function strtr;
use function substr;
use function var_export;

/**
 * Generates factory classes
 */
class FactoryGenerator
{
    use GeneratorTrait;

    private const INDENTATION_SPACES  = 4;
    private const TEMPLATE_FILE       = __DIR__ . '/../../templates/factory.template';
    private const PARAMETERS_TEMPLATE = <<<'__CODE__'

        $args = empty($options)
            ? [
                %s
            ]
            : [
                %s
            ];

__CODE__;

    /** @var string */
    private $namespace;

    /** @var DependencyResolverInterface */
    private $resolver;

    /** @var ConfigInterface */
    private $config;

    /** @var array */
    private $classmap = [];

    public function __construct(
        ConfigInterface $config,
        DependencyResolverInterface $resolver,
        ?string $namespace = null
    ) {
        $this->resolver  = $resolver;
        $this->config    = $config;
        $this->namespace = $namespace ?: 'LaminasDiGenerated';
    }

    protected function buildClassName(string $name): string
    {
        return preg_replace('~[^a-z0-9\\\\]+~i', '_', $name) . 'Factory';
    }

    protected function buildFileName(string $name): string
    {
        return str_replace('\\', '/', $this->buildClassName($name)) . '.php';
    }

    /**
     * @return string[] The resulting parts as [$namspace, $unqualifiedClassName]
     */
    private function splitFullyQualifiedClassName(string $class): array
    {
        $pos = strrpos($class, '\\');

        if ($pos === false) {
            return ['', $class];
        }

        $namespace            = substr($class, 0, $pos);
        $unqualifiedClassName = substr($class, $pos + 1);

        return [$namespace, $unqualifiedClassName];
    }

    private function getClassName(string $type): string
    {
        if ($this->config->isAlias($type)) {
            return $this->config->getClassForAlias($type) ?? $type;
        }

        return $type;
    }

    /**
     * @param InjectionInterface[] $injections
     */
    private function canGenerateForParameters(iterable $injections): bool
    {
        foreach ($injections as $injection) {
            if (! $injection->isExportable()) {
                return false;
            }
        }

        return true;
    }

    /**
     * Builds the code for constructor parameters
     *
     * @param InjectionInterface[] $injections
     */
    private function buildParametersCode(iterable $injections): ?string
    {
        $withOptions    = [];
        $withoutOptions = [];

        foreach ($injections as $name => $injection) {
            $code = $injection->export();

            if ($injection instanceof TypeInjection) {
                $code = '$container->get(' . $code . ')';
            }

            // build for two cases:
            // 1. Parameters are passed at call time
            // 2. No Parameters were passed at call time (might be slightly faster)
            $withoutOptions[] = sprintf('%s, // %s', $code, $name);
            $withOptions[]    = sprintf(
                'array_key_exists(%1$s, $options) ? $options[%1$s] : %2$s,',
                var_export($name, true),
                $code
            );
        }

        if (! $withOptions) {
            return null;
        }

        $tabs = sprintf("\n%s", str_repeat(' ', self::INDENTATION_SPACES * 4));

        // Build conditional initializer code:
        // If no $params were provided ignore it completely
        // otherwise check if there is a value for each dependency in $params.
        return sprintf(
            self::PARAMETERS_TEMPLATE,
            implode($tabs, $withoutOptions),
            implode($tabs, $withOptions)
        );
    }

    /**
     * @throws RuntimeException When generating the factory failed.
     */
    public function generate(string $class): string
    {
        $className  = $this->getClassName($class);
        $injections = $this->resolver->resolveParameters($className);

        if (! $this->canGenerateForParameters($injections)) {
            throw new RuntimeException(sprintf(
                'Cannot generate parameter code for type "%s" (class: "%s")',
                $class,
                $className
            ));
        }

        $paramsCode                                = $this->buildParametersCode($injections);
        $absoluteClassName                         = '\\' . $className;
        $factoryClassName                          = $this->namespace . '\\' . $this->buildClassName($class);
        [$namespace, $unqualifiedFactoryClassName] = $this->splitFullyQualifiedClassName($factoryClassName);

        $filename = $this->buildFileName($class);
        $filepath = $this->outputDirectory . '/' . $filename;
        $template = file_get_contents(self::TEMPLATE_FILE);

        assert(is_string($template));

        $code = strtr(
            $template,
            [
                '%class%'                => $absoluteClassName,
                '%namespace%'            => $namespace ? "namespace $namespace;\n" : '',
                '%factory_class%'        => $unqualifiedFactoryClassName,
                '%options_to_args_code%' => $paramsCode,
                '%use_array_key_exists%' => $paramsCode ? "\nuse function array_key_exists;" : '',
                '%args%'                 => $paramsCode ? '...$args' : '',
            ]
        );

        $this->ensureDirectory(dirname($filepath));

        $output = new SplFileObject($filepath, 'w');
        $output->fwrite($code);
        $this->classmap[$factoryClassName] = $filename;

        return $factoryClassName;
    }

    public function getClassmap(): array
    {
        return $this->classmap;
    }
}
