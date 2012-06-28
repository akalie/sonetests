<?php
    Package::Load( "Base.VFS");
    
    class VfsHelper {
 
        /**
         * Form VFS File
         *
         * @param string   $controlName
         * @return string
         */
        public static function FormVfsFile( $controlName, $controlId, $file, $previewType = "none" ) {
        	return self::renderVfsFile( $controlName, $controlId, $file, $previewType, false );
        }
        
        public static function FormVfsMultiFile( $controlName, $controlId, $file, $previewType = "none" ) {
        	return self::renderVfsFile( $controlName, $controlId, $file, $previewType, true );
        }
        
        private static function renderVfsFile( $controlName, $controlId, $file, $previewType = "none", $selfDelete = false ) {
        	if ( !empty($selfDelete) ) {
        		$inputClass = "vfsMultiFile";
        	} else $inputClass = "vfsFile";
            if ( empty( $file ) ) {
                $xhtml = sprintf( '<input type="hidden" class="%s" name="%s" id="%s" vfs:previewType="%s" />'
                    , $inputClass
                    , $controlName
                    , $controlId
                    , $previewType
                );
            } else if ( is_object( $file ) ) {
                $xhtml = sprintf( '<input type="hidden" class="%s" name="%s" id="%s" vfs:previewType="%s" value="%s" vfs:src="%s" vfs:name="%s" />'
                    , $inputClass
                    , $controlName
                    , $controlId
                    , $previewType
                    , $file->fileId
                    , Site::GetWebPath( "vfs://" . $file->path )
                    , FormHelper::RenderToForm( $file->title )
                );
            }
            
            return $xhtml;
        }
        
        /**
         * Form VFS Folder
         *
         * @param string     $controlName
         * @param string     $controlId
         * @param VfsFolder  $folder
         * @return string
         */
        public static function FormVfsFolder( $controlName, $controlId, $folder) {
            if ( empty( $folder ) ) {
                $xhtml = sprintf( '<input type="hidden" class="vfsFolder" name="%s" id="%s" />'
                    , $controlName
                    , $controlId
                );
            } else if ( is_object( $folder ) ) {
                $xhtml = sprintf( '<input type="hidden" class="vfsFolder" name="%s" id="%s" value="%s" vfs:name="%s" />'
                    , $controlName
                    , $controlId
                    , $folder->folderId
                    , FormHelper::RenderToForm( $folder->title )
                );
            }
            
            return $xhtml;
        }
    }
?>