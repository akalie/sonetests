<?php

    class ChangeSenderException extends Exception{}

    /**
     * SenderVkontakte
     * @package    SPS
     * @subpackage VK
     * @author     Shuler
     */
    class SenderVkontakte {
        protected $post_photo_array;    //массив адресов фоток
        protected $post_text;           //текст поста
        protected $attachments = '';    //аттачи
        protected $vk_access_token;
        protected $vk_group_id;         //id паблика, куда постим
        protected $vk_aplication_id;    //id аппа, с которого постим
        protected $vk_app_seckey;       //
        protected $link;                //ссылка на источник
        protected $sign;                //ссыль на пользователя, пока неактивно
        protected $header;              //заголовок ссылки

        const METH          =   'https://api.vk.com/method/';
        const ANTIGATE_KEY  =   'cae95d19a0b446cafc82e21f5248c945';
        const TEMP_PATH     =   'c:\\wrk\\'; //обязательно полный путь, иначе curl теряется\
        const TESTING       =   false;
        const FALSE_COUNTER =   3; //количество попыток совершить какое-либо действие
        //(например, получение разгаданной капчи)

        public function __construct($post_data)
        {
            $this->post_photo_array = $post_data['photo_array']; //массив вида array('photoXXXX_YYYYYYY','...')
            $this->post_text = $post_data['text'];
            $this->vk_group_id = $post_data['group_id'];
            $this->vk_app_seckey = $post_data['vk_app_seckey'];
            $this->vk_access_token = $post_data['vk_access_token'];
            $this->audio_id = $post_data['audio_id'];//массив вида array('videoXXXX_YYYYYYY','...')
            $this->video_id = $post_data['video_id'];//массив вида array('audioXXXX_YYYYYYY','...')
            $this->link = $post_data['link'];
            $this->header = $post_data['header'];
        }

        private function qurl_request($url, $arr_of_fields, $headers = '', $uagent = '')
        {
            if (empty($url)) {
                return false;
            }

            $ch = curl_init($url);
            print_r($arr_of_fields);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            //        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));

            if (is_array($headers)) { // если заданы какие-то заголовки для браузера
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            }

            if (!empty($uagent)) { // если задан UserAgent
                curl_setopt($ch, CURLOPT_USERAGENT, $uagent);
            } else{
                curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; rv:2.0.1) Gecko/20100101 Firefox/4.0.1)');
            }

            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            if (is_array($arr_of_fields)) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $arr_of_fields);

            } else return false;

            $result = curl_exec($ch);
            if (curl_errno($ch)){
                echo "<br>error in curl: ". curl_error($ch) ."<br>";
                throw new Exception('error in curl: '. curl_error($ch)) ;
            }

            curl_close($ch);
            return $result;
        }

        //возвращаемые значения
        //Удачная отсылка
        //      true - пост со ссылкой
        //      -ХХХ_УУУ - id поста (ХХХ - id паблика, УУУ - поста в этом паблике)
        //Неудачная
        //      исключение 'please change admin'  -  всплыла капча и не удалось ее убить антигейтом, либо слишком много сообщений
        //              от данного издателя. Нужно его поменять в обоих случаях
        //
        //      исключения на все остальное
        public function send_post()
        {
            $try_cntr = 0; #счетчик количества попыток послать запрос
            $attachment = array();

            $fields1 = array(    'gid'           =>  $this->vk_group_id,
                                 'access_token'  =>  $this->vk_access_token);
            if (is_array($this->post_photo_array)){
                foreach($this->post_photo_array as $photo_adr)
                {
                    //первый запрос, получение адреса для заливки фото
                    $url = self::METH . "photos.getWallUploadServer";
                    $fwd = $this->qurl_request($url, $fields1);
                    $tmp = $fwd;

                    $fwd = json_decode($fwd);

                    if (!empty ($fwd->error)){
                        $fwd = $fwd->error;
                        echo '<br>ERROR!<br>';
                        print_r($fwd);
                        throw new exception("Error in photos.getWallUploadServer : $fwd->error_msg");
                    }

                    $fwd = $fwd -> response;

                    sleep(1);
                    $upload_url = $fwd -> upload_url;

                    if(empty($fwd->upload_url)){
                        throw new exception("Smthg wrong in photos.getWallUploadServer : $tmp");
                    }

                    //заливка фото
                    $content = $this->qurl_request($upload_url, array('file1' => '@'.$photo_adr));
                    $content = json_decode($content);
                    if (empty($content->photo)) {
                        throw new exception(" Error uploading photo. Response : $content");
                    }

                    sleep(0.5);
                    //"закрепляем" фотку
                    $url2 = self::METH . "photos.saveWallPhoto";
                    $fields = array(    'gid'           =>  $this->vk_group_id,
                                        'server'        =>  $content->server,
                                        'hash'          =>  $content->hash,
                                        'photo'         =>  $content->photo,
                                        'access_token'  =>  $this->vk_access_token );

                    $fwd2 = $this->qurl_request($url2, $fields);
                    $fwd2 = json_decode($fwd2);
                    if (!empty ($fwd2->error)){
                        $fwd2 = $fwd2->error;
                        echo '<br>ERROR!<br>';
                        //                    print_r($fwd2);
                        throw new exception("Error in photos.saveWallPhoto : $fwd2->error_msg");
                    }

                    $fwd2 = $fwd2->response;
                    $fwd2 = $fwd2[0];
                    $attachment[] = $fwd2->id;
                }
            }
            //только фотки
            $attachment = implode(',', $attachment);

            //другие аттачи
            $other_attachments = '';
            if (!empty($this->audio_id)) {
                $other_attachments .= ',' . implode(',', $this->audio_id);
            }

            if (!empty($this->video_id)) {
                $other_attachments .= ',' . implode(',', $this->video_id);
            }
            $other_attachments = trim($other_attachments, ',');
            if($this->post_text == '') {
                $this->post_text = $this->header;
            }

            if (($this->post_text =='©' || $this->post_text == '') && $attachment == '' && $other_attachments == '') {
                $this->post_text = "&#01;";
            }

            $arr_fields = array('owner_id'      =>  '-'.$this->vk_group_id,
                                'message'       =>  $this->post_text,
                                'access_token'  =>  $this->vk_access_token,
                                'attachment'    =>  $attachment . ',' . $other_attachments,
                                'from_group'    =>   1
            );


            $url = self::METH . "/wall.post";
            $fwd3 = '';

            $link_attached = 0;
            if (strlen($arr_fields['attachment']) < 2) {
                $arr_fields['attachment'] = $this->link;
                $link_attached = 1;
            }

            //цикл для отправки
            $capcha_tries = 0;
            $old_id = '';
            $counter = 0;
            while (true) {
                $counter ++;
                if ($counter > 8)
                    throw new exception( __CLASS__ . '::' . __FUNCTION__ .
                        " it seems we have an endless circle here");
                if ( $capcha_tries > self::FALSE_COUNTER ) {
                    throw new ChangeSenderException();
                }

                $fwd3 = $this->qurl_request(self::METH . "/wall.post", $arr_fields);
                $fwd3 = json_decode($fwd3);
                if (self::TESTING) {
                    echo '<br>ответ отправки<br>';
                    print_r($fwd3);
                    echo '<br><br>';
                }
                $old_id = $fwd3->response->post_id;

                //проверка на капчу
                if (!empty ($fwd3->error)) {
                    $fwd3 = $fwd3->error;
                    if ($fwd3->error_code == '14') {
                        $cew = $this->captcha($fwd3->captcha_img, $fwd3->captcha_sid);
                        if ($cew) {
                            $arr_fields['captcha_sid'] = $fwd3->captcha_sid;
                            $arr_fields['captcha_key'] = $cew;
                        } else $capcha_tries ++;

                    } elseif ($fwd3->error_code == '214') {
                        throw new ChangeSenderException();
                    } else
                        throw new exception("Error in wall.post : $fwd3->error_msg");
                    // на ссылку
                } elseif ( $this->link && $old_id && !$link_attached) {
                    //получение массива фоток из ^ поста, его удаление
                    $arr_tmp = array(   'owner_id'      =>  '-' . $this->vk_group_id,
                                        'access_token'  =>  $this->vk_access_token,
                                        'count'         =>  5,
                                        'from_group'    =>  1
                    );
                    $url3 = self::METH . "/wall.get";
                    $fwd3 = json_decode($this->qurl_request($url3, $arr_tmp))->response;

                    unset($fwd3[0]);
                    //                print_r($fwd3);
                    $attachment = '-1';
                    foreach($fwd3 as $f) {
                        echo $f->id . ' vs ' . $old_id;
                        echo '<br>';
                        echo '<br>';
                        if ($f->id == $old_id) {
                            if (is_array($f->attachments)) {
                                $attachment = array();
                                foreach($f->attachments as $k) {
                                    if (!empty($k->photo->owner_id)) {
                                        $attachment[] = 'photo' . $k->photo->owner_id . '_' . $k->photo->pid;
                                        echo $k->photo->owner_id . '_' . $k->photo->pid . '<br>';
                                    }
                                }
                            }
                            $attachment = implode(',', $attachment);
                            break;

                        }
                    }
                    if ($attachment == '-1') {
                        throw new exception( __CLASS__ . '::' . __FUNCTION__ .
                            " Can't find post : $old_id in $this->vk_group_id");
                    }

                    // удаляем старый пост
                    $url = self::METH . 'wall.delete';

                    $params = array(
                        'owner_id'      =>  '-' . $this->vk_group_id,
                        'post_id'       =>  $old_id,
                        'access_token'  =>  $this->vk_access_token
                    );
                    $fwd = $this->qurl_request($url, $params);
                    $fwd = json_decode($fwd);
                    if (!empty ($fwd3->error)) {
                        $fwd3 = $fwd3->error;
                        throw new exception("Error in wall.delete : $fwd->error_msg");
                    }
                    sleep(0.6);

                    if (!$link_attached) {
                        $attachment .= ',' . $this->link;
                        $link_attached = 1;
                        $arr_fields['attachment'] = $attachment . ',' . $other_attachments;
                        $arr_fields['attachment'] = trim($arr_fields['attachment'], ',');
                    }

                    echo '<br><br>';
                }

                //выходы из цикла
                if ($fwd3->response->processing == 1 || (!$this->link && $old_id))
                    break;
                unset ($fwd3);
            }

            if ($old_id)
                return '-' . $this->vk_group_id . '_' . $old_id;
            return true;

        }


        private function remove_tags()
        {
            $this->post_text = str_replace( '<br>', "\r\n", $this->post_text );
            $this->post_text = htmlspecialchars_decode($this->post_text);
            $this->post_text = strip_tags( $this->post_text );
        }

        //!!!распознование капчи - долгий и неблагодарный процесс(до полуминуты,
        // + он может вернуть нераспознанную)
        // нужно учитывать это время
        //если повезет, возвращает  текст капчи,
        // false в случае неправильной разгадки/недоступности работников распознавания
        public function captcha($url, $vk_sid)
        {
            //не требующие пока изменений настройки
            $domain="antigate.com";
            $rtimeout = 5;
            $mtimeout = 120;
            $is_phrase = 0;
            $is_regsense = 0;
            $is_numeric = 0;
            $min_len = 0;
            $max_len = 0;
            $is_russian = 1;

            $try_counter = 0;
            while (true) {
                $try_counter ++;
                if ($try_counter > self::FALSE_COUNTER)
                    return false;
                //            print_r($url);
                $jp = file_get_contents($url );
                file_put_contents('capcha.jpg', $jp);

                $filename = realpath('capcha.jpg');

                //            if(!file_put_contents(TEMP_PATH . $vk_sid . '.jpg', file_get_contents(stripslashes($url))))
                //                return false;
                //            $filename = self::TEMP_PATH . $vk_sid . '.jpg';
                //            echo "<img src='" . self::TEMP_PATH . "$vk_sid.jpg'>";

                if (!file_exists($filename))
                {
                    if (self::TESTING) echo "file $filename not found\n";
                    return false;
                }
                $postdata = array(
                    'method'        => 'post',
                    'key'           => self::ANTIGATE_KEY,
                    'file'          => '@' . $filename,
                    'phrase'        => $is_phrase,
                    'regsense'      => $is_regsense,
                    'numeric'       => $is_numeric,
                    'min_len'       => $min_len,
                    'max_len'       => $max_len,

                );
                //            print_r($postdata);
                //            die();
                //            $result = $this->qurl_request("http://$domain/in.php", $postdata);
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL,             "http://$domain/in.php");
                curl_setopt($ch, CURLOPT_RETURNTRANSFER,     1);
                curl_setopt($ch, CURLOPT_TIMEOUT,             60);
                curl_setopt($ch, CURLOPT_POST,                 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS,         $postdata);
                $result = curl_exec($ch);
                if (curl_errno($ch))
                {
                    if (self::TESTING) echo "CURL returned error: ".curl_error($ch)."\n";
                    return false;
                }
                curl_close($ch);
                if (strpos($result, "ERROR")!==false) {
                    if (self::TESTING) echo "server returned error: $result\n";
                    return false;
                } else {
                    $ex = explode("|", $result);
                    $captcha_id = $ex[1];
                    if (self::TESTING) echo "captcha sent, got captcha ID $captcha_id\n";
                    $waittime = 0;
                    if (self::TESTING) echo "waiting for $rtimeout seconds\n";
                    sleep($rtimeout);
                    while(true) {
                        $result = file_get_contents("http://$domain/res.php?key=".self::ANTIGATE_KEY.'&action=get&id='.$captcha_id);
                        if (strpos($result, 'ERROR') !== false) {
                            if (self::TESTING) echo "server returned error: $result\n";
                            continue(2);
                        }
                        if ($result=="CAPCHA_NOT_READY") {
                            if (self::TESTING) echo "captcha is not ready yet\n";
                            $waittime += $rtimeout;
                            if ($waittime>$mtimeout) {
                                if (self::TESTING) echo "timelimit ($mtimeout) hit\n";
                                continue(2);
                            }
                            if (self::TESTING) echo "waiting for $rtimeout seconds\n";
                            sleep($rtimeout);
                        } else {
                            $ex = explode('|', $result);
                            if (trim($ex[0])=='OK') return trim($ex[1]);
                        }
                    }
                    return false;
                }
            }
            return false;
        }
    }

?>