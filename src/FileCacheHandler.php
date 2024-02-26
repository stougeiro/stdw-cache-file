<?php declare(strict_types=1);

    namespace STDW\Cache\File;

    use STDW\Contract\Cache\CacheHandlerInterface;
    use STDW\Cache\File\Exception\FileCacheException;


    class FileCacheHandler implements CacheHandlerInterface
    {
        protected $storage;

        protected $file_extension = '.cache';


        public function __construct(string $storage)
        {
            if ( ! is_dir($storage) || ! file_exists($storage)) {
                throw FileCacheException::storageNotFound($storage);
            }

            $this->storage = $storage;
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

            return (bool) file_put_contents($this->storage . $this->hash($key).'-'.$ttl.$this->file_extension, base64_encode( serialize($value)));
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
            $files = glob($this->storage . '*'.$this->file_extension);
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
            $file = glob($this->storage . $this->hash($key).'-*'.$this->file_extension);

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