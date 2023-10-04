<?php

namespace App\Utils\Files;

final class Reader
{
    /** @var resource */
    private $resource;

    public function __construct(private string $path)
    {
    }

    public function __destruct()
    {
        if (null !== $this->resource) {
            fclose($this->resource);
        }
    }

    public function readChar(): string|false
    {
        $this->initResource();

        return fread($this->resource, 1);
    }

    public function readLine(): string
    {
        $line = '';

        while (
            !$this->feof()
            && ($character = $this->readChar()) !== false
            && PHP_EOL !== $character
        ) {
            $line .= $character;
        }

        return $line;
    }

    /** @retrun string[] */
    public function readLines(int $count): array
    {
        $lines = [];
        while (!$this->feof() && $count > 0) {
            $lines[] = $this->readLine();

            --$count;
        }

        return $lines;
    }

    public function feof(): bool
    {
        return null !== $this->resource && feof($this->resource);
    }

    private function initResource(): void
    {
        if (null === $this->resource) {
            $this->resource = fopen($this->path, 'r');
        }
    }
}
