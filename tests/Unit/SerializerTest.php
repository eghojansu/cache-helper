<?php declare(strict_types=1);

/**
 * This file is part of the eghojansu/cache-helper library.
 *
 * (c) Eko Kurniawan <ekokurniawanbs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fal\Cache\Test\Unit;

use Fal\Cache\Serializer;
use PHPUnit\Framework\TestCase;

class SerializerTest extends TestCase
{
    private $serializer;

    public function setUp()
    {
        $this->serializer = new Serializer('php');
    }

    public function testGetEngine()
    {
        $expected = 'php';
        $result = $this->serializer->getEngine();
        $this->assertEquals($expected, $result);
    }

    public function testSetEngine()
    {
        $expected = $this->serializer;
        $engine = 'foo';
        $result = $this->serializer->setEngine($engine);
        $this->assertEquals($expected, $result);
    }

    public function testSerialize()
    {
        $arg = ['foo'=>'bar'];
        $expected = serialize($arg);
        $result = $this->serializer->serialize($arg);
        $this->assertEquals($expected, $result);

        if (extension_loaded('igbinary')) {
            $expected = igbinary_serialize($arg);
            $this->serializer->setEngine('igbinary');
            $result = $this->serializer->serialize($arg);

            $this->assertEquals($expected, $result);
        }
    }

    public function testUnserialize()
    {
        $expected = ['foo'=>'bar'];
        $arg = serialize($expected);
        $result = $this->serializer->unserialize($arg);
        $this->assertEquals($expected, $result);

        if (extension_loaded('igbinary')) {
            $arg = igbinary_serialize($expected);
            $this->serializer->setEngine('igbinary');
            $result = $this->serializer->unserialize($arg);

            $this->assertEquals($expected, $result);
        }
    }
}
