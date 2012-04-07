<?php
require 'global.inc';

class sImageTest extends PHPUnit_Framework_TestCase {
  const IMAGE_FILE_NO_EXIF_PNG = './resources/flip-this.png';
  const IMAGE_FILE_NO_EXIF_GIF = './resources/flip-this.gif';
  const IMAGE_FILE_NO_EXIF_JPG = './resources/flip-this.jpg';

  /**
   * @var sImage
   */
  private static $image = NULL;

  public static function setUpBeforeClass() {
    self::$image = new sImage(self::IMAGE_FILE_NO_EXIF_PNG);
  }

  public function testFlipImagickNoOverwrite() {
    if (!extension_loaded('imagick')) {
      $this->markTestSkipped('Imagick extension is not installed or loaded.');
    }

    $renamed = self::$image->flip(sImage::FLIP_HORIZONTAL);
    $this->assertNotEquals(self::$image->getName(), $renamed->getName());
    $this->assertNotEquals(self::$image->read(), $renamed->read());
    $renamed->delete();
  }

  public function testFlipImagick() {
    if (!extension_loaded('imagick')) {
      $this->markTestSkipped('Imagick extension is not installed or loaded.');
    }

    $new = clone self::$image;
    $new->rename(self::$image->getName(), FALSE);
    $old_data = $new->read();
    $new->flip(sImage::FLIP_VERTICAL, TRUE, 'imagemagick');
    $this->assertNotEquals($new->read(), $old_data);
    $new->delete();

    $new = clone self::$image;
    $new->rename(self::$image->getName(), FALSE);
    $old_data = $new->read();
    $new->flip(sImage::FLIP_HORIZONTAL, 90, TRUE, 'imagemagick');
    $this->assertNotEquals($new->read(), $old_data);
    $new->delete();

    $new = clone self::$image;
    $new->rename(self::$image->getName(), FALSE);
    $old_data = $new->read();
    $new->flip(sImage::FLIP_BOTH, 90, TRUE, 'imagemagick');
    $this->assertNotEquals($new->read(), $old_data);
    $new->delete();
  }

  public function testFlipGDNoOverwrite() {
    if (!function_exists('gd_info')) {
      $this->markTestSkipped('GD extension is not installed or loaded.');
    }

    $renamed = self::$image->flip(sImage::FLIP_HORIZONTAL, 90, FALSE, 'gd');
    $this->assertNotEquals(self::$image->getName(), $renamed->getName());
    $this->assertNotEquals(self::$image->read(), $renamed->read());
    $renamed->delete();
  }

  public function testFlipGD() {
    if (!function_exists('gd_info')) {
      $this->markTestSkipped('GD extension is not installed or loaded.');
    }

    $new = clone self::$image;
    $new->rename(self::$image->getName(), FALSE);
    $old_data = $new->read();
    $new->flip(sImage::FLIP_VERTICAL, TRUE, 'gd');
    $this->assertNotEquals($new->read(), $old_data);
    $new->delete();

    $new = clone self::$image;
    $new->rename(self::$image->getName(), FALSE);
    $old_data = $new->read();
    $new->flip(sImage::FLIP_HORIZONTAL, 90, TRUE, 'gd');
    $this->assertNotEquals($new->read(), $old_data);
    $new->delete();

    $new = clone self::$image;
    $new->rename(self::$image->getName(), FALSE);
    $old_data = $new->read();
    $new->flip(sImage::FLIP_BOTH, 90, TRUE, 'gd');
    $this->assertNotEquals($new->read(), $old_data);
    $new->delete();
  }

  // For code coverage purposes
  public function testFlipGIF() {
    $image = new sImage(self::IMAGE_FILE_NO_EXIF_GIF);
    $new = $image->flip(sImage::FLIP_HORIZONTAL, FALSE, 'gd');
    $this->assertNotEquals($new->read(), $image->read());
    $this->assertNotEquals($new->getName(), $image->getName());
    $new->delete();
  }

  public function testFlipJPEG() {
    $image = new sImage(self::IMAGE_FILE_NO_EXIF_JPG);
    $new = $image->flip(sImage::FLIP_HORIZONTAL, 90, FALSE, 'gd');
    $this->assertNotEquals($new->read(), $image->read());
    $this->assertNotEquals($new->getName(), $image->getName());
    $new->delete();
  }

  public function testFlipNone() {
    $file = self::$image->flip(sImage::FLIP_NONE);
    $this->assertEquals($file->read(), self::$image->read());
  }

