<?php declare(strict_types=1);

    namespace STDW\Cache\File\ValueObject;


    final class FileExtension
    {
        public static function create(string $file_extension): object
        {
            return new static($file_extension);
        }


        public function __construct(
            protected string $file_extension)
        {
            $this->file_extension = $file_extension;
        }


        public function get(): string
        {
            return $this->file_extension;
        }

        public function isValid(): bool
        {
            if ( ! preg_match('^\.[a-zA-Z0-9]{1,}$', $this->file_extension)) {
                return false;
            }

            return true;
        }
    }