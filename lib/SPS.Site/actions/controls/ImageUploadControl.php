<?php
    Package::Load( 'SPS.Site' );

    /**
     * Handle file uploads via XMLHttpRequest
     */
    class qqUploadedFileXhr {
        /**
         * Save the file to the specified path
         * @return boolean TRUE on success
         */
        function save($path) {
            $input = fopen("php://input", "r");
            $temp = tmpfile();
            $realSize = stream_copy_to_stream($input, $temp);
            fclose($input);

            if ($realSize != $this->getSize()){
                return false;
            }

            $target = fopen($path, "w");
            fseek($temp, 0, SEEK_SET);
            stream_copy_to_stream($temp, $target);
            fclose($target);

            return true;
        }
        function getName() {
            return $_GET['qqfile'];
        }
        function getSize() {
            if (isset($_SERVER["CONTENT_LENGTH"])){
                return (int)$_SERVER["CONTENT_LENGTH"];
            } else {
                throw new Exception('Getting content length is not supported.');
            }
        }
    }

    /**
     * Handle file uploads via regular form post (uses the $_FILES array)
     */
    class qqUploadedFileForm {
        /**
         * Save the file to the specified path
         * @return boolean TRUE on success
         */
        function save($path) {
            if(!move_uploaded_file($_FILES['qqfile']['tmp_name'], $path)){
                return false;
            }
            return true;
        }
        function getName() {
            return $_FILES['qqfile']['name'];
        }
        function getSize() {
            return $_FILES['qqfile']['size'];
        }
    }

    /**
     * ImageUploadControl Action
     * @package    SPS
     * @subpackage Site
     * @author     Shuler
     */
    class ImageUploadControl {

        private $file;

        /**
         * Entry Point
         */
        public function Execute() {
            if (isset($_GET['qqfile'])) {
                $this->file = new qqUploadedFileXhr();
            } elseif (isset($_FILES['qqfile'])) {
                $this->file = new qqUploadedFileForm();
            } else {
                $this->uploadByFlash();
                return;
            }

            $result = $this->uploadByHTML();
            echo htmlspecialchars(json_encode($result), ENT_NOQUOTES);
        }

        private function uploadByHTML() {
            if (!$this->file){
                return array('error' => 'No files were uploaded.');
            }

            $pathinfo = pathinfo($this->file->getName());
            $filename = md5(uniqid()) . '.jpg';
            $ext = $pathinfo['extension'];

            $tmpPath = Site::GetRealPath('temp://' . $filename);
            if ($this->file->save($tmpPath)){
                $file = array(
                    'tmp_name'  => $tmpPath,
                    'name'      => $tmpPath,
                );
                $result = MediaUtility::SaveTempFile( $file, 'Article', 'photos' );
                if( !empty( $result['filename'] ) ) {
                    MediaUtility::MoveObjectFilesFromTemp( 'Article', 'photos', array($result['filename']) );
                    $result['image'] = MediaUtility::GetFilePath( 'Article', 'photos', 'small', $result['filename'], MediaServerManager::$MainLocation );
                    $result['success'] = true;
                } else if( !empty( $result['error'] ) ) {
                    $result['error'] = LocaleLoader::Translate('errors.files.' . $result['error']);
                }

                unlink($tmpPath);

                return $result;
            } else {
                return array('error'=> 'Could not save uploaded file.' .
                    'The upload was cancelled, or server error encountered');
            }
        }

        private function uploadByFlash() {
            $result = MediaUtility::SaveTempFile( !empty($_FILES['Filedata']) ? $_FILES['Filedata'] : null, 'Article', 'photos' );
            if( !empty( $result['filename'] ) ) {
                MediaUtility::MoveObjectFilesFromTemp( 'Article', 'photos', array($result['filename']) );
                $result['path'] = MediaUtility::GetFilePath( 'Article', 'photos', 'small', $result['filename'], MediaServerManager::$MainLocation );
            } else if( !empty( $result['error'] ) ) {
                $result['error'] = LocaleLoader::Translate('errors.files.' . $result['error']);
            }

            echo ObjectHelper::ToJSON( $result );
        }
    }
?>