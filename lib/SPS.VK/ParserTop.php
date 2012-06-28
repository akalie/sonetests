<?php
    /**
     * ParserTop
     * @package    SPS
     * @subpackage VK
     * @author     Shuler
     */
    class ParserTop {
        const API_URL = 'api.topface.ru';
        const TESTING = 0;

        //отправляет данные в json
        public function qurl_request_js($url, $arr_of_fields, $headers = '', $uagent = '')
        {
            if (empty($url)) {
                return false;
            }
            if(self::TESTING) print_r($arr_of_fields);

            $ch = curl_init($url);


            $options = array(
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => array('Content-type: application/json') ,
                CURLOPT_POSTFIELDS => $arr_of_fields
            );

            // Setting curl options
            curl_setopt_array( $ch, $options );


            $result = curl_exec($ch);
            if (curl_errno($ch)){
                echo "<br>error in curl: ". curl_error($ch) ."<br>";
                return 'error in curl: '. curl_error($ch);
            }

            curl_close($ch);
            return $result;
        }

        //возвращает массив в json
        //имеющиеся поля совпадают с парсером контакта: id, photo, link, likes, text
        //остальных нет
        //в поле id - идентификатор юзера в topface
        public function get_top($sex)
        {
            //данные Мельникова, что такое clienttype - в душе не знаю, без него не идет
            $request_params = array(
                'locale'    =>  'ru',
                'platform'  =>  'vk',
                'sandbox'   =>  1,
                'sid'       =>  134497174,
                'token'     =>  '7770233f7f7460a97f7460a96c7f5cc82477f747f7140af4eaf4fcadc9b46dd',
                'clienttype'=>  'sdasd'
            );

            //массив городов
            $cities = array(
                'Ekaterinburg'      =>  49,
                'Kazan'             =>  60,
                'Kiev'              =>  314,
                'Minsk'             =>  282,
                'Moscow'            =>  1,
                'Novosibirsk'       =>  99,
                'Samara'            =>  123,
                'Saint Petersburg'  =>  2,
                'Ufa'               =>  151,
                'Harkov'            =>  280
            );

            $request = array(   'service'   =>  'auth',
                                'data'      =>  $request_params
            );

            $request = json_encode($request);

            //авторизация, получение сессии
            $response = $this->qurl_request_js(self::API_URL, $request);
            $response = json_decode($response);
            if (isset($response->error)){
                throw new exception('Error in ' . __CLASS__ . '::' . __FUNCTION__ .
                    ", problems with authorisation request : " . $response->error->message);
            }

            $response = $response->result;
            $ssid = $response->ssid;
            $res = array();
            $uids = array();
            //перебор топов городов
            foreach($cities as $city){
                $request_params  = array('sex'  =>  $sex, 'city' => $city);
                $request = array(   'service'   =>  'top',
                                    'data'      =>  $request_params,
                                    'ssid'      =>  $ssid
                );
                $request  = json_encode($request);
                $response = $this->qurl_request_js(self::API_URL, $request);
                $response = json_decode($response);
                if (isset($response->error)){
                    throw new exception('Error in ' . __CLASS__ . '::' . __FUNCTION__ .
                        ", problems with top request : " . $response->error->message);
                }

                foreach($response->result->top as &$entry){
                    $uids[] = $entry->uid;
                    $res[] = array(
                        'id'      =>  $entry->uid,
                        'link'    =>  'http://topface.ru/vklike/' . $entry->uid. '/',
                        'likes'   =>  $entry->liked,
                        'photo'   =>  array(
                            '0' => array(
                                'url' => $entry->photo
                            )
                        )
                    );
                }

                sleep(0.1);
            }

            $request_params = array(
                'uids'      =>  $uids,
                'fields'    =>  array('first_name', 'age')
            );
            $request = array(   'service'   =>  'profiles',
                                'data'      =>  $request_params,
                                'ssid'      =>  $ssid
            );

            $request = json_encode($request);
            //запрос на получение доп данных о пользователях
            $response = $this->qurl_request_js(self::API_URL, $request);
            $response = json_decode($response);
            if (isset($response->error)){
                throw new exception('Error in ' . __CLASS__ . '::' . __FUNCTION__ .
                    ", problems with getting extra data about users : " . $response->error->message);
            }

            $response = $response->result;

            $i = 0;
            foreach($response->profiles as $entry){
                while (1){
                    if ($res[$i]['id'] == $entry->uid){
                        $res[$i]['text'] = $entry->first_name . ', ' . $entry->age;
                        $i++;
                        break;
                    }else{
                        unset($res[$i]);
                        $i++;
                        if ($i > 10000){
                            throw new exception('Error in ' . __CLASS__ . '::' . __FUNCTION__ .
                                'something really goes wrong with arrays of top and extra data');
                        }
                    }
                }
            }

            if (self::TESTING){
                echo 'count uids = ' . count($uids) . '<br>';
                echo 'res  = '. count($res) . '<br>';
                echo '<br>res 2 = ' . $i . '<br>';
                foreach($res as $entry){
                    print_r($entry);
                    echo '<br>';
                }
            }
            return $res;
        }
    }
?>