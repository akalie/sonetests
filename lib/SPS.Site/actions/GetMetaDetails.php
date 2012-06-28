<?php
    class GetMetaDetails {

        /**
         * Execute GetMetaDetails
         */
        public function Execute() {
            $conn = ConnectionFactory::Get();

            if( $conn instanceof PgSqlConnection ) {
                $url = PgSqlConvert::ToString( LocaleLoader::TryFromUTF8( Page::$RequestData[0] ));
                $sql = <<<eof
                AND ( ("url" = {$url} AND "isInheritable" = false )
                    OR ( position( "url" in {$url} ) = 1 AND "isInheritable" = true )
                )
                ORDER BY length(url) DESC
eof
                ;
            }

            if( $conn instanceof MySqlConnection ) {
                $url = MySqlConvert::ToString( LocaleLoader::TryFromUTF8( Page::$RequestData[0] ));
                $sql = <<<eof
                AND ( (`url` = {$url} AND `isInheritable` = false )
                    OR ( position( `url` in {$url} ) = 1 AND `isInheritable` = true )
                )
                ORDER BY length(url) DESC
eof
                ;
            }

            $metaDetails = MetaDetailFactory::Get( array("pageSize"=>1), array( BaseFactory::CustomSql => $sql ) );
            foreach( $metaDetails as $metaDetail ) {
                Response::setParameter( "__metaDetail", $metaDetail );
                return;
            }
        }
    }
?>