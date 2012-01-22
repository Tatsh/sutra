<?php
/**
 * Provides more methods to fImage, most notably rotation based on EXIF data
 *   which may be present in the file.
 *
 * Requires Flourish's fImage::determineProcessor() method to be made at least
 *   protected (at the moment it is private).
 *
 * @copyright Copyright (c) 2011 Poluza.
 * @author Andrew Udvare [au] <andrew@poluza.com>
 * @license http://www.opensource.org/licenses/mit-license.php
 *
 * @package Sutra
 * @link http://www.example.com/
 *
 * @version 1.0
 *
 * @todo Add method to set an image's opacity.
 * @todo Add method to create an image with images as layers.
 */
class sImage extends fImage {
  /**
   * Rotation direction upside-up.
   *
   * @var integer
   */
  const DIRECTION_UPSIDE_UP = 0;

  /**
   * Rotation direction upside-down.
   *
   * @var integer
   */
  const DIRECTION_UPSIDE_DOWN = 1;

  /**
   * Rotation direction upside-left.
   *
   * @var integer
   */
  const DIRECTION_UPSIDE_LEFT = 2;

  /**
   * Rotation direction upside-right.
   *
   * @var integer
   */
  const DIRECTION_UPSIDE_RIGHT = 3;

  /**
   * Flip direction horizontal.
   *
   * @var integer
   */
  const FLIP_HORIZONTAL = 0;

  /**
   * Flip direction vertical.
   *
   * @var integer
   */
  const FLIP_VERTICAL = 1;

  /**
   * Flip direction both ways.
   *
   * @var integer
   */
  const FLIP_BOTH = 2;

  /**
   * No flip.
   *
   * @var integer
   */
  const FLIP_NONE = 3;

  /**
   * Operations queue.
   *
   * @var array
   */
  private $operations_queue = array();

  /**
   * Override saveChanges() so that our queue runs first.
   *
   * @param string $new_image_type The new file format for the image. One of: 'jpg', 'gif', 'png'. Default is NULL (no change).
   * @param integer $jpeg_quality Quality value from 0 to 100 if the image is JPEG. Otherwise ignored. Default is 90.
   * @param boolean $overwrite Overwrite the old image if true. Default is FALSE.
   * @return sImage The image object, to allow for method chaining.
   */
  public function saveChanges($new_image_type = NULL, $jpeg_quality = 90, $overwrite = FALSE) {
    foreach ($this->operations_queue as $method => $args) {
      $args[] = $jpeg_quality;
      $args[] = $overwrite;
      call_user_func_array(array($this, $method), $args);
    }
    $this->operations_queue = array();

    parent::saveChanges($new_image_type, $jpeg_quality, $overwrite);

    return $this;
  }

  /**
   * Flip the image in a specified direction. If PECL Imagick class is
   *   not found, GD will be used.
   *
   * @throws fUnexpectedException If Imagick fails to return data, or if
   *   an ImagickException is thrown.
   * @throws fEnvironmentException If no image processor is found; if the image
   *   type is invalid for GD.
   *
   * Additional notes:
   * - To use ImageMagick (which is much faster), you must install the PECL
   *   Imagick extension.
   * - PJPEG is not supported if GD is used.
   * - This overwrites the data in the file before returning (this is NOT
   *  part of the operation queue).
   *
   * @todo Accept third argument boolean overwrite.
   * @todo Use ImageMagick command line instead of Imagick.
   *
   * @param integer $type One of the FLIP_* constants.
   * @param integer $jpeg_quality Because this saves changes to the file
   *   directly, if the image is JPEG, specify a quality from 0 (worst
   *   quality) to 100 (best quality). Default is 90.
   * @return sImage The image object, to allow method chaining.
   */
  public function flip($type, $jpeg_quality = 90) {
    $this->tossIfDeleted();

    if ($type == self::FLIP_NONE) {
      return $this;
    }

    $processor = self::determineProcessor();
    if ($processor == 'none') {
      throw new fEnvironmentException('No image processor was found.');
    }

    $mime = strtolower($this->getMimeType());
    $supported = self::getCompatibleMimeTypes();

    if (!class_exists('Imagick')) {
      $processor = 'gd';
      $supported = array('image/gif', 'image/jpeg', 'image/png');
    }
    else {
      $processor = 'imagemagick';
    }

    if (!in_array($mime, $supported)) {
      return $this;
    }

    if ($processor == 'imagemagick') {
      fCore::debug(__CLASS__.'->'.__FUNCTION__.': Using Imagick class.');

      $image = new Imagick($this->getPath());
      if ($type == self::FLIP_VERTICAL) {
        $image->flipImage();
      }
      else if ($type == self::FLIP_HORIZONTAL) {
        $image->flopImage();
      }
      else {
        $image->flipImage();
        $image->flopImage();
      }

      try {
        $data = $image->getImageBlob();

        if (!strlen($data)) {
          throw new fUnexpectedException('Imagick->getImageBlob() returned 0 bytes.');
        }

        $this->write($data);
      }
      catch (ImagickException $e) {
        throw new fUnexpectedException('Caught ImagickException: '.$e->getMessage());
      }
    }
    else {
      fCore::debug(__CLASS__.'->'.__FUNCTION__.': Using GD.');

      // GD
      $img_src = NULL;

      switch ($mime) {
        case 'image/gif':
          $img_src = imagecreatefromgif($this->getPath());
          break;

        case 'image/jpeg':
          $img_src = imagecreatefromjpeg($this->getPath());
          break;

        case 'image/png':
          $img_src = imagecreatefrompng($this->getPath());
          imagealphablending($img_src, FALSE);
          imagesavealpha($img_src, TRUE);
          break;

        default:
          throw new fEnvironmentException('GD cannot handle this image type.');
      }

      if (!$img_src) {
        throw new fUnexpectedException('Cannot open file %s.', $this->getName());
      }

      $width = imagesx($img_src);
      $height = imagesy($img_src);
      $img_destination = imagecreatetruecolor($width, $height);

      if ($mime == 'image/png') {
        imagealphablending($img_destination, FALSE);
        imagesavealpha($img_destination, TRUE);
      }

      for ($x = 0; $x < $width; $x++) {
        for ($y = 0; $y < $height; $y++) {
          if ($type == self::FLIP_HORIZONTAL) {
            imagecopy($img_destination, $img_src, $width - $x - 1, $y, $x, $y, 1, 1);
          }
          else if ($type == self::FLIP_VERTICAL) {
            imagecopy($img_destination, $img_src, $x, $height - $y - 1, $x, $y, 1, 1);
          }
          else if ($type == self::FLIP_BOTH) {
            imagecopy($img_destination, $img_src, $width - $x - 1, $height - $y - 1, $x, $y, 1, 1);
          }
        }
      }

      fBuffer::startCapture();

      $ret = FALSE;

      switch ($mime) {
        case 'image/gif':
          $ret = imagegif($img_destination);
          break;

        case 'image/jpeg':
          $ret = imagejpeg($img_destination, $this->getPath(), $jpeg_quality);
          print $this->read();
          break;

        case 'image/png':
          $ret = imagepng($img_destination);
          break;
      }

      if (!$ret) {
        fBuffer::stopCapture();
        throw new fUnexpectedException('Unexpected error while using GD.');
      }

      $data = fBuffer::stopCapture();
      $this->write($data);

      imagedestroy($img_src);
      imagedestroy($img_destination);
    }

    return $this;
  }

