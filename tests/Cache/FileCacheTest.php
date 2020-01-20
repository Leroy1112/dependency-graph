<?php

declare(strict_types=1);

namespace Nusje2000\DependencyGraph\Tests\Cache;

use Nusje2000\DependencyGraph\Cache\FileCache;
use Nusje2000\DependencyGraph\Dependency\Dependency;
use Nusje2000\DependencyGraph\Dependency\DependencyCollection;
use Nusje2000\DependencyGraph\Dependency\DependencyTypeEnum;
use Nusje2000\DependencyGraph\DependencyGraph;
use Nusje2000\DependencyGraph\Exception\CacheException;
use Nusje2000\DependencyGraph\Package\Package;
use Nusje2000\DependencyGraph\Package\PackageCollection;
use PHPUnit\Framework\TestCase;

final class FileCacheTest extends TestCase
{
    public function testSave(): void
    {
        $graph = new DependencyGraph($this->getRootPath(), new PackageCollection([
            new Package('foo/foo-package', '/path/to/package', false, new DependencyCollection([
                new Dependency('bar/bar-package', 'some-version', false, new DependencyTypeEnum(DependencyTypeEnum::PACKAGE)),
            ])),
            new Package('bar/bar-package', '/path/to/package', false),
        ]));

        $cache = new FileCache();
        self::assertFalse($cache->exists($this->getRootPath()));
        $cache->save($graph);
        self::assertTrue($cache->exists($this->getRootPath()));
    }

    /**
     * @depends testSave
     */
    public function testExists(): void
    {
        $cache = new FileCache();
        self::assertTrue($cache->exists($this->getRootPath()));
    }

    /**
     * @depends testExists
     */
    public function testLoad(): void
    {
        $cache = new FileCache();
        $loadedGraph = $cache->load($this->getRootPath());

        $packages = $loadedGraph->getPackages();
        self::assertTrue($packages->hasPackageByName('bar/bar-package'));
        self::assertTrue($packages->hasPackageByName('foo/foo-package'));
    }

    /**
     * @depends testLoad
     */
    public function testRemove(): void
    {
        $cache = new FileCache();
        self::assertTrue($cache->exists($this->getRootPath()));
        $cache->remove($this->getRootPath());
        self::assertFalse($cache->exists($this->getRootPath()));
    }

    public function testLoadWithoutCache(): void
    {
        $cache = new FileCache();
        self::assertFalse($cache->exists($this->getRootPath()));

        $this->expectException(CacheException::class);
        $cache->load($this->getRootPath());
    }

    private function getRootPath(): string
    {
        return (string)realpath(__DIR__ . '/../../example-structure');
    }
}
