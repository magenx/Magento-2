<?php

declare(strict_types=1);

namespace Qameta\Allure\Test\Legacy\Support;

use PHPUnit\Framework\TestCase;
use Qameta\Allure\Allure;
use Qameta\Allure\AllureLifecycleInterface;
use Qameta\Allure\Io\DataSourceInterface;
use Qameta\Allure\Model\AttachmentResult;
use Qameta\Allure\Model\ResultFactoryInterface;
use Qameta\Allure\Setup\LifecycleBuilderInterface;
use Yandex\Allure\Adapter\Support\AttachmentSupport;

use function fclose;
use function fread;

/**
 * @covers \Yandex\Allure\Adapter\Support\AttachmentSupport
 */
class AttachmentSupportTest extends TestCase
{
    public function setUp(): void
    {
        Allure::reset();
    }

    public function testAddAttachment_ResultFactoryProvidesAttachment_LifecycleAddsSameAttachment(): void
    {
        $attachment = new AttachmentResult('a');
        $lifecycle = $this->createMock(AllureLifecycleInterface::class);
        Allure::setLifecycleBuilder(
            $this->createLifecycleBuilder(
                $this->createResultFactoryWithAttachment($attachment),
                $lifecycle,
            ),
        );

        /**
         * @psalm-suppress DeprecatedTrait
         */
        $object = new class () {
            use AttachmentSupport;
        };
        $lifecycle
            ->expects(self::once())
            ->method('addAttachment')
            ->with(self::identicalTo($attachment), self::isInstanceOf(DataSourceInterface::class));
        $object->addAttachment('b', 'c');
    }

    public function testAddAttachment_GivenCaption_AttachmentHasSameName(): void
    {
        $attachment = new AttachmentResult('a');
        $lifecycle = $this->createMock(AllureLifecycleInterface::class);
        Allure::setLifecycleBuilder(
            $this->createLifecycleBuilder(
                $this->createResultFactoryWithAttachment($attachment),
                $lifecycle,
            ),
        );

        /**
         * @psalm-suppress DeprecatedTrait
         */
        $object = new class () {
            use AttachmentSupport;
        };
        $object->addAttachment('b', 'c');
        self::assertSame('c', $attachment->getName());
    }

    public function testAddAttachment_NoTypeGiven_AttachmentHasNullType(): void
    {
        $attachment = new AttachmentResult('a');
        $lifecycle = $this->createMock(AllureLifecycleInterface::class);
        Allure::setLifecycleBuilder(
            $this->createLifecycleBuilder(
                $this->createResultFactoryWithAttachment($attachment),
                $lifecycle,
            ),
        );

        /**
         * @psalm-suppress DeprecatedTrait
         */
        $object = new class () {
            use AttachmentSupport;
        };
        $object->addAttachment('b', 'c');
        self::assertNull($attachment->getType());
    }

    public function testAddAttachment_GivenType_AttachmentHasSameType(): void
    {
        $attachment = new AttachmentResult('a');
        $lifecycle = $this->createMock(AllureLifecycleInterface::class);
        Allure::setLifecycleBuilder(
            $this->createLifecycleBuilder(
                $this->createResultFactoryWithAttachment($attachment),
                $lifecycle,
            ),
        );

        /**
         * @psalm-suppress DeprecatedTrait
         */
        $object = new class () {
            use AttachmentSupport;
        };
        $object->addAttachment('b', 'c', 'd');
        self::assertSame('d', $attachment->getType());
    }

    public function testAddAttachment_GivenFile_FileContentPassedToLifecycle(): void
    {
        $attachment = new AttachmentResult('a');
        $lifecycle = $this->createMock(AllureLifecycleInterface::class);
        Allure::setLifecycleBuilder(
            $this->createLifecycleBuilder(
                $this->createResultFactoryWithAttachment($attachment),
                $lifecycle,
            ),
        );

        /**
         * @psalm-suppress DeprecatedTrait
         */
        $object = new class () {
            use AttachmentSupport;
        };
        $lifecycle
            ->expects(self::once())
            ->method('addAttachment')
            ->with(
                self::anything(),
                self::callback(
                    function (DataSourceInterface $dataSource): bool {
                        $resource = $dataSource->createStream();
                        try {
                            $data = fread($resource, 5);
                        } finally {
                            fclose($resource);
                        }

                        return '<?php' === $data;
                    }
                ),
            );

        $object->addAttachment(__FILE__, 'b');
    }

    public function testAddAttachment_GivenString_StringContentPassedToLifecycle(): void
    {
        $attachment = new AttachmentResult('a');
        $lifecycle = $this->createMock(AllureLifecycleInterface::class);
        Allure::setLifecycleBuilder(
            $this->createLifecycleBuilder(
                $this->createResultFactoryWithAttachment($attachment),
                $lifecycle,
            ),
        );

        /**
         * @psalm-suppress DeprecatedTrait
         */
        $object = new class () {
            use AttachmentSupport;
        };
        $lifecycle
            ->expects(self::once())
            ->method('addAttachment')
            ->with(
                self::anything(),
                self::callback(
                    function (DataSourceInterface $dataSource): bool {
                        $resource = $dataSource->createStream();
                        try {
                            $data = fread($resource, 4);
                        } finally {
                            fclose($resource);
                        }

                        return 'bcde' === $data;
                    }
                ),
            );

        $object->addAttachment('bcde', 'f');
    }

    private function createLifecycleBuilder(
        ?ResultFactoryInterface $resultFactory = null,
        ?AllureLifecycleInterface $lifecycle = null,
    ): LifecycleBuilderInterface {
        $builder = $this->createStub(LifecycleBuilderInterface::class);
        if (isset($resultFactory)) {
            $builder
                ->method('getResultFactory')
                ->willReturn($resultFactory);
        }
        if (isset($lifecycle)) {
            $builder
                ->method('createLifecycle')
                ->willReturn($lifecycle);
        }

        return $builder;
    }

    private function createResultFactoryWithAttachment(AttachmentResult $attachment): ResultFactoryInterface
    {
        $resultFactory = $this->createStub(ResultFactoryInterface::class);
        $resultFactory
            ->method('createAttachment')
            ->willReturn($attachment);

        return $resultFactory;
    }
}