  /**
   * Rotate an image a certain way based on EXIF information embedded. Only
   *   JPEG and TIFF images are supported.
   *
   * @param string $direction Optional. One of the DIRECTION_* constant values.
   *   Default is up-side up.
   * @return sImage Object to allow method chaining.
   */
  public function rotateAccordingToEXIFData($direction = self::DIRECTION_UPSIDE_UP) {
    $this->tossIfDeleted();

    $direction = strtolower($direction);
    $mime = strtolower($this->getMimeType());

    if (!in_array($mime, array('image/tiff', 'image/jpeg'))) {
      return $this;
    }

    if (!function_exists('exif_read_data')) {
      throw new fEnvironmentException('EXIF library must be installed to use this method.');
    }

    // First rotate up anyways
    $exif_data = exif_read_data($this->getPath());
    $rotated = FALSE;
    $flip_type = self::FLIP_NONE;

    // NOTE Sometimes stored in other places like $exif_data['IFD0']['Orientation']
    if (!isset($exif_data['Orientation'])) {
      return $this;
    }

    switch ($exif_data['Orientation']) {
      case 2: // Horizontal flip
        $flip_type = self::FLIP_HORIZONTAL;
        break;

      case 3: // 180 CCW
        $this->rotate(180);
        $rotated = TRUE;
        break;

      case 4: // Vertical flip
        $flip_type = self::FLIP_VERTICAL;
        break;

      case 5: // Vertical flip + 90 CCW
        $this->rotate(90);
        $rotated = TRUE;
        $flip_type = self::FLIP_VERTICAL;
        break;

      case 6: // 90 CCW
        $this->rotate(90);
        $rotated = TRUE;
        break;

      case 7: // Horizontal flip + 90 CCW
        $this->rotate(90);
        $rotated = TRUE;
        $flip_type = self::FLIP_HORIZONTAL;
        break;

      case 8: // 90 CW
        $this->rotate(-90);
        $rotated = TRUE;
        break;

      default:
        return $this;
    }

    if ($rotated) {
      if ($direction != self::DIRECTION_UPSIDE_UP) {
        // Now rotate according to the direction specified
        switch ($direction) {
          case self::DIRECTION_UPSIDE_DOWN:
            $this->rotate(180);
            break;

          case self::DIRECTION_UPSIDE_LEFT:
            $this->rotate(-90);
            break;

          case self::DIRECTION_UPSIDE_RIGHT:
            $this->rotate(90);
            break;

          default:
            throw new fProgrammerException('Invalid direction specified for rotation.');
        }
      }
    }

    if ($flip_type != self::FLIP_NONE) {
      $this->operations_queue['flip'] = array($flip_type);
    }

    $this->saveChanges();

    return $this;
  }
}
