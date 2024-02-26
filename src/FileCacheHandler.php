<?php declare(strict_types=1);

    namespace STDW\Cache\File;

    use STDW\Contract\Cache\CacheHandlerInterface;
    use STDW\Cache\File\Exception\FileCacheException;
    use STDW\Cache\File\ValueObject\FileExtension;
    use STDW\Cache\File\ValueObject\Storage;


    class FileCacheHandler implements CacheHandlerInterface
    {
        protected Storage $storage;

        protected FileExtension $file_extension;


        public function __construct(string $storage, string $file_extension = '.cache')
        {
            $this->storage = Storage::create($storage);
            $this->file_extension = FileExtension::create($file_extension);

            if ( ! $this->storage->isValid()) {
                throw FileCacheException::storageNotFound($this->storage);
            }

            if ( ! $this->file_extension->isValid()) {
                throw FileCacheException::fileExtensionNotValid($this->file_extension);
            }
        }


        public function has(string $key): bool
        {
            return $this->isValid($key);
        }

        public function get(string $key, mixed $default = null): mixed
        {
            if ( ! $this->has($key)) {
                return $default;
            }

            return unserialize( base64_decode( file_get_contents( $this->getFile($key))));
        }

        public function getMultiple(iterable $keys, mixed $default = null): iterable
        {
            $collection = [];

            foreach ($keys as $key) {
                $collection[$key] = $this->get($key, $default);
            }

            return $collection;
        }

        public function set(string $key, mixed $value, null|int|\DateInterval $ttl = null): bool
        {
            $this->delete($key);

            return (bool) file_put_contents($this->storage->get() . $this->hash($key) .'-'. $ttl . $this->file_extension->get(), base64_encode( serialize($value)));
        }

        public function setMultiple(iterable $values, null|int|\DateInterval $ttl = null): bool
        {
            foreach ($values as $key => $value) {
                $this->set($key, $value, $ttl);
            }

            return true;
        }

        public function delete(string $key): bool
        {
            $file = $this->getfile($key);

            if (file_exists($file)) {
                unlink($file);
                clearstatcache();

                return true;
            }

            return false;
        }

        public function deleteMultiple(iterable $keys): bool
        {
            foreach ($keys as $key) {
                $this->delete($key);
            }

            return true;
        }

        public function clear(): bool
        {
            $files = glob($this->storage->get() . '*'.$this->file_extension->get());
            $c = 0;

            foreach ($files as $file)  {
                if (file_exists($file)) {
                    unlink($file); $c++;
                }
            }

            clearstatcache();

            return (bool) $c;
        }


        protected function hash(string $key): string
        {
            return hash('haval160,4', $key);
        }

        protected function getfile(string $key): string
        {
            $file = glob($this->storage->get() . $this->hash($key).'-*'.$this->file_extension->get());

            if (count($file)) {
                return $file[0];
            }

            return '';
        }

        protected function isValid($key): bool
        {
            $file = $this->getFile($key);

            if ( ! file_exists($file)) {
                return false;
            }


            $filename = pathinfo($file, PATHINFO_FILENAME);

            list( , $ttl) = explode('-', $filename);

            if ((time() - filemtime($file)) > $ttl) {
                $this->delete($key);

                return false;
            }

            return true;
        }
    }