<?php

namespace MaplePHP\Http;


use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use RuntimeException;

class StreamFactory implements StreamFactoryInterface
{
    public function createStream(string $content = ''): StreamInterface
    {
        $stream = new Stream(Stream::TEMP);
        if ($content !== '') {
            $stream->write($content);
        }
        return $stream;
    }

    public function createStreamFromFile(string $filename, string $mode = 'r'): StreamInterface
    {
        $stream = new Stream($filename, $mode);
        // PSR-17: MUST throw RuntimeException if the file cannot be opened.
        if (!is_resource($stream->getResource())) {
            throw new RuntimeException(sprintf('Unable to open file "%s" with mode "%s".', $filename, $mode));
        }
        return $stream;
    }

    public function createStreamFromResource($resource): StreamInterface
    {
        // PSR-17: MUST throw InvalidArgumentException if $resource is not a resource.
        if (!is_resource($resource)) {
            throw new \InvalidArgumentException('StreamFactory::createStreamFromResource() expects a resource.');
        }
        return new Stream($resource);
    }

}
