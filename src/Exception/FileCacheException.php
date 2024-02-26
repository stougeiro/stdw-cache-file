<?php declare(strict_types=1);

    namespace STDW\Cache\File\Exception;

    use STDW\Cache\File\ValueObject\FileExtension;
    use STDW\Cache\File\ValueObject\Storage;
    use Exception;


    class FileCacheException extends Exception
    {
        public static function storageNotFound(Storage $storage): object
        {
            return new static("Storage path '".$storage->get()."' not found. Storage path must be a valid writable folder.");
        }

        public static function fileExtensionNotValid(FileExtension $file_extension): object
        {
            return new static("Invalid file extension: '".$file_extension->get()."'. File extension must be alphanumeric string, preceded by a single dot.");
        }
    }