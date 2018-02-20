<?php declare(strict_types=1);

/**
 * This file is part of the eghojansu/cache-helper library.
 *
 * (c) Eko Kurniawan <ekokurniawanbs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fal\Cache;

use Fal\Cache\Driver\CacheInterface;

class Cache
{
    /** @var string */
    protected $dsn;

    /** @var CacheInterface */
    protected $driver;

    /** @var string */
    protected $prefix;

    /** @var string */
    protected $dir;

    /** @var string */
    public $serializer;

    /**
     * Class constructor
     *
     * @param string $dsn
     * @param string $prefix
     * @param string $dir
     * @param string $serializer
     */
    public function __construct(string $dsn, string $prefix, string $dir, string $serializer = '')
    {
        $this->dir = $dir;
        $this->serializer = '' === $serializer ? (extension_loaded('igbinary') ? 'igbinary' : 'php') : $serializer;
        $this->setPrefix($prefix);
        $this->setDsn($dsn);
    }

    /**
     * Get item
     *
     * @param  string $key
     * @return array
     */
    public function get(string $key): array
    {
        $raw = $this->getDriver()->get($this->prefix . '.' . $key);

        if ($raw) {
            list($val, $time, $ttl) = (array) $this->unserialize($raw);

            if (0 === $ttl || $time+$ttl > microtime(true)) {
                return [$val, $time, $ttl];
            }

            $this->clear($key);
        }

        return [];
    }

    /**
     * Set item
     *
     * @param string $key
     * @param mixed $value
     * @param int $ttl
     * @return Cache
     */
    public function set(string $key, $value, int $ttl = 0): Cache
    {
        $time = microtime(true);
        $cached = $this->get($key);
        if ($cached) {
            list($old_value, $time, $ttl) = $cached;
        }

        $data = $this->serialize([$value, $time, $ttl]);

        $this->getDriver()->set($this->prefix . '.' . $key, $data, $ttl);

        return $this;
    }

    /**
     * Remove item
     *
     * @param  string $key
     * @return bool
     */
    public function clear(string $key): bool
    {
        return $this->getDriver()->clear($this->prefix . '.' . $key);
    }

    /**
     * Remove all item
     *
     * @param  string $suffix
     * @return bool
     */
    public function reset(string $suffix = ''): bool
    {
        return $this->getDriver()->reset($this->prefix . '.', $suffix);
    }

    /**
     * Get prefix
     *
     * @return string
     */
    public function getPrefix(): string
    {
        return $this->prefix;
    }

    /**
     * Set prefix
     *
     * @param string $prefix
     * @return Cache
     */
    public function setPrefix(string $prefix): Cache
    {
        if ($prefix) {
            $this->prefix = $prefix;
        }

        return $this;
    }

    /**
     * Get dsn
     *
     * @return string
     */
    public function getDsn(): string
    {
        return $this->dsn;
    }

    /**
     * Set dsn
     *
     * @param string $dsn
     * @return $this
     */
    public function setDsn(string $dsn): Cache
    {
        $this->dsn = trim($dsn);
        $this->cache = null;

        return $this;
    }

    /**
     * Get cache
     *
     * @return CacheInterface
     */
    public function getDriver(): ?CacheInterface
    {
        if (!$this->driver) {
            $this->load();
        }

        return $this->driver;
    }

    /**
     * Load cache by dsn
     *
     * @return void
     */
    protected function load(): void
    {
        $parts = array_map('trim', explode('=', $this->dsn) + [1 => '']);
        $auto = '/^(apc|apcu|wincache|xcache)/';

        if (!$this->dsn) {
            $this->driver = new Driver\NoCache();
        } elseif ('redis' === $parts[0] && $parts[1] && extension_loaded('redis')) {
            list($host, $port, $db) = explode(':', $parts[1]) + [1=>0, 2=>null];

            $this->driver = new Driver\Redis($host, $db, (int) $port);
        } elseif ('memcached' === $parts[0] && $parts[1] && extension_loaded('memcached')) {
            $servers = explode(';', $parts[1]);

            $this->driver = new Driver\Memcached(...$servers);
        } elseif ('folder' === $parts[0] && $parts[1]) {
            $this->driver = new Driver\FileCache($parts[1]);
        } elseif (preg_match($auto, $this->dsn, $parts)) {
            $class = __NAMESPACE__ . '\\Driver\\' . $parts[1];

            $this->driver = new $class();
        } elseif ('auto' === strtolower($this->dsn) && $grep = preg_grep($auto, array_map('strtolower', get_loaded_extensions()))) {
            // Auto-detect
            $class = __NAMESPACE__ . '\\Driver\\' . ucfirst(current($grep));

            $this->driver = new $class();
        } else {
            // Fallback to filesystem cache
            $this->driver = new Driver\FileCache($this->dir);
        }
    }

    /**
     * Return string representation of PHP value
     *
     * @param mixed $arg
     * @return string
     *
     * @codeCoverageIgnore
     */
    protected function serialize($arg): string
    {
        switch ($this->serializer) {
            case 'igbinary':
                return igbinary_serialize($arg);
            default:
                return serialize($arg);
        }
    }

    /**
     * Return PHP value derived from string
     *
     * @param mixed $arg
     * @return mixed
     *
     * @codeCoverageIgnore
     */
    protected function unserialize($arg)
    {
        switch ($this->serializer) {
            case 'igbinary':
                return igbinary_unserialize($arg);
            default:
                return unserialize($arg);
        }
    }
}
