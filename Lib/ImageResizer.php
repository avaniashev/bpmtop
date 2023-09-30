<?php
App::uses('Watermark', 'Lib');
class ImageResizer
{
public $name = 'Image';
	private $__errors = array();

    /**
     * Determines image type, calculates scaled image size, and returns resized image. If no width or height is
     * specified for the new image, the dimensions of the original image will be used, resulting in a copy
     * of the original image.
     *
     * @param string $original absolute path to original image file
     * @param string $new_filename absolute path to new image file to be created
     * @param integer $new_width (optional) width to scale new image (default 0)
     * @param integer $new_height (optional) height to scale image (default 0)
     * @param integer $quality quality of new image (default 100, resizePng will recalculate this value)
     *
     * @access public
     *
     * @return returns new image on success, false on failure. use ImageComponent::getErrors() to get an array
     * of errors on failure
     */
    public function resize($original, $new_filename, $new_width = 0, $new_height = 0, $quality = 100, $crop = false) {
        if (!is_file($original)) {
            $this->__errors[] = "Image file missing: $original";
            return false;
        }

        if(!($image_params = getimagesize($original))) {
            $this->__errors[] = 'Original file is not a valid image: ' . $original;
            return false;
        }
        $width = $image_params[0];
        $height = $image_params[1];

        if(0 != $new_width && 0 == $new_height) {
            $scaled_width = $new_width;
            $scaled_height = floor($new_width * $height / $width);
        } elseif(0 != $new_height && 0 == $new_width) {
            $scaled_height = $new_height;
            $scaled_width = floor($new_height * $width / $height);
        } elseif(0 == $new_width && 0 == $new_height) { //assume we want to create a new image the same exact size
            $scaled_width = $width;
            $scaled_height = $height;
        } elseif($new_width < 0 && $new_height < 0) {
        	// Cropping image
        	$scaled_width = -$new_width;
        	$scaled_height = -$new_height;
            $crop = true;
            //echo "" . $new_width . ' ' . $new_height;
        } else { //assume we want to create an image with these exact dimensions, most likely resulting in distortion
            $scaled_width = $new_width;
            $scaled_height = $new_height;
        }

        //create image
        $ext = $image_params[2];
        switch($ext) {
            case IMAGETYPE_GIF:
                $return = $this->__resizeGif($original, $new_filename, $scaled_width, $scaled_height, $width, $height, $quality, $crop);
                break;
            case IMAGETYPE_JPEG:
                $return = $this->__resizeJpeg($original, $new_filename, $scaled_width, $scaled_height, $width, $height, $quality, $crop);
                break;
            case IMAGETYPE_PNG:
                $return = $this->__resizePng($original, $new_filename, $scaled_width, $scaled_height, $width, $height, $quality, $crop);
                break;
            default:
                $return = $this->__resizeJpeg($original, $new_filename, $scaled_width, $scaled_height, $width, $height, $quality, $crop);
                break;
        }

        return $return;
    }

    public function getErrors() {
        return $this->__errors;
    }

    private function copyToTmp($src, $scaled_width, $scaled_height, $width, $height, $alpha = false, $crop = false)
    {
    	$srcX = 0;
    	$srcY = 0;
    	$srcWidth = $width;
    	$srcHeight = $height;
    	if ($crop)
    	{
    		$resultRatio = $scaled_width / $scaled_height;
    		$srcHeight = floor($width / $resultRatio) - 2;
    		if ($srcHeight > $height)
    		{
    			$srcHeight = $height;
    			$srcWidth = $height * $resultRatio;
    		}
    		$srcY = floor(($height - $srcHeight) / 2);
    		$srcX = floor(($width - $srcWidth) / 2);
    	}
    	$tmp = false;
    	if(!($tmp = imagecreatetruecolor($scaled_width, $scaled_height))) {
            $this->__errors[] = 'There was an error creating your true color image.';
            $tmp = false;
        }
        if ($alpha)
        	imagealphablending($tmp, false);
        if(!imagecopyresampled($tmp, $src, 0, 0, $srcX, $srcY, $scaled_width, $scaled_height, $srcWidth, $srcHeight)) {
            $this->__errors[] = 'There was an error creating your true color image.';
            $tmp = false;
        }
//        $backgroundColor = imagecolorallocate($tmp, 255, 255, 255);
//        imagefill($tmp, 0, 0, $backgroundColor);
        if ($alpha)
        	imagesavealpha($tmp, true);
        return $tmp;
    }

