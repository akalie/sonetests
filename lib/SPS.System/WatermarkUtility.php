<?php
    /**
     * WatermarkUtility
     * @author Shuler
     * @package H4U.System
     */
    class WatermarkUtility {

        /**
         * path to default watermark image
         * @var string
         */
        public static $WatermarkPath = 'images://fe/watermark.png';

        /**
         * quality of gif and jpeg compression
         * @var int
         */
        public static $Quality = 95;

        /**
         * Place watermark on image
         * @static
         * @param $file full path for source image
         * @param $watermarkPath full real path of user watermark
         * @return bool
         */
        public static function PlaceWatermark( $file, $watermarkPath = null ) {
            $save = false;

            list($imageX, $imageY, $type) = getimagesize($file);
            $image                        = self::Load( $file, $type );
            $waterMarkFile                = !empty( $watermarkPath ) ? $watermarkPath : Site::GetRealPath( self::$WatermarkPath );
            list($sx, $sy)                = getimagesize($waterMarkFile);
            $waterMark                    = self::Load( $waterMarkFile, IMAGETYPE_PNG );

            $marginRight  = 0;
            $marginBottom = 0;

            imagecopy(
                $image,
                $waterMark,
                $imageX - $sx - $marginRight,
                $imageY - $sy - $marginBottom,
                0,
                0,
                $sx,
                $sy
            );

            switch ($type) {
                case IMAGETYPE_GIF:
                    $save = imagegif($image, $file, self::$Quality);
                    break;
                case IMAGETYPE_JPEG:
                    $save = imagejpeg($image, $file, self::$Quality);
                    break;
                case IMAGETYPE_PNG:
                    $save = imagepng($image, $file, 8);
                    break;
            }

            imagedestroy($image);
            imagedestroy($waterMark);

            return $save;
        }

        public static function GenerateWatermark( $text ) {
            //creating temp file
            if( !is_dir( Site::GetRealPath( 'temp://watermarks/' ) ) ) {
                mkdir( Site::GetRealPath( 'temp://watermarks/' ) );
            }
            $tmpFilename = Site::GetRealPath( 'temp://watermarks/' . md5( $text ) . time() . '.png' );

            // font
            $font = Site::GetRealPath( 'shared://fonts/arial.ttf' );

            //dimensions
            $bbox 	= imagettfbbox ( 20, 0, $font, $text );
            $width 	= $bbox[2] - $bbox[0] + 5;
            $height = - $bbox[5] - $bbox[3] + 15;

            // Create the image
            $im = imagecreatetruecolor($width, $height);

            //saving all full alpha channel information
            imagesavealpha($im, true);

            //setting completely transparent color
            $transparent = imagecolorallocatealpha($im, 0, 0, 0, 127);

            //filling created image with transparent color
            imagefill($im, 0, 0, $transparent);

            // Create some colors
            $white = imagecolorallocate($im, 255, 255, 255);
            $black = imagecolorallocate($im, 0, 0, 0);

            // Add some shadow to the text
            $x = 1;
            $y = $height - 8;
            imagettftext($im, 20, 0, $x + 1, $y + 1, $black, $font, $text);

            // Add the text
            imagettftext($im, 20, 0, $x, $y, $white, $font, $text);

            // Using imagepng() results in clearer text compared with imagejpeg()
            imagepng( $im, $tmpFilename );
            imagedestroy($im);

            return $tmpFilename;
        }

        /**
         * Load file by format
         *
         * @static
         * @param string $file
         * @param int $type
         * @return resource
         */
        public static function Load( $file , $type = IMAGETYPE_JPEG ) {
            $result = null;

            if ( $type == IMAGETYPE_GIF ) {
                $result = imagecreatefromgif($file);
            } else
            if ( $type == IMAGETYPE_JPEG ) {
                $result = imagecreatefromjpeg($file);
            } else
            if ( $type == IMAGETYPE_PNG ) {
                $result = imagecreatefrompng($file);
            }

            return $result;
        }
    }
?>