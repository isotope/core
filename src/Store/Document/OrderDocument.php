<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Store\Document;

use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\String\UnicodeString;

class OrderDocument
{
    /**
     * @param string|resource $contents
     */
    public function __construct(
        private readonly string $documentNumber,
        private readonly \DateTimeInterface $createdAt,
        private readonly string $filename,
        private readonly string $contentType,
        private $contents,
    ) {
    }

    public function getDocumentNumber(): string
    {
        return $this->documentNumber;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getFilename(): string
    {
        return $this->filename;
    }

    public function getContentType(): string
    {
        return $this->contentType;
    }

    /**
     * @return string|resource
     */
    public function getContents()
    {
        return $this->contents;
    }

    public function getResponse(): Response
    {
        $contents = $this->getContents();

        if (\is_resource($contents)) {
            $response = new StreamedResponse(
                static function () use ($contents): void {
                    fseek($contents, 0);

                    while (!feof($contents)) {
                        echo fread($contents, 8192);
                        flush();
                    }
                    fclose($contents);
                },
            );
        } else {
            $response = new Response($contents);
        }

        $response->headers->set('Content-Type', $this->getContentType());
        $response->headers->set('Content-Disposition', HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_INLINE,
            $this->getFilename(),
            (new UnicodeString($this->getFilename()))->ascii()->toString(),
        ));

        return $response;
    }
}
