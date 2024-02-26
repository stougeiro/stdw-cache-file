<?php declare(strict_types=1);

    namespace STDW\Cache\File\Exception;

    use Exception;


    class FileCacheException extends Exception
    {
        public static function storageNotFound(string $storage): self
        {
            return new self("Caching: Storage path '".$storage."' not found.");
        }
    }
