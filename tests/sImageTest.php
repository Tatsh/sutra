<?php
require './includes/global.inc';

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

  /**
   * Ugly hacks.
   */
  public function testEdgeCases() {
    if (!extension_loaded('runkit') || !defined('RUNKIT_ACC_STATIC')) {
      $this->markTestSkipped('Runkit not loaded or RUNKIT_ACC_STATIC undefined. Cannot test.');
    }

    $new_class_exists =<<<'CODE'
      if ($ext == "imagick") {
        return FALSE;
      }
      return el_original($ext);
CODE;

    runkit_function_copy('extension_loaded', 'el_original');
    runkit_function_redefine('extension_loaded', '$ext', $new_class_exists);
    $renamed = self::$image->flip(sImage::FLIP_HORIZONTAL);
    $this->assertNotEquals(self::$image->getName(), $renamed->getName());
    $renamed->delete();
    runkit_function_remove('extension_loaded');
    runkit_function_rename('el_original', 'extension_loaded');

    $function_exists =<<<'CODE'
      if ($func == 'exif_read_data') {
        return FALSE;
      }
      return fe_original2($func);
CODE;

    runkit_function_copy('function_exists', 'fe_original2');
    runkit_function_redefine('function_exists', '$func', $function_exists);
    try {
      $image = new sImage('./resources/rotate-this-no-exif.jpg');
      $image->rotateAccordingToEXIFData();
      $this->assertTrue(FALSE);
    }
    catch (fEnvironmentException $e) {
      $this->assertTrue(TRUE);
    }
    runkit_function_remove('function_exists');
    runkit_function_rename('fe_original2', 'function_exists');

    runkit_method_copy('fImage', 'dp_original', 'fImage', 'determineProcessor');
    runkit_method_redefine('fImage', 'determineProcessor', '', 'return "none";', RUNKIT_ACC_STATIC);
    try {
      $image = new sImage('./resources/rotate-this-no-exif.jpg');
      $ren = $image->flip(sImage::FLIP_HORIZONTAL);
      $ren->delete();
      $this->assertTrue(FALSE);
    }
    catch (fEnvironmentException $e) {
      $this->assertEquals('No image processor was found.', $e->getMessage());
    }
    runkit_method_remove('fImage', 'determineProcessor');
    runkit_method_rename('fImage', 'dp_original', 'determineProcessor');

    runkit_function_copy('imagejpeg', 'ijpeg_original');
    runkit_function_redefine('imagejpeg', '', 'return FALSE;');
    try {
      $image = new sImage('./resources/rotate-this-no-exif.jpg');
      $ren = $image->flip(sImage::FLIP_HORIZONTAL, FALSE, 'gd');
      $ren->delete();
      $this->assertTrue(FALSE);
    }
    catch (fUnexpectedException $e) {
      $this->assertEquals('Unexpected error while using GD.', $e->getMessage());
    }
    runkit_function_remove('imagejpeg');
    runkit_function_rename('ijpeg_original', 'imagejpeg');
  }

  /**
   * @depends testEdgeCases
   */
  public function testFlipImagickNoOverwrite() {
    if (!extension_loaded('imagick')) {
      $this->markTestSkipped('Imagick extension is not installed or loaded.');
      return;
    }

    $renamed = self::$image->flip(sImage::FLIP_HORIZONTAL);
    $this->assertNotEquals(self::$image->getName(), $renamed->getName());
    $this->assertNotEquals(self::$image->read(), $renamed->read());
    $renamed->delete();
  }

  /**
   * @depends testEdgeCases
   */
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

  /**
   * @depends testEdgeCases
   */
  public function testFlipGDNoOverwrite() {
    if (!function_exists('gd_info')) {
      $this->markTestSkipped('GD extension is not installed or loaded.');
    }

    $renamed = self::$image->flip(sImage::FLIP_HORIZONTAL, 90, FALSE, 'gd');
    $this->assertNotEquals(self::$image->getName(), $renamed->getName());
    $this->assertNotEquals(self::$image->read(), $renamed->read());
    $renamed->delete();
  }

  /**
   * @depends testEdgeCases
   */
  public function testFlipGDUnuspportedMimeType() {
    // Still will at least perform the rename
    $image = new sImage('./resources/flip-this.tiff');
    $ren = $image->flip(sImage::FLIP_HORIZONTAL, FALSE, 'gd');
    $this->assertNotEquals($ren->getName(), $image->getName());
    $ren->delete();
  }

  /**
   * @depends testEdgeCases
   */
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
  /**
   * @depends testEdgeCases
   * @todo Doesn't pass yet, but works in normal code.
   */
  public function testFlipGIF() {
    $image = new sImage(self::IMAGE_FILE_NO_EXIF_GIF);
    $new = $image->flip(sImage::FLIP_HORIZONTAL, FALSE, 'gd');
    $this->assertNotEquals($new->getName(), $image->getName());
    $this->assertNotEquals(md5_file($new->getPath()), md5_file($image->getPath()));
    $new->delete();
  }

  /**
   * @depends testEdgeCases
   */
  public function testFlipJPEG() {
    $image = new sImage(self::IMAGE_FILE_NO_EXIF_JPG);
    $new = $image->flip(sImage::FLIP_HORIZONTAL, 90, FALSE, 'gd');
    $this->assertNotEquals($new->read(), $image->read());
    $this->assertNotEquals($new->getName(), $image->getName());
    $new->delete();
  }

  /**
   * @depends testEdgeCases
   */
  public function testFlipNone() {
    $file = self::$image->flip(sImage::FLIP_NONE);
    $this->assertEquals($file->read(), self::$image->read());
  }

  /**
   * @depends testEdgeCases
   */
  public function testRotateWithEXIFNoEXIF() {
    $image = new sImage('./resources/rotate-this-no-exif.jpg');
    $file = $image->rotateAccordingToEXIFData(sImage::DIRECTION_UPSIDE_UP);
    $this->assertNotEquals($image->getName(), $file->getName());
    $this->assertEquals($image->read(), $file->read());
  }

  /**
   * @depends testEdgeCases
   */
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

  /**
   * @depends testEdgeCases
   */
  public function testRotateWithEXIFCase3() {
    $image = new sImage('./resources/rotate-this-exif-case-3.jpg');
    $file = $image->rotateAccordingToEXIFData(sImage::DIRECTION_UPSIDE_DOWN);
    $this->assertNotEquals($image->getName(), $file->getName());
    $this->assertNotEquals($image->read(), $file->read());
  }

  /**
   * @depends testEdgeCases
   */
  public function testRotateWithEXIFCase6() {
    $image = new sImage('./resources/rotate-this-exif-case-6.jpg');
    $file = $image->rotateAccordingToEXIFData(sImage::DIRECTION_UPSIDE_UP);
    $this->assertNotEquals($image->getName(), $file->getName());
    $this->assertNotEquals($image->read(), $file->read());
    $this->assertNotEquals($image->getHeight(), $file->getHeight());
  }

  /**
   * @depends testEdgeCases
   */
  public function testRotateWithEXIFCase8() {
    $image = new sImage('./resources/rotate-this-exif-case-8.jpg');
    $file = $image->rotateAccordingToEXIFData(sImage::DIRECTION_UPSIDE_LEFT);
    $this->assertNotEquals($image->getName(), $file->getName());
    $this->assertNotEquals($image->read(), $file->read());
    $this->assertEquals($image->getHeight(), $file->getHeight());
  }
}
