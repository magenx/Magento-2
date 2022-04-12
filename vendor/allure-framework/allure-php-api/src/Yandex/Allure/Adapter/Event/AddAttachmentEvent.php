<?php

namespace Yandex\Allure\Adapter\Event;

use Symfony\Component\Mime\MimeTypes;
use Yandex\Allure\Adapter\AllureException;
use Yandex\Allure\Adapter\Model\Attachment;
use Yandex\Allure\Adapter\Model\Entity;
use Yandex\Allure\Adapter\Model\Provider;
use Yandex\Allure\Adapter\Model\Step;
use Yandex\Allure\Adapter\Support\Utils;

const DEFAULT_FILE_EXTENSION = 'txt';
const DEFAULT_MIME_TYPE = 'text/plain';

class AddAttachmentEvent implements StepEvent
{
    private $filePathOrContents;

    private $caption;

    private $type;

    public function __construct($filePathOrContents, $caption, $type = null)
    {
        $this->filePathOrContents = $filePathOrContents;
        $this->caption = $caption;
        $this->type = $type;
    }

    public function process(Entity $context)
    {
        if ($context instanceof Step) {
            $newFileName = $this->getAttachmentFileName($this->filePathOrContents, $this->type);
            $attachment = new Attachment($this->caption, $newFileName, $this->type);
            $context->addAttachment($attachment);
        }
    }

    public function getAttachmentFileName($filePathOrContents, $type)
    {
        $filePath = $filePathOrContents;
        if (!file_exists($filePath) || !is_file($filePath)) {
            //Save contents to temporary file
            $filePath = tempnam(sys_get_temp_dir(), 'allure-attachment');
            if (!file_put_contents($filePath, $filePathOrContents)) {
                throw new AllureException("Failed to save attachment contents to $filePath");
            }
        }

        if (!isset($type)) {
            $type = $this->guessFileMimeType($filePath);
            $this->type = $type;
        }

        $fileExtension = $this->guessFileExtension($type);

        $fileSha1 = sha1_file($filePath);
        $outputPath = $this->getOutputPath($fileSha1, $fileExtension);
        if (!copy($filePath, $outputPath)) {
            throw new AllureException("Failed to copy attachment from $filePath to $outputPath.");
        }

        return $this->getOutputFileName($fileSha1, $fileExtension);
    }

    private function guessFileMimeType($filePath)
    {
        return MimeTypes::getDefault()->guessMimeType($filePath) ?? DEFAULT_MIME_TYPE;
    }

    private function guessFileExtension($mimeType)
    {
        $extensions = MimeTypes::getDefault()->getExtensions($mimeType);

        return $extensions[0] ?? DEFAULT_FILE_EXTENSION;
    }

    public function getOutputFileName($sha1, $extension)
    {
        return $sha1 . '-attachment.' . $extension;
    }

    public function getOutputPath($sha1, $extension)
    {
        return Provider::getOutputDirectory() . DIRECTORY_SEPARATOR . $this->getOutputFileName($sha1, $extension);
    }

    /**
     * @return mixed
     */
    public function getCaption()
    {
        return $this->caption;
    }

    /**
     * @return mixed
     */
    public function getFilePathOrContents()
    {
        return $this->filePathOrContents;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }
}
