<?php declare(strict_types=1);

    namespace STDW\Cache\File\ValueObject;


    final class Storage
    {
        public static function create(string $path): object
        {
            return new self($path);
        }


        public function __construct(
            protected string $path)
        {
            $this->path = $path;
        }


        public function get(): string
        {
            return $this->path;
        }

        public function isValid(): bool
        {
            if (
                   ! file_exists($this->path)
                || ! is_dir($this->path)
                || ! is_writable($this->path))
            {
                return false;
            }

            return true;
        }
    }
