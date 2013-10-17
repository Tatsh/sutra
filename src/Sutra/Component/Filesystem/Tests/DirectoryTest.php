<?php
namespace Sutra\Component\Filesystem\Tests;

use Sutra\Component\Cryptography\Cryptography;
use Sutra\Component\Filesystem\Directory;
use Sutra\Component\Filesystem\Exception\EnvironmentException;
use Sutra\Component\Filesystem\Exception\ValidationException;
use Sutra\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class DirectoryTest extends TestCase
{
    protected static $popBackTo;
    protected static $fs;
    protected static $dir;
    protected static $dirs = array();
    protected static $clearedDir;

    public static function setUpBeforeClass()
    {
        static::$popBackTo = getcwd();
        static::$fs = new Filesystem();

        $crypto = new Cryptography();
        $workDirPath = static::$popBackTo.'/____tmp____';

        static::$fs->mkdir($workDirPath);
        chdir($workDirPath);

        static::$dir = new Directory($workDirPath);

        // Generate some random directories and files with random levels of depth
        foreach (range(0, 100) as $i) {
            $rand = $crypto->random(0, 1);
            $depths = range(2, $crypto->random(1, 6));
            $type = 'file';

            if ($rand === 0) {
                $type = 'dir';
            }

            $name = $crypto->randomString($crypto->random(5, 20), 'alpha');
            $ext = $crypto->randomString(3, 'alpha');
            $name = static::$fs->makeUniqueName(strtolower($name.($type !== 'dir' ? '.'.$ext : '')));

            // Split the name into chunks
            $names = array_chunk(str_split($name), count($depths));
            foreach ($names as $k => $pieces) {
                $names[$k] = join('', $pieces);
            }

            $filename = &$names[count($names) - 1];

            // If the last piece doesn't have something usable for a filename, then take pieces before it till the name is good
            if ($type === 'file' && !preg_match('/^\w+\.\w{3}$/', $filename)) {
                for ($i = (count($names) - 2), $good = false; $i > 0 && !$good; $i--) {
                    $filename = $names[$i].$filename;
                    $good = (bool) preg_match('/^\w+\.\w{3}$/', $filename);
                }
            }

            $fullPath = $workDirPath.'/'.str_replace('./', '/', join('/', $names));
            $dir = dirname($fullPath);

            if ($dir === $workDirPath) {
                continue;
            }

            static::$fs->mkdir($dir);
            static::$fs->touch($fullPath);

            static::$dirs[] = new Directory($dir, true);
        }

        // Sort directories by how many levels they go descending
        usort(static::$dirs, function ($a, $b) {
            $a = count(explode('/', (string) $a));
            $b = count(explode('/', (string) $b));

            if ($a === $b) {
                return 0;
            }

            return $a > $b ? -1 : 1;
        });
    }

    public function testClear()
    {
        static::$dirs[0]->clear();
        $this->assertEmpty(static::$dirs[0]->scanRecursive());
    }

    public function testDelete()
    {
        $dir = static::$dirs[1];
        $dir->delete();
        $info = static::$fs->getPathInfo((string) $dir);

        $this->assertFalse(file_exists($info['dirname'].$info['basename']));
    }

    /**
     * @expectedException Sutra\Component\Filesystem\Exception\ProgrammerException
     * @expectedExceptionMessage The action requested cannot be performed because the directory has been deleted
     */
    public function testDeletedException()
    {
        $dir = static::$dirs[1];
        $dir->delete();
    }

    public function testDeletedExceptionParentNotWritable()
    {
        $dir = static::$dirs[2];
        $parent = $dir->getParent()->getPath();
        $caught = false;
        chmod($parent, 0550);

        try {
            $dir->delete();
        }
        catch (EnvironmentException $e) {
            $caught = true;
            $this->assertStringMatchesFormat('The directory %s cannot be deleted because the parent directory is not writable', $e->getMessage());
        }

        chmod($parent, 0750);

        if (!$caught) {
            $this->fail('Exception not thrown');
        }
    }

    public function testGetName()
    {
        $dir = static::$dirs[2];
        $this->assertNotRegExp('#[/\\\\]#', $dir->getName());
    }

    public function testGetPath()
    {
        $dir = static::$dirs[3];
        $this->assertRegExp('#[/\\\\]#', $dir->getPath());
    }

    public function testGetSize()
    {
        $dir = static::$dirs[3];
        $size = $dir->getSize();
        $this->assertEquals(0, $size);

        $files = $dir->scanRecursive('files');
        $file = $files[0];
        file_put_contents($file, 'bytes');
        $this->assertEquals(5, $dir->getSize());
        $this->assertEquals('5 B', $dir->getSize(true));

        $h = fopen($file, 'w+');
        for ($i = 0; $i < 4334; $i++) {
            fwrite($h, 'b');
        }
        fclose($h);

        $this->assertEquals(4334, $dir->getSize());
        $this->assertEquals('4.2 KiB', $dir->getSize(true));
        $this->assertEquals('4.23 KiB', $dir->getSize(true, 2));
    }

    public function testIsWritable()
    {
        $dir = static::$dirs[3];
        $this->assertTrue($dir->isWritable());

        $dir = static::$dirs[4];
        chmod($dir, 0550);
        $this->assertFalse($dir->isWritable());
        chmod($dir, 0750);
    }

    public function testMove()
    {
        $newParentDir = (string) static::$dirs[5];
        $dir = static::$dirs[4];
        $oldPath = (string) $dir;

        $dir->move($newParentDir);

        $this->assertNotEquals($oldPath, (string) $dir);
    }

    public function testMoveOverwrite()
    {
        $newParentDir = static::$dirs[6]->getParent();
        $firstDir = $newParentDir->scan('directories');
        $previousContents = $firstDir[0]->scanRecursive();
        $firstDirName = basename($firstDir[0]);

        $toMove = static::$dirs[7];
        $toMove->rename($firstDirName, true);
        $toMove->move($newParentDir, true);

        $newContents = $toMove->scanRecursive();

        $this->assertNotEquals($previousContents, $newContents);
    }

    /**
     * @expectedException Sutra\Component\Filesystem\Exception\ValidationException
     * @expectedExceptionMessage It is not possible to move a directory into one of its sub-directories
     */
    public function testMoveException()
    {
        $parentDir = static::$dirs[8]->getParent()->getParent();
        $subDir = $parentDir->scan('directories');
        $subDir = $subDir[0];
        $parentDir->move($subDir);
    }

    public function testRenameIsWritableException()
    {
        $parent = static::$dirs[9]->getParent();
        $caught = false;
        chmod($parent, 0550);

        try {
            static::$dirs[9]->rename('a');
        }
        catch (EnvironmentException $e) {
            $caught = true;
            $this->assertStringMatchesFormat('The directory, %s, can not be renamed because the directory containing it is not writable', $e->getMessage());
        }

        chmod($parent, 0750);

        if (!$caught) {
            $this->fail('Exception not thrown');
        }
    }

    public function testScan()
    {
        $dir = static::$dirs[10]->getParent();

        $dirs = $dir->scan(array(
            'files' => null,
            'depth' => 200, // ignored silently
        ));
        $this->assertEquals(0, count($dirs));

        $dirs = $dir->scan(function ($f) {
            return !is_dir($f);
        });
        $this->assertEquals(0, count($dirs));

        $dirs = $dir->scan('files');
        $this->assertEquals(0, count($dirs));

        $files = $dir->scan('__rand__.php');
        $this->assertEquals(0, count($files));
    }

    public function testScanRecursive()
    {
        $dir = static::$dirs[10]->getParent();

        $dirs = $dir->scanRecursive(array(
            'files' => null,
            'sort' => function () {}, // ignored silently
        ));
        $this->assertEquals(1, count($dirs));

        $dirs = $dir->scanRecursive(function ($f) {
            return !is_dir($f);
        });
        $this->assertEquals(1, count($dirs));

        $dirs = $dir->scanRecursive('files');
        $this->assertEquals(1, count($dirs));

        $dirs = $dir->scanRecursive('directories');
        $this->assertEquals(1, count($dirs));

        $files = $dir->scanRecursive('__rand__.php');
        $this->assertEquals(0, count($files));
    }

    /**
     * @expectedException Sutra\Component\Filesystem\Exception\ValidationException
     * @expectedExceptionMessage The directory specified, ./non-existant, does not exist or is not readable
     */
    public function testConstructorNoDirectory()
    {
        new Directory('./non-existant');
    }

    public function testConstructorNotADirectory()
    {
        $failed = true;
        static::$fs->touch('existant');

        try {
            new Directory('existant');
        }
        catch (ValidationException $e) {
            $failed = false;
            $this->assertStringMatchesFormat('The directory specified, %s, is not a directory', $e->getMessage());
        }

        unlink('existant');

        if ($failed) {
            $this->fail('Exception not thrown');
        }
    }

    public static function tearDownAfterClass()
    {
        chdir(static::$popBackTo);
        static::$dir->delete();
    }
}