    private function __resizeGif($original, $new_filename, $scaled_width, $scaled_height, $width, $height, $crop = false) {
        $error = false;

        if(!($src = imagecreatefromgif($original))) {
            $this->__errors[] = 'There was an error creating your resized image (gif).';
            $error = true;
        }

        if (!($tmp = $this->copyToTmp($src, $scaled_width, $scaled_height, $width, $height, false, $crop)))
        {
        	$error = true;
        }

        if(!($new_image = imagegif($tmp, $new_filename))) {
            $this->__errors[] = 'There was an error writing your image to file (gif).';
            $error = true;
        }

        imagedestroy($tmp);

        if(false == $error) {
            return $new_image;
        }

        return false;
    }

    private function __resizeJpeg($original, $new_filename, $scaled_width, $scaled_height, $width, $height, $quality, $crop = false) {
        $error = false;

        if(!($src = imagecreatefromjpeg($original))) {
            $this->__errors[] = 'There was an error creating your resized image (jpg).';
            $error = true;
        }

    	if (!($tmp = $this->copyToTmp($src, $scaled_width, $scaled_height, $width, $height, false, $crop)))
        {
        	$error = true;
        }

        if(!($new_image = imagejpeg($tmp, $new_filename, $quality))) {
            $this->__errors[] = 'There was an error writing your image to file (jpg).';
            $error = true;
        }

        imagedestroy($tmp);

        if(false == $error) {
            return $new_image;
        }

        return false;
    }

    private function __resizePng($original, $new_filename, $scaled_width, $scaled_height, $width, $height, $quality, $crop = false) {
        $error = false;
        /**
         * we need to recalculate the quality for imagepng()
         * the quality parameter in imagepng() is actually the compression level,
         * so the higher the value (0-9), the lower the quality. this is pretty much
         * the opposite of how imagejpeg() works.
         */
        $quality = ceil($quality / 10); // 0 - 100 value
        if(0 == $quality) {
            $quality = 9;
        } else {
            $quality = ($quality - 1) % 9;
        }


        if(!($src = imagecreatefrompng($original))) {
            $this->__errors[] = 'There was an error creating your resized image (png).';
            $error = true;
        }

    	if (!($tmp = $this->copyToTmp($src, $scaled_width, $scaled_height, $width, $height, true, $crop)))
        {
        	$error = true;
        }

        if(!($new_image = imagepng($tmp, $new_filename, $quality))) {
            $this->__errors[] = 'There was an error writing your image to file (png).';
            $error = true;
        }

        imagedestroy($tmp);

        if(false == $error) {
            return $new_image;
        }

        return false;
    }

	public function insertWatermark($path, $path_to_wm) {
		$watermark = new Watermark();
		$ext = substr($path, strrpos($path, '.') + 1);
		switch($ext) {
			case 'jpg':
			case 'jpeg':
				$main_img_obj = imagecreatefromjpeg(WWW_ROOT.$path);
				break;
			case 'gif':
				$main_img_obj = imagecreatefromgif(WWW_ROOT.$path);
				break;
			case 'png':
				$main_img_obj = imagecreatefrompng(WWW_ROOT.$path);
				break;
			default:
				return;
		}
		$wm = $path_to_wm;
		if (is_array($wm))
		{
			$width = imagesx($main_img_obj);
			$watermarkIndex = 0;
			if ($width > 1000 && $width < 2000)
			{
				$watermarkIndex = 1;
			}
			else if ($width >= 2000)
			{
				$watermarkIndex = 2;
			}
			$count = count($wm);
			if ($count > $watermarkIndex)
			{
				$wm = $wm[$watermarkIndex];
			}
			else
			{
				$wm = $wm[$count - 1];
			}
		}
		$watermark_img_obj = imagecreatefrompng($wm);
		$return_img_obj = $watermark->create_watermark($main_img_obj, $watermark_img_obj, 66);
		switch($ext) {
			case 'jpg':
			case 'jpeg':
				imagejpeg($return_img_obj, WWW_ROOT.$path, 90);
				break;
			case 'gif':
				imagegif($return_img_obj, WWW_ROOT.$path);
				break;
			case 'png':
				imagepng($return_img_obj, WWW_ROOT.$path);
				break;
			default:
				return;
		}
	}
}
