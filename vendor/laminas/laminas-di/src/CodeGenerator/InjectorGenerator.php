<?php

declare(strict_types=1);

namespace Laminas\Di\CodeGenerator;

use Laminas\Di\CodeGenerator\AutoloadGenerator;
use Laminas\Di\CodeGenerator\FactoryGenerator;
use Laminas\Di\ConfigInterface;
use Laminas\Di\Definition\DefinitionInterface;
use Laminas\Di\Resolver\DependencyResolverInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use SplFileObject;
use Throwable;

use function array_keys;
use function array_map;
use function assert;
use function file_get_contents;
use function implode;
use function is_string;
use function sprintf;
use function str_repeat;
use function strtr;
use function var_export;

/**
 * Generator for the dependency injector
 *
 * Generates an Injector class that will use a generated factory for a requested
 * type, if available. This factory will contain pre-resolved dependencies
 * from the provided configuration, definition and resolver instances.
 */
class InjectorGenerator
{
    use GeneratorTrait;

    private const FACTORY_LIST_TEMPLATE = __DIR__ . '/../../templates/factory-list.template';
    private const INJECTOR_TEMPLATE     = __DIR__ . '/../../templates/injector.template';
    private const INDENTATION_SPACES    = 4;

    /**
     * @deprecated
     *
     * @var DefinitionInterface|null
     */
    protected $definition;

    private string $namespace;

    private FactoryGenerator $factoryGenerator;

    private AutoloadGenerator $autoloadGenerator;

    private LoggerInterface $logger;

    /**
     * Constructs the compiler instance
     *
     * @param ConfigInterface             $config The configuration to compile from
     * @param DependencyResolverInterface $resolver The resolver to utilize
     * @param string|null                 $namespace Namespace to use for generated class; defaults
     *                     to Laminas\Di\Generated.
     * @param LoggerInterface|null        $logger An optional logger instance to log failures
     *            and processed classes.
     */
    public function __construct(
        private ConfigInterface $config,
        DependencyResolverInterface $resolver,
        ?string $namespace = null,
        ?LoggerInterface $logger = null
    ) {
        $this->namespace         = $namespace ? : 'Laminas\Di\Generated';
        $this->factoryGenerator  = new FactoryGenerator($config, $resolver, $this->namespace . '\Factory');
        $this->autoloadGenerator = new AutoloadGenerator($this->namespace);
        $this->logger            = $logger ?? new NullLogger();
    }

    private function buildFromTemplate(string $templateFile, string $outputFile, array $replacements): void
    {
        $template = file_get_contents($templateFile);

        assert(is_string($template));

        $code = strtr($template, $replacements);
        $file = new SplFileObject($outputFile, 'w');

        $file->fwrite($code);
        $file->fflush();
    }

    private function generateInjector(): void
    {
        assert(is_string($this->outputDirectory));

        $this->buildFromTemplate(
            self::INJECTOR_TEMPLATE,
            sprintf('%s/GeneratedInjector.php', $this->outputDirectory),
            [
                '%namespace%' => $this->namespace ? "namespace {$this->namespace};\n" : '',
            ]
        );
    }

    /**
     * @param array<string, string> $factories
     */
    private function generateFactoryList(array $factories): void
    {
        $indentation = sprintf("\n%s", str_repeat(' ', self::INDENTATION_SPACES));
        $codeLines   = array_map(
            static fn(string $key, string $value): string =>
                sprintf('%s => %s,', var_export($key, true), var_export($value, true)),
            array_keys($factories),
            $factories
        );

        assert(is_string($this->outputDirectory));

        $this->buildFromTemplate(self::FACTORY_LIST_TEMPLATE, sprintf('%s/factories.php', $this->outputDirectory), [
            '%factories%' => implode($indentation, $codeLines),
        ]);
    }

    /**
     * @param array<string, string> $factories
     */
    private function generateTypeFactory(string $class, array &$factories): void
    {
        if (isset($factories[$class])) {
            return;
        }

        $this->logger->debug(sprintf('Generating factory for class "%s"', $class));

        try {
            $factory = $this->factoryGenerator->generate($class);

            if ($factory) {
                $factories[$class] = $factory;
            }
        } catch (Throwable $e) {
            $this->logger->error(sprintf(
                'Could not create factory for "%s": %s',
                $class,
                $e->getMessage()
            ));
        }
    }

    private function generateAutoload(): void
    {
        $addFactoryPrefix = static fn(string $value): string => 'Factory/' . $value;

        $classmap = array_map($addFactoryPrefix, $this->factoryGenerator->getClassmap());

        $classmap[$this->namespace . '\\GeneratedInjector'] = 'GeneratedInjector.php';

        $this->autoloadGenerator->generate($classmap);
    }

    /**
     * Returns the namespace this generator uses
     */
    public function getNamespace(): string
    {
        return $this->namespace;
    }

    /**
     * Generate the injector
     *
     * This will generate the injector and its factories into the output directory
     *
     * @param class-string[] $classes
     */
    public function generate($classes = []): void
    {
        $this->ensureOutputDirectory();
        $this->factoryGenerator->setOutputDirectory($this->outputDirectory . '/Factory');
        $this->autoloadGenerator->setOutputDirectory($this->outputDirectory);
        $factories = [];

        foreach ($classes as $class) {
            $this->generateTypeFactory($class, $factories);
        }

        foreach ($this->config->getConfiguredTypeNames() as $type) {
            $this->generateTypeFactory($type, $factories);
        }

        $this->generateAutoload();
        $this->generateInjector();
        $this->generateFactoryList($factories);
    }
}