  public function testRotateWithEXIFNoEXIF() {
    $image = new sImage('./resources/rotate-this-no-exif.jpg');
    $file = $image->rotateAccordingToEXIFData(sImage::DIRECTION_UPSIDE_UP);
    $this->assertNotEquals($image->getName(), $file->getName());
    $this->assertEquals($image->read(), $file->read());
  }

  public function testRotateWithEXIF() {
    // Requires no modification but should be a new file
    $image = new sImage('./resources/rotate-this-exif-case-default.jpg');
    $file = $image->rotateAccordingToEXIFData(sImage::DIRECTION_UPSIDE_UP);
    $this->assertNotEquals($image->getName(), $file->getName());
    $this->assertEquals($image->read(), $file->read());

    $image = new sImage('./resources/rotate-this-no-exif.jpg');
    $file = $image->rotateAccordingToEXIFData(sImage::DIRECTION_UPSIDE_UP, TRUE);
    $this->assertEquals($image, $file);
    $this->assertEquals($image->read(), $file->read());

    $image = clone self::$image;
    $file = $image->rotateAccordingToEXIFData(sImage::DIRECTION_UPSIDE_UP);
    $this->assertNotEquals($image->getName(), $file->getName());
    $this->assertEquals($image->read(), $file->read());

    $image = clone self::$image;
    $file = $image->rotateAccordingToEXIFData(sImage::DIRECTION_UPSIDE_UP, TRUE);
    $this->assertEquals($image, $file);
    $this->assertEquals($image->read(), $file->read());

    // Original is mirrored horizontally
    $image = new sImage('./resources/rotate-this-exif-case-2.jpg');
    $file = $image->rotateAccordingToEXIFData(sImage::DIRECTION_UPSIDE_UP);
    $this->assertNotEquals($image->getName(), $file->getName());
    $this->assertNotEquals($image->read(), $file->read());

    // Original is upside-down and mirrored horizontally
    $image = new sImage('./resources/rotate-this-exif-case-4.jpg');
    $file = $image->rotateAccordingToEXIFData(sImage::DIRECTION_UPSIDE_UP);
    $this->assertNotEquals($image->getName(), $file->getName());
    $this->assertNotEquals($image->read(), $file->read());
    $this->assertEquals($image->getHeight(), $file->getHeight());

    // Original is mirrored horizontally and rotated CCW 90
    $image = new sImage('./resources/rotate-this-exif-case-5.jpg');
    $file = $image->rotateAccordingToEXIFData(sImage::DIRECTION_UPSIDE_DOWN);
    $this->assertNotEquals($image->getName(), $file->getName());
    $this->assertNotEquals($image->read(), $file->read());
    $this->assertNotEquals($image->getHeight(), $file->getHeight());

    $image = new sImage('./resources/rotate-this-exif-case-7.jpg');
    $file = $image->rotateAccordingToEXIFData(sImage::DIRECTION_UPSIDE_RIGHT);
    $this->assertNotEquals($image->getName(), $file->getName());
    $this->assertNotEquals($image->read(), $file->read());
    $this->assertEquals($image->getHeight(), $file->getHeight());
  }

  public function testRotateWithEXIFCase3() {
    $image = new sImage('./resources/rotate-this-exif-case-3.jpg');
    $file = $image->rotateAccordingToEXIFData(sImage::DIRECTION_UPSIDE_DOWN);
    $this->assertNotEquals($image->getName(), $file->getName());
    $this->assertNotEquals($image->read(), $file->read());
  }

  public function testRotateWithEXIFCase6() {
    $image = new sImage('./resources/rotate-this-exif-case-6.jpg');
    $file = $image->rotateAccordingToEXIFData(sImage::DIRECTION_UPSIDE_UP);
    $this->assertNotEquals($image->getName(), $file->getName());
    $this->assertNotEquals($image->read(), $file->read());
    $this->assertNotEquals($image->getHeight(), $file->getHeight());
  }

  public function testRotateWithEXIFCase8() {
    $image = new sImage('./resources/rotate-this-exif-case-8.jpg');
    $file = $image->rotateAccordingToEXIFData(sImage::DIRECTION_UPSIDE_LEFT);
    $this->assertNotEquals($image->getName(), $file->getName());
    $this->assertNotEquals($image->read(), $file->read());
    $this->assertEquals($image->getHeight(), $file->getHeight());
  }
}
