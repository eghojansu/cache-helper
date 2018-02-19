<?php declare(strict_types=1);

/**
 * This file is part of the eghojansu/cache-helper library.
 *
 * (c) Eko Kurniawan <ekokurniawanbs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fal\Cache\Driver;

class FileCache implements CacheInterface
{
    /**
     * Cache dir
     *
     * @var string
     */
    protected $dir;

    public function __construct(string $dir)
    {
        $this->dir = $dir;

        if (!file_exists($dir)) {
            @mkdir($dir, 0755, true);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function name(): string
    {
        return 'filecache';
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $key): string
    {
        return (string) @file_get_contents($this->dir . $key);
    }

    /**
     * {@inheritdoc}
     */
    public function set(string $key, string $value, int $ttl = 0): CacheInterface
    {
        file_put_contents($this->dir . str_replace(['/', '\\'], '', $key), $value);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function clear(string $key): bool
    {
        return @unlink($this->dir . $key);
    }

    /**
     * {@inheritdoc}
     */
    public function reset(string $prefix = '', string $suffix = ''): bool
    {
        foreach ((@glob($this->dir . $prefix . '*' . $suffix) ?: []) as $file) {
            @unlink($file);
        }

        return true;
    }
}
