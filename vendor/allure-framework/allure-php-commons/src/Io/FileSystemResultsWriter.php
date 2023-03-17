<?php

namespace Qameta\Allure\Io;

use JsonException;
use Psr\Log\LoggerInterface;
use Qameta\Allure\Internal\LoggerAwareTrait;
use Qameta\Allure\Io\Exception\DirectoryNotCreatedException;
use Qameta\Allure\Io\Exception\DirectoryNotResolvedException;
use Qameta\Allure\Io\Exception\InvalidDirectoryException;
use Qameta\Allure\Io\Exception\IoFailedException;
use Qameta\Allure\Io\Exception\StreamCopyFailedException;
use Qameta\Allure\Io\Exception\StreamOpenFailedException;
use Qameta\Allure\Model\AttachmentResult;
use Qameta\Allure\Model\ContainerResult;
use Qameta\Allure\Model\TestResult;
use Throwable;

use function error_clear_last;
use function error_get_last;
use function fclose;
use function file_exists;
use function fopen;
use function is_dir;
use function mkdir;
use function realpath;
use function rtrim;
use function stream_copy_to_stream;

use const DIRECTORY_SEPARATOR;

/**
 * Class FileSystemResultsWriter
 * @package Qameta\Qameta\Allure
 */
class FileSystemResultsWriter implements ResultsWriterInterface
{
    use LoggerAwareTrait;

    private const FILE_EXTENSION = '.json';

    private const ATTACHMENT_FILE_POSTFIX = '-attachment';

    private const RESULT_FILE_POSTFIX = '-result' . self::FILE_EXTENSION;

    private const CONTAINER_FILE_POSTFIX = '-container' . self::FILE_EXTENSION;

    private string $outputDirectory;

    public function __construct(string $outputDirectory, LoggerInterface $logger)
    {
        $this->outputDirectory = rtrim($outputDirectory, '\\/');
        $this->logger = $logger;
    }

    /**
     * @throws JsonException
     */
    public function writeTest(TestResult $test): void
    {
        $this->write(
            $test->getUuid() . self::RESULT_FILE_POSTFIX,
            DataSourceFactory::fromSerializable($test),
        );
    }

    /**
     * @throws JsonException
     */
    public function writeContainer(ContainerResult $container): void
    {
        $this->write(
            $container->getUuid() . self::CONTAINER_FILE_POSTFIX,
            DataSourceFactory::fromSerializable($container),
        );
    }

    public function writeAttachment(AttachmentResult $attachment, DataSourceInterface $data): void
    {
        $source = $this->getAttachmentSource($attachment);
        $attachment->setSource($source);
        $this->write($source, $data);
    }

    public function removeAttachment(AttachmentResult $attachment): void
    {
        $source = $attachment->getSource();
        if (isset($source)) {
            $this->remove($source);
        }
    }

    public function removeTest(TestResult $test): void
    {
        $this->remove(
            $test->getUuid() . self::RESULT_FILE_POSTFIX,
        );
    }

    private function write(string $target, DataSourceInterface $source): void
    {
        $sourceStream = $source->createStream();
        try {
            if ($this->shouldCreateOutputDirectory()) {
                $this->createOutputDirectory();
            }
            $file = $this->getRealOutputDirectory() . DIRECTORY_SEPARATOR . $target;
            $targetStream = $this->createTargetStream($file);
            try {
                $this->copyStream($sourceStream, $targetStream);
            } finally {
                error_clear_last();
                $closeResult = @fclose($targetStream);
                if (!$closeResult) {
                    $this->logLastError('Target stream not closed', error_get_last());
                }
            }
        } finally {
            error_clear_last();
            $closeResult = @fclose($sourceStream);
            if (!$closeResult) {
                $this->logLastError('Source stream not closed', error_get_last());
            }
        }
    }

    private function remove(string $target): void
    {
        try {
            $file = $this->getRealOutputDirectory() . DIRECTORY_SEPARATOR . $target;
            if (!file_exists($file)) {
                return;
            }
            error_clear_last();
            $unlinkResult = @unlink($file);
            if (!$unlinkResult) {
                $this->logLastError('File not removed from output directory', error_get_last());
            }
        } catch (Throwable $e) {
            $this->logException('File {file} not removed', $e, ['file' => $target]);
        }
    }

    private function getAttachmentSource(AttachmentResult $attachment): string
    {
        $source = $attachment->getUuid() . self::ATTACHMENT_FILE_POSTFIX;
        $fileExtension = $attachment->getFileExtension();
        if (isset($fileExtension) && '' != $fileExtension) {
            $source .= '.' == $fileExtension[0] ? $fileExtension : '.' . $fileExtension;
        }

        return $source;
    }

    /**
     * @param resource $sourceStream
     * @param resource $targetStream
     * @throws StreamCopyFailedException
     */
    private function copyStream($sourceStream, $targetStream): void
    {
        error_clear_last();
        $result = @stream_copy_to_stream($sourceStream, $targetStream);
        if (false === $result) {
            $error = error_get_last();
            throw new StreamCopyFailedException($error['message'] ?? null);
        }
    }

    /**
     * @param string $file
     * @return resource
     * @throws StreamOpenFailedException
     */
    private function createTargetStream(string $file)
    {
        error_clear_last();
        $targetStream = @fopen($file, 'w+b');
        if (false === $targetStream) {
            $error = error_get_last();
            throw new StreamOpenFailedException($file, $error['message'] ?? null);
        }

        return $targetStream;
    }

    private function getRealOutputDirectory(): string
    {
        error_clear_last();
        $realDirectory = @realpath($this->outputDirectory);
        if (false === $realDirectory) {
            $error = error_get_last();
            throw new DirectoryNotResolvedException(
                $this->outputDirectory,
                $error['message'] ?? null,
            );
        }

        return $realDirectory;
    }

    private function shouldCreateOutputDirectory(): bool
    {
        error_clear_last();
        $dirExists = @file_exists($this->outputDirectory);
        $error = error_get_last();
        if (isset($error)) {
            throw new IoFailedException($error['message'] ?? null);
        }
        if (!$dirExists) {
            return true;
        }

        error_clear_last();
        $isDir = @is_dir($this->outputDirectory);
        $error = error_get_last();
        if (isset($error)) {
            throw new IoFailedException($error['message'] ?? null);
        }
        if ($isDir) {
            return false;
        }

        throw new InvalidDirectoryException($this->outputDirectory);
    }

    private function createOutputDirectory(): void
    {
        error_clear_last();
        $isCreated = @mkdir($this->outputDirectory, 0777, true);
        if (!$isCreated) {
            $error = error_get_last();
            if (!$this->shouldCreateOutputDirectory()) {
                // Output directory was successfully created by another thread.
                return;
            }
            throw new DirectoryNotCreatedException(
                $this->outputDirectory,
                $error['message'] ?? null,
            );
        }
    }
}
