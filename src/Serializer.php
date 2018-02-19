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

class Serializer implements SerializerInterface
{
    /** @var string */
    protected $engine;

    /**
     * Class constructor
     *
     * @param string $engine
     */
    public function __construct(string $engine = '')
    {
        $this->setEngine($engine ?: (extension_loaded('igbinary') ? 'igbinary' : 'php'));
    }

    /**
     * Get engine
     *
     * @return string
     */
    public function getEngine(): string
    {
        return $this->engine;
    }

    /**
     * Set engine
     *
     * @param string $engine
     * @return $this
     */
    public function setEngine(string $engine): Serializer
    {
        $this->engine = $engine;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize($arg): string
    {
        switch ($this->engine) {
            case 'igbinary':
                return igbinary_serialize($arg);
            default:
                return serialize($arg);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($arg)
    {
        switch ($this->engine) {
            case 'igbinary':
                return igbinary_unserialize($arg);
            default:
                return unserialize($arg);
        }
    }
}
