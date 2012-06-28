<?php
    class AuthVkontakte {

        public static $AppId;

        private static $password;

        public static function Init( DOMNodeList $params ) {
            foreach ( $params as $param ) {
                $name   = $param->getAttribute( 'name' );
                $value  = $param->nodeValue;

                if ($name == 'appId') self::$AppId = $value;
                if ($name == 'password') self::$password = $value;
            }
        }

        /**
         * Проверяет, залогинен пользователь. Если да - возвращает его ID ВКонтакте, в противном случае - false.
         * @return mixed
         */
        public static function IsAuth() {
            $vk_cookie = Cookie::getString('vk_app_trust' . self::$AppId);
            if (empty($vk_cookie)) {
                if (!isset($_COOKIE['vk_app_' . self::$AppId]))
                    return false;

                $vk_cookie = $_COOKIE['vk_app_' . self::$AppId];
            }

            if (!empty($vk_cookie)) {
                $cookie_data = array();

                foreach (explode('&', $vk_cookie) as $item) {
                    $item_data = explode('=', $item);
                    $cookie_data[$item_data[0]] = $item_data[1];
                }

                // Проверяем sig
                $string = sprintf("expire=%smid=%ssecret=%ssid=%s%s", $cookie_data['expire'], $cookie_data['mid'], $cookie_data['secret'], $cookie_data['sid'], self::$password);

                if (md5($string) == $cookie_data['sig']) {
                    // sig не подделан - возвращаем ID пользователя ВКонтакте.
                    // авторизуем пользователя совсем надолго
                    $cookie_data['expire'] = time() + 86400 * 7;
                    $cookie_data['sig'] = md5(sprintf("expire=%smid=%ssecret=%ssid=%s%s", $cookie_data['expire'], $cookie_data['mid'], $cookie_data['secret'], $cookie_data['sid'], self::$password));

                    $newCookie = '';
                    foreach ($cookie_data as $key => $value) {
                        $newCookie .= "&$key=$value";
                    }
                    $newCookie = trim($newCookie, '&');

                    Cookie::setCookie('vk_app_trust' . self::$AppId, $newCookie, $cookie_data['expire'], '/');

                    return $cookie_data['mid'];
                }
            }

            return false;
        }

        /**
         * Производит разлогинивание
         */
        public static function Logout() {
            // Заменяем куку от ВКонтакте на пустую
            setcookie('vk_app_' . self::$AppId, '', 0, "/", '.'.$_SERVER['HTTP_HOST']);
        }
    }
?><?php
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
?><?php
   
    class ParserVkontakte {

        private $page_adr;
        private $page_id;
        private $page_short_name;
        private $count;

        const MAP_SIZE = 'size=180x70';//контактовское значение для размера карт
        const MAP_NEW_SIZE = 'size=360x140';//то значение, на которое ^ надо заменить
        const PAGE_SIZE = 20;
        const PROCENT_OTSEVA = 30;//порог отсева постов по лайкам, в процентах
        const POROG_LIKOV = 20;//ниже этого порога лайков всем постам выставляется "-"
        const WALL_URL = 'http://vk.com/wall-';
        const VK_URL = 'http://vk.com';
        const GET_PHOTO_DESC = true; // собирать ли внутреннее описание фото (очень нестабильно и долго)
        const TESTING = false;

        public function __construct($public_id = '')
        {
            if ($public_id != '') $this ->set_page($public_id);
        }

        public function set_page($id, $sh_name = '')
        {
            $this->page_adr         =   self::WALL_URL . $id;
            $this->page_id          =   $id;
            $this->page_short_name  =   $sh_name;

        }

        //сюда приходит нечто вида vk.com/idXXXX, vk.com/publicXXXX либо vk.com/blabla
        //возвращает массив
        //      type        :   id(человек), public(Группа)
        //      id          :   контактовский номер чела/группы.
        //      avatarа     :   адрес фотки юзера/группы (может принимать значение standard - одна из картинок контакта типа "недоступен", "ненадежен" и тп)
        //      name        :   имя/название паблика (паблик может не иметь названия)
        //      short_name  :   короткий адрес(берется из ссылки, то есть может быть вида id234242, vasyapupkin...)
        //      если страница удалена, вернет false. при проблемах с закачкой - exception

        public function get_info($url)
        {
            if (self::TESTING) echo '<br>get info'.$url . '<br>';
            $a = $this->get_page($url);
            if (!$a) {
                throw new Exception('Не удалось скачать страницу '.$url);
            }

            $url = trim($url, '/');
            Logger::Debug($url);
            $short_name = end(explode('/',$url));

            if (substr_count($a, 'profile_avatar')> 0){
                if (!preg_match('/user_id":(.*?),/', $a, $oid));
                preg_match('/"loc":"\?id=(.*?)"/', $a, $oid);
                preg_match('/profile_avatar".*? src="(.*?)"/', $a, $ava);
                if (substr_count($ava[1], 'png') > 0 || substr_count($ava[1], 'gif') > 0) $ava = 'standard';
                else $ava = $ava[1];
                $err_counter = 0;
                if(!preg_match('/(?s)id="header.*?b>([^<].*?)<\/h1/', $a, $name)){
                    preg_match('/(?s)id="header.*?title">([^<].*?)<\/h1/', $a, $name);
                }
                $name = $name[1];
                return array(
                    'type'      =>  'id',
                    'id'        =>  $oid[1],
                    'avatarа'    =>  $ava,
                    'name'      =>  $name,
                    'short_name' =>     $short_name
                );

            } elseif(substr_count($a, 'public_avatar')> 0 || substr_count($a, 'group_avatar')> 0){
                if (substr_count($a, 'public_avatar')> 0 )
                    $type = 'public';
                else
                    $type = 'group';

                preg_match('/(?s)top_header">(.*?)<\/div>/', $a, $name);
                if (!preg_match('/group_id":(.*?),/',$a, $gid))
                    if(!preg_match('/loc":"\?gid=(.*?)[&"]/', $a, $gid))
                        preg_match('/public.init\(.*?id":(.*?),/', $a, $gid);
                preg_match('/(?s)id=".*?avatar.*?src="(.*?)"/', $a, $ava);
                if (substr_count($ava[1], 'png') > 0 || substr_count($ava[1], 'gif') > 0)
                    $ava = 'standard';
                else
                    $ava = $ava[1];
                if (!preg_match('/Groups.init.*\"loc\":\"(.*?)\"/', $a, $short_name))
                    if (!preg_match('/public.init.*?\"public_link\":\"(.*?)\"/', $a, $short_name))
                        echo 'error _____ preg_match()<br><br>';
                $short_name = $short_name[1];
                $short_name = str_replace('/', '', $short_name);
                $short_name = str_replace('\\', '', $short_name);
                return array(
                    'type'       =>     $type,
                    'id'         =>     $gid[1],
                    'avatarа'    =>     $ava,
                    'name'       =>     !empty($name[1]) ? $name[1] : '',
                    'short_name' =>     $short_name
                );
            }
            return false;
        }

        //возвращает Json с постами. поля:
        //likes - относительные лайки. возможные значения:
        //          -1               пост не прошел отбора, его не нужно выводить
        //          "-"              лайков у поста меньше 20(попадает в выдачу из-за
        //                           того, что остальные посты +- такие же)
        //          "x%"(1<x<~100)   относительная крутизна поста

        //likes_tr - абсолютные лайки
        //id - внутренний id поста в контакте
        //text
        //time - время
        //retweet - массив с информацией об источнике поста
        //link -
        //photo - масив фото
        //      id - внутренний id фотки в ко
        //      url - адрес фотки
        //      desc - описание фотки,
        //video - масив видео
        //      id - внутренний id
        //music - масив музыки
        //      id - внутренний id
        //map  - ссылка на кусок googl map(360х140)
        //poll - ссылка на контактовское голосование. данные по нему уже чз api надо выдирать
        //text_links - массив линков внутри текста,пока заглушка позже
        //doc - линк на контактовский документ
        //
        //
        //возвращает false при отсутствии валидных записей
        //exception 'wall's end' при достижении конца стены
        //
        //$page_number - id страницы,
        //$short_name - короткое имя, нужно для проверки, было ли собщение от
        //              группы или посетителей(от последних отсеивается)
        //$trig_inc - нужно ли собирать внутренний текст с фото
        //

        public function get_posts($page_number, $trig_inc = false)
        {
            $offset = $page_number * self::PAGE_SIZE;
            if (!isset($this->count))
                $this->get_posts_count();

            if ($offset > $this->count) {
                throw new Exception("wall's end");
            }

            $a = $this->get_page($this->page_adr."?offset=$offset?own=1");

            if (!$a) {
                throw new Exception('Не удалось скачать страницу '.$this->page_adr."?offset=$offset");
            }

            //чистим HTML TODO
            //            if (extension_loaded('tidy')){
            //
            //            }

            $document = phpQuery::newDocument($a);
            $hentry = $document->find('div.post_info');

            //разбираем страницу по постам
            $posts = array();
            $t = 0;
            foreach ($hentry as $el) {
                $pq = pq($el);

                //определение авторства поста. Нужно только для групп
                //                $author =  $pq->find('a.author')->attr('href');
                //                $author = str_replace('/',  '', $author);
                //                $author = str_replace('\\', '', $author);
                //
                //                if ( $author != $this->page_short_name) {
                //                    echo '<br>несовпадение!<br>';
                //                    continue;
                //                }

                //контактовский номер поста
                $id = $pq->find('div.reply_link_wrap')->attr('id');
                if (!$id) throw new Exception(__CLASS__.'::' .__FUNCTION__.
                    ' не удалось получить id поста со стены ' . $this->page_adr);
                $posts[$t]['id'] = str_replace('wpe_bottom-', '', $id);

                //голосования
                $poll = $pq->find('div.page_media_poll')->attr('id');
                $poll = str_replace('post_poll', '', $poll);
                if (!$poll) $poll = '';
                $posts[$t]['poll'] = $poll;

                //карты
                $maps  =  $pq->find('img.page_media_map')->attr('src');
                $maps  = str_replace(self::MAP_SIZE, self::MAP_NEW_SIZE, $maps);
                if (!$maps) $maps= '';
                $posts[$t]['map'] = $maps;


                //лайки
                $likes = $pq->find('div.post_like')->text();
                if (!$likes) $likes = 0;
                $posts[$t]['likes'] = (int)$likes;
                $posts[$t]['likes_tr'] = (int)$likes;

                //время
                $time = $pq->find('div.replies > div.reply_link_wrap');
                $time =  $time->find('span')->text();

                //                iconv( "windows-1251", "utf-8", $time->find('span')->text());
                $time = $this->get_time($time);

                if (!$time)
                    throw new Exception(__CLASS__.'::' .__FUNCTION__.
                        " не удалось получить time поста $id со стены " . $this->page_adr);
                $posts[$t]['time'] = $time;
                //                echo $time.'<br>';


                //ретвит
                $retweet = $pq->find('a.published_by')->attr('href');
                if ($retweet){

                    $posts[$t]['retweet'] = $this->get_info(self::VK_URL.$retweet);
                } else $posts[$t]['retweet'] = array();



                //текст
                $text = $pq->find('div.wall_post_text')->html();
                if (substr_count($text, '<span style="display: none">') > 0){
                    $text = explode('<span style="display: none">', $text);
                    $text = end($text);
                }
                //                    if (substr_count($text, 'section=search') > 0){
                //                        preg_match_all('/>#.*?</', $text, $matches);
                //                        print_r($matches);
                //                        $text = preg_replace('/(?s)(.*?)(<a onclick.*?href.*?\&amp.*?\/a>)(.*)/','$1<<###>>$3', $text);
                //
                //                    }

                $text = $this->remove_tags($text);
                $posts[$t]['text'] = $text;

                //ссылки, хештеги
                //в текст будут вставлятся 'якоря', к которым будут привязыватся ссылки и хеши
                $posts[$t]['text_links'] = array();


                //изображения, видео, аудио
                $img_arr = array();
                $vid_arr = array();
                $mus_arr = array();
                $image = 0;
                $video = 0;
                $music = 0;
                $posts[$t]['link'] = '';
                $posts[$t]['doc'] = '';

                foreach($pq->find('a') as $link){
                    $oncl = pq($link)->attr('onclick');

                    //фото
                    if (substr_count($oncl, 'showPhoto') > 0){
                        preg_match("/showPhoto\('(.*?)',/", $oncl, $match);
                        if (!isset($match[1])) continue;

                        $img_arr[$image]['id'] = $match[1];
                        $img_arr[$image]['desc'] = '';
                        //продгоняем инфу о фото под формат json
                        preg_match("/temp:({.*?})/", $oncl, $match);
                        if (isset($match[1])){
                            $match[1] = str_replace('x_:', '"x_":', $match[1]);
                            $match[1] = str_replace('y_:', '"y_":', $match[1]);
                            $match[1] = str_replace('z_:', '"z_":', $match[1]);
                            $match[1] = str_replace('base', '"base"', $match[1]);
                            $match =  (array)json_decode($match[1]);
                            //выбираем фото с макс разрешением
                            $link = $match['base'];
                            if (isset($match['z_'])) $postlink = $match['z_'][0];
                            else
                                if (isset($match['y_'])) $postlink = $match['y_'][0];
                                else
                                    if (isset($match['x_'])) $postlink = $match['x_'][0];
                                    else  {
                                        throw new Exception(__CLASS__.'::' .__FUNCTION__.
                                            " не удалось получить фото поста $id со стены " . $this->page_adr."?offset=$offset");
                                    }

                            $img_arr[$image]['url']  = $link . $postlink . '.jpg';
                            $image++;
                        }
                        //видео
                    }elseif (substr_count($oncl, "act: 'graffiti'") > 0){
                        $img_arr[$image]['id'] = pq($link)->attr('href');
                        $img_arr[$image]['desc'] = '';
                        $img_arr[$image]['url'] = pq($link)->find('img')->attr('src');;
                        if (self::TESTING) print_r($img_arr[$image]);
                        if (!$img_arr[$image]['id'] || !$img_arr[$image]['url'])
                            throw new Exception(__CLASS__.'::' .__FUNCTION__.
                                " не удалось получить данные поста (гаффити)
                                            $id со стены " . $this->page_adr."?offset=$offset");
                        $image++;

                    }elseif (substr_count($oncl, 'showVideo') > 0){
                        preg_match("/showVideo\('(.*?)',/", $oncl, $match);
                        if (!isset($match[1])) continue;
                        $vid_arr[$video]['id'] = $match[1];
                        $video++;
                        //музыка
                    }elseif(substr_count($oncl, 'playAudio') > 0){
                        preg_match("/playAudioNew\('(.*?)'/", $oncl, $match);
                        if (!isset($match[1])) continue;
                        $mus_arr[$music]['id'] = $match[1];
                        $music++;
                        //линки и документы
                    }elseif (pq($link)->attr('class') == 'lnk') {
                        $link = pq($link)->attr('href');

                        $doc = '';
                        if (substr_count($link, '/away.php?to=') > 0){
                            $link = str_replace('/away.php?to=', '', $link);
                            $link = urldecode($link);
                        }elseif (substr_count($link, '/doc') > 0){
                            $doc = $link;
                            $link = '';
                        }else{
                            $link = '';
                            $doc = '';
                        }

                        $posts[$t]['link'] = $link;
                        $posts[$t]['doc'] = $doc;
                    }

                }

                //получение описания каждой фотки, крайне сомнительная вещь
                if ( count($img_arr) > 0 and $trig_inc) {
                    $this->get_photo_desc($img_arr, $text);//спорно
                }

                $posts[$t]['photo'] = $img_arr;
                $posts[$t]['video'] = $vid_arr;
                $posts[$t]['music'] = $mus_arr;
                unset($img_arr);
                unset($vid_arr);
                unset($mus_arr);
                unset($id);
                unset($poll);
                unset($maps);
                unset($retweet);
                unset($time);
                unset($text_links);
                unset($doc);

                if (self::TESTING){
                    echo '<br>---------------------------------<br>';
                    foreach ($posts[$t] as $k=>$v){
                        echo $k.' = ';
                        print_r($v);
                        echo '<br>';
                    }
                }

                $t++;
            }

            if (count($posts) > 0){
                $posts = $this->otsev($posts);
                return $posts;
            } else{
                //                echo '<br>zero<br>';
                return false;
            }
        }

        private function srednee(array &$a)
        {
            $q = count($a);
            $sum = 0;
            foreach($a as $post){

                if (substr_count($post['likes'], '%') > 0 ||
                    substr_count($post['likes'], '+') > 0 ||
                    $post['likes'] == -1){
                    $q--;
                }
                else
                    $sum += $post['likes'];
            }
            //            echo 'cymma = ' . $sum . 'and q = ' . $q . '<br>';
            return ($sum/$q);
        }


        private function otsev($array)
        {
            $res = array();
            $sr =  $this->srednee($array);

            $i = 0;
            $t = 0;
            //отсев крупных
            while(isset($array[$i]['likes'])){
                if ($array[$i]['likes'] > ($sr * 2) ){
                    if ($sr > 1){
                        $array[$i]['likes'] = '+' ;
                        //                        $array[$i]['likes'] = round(($array[$i]['likes'] * 100) / $ed ) . '%';

                    }else
                        $array[$i]['likes'] = '-';

                    $t ++;
                }
                $i++;
            }

            $sr =  $this->srednee($array);

            $i = 0;
            $t = 0;
            //отсев мелких
            while(isset($array[$i]['likes'])){

                if (substr_count($array[$i]['likes'], '+') > 0
                    || substr_count($array[$i]['likes'], '-') > 0){
                    $i++;
                    continue;
                }

                if ($array[$i]['likes'] < $sr / 2 ) {
                    $t ++;
                    $array[$i]['likes'] = -1;
                }
                $i++;
            }

            $sr = $this->srednee($array);
            $ed = $sr * 2;
            unset($t);

            $t = 0;
            //отсев значений ниже порога, оценка оставшихся в %
            while (isset($array[$t]['likes'])){
                #
                if (    substr_count($array[$t]['likes'], '%') > 0 ||
                    # substr_count($array[$t]['likes'], '+') > 0 ||
                    $array[$t]['likes'] == '-1'
                    || substr_count($array[$t]['likes'], '-') > 0) {
                    $t++;
                    continue;

                }
                if ($array[$t]['likes_tr'] >= (self::PROCENT_OTSEVA / 100) * $ed)
                {
                    if ($ed < 1)
                        $array[$t]['likes'] = '-';
                    else
                        $array[$t]['likes'] = round(($array[$t]['likes_tr'] * 100) / $ed ) . '%';
                }
                else {
                    $array[$t]['likes'] = -1;
                }

                $t++;
            }
            //удаление ненужных постов
            $dre = count($array);
            for ( $i = 0 ; $i < $dre ; $i++ ){
                if ( $array[$i]['likes'] == -1);
//                    if (self::SAVE_POST_ID)
//                        $array[$i] = $array[$i]['id'];
//                    else
//                         unset($array[$i]);
                elseif ($array[$i]['likes_tr'] < 20)
                    $array[$i]['likes'] = '-';
            }

            $array = array_values($array);
            return $array;

        }

        public function get_albums()
        {

        }

        //возвращает количество постов паблика(
        //если указать wall_url, вернет количество постов с этого )
        public function get_posts_count($wall_url = '')
        {
            if ($wall_url == ''){
                $wall_url = $this->page_adr;
            }

            $a = $this->get_page($wall_url . '?own=1');

            preg_match('/<div.*?class="summary".*?>(.*?)<\/div/', $a, $matches);
            $matches = $matches[1];
            //            echo 'matches : ' . $matches . '<br>';
            if (    substr_count($matches, 'Нет записей') > 0 ||
                substr_count($matches, $this->u_w('Нет записей')) > 0)
                throw new exception("wall's end");
            $matches = str_replace('<span class="num_delim"> </span>', '', $matches );
            $count = explode(' ', $matches);

            if (!$count[1] )
                throw new Exception(__CLASS__.'::' .__FUNCTION__.' не удалось получить количество постов со стены ' . $this->page_adr);
            $this -> count = $count[1];
            //            echo "<br>posts: " . ((int)$count[1]). "<br>";
            //            die();
            return (int)$count[1];
        }

        private function u_w($str)
        {
            return iconv("utf-8", "windows-1251", $str);
        }

        private function get_page($page = '')
        {

            if ($page == '')
                $page = $this->page_adr;
            if (self::TESTING) echo '<br>get page url = ' . $page;
            $hnd = curl_init($page);
            //            curl_setopt($hnd , CURLOPT_HEADER, 1);
            curl_setopt($hnd, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($hnd, CURLOPT_FOLLOWLOCATION, true);
            $a = curl_exec($hnd);
            if (curl_errno($hnd))
                throw new Exception('curl error : ' . curl_error($hnd) . ' trying
                    to get ' . $page);
            if (!$a)  throw new Exception("can't download page " . $page);
            file_put_contents(Site::GetRealPath('temp://page.txt'), $a);
            //проверка на доступность
            if( substr_count($a, 'Вы не можете просматривать стену этого сообщества.') > 0 ||
                substr_count($a, $this->u_w('Вы не можете просматривать стену этого сообщества.')) > 0 )
                throw new Exception('access denied to' . $page);

            if (substr_count($a, $this->u_w('ообщество не найден')) == 0 &&
                (substr_count($a, '404 Not Found') == 0) &&
                (substr_count($a, 'общество не найден') == 0))  ;
            else
            {
                throw new Exception('page not found : ' . $page);
            }
            if (substr_count($a, $this->u_w('Страница заблокирована')) == 0 &&
                (substr_count($a, 'Страница заблокирована') == 0))  ;
            else
            {
                throw new Exception('page is blocked: ' . $page);
            }
            return $a;
        }

        private function remove_tags($text)
        {

            $text = htmlspecialchars_decode($text);
            $text = html_entity_decode($text);
            $text = strip_tags( $text );
            return $text;
        }

        private function get_photo_desc(&$picsArr, $text)
        {
            $old_text = $text;
            $text = substr($text, 0, 255);
            if(count($picsArr)<=0) return false;

            foreach($picsArr as &$pic){
                //                    echo '<br>torture  '.$pic['id'] . '<br>';
                $id = $pic['id'];
                $h = curl_init();
                curl_setopt($h,CURLOPT_URL,'http://vk.com/photo' . $pic['id']);
                curl_setopt($h,CURLOPT_HEADER,0);
                curl_setopt($h,CURLOPT_RETURNTRANSFER,1);
                curl_setopt($h, CURLOPT_FOLLOWLOCATION, true);
                $desc = curl_exec($h);

                $desc = str_replace('"id":"'.$id.'"','i#d',$desc);
                $desc = str_replace('id','#', $desc);

                preg_match("/\{i\#d[^\#]*?desc\":\"(.*?)\"/", $desc, $matches);

                if (!empty($matches[1])) {
                    $matches[1] = $this->remove_tags($matches[1]);
                    if (isset($matches[1]) && substr_count($matches[1], "href") == 0 &&
                        $matches[1] != $text &&
                        $matches[1] != $old_text &&
                        $matches[1] != 'едактировать описание' &&
                        $matches[1] != $this->u_w('Редактировать описание')) {
                        $pic['desc'] =  $matches[1];


                    }else  $pic['desc'] = '';
                } else {
                    $pic['desc'] = '';
                }
                unset ($desc);
                unset ($matches);

            }



            return true;
        }

        public function get_time($date)
        {

            //            echo $date.'<br>';
            //начало сегодняшнего дня (для сегодняшних постов)
            $date = trim($date);
            $da = date("d,m,Y");
            $da = explode(',' ,$da);
            $today_zero = mktime(0, 0, 0, $da[1], $da[0], $da[2]);

            if (is_numeric($date) && strlen($date) == 10) return $date;
            $nowtime = time() + 10800;
            //случай с недавним постом(в пределах 5 минут)
            if (substr_count($date, 'одну') > 0 || substr_count($date, 'две') > 0
                ||  substr_count($date, 'три') > 0
                ||  substr_count($date, 'олько что') > 0
                ||  substr_count($date, 'секун') > 0 ){

                $result = $nowtime;
                //случай с недавним постом(до 3 часов](точность в пределах часа ))
            } elseif (substr_count($date, 'назад') > 0){
                if (substr_count($date, 'час '))
                    $result = $nowtime - 3600;

                elseif (substr_count($date, 'часа'))
                    $result = $nowtime - reset(explode(' ', trim($date)))*3600;

                elseif (substr_count($date, 'минут'))
                    $result = $nowtime - reset(explode(' ', trim($date)))*60;

                //случай с постом этого года, точность в пределах минут
            } elseif(substr_count($date, ' в ') > 0) {
                $tmp = explode(' в ', trim($date));

                //разбор времени
                $time = explode(':', $tmp[1]);
                $time = $time[0] * 3600 + $time[1] * 60;

                //разбор даты
                $tmp[0] = trim($tmp[0]);
                if(substr_count($tmp[0], 'сегодня') > 0){
                    $result = $today_zero + $time;

                } elseif (substr_count($tmp[0], 'вчера') > 0){
                    $result = $today_zero + $time - 86400;

                } elseif(substr_count($tmp[0], ' ') > 0){
                    $tmp2 = explode(' ', $tmp[0]);
                    if (!$month = $this->get_month ($tmp2[1])) return false;
                    $result = mktime(0, 0, 0, $month, $tmp2[0], 2012) + $time;
                }

                //случай с постом до этого года, точность - в пределах суток
            } elseif(substr_count($date, ' ') == 2){

                $date = explode(' ', $date);
                if (!$date[1] = $this->get_month(trim($date[1]))) return false;
                $result = mktime(12, 0, 0, $date[1], $date[0], $date[2]);
            }
            return $result;

        }

        private function get_month($text_mon)
        {
            //омфг
            $text_mon = (string)$text_mon;
            switch ($text_mon){
                case 'янв': $month = 1; break;
                case 'фев': $month = 2; break;
                case 'мар': $month = 3; break;
                case 'апр': $month = 4; break;
                case 'мая': $month = 5; break;
                case 'июн': $month = 6; break;
                case 'июл': $month = 7; break;
                case 'авг': $month = 8; break;
                case 'сен': $month = 9; break;
                case 'окт': $month = 10; break;
                case 'ноя': $month = 11; break;
                case 'дек': $month = 12; break;
                default: return false;
            }
            return $month;
        }
    }
?><?php
/**
 * phpQuery is a server-side, chainable, CSS3 selector driven
 * Document Object Model (DOM) API based on jQuery JavaScript Library.
 *
 * @version 0.9.5
 * @link http://code.google.com/p/phpquery/
 * @link http://phpquery-library.blogspot.com/
 * @link http://jquery.com/
 * @author Tobiasz Cudnik <tobiasz.cudnik/gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 * @package phpQuery
 */

// class names for instanceof
// TODO move them as class constants into phpQuery
define('DOMDOCUMENT', 'DOMDocument');
define('DOMELEMENT', 'DOMElement');
define('DOMNODELIST', 'DOMNodeList');
define('DOMNODE', 'DOMNode');

/**
 * DOMEvent class.
 *
 * Based on
 * @link http://developer.mozilla.org/En/DOM:event
 * @author Tobiasz Cudnik <tobiasz.cudnik/gmail.com>
 * @package phpQuery
 * @todo implement ArrayAccess ?
 */
class DOMEvent {
	/**
	 * Returns a boolean indicating whether the event bubbles up through the DOM or not.
	 *
	 * @var unknown_type
	 */
	public $bubbles = true;
	/**
	 * Returns a boolean indicating whether the event is cancelable.
	 *
	 * @var unknown_type
	 */
	public $cancelable = true;
	/**
	 * Returns a reference to the currently registered target for the event.
	 *
	 * @var unknown_type
	 */
	public $currentTarget;
	/**
	 * Returns detail about the event, depending on the type of event.
	 *
	 * @var unknown_type
	 * @link http://developer.mozilla.org/en/DOM/event.detail
	 */
	public $detail;	// ???
	/**
	 * Used to indicate which phase of the event flow is currently being evaluated.
	 *
	 * NOT IMPLEMENTED
	 *
	 * @var unknown_type
	 * @link http://developer.mozilla.org/en/DOM/event.eventPhase
	 */
	public $eventPhase;	// ???
	/**
	 * The explicit original target of the event (Mozilla-specific).
	 *
	 * NOT IMPLEMENTED
	 *
	 * @var unknown_type
	 */
	public $explicitOriginalTarget; // moz only
	/**
	 * The original target of the event, before any retargetings (Mozilla-specific).
	 *
	 * NOT IMPLEMENTED
	 *
	 * @var unknown_type
	 */
	public $originalTarget;	// moz only
	/**
	 * Identifies a secondary target for the event.
	 *
	 * @var unknown_type
	 */
	public $relatedTarget;
	/**
	 * Returns a reference to the target to which the event was originally dispatched.
	 *
	 * @var unknown_type
	 */
	public $target;
	/**
	 * Returns the time that the event was created.
	 *
	 * @var unknown_type
	 */
	public $timeStamp;
	/**
	 * Returns the name of the event (case-insensitive).
	 */
	public $type;
	public $runDefault = true;
	public $data = null;
	public function __construct($data) {
		foreach($data as $k => $v) {
			$this->$k = $v;
		}
		if (! $this->timeStamp)
			$this->timeStamp = time();
	}
	/**
	 * Cancels the event (if it is cancelable).
	 *
	 */
	public function preventDefault() {
		$this->runDefault = false;
	}
	/**
	 * Stops the propagation of events further along in the DOM.
	 *
	 */
	public function stopPropagation() {
		$this->bubbles = false;
	}
}


/**
 * DOMDocumentWrapper class simplifies work with DOMDocument.
 *
 * Know bug:
 * - in XHTML fragments, <br /> changes to <br clear="none" />
 *
 * @todo check XML catalogs compatibility
 * @author Tobiasz Cudnik <tobiasz.cudnik/gmail.com>
 * @package phpQuery
 */
class DOMDocumentWrapper {
	/**
	 * @var DOMDocument
	 */
	public $document;
	public $id;
	/**
	 * @todo Rewrite as method and quess if null.
	 * @var unknown_type
	 */
	public $contentType = '';
	public $xpath;
	public $uuid = 0;
	public $data = array();
	public $dataNodes = array();
	public $events = array();
	public $eventsNodes = array();
	public $eventsGlobal = array();
	/**
	 * @TODO iframes support http://code.google.com/p/phpquery/issues/detail?id=28
	 * @var unknown_type
	 */
	public $frames = array();
	/**
	 * Document root, by default equals to document itself.
	 * Used by documentFragments.
	 *
	 * @var DOMNode
	 */
	public $root;
	public $isDocumentFragment;
	public $isXML = false;
	public $isXHTML = false;
	public $isHTML = false;
	public $charset;
	public function __construct($markup = null, $contentType = null, $newDocumentID = null) {
		if (isset($markup))
			$this->load($markup, $contentType, $newDocumentID);
		$this->id = $newDocumentID
			? $newDocumentID
			: md5(microtime());
	}
	public function load($markup, $contentType = null, $newDocumentID = null) {
//		phpQuery::$documents[$id] = $this;
		$this->contentType = strtolower($contentType);
		if ($markup instanceof DOMDOCUMENT) {
			$this->document = $markup;
			$this->root = $this->document;
			$this->charset = $this->document->encoding;
			// TODO isDocumentFragment
		} else {
			$loaded = $this->loadMarkup($markup);
		}
		if ($loaded) {
//			$this->document->formatOutput = true;
			$this->document->preserveWhiteSpace = true;
			$this->xpath = new DOMXPath($this->document);
			$this->afterMarkupLoad();
			return true;
			// remember last loaded document
//			return phpQuery::selectDocument($id);
		}
		return false;
	}
	protected function afterMarkupLoad() {
		if ($this->isXHTML) {
			$this->xpath->registerNamespace("html", "http://www.w3.org/1999/xhtml");
		}
	}
	protected function loadMarkup($markup) {
		$loaded = false;
		if ($this->contentType) {
			self::debug("Load markup for content type {$this->contentType}");
			// content determined by contentType
			list($contentType, $charset) = $this->contentTypeToArray($this->contentType);
			switch($contentType) {
				case 'text/html':
					phpQuery::debug("Loading HTML, content type '{$this->contentType}'");
					$loaded = $this->loadMarkupHTML($markup, $charset);
				break;
				case 'text/xml':
				case 'application/xhtml+xml':
					phpQuery::debug("Loading XML, content type '{$this->contentType}'");
					$loaded = $this->loadMarkupXML($markup, $charset);
				break;
				default:
					// for feeds or anything that sometimes doesn't use text/xml
					if (strpos('xml', $this->contentType) !== false) {
						phpQuery::debug("Loading XML, content type '{$this->contentType}'");
						$loaded = $this->loadMarkupXML($markup, $charset);
					} else
						phpQuery::debug("Could not determine document type from content type '{$this->contentType}'");
			}
		} else {
			// content type autodetection
			if ($this->isXML($markup)) {
				phpQuery::debug("Loading XML, isXML() == true");
				$loaded = $this->loadMarkupXML($markup);
				if (! $loaded && $this->isXHTML) {
					phpQuery::debug('Loading as XML failed, trying to load as HTML, isXHTML == true');
					$loaded = $this->loadMarkupHTML($markup);
				}
			} else {
				phpQuery::debug("Loading HTML, isXML() == false");
				$loaded = $this->loadMarkupHTML($markup);
			}
		}
		return $loaded;
	}
	protected function loadMarkupReset() {
		$this->isXML = $this->isXHTML = $this->isHTML = false;
	}
	protected function documentCreate($charset, $version = '1.0') {
		if (! $version)
			$version = '1.0';
		$this->document = new DOMDocument($version, $charset);
		$this->charset = $this->document->encoding;
//		$this->document->encoding = $charset;
		$this->document->formatOutput = true;
		$this->document->preserveWhiteSpace = true;
	}
	protected function loadMarkupHTML($markup, $requestedCharset = null) {
		if (phpQuery::$debug)
			phpQuery::debug('Full markup load (HTML): '.substr($markup, 0, 250));
		$this->loadMarkupReset();
		$this->isHTML = true;
		if (!isset($this->isDocumentFragment))
			$this->isDocumentFragment = self::isDocumentFragmentHTML($markup);
		$charset = null;
		$documentCharset = $this->charsetFromHTML($markup);
		$addDocumentCharset = false;
		if ($documentCharset) {
			$charset = $documentCharset;
			$markup = $this->charsetFixHTML($markup);
		} else if ($requestedCharset) {
			$charset = $requestedCharset;
		}
		if (! $charset)
			$charset = phpQuery::$defaultCharset;
		// HTTP 1.1 says that the default charset is ISO-8859-1
		// @see http://www.w3.org/International/O-HTTP-charset
		if (! $documentCharset) {
			$documentCharset = 'ISO-8859-1';
			$addDocumentCharset = true;	
		}
		// Should be careful here, still need 'magic encoding detection' since lots of pages have other 'default encoding'
		// Worse, some pages can have mixed encodings... we'll try not to worry about that
		$requestedCharset = strtoupper($requestedCharset);
		$documentCharset = strtoupper($documentCharset);
		phpQuery::debug("DOC: $documentCharset REQ: $requestedCharset");
		if ($requestedCharset && $documentCharset && $requestedCharset !== $documentCharset) {
			phpQuery::debug("CHARSET CONVERT");
			// Document Encoding Conversion
			// http://code.google.com/p/phpquery/issues/detail?id=86
			if (function_exists('mb_detect_encoding')) {
				$possibleCharsets = array($documentCharset, $requestedCharset, 'AUTO');
				$docEncoding = mb_detect_encoding($markup, implode(', ', $possibleCharsets));
				if (! $docEncoding)
					$docEncoding = $documentCharset; // ok trust the document
				phpQuery::debug("DETECTED '$docEncoding'");
				// Detected does not match what document says...
				if ($docEncoding !== $documentCharset) {
					// Tricky..
				}
				if ($docEncoding !== $requestedCharset) {
					phpQuery::debug("CONVERT $docEncoding => $requestedCharset");
					$markup = mb_convert_encoding($markup, $requestedCharset, $docEncoding);
					$markup = $this->charsetAppendToHTML($markup, $requestedCharset);
					$charset = $requestedCharset;
				}
			} else {
				phpQuery::debug("TODO: charset conversion without mbstring...");
			}
		}
		$return = false;
		if ($this->isDocumentFragment) {
			phpQuery::debug("Full markup load (HTML), DocumentFragment detected, using charset '$charset'");
			$return = $this->documentFragmentLoadMarkup($this, $charset, $markup);
		} else {
			if ($addDocumentCharset) {
				phpQuery::debug("Full markup load (HTML), appending charset: '$charset'");
				$markup = $this->charsetAppendToHTML($markup, $charset);
			}
			phpQuery::debug("Full markup load (HTML), documentCreate('$charset')");
			$this->documentCreate($charset);
			$return = phpQuery::$debug === 2
				? $this->document->loadHTML($markup)
				: @$this->document->loadHTML($markup);
			if ($return)
				$this->root = $this->document;
		}
		if ($return && ! $this->contentType)
			$this->contentType = 'text/html';
		return $return;
	}
	protected function loadMarkupXML($markup, $requestedCharset = null) {
		if (phpQuery::$debug)
			phpQuery::debug('Full markup load (XML): '.substr($markup, 0, 250));
		$this->loadMarkupReset();
		$this->isXML = true;
		// check agains XHTML in contentType or markup
		$isContentTypeXHTML = $this->isXHTML();
		$isMarkupXHTML = $this->isXHTML($markup);
		if ($isContentTypeXHTML || $isMarkupXHTML) {
			self::debug('Full markup load (XML), XHTML detected');
			$this->isXHTML = true;
		}
		// determine document fragment
		if (! isset($this->isDocumentFragment))
			$this->isDocumentFragment = $this->isXHTML
				? self::isDocumentFragmentXHTML($markup)
				: self::isDocumentFragmentXML($markup);
		// this charset will be used
		$charset = null;
		// charset from XML declaration @var string
		$documentCharset = $this->charsetFromXML($markup);
		if (! $documentCharset) {
			if ($this->isXHTML) {
				// this is XHTML, try to get charset from content-type meta header
				$documentCharset = $this->charsetFromHTML($markup);
				if ($documentCharset) {
					phpQuery::debug("Full markup load (XML), appending XHTML charset '$documentCharset'");
					$this->charsetAppendToXML($markup, $documentCharset);
					$charset = $documentCharset;
				}
			}
			if (! $documentCharset) {
				// if still no document charset...
				$charset = $requestedCharset;
			}
		} else if ($requestedCharset) {
			$charset = $requestedCharset;
		}
		if (! $charset) {
			$charset = phpQuery::$defaultCharset;
		}
		if ($requestedCharset && $documentCharset && $requestedCharset != $documentCharset) {
			// TODO place for charset conversion
//			$charset = $requestedCharset;
		}
		$return = false;
		if ($this->isDocumentFragment) {
			phpQuery::debug("Full markup load (XML), DocumentFragment detected, using charset '$charset'");
			$return = $this->documentFragmentLoadMarkup($this, $charset, $markup);
		} else {
			// FIXME ???
			if ($isContentTypeXHTML && ! $isMarkupXHTML)
			if (! $documentCharset) {
				phpQuery::debug("Full markup load (XML), appending charset '$charset'");
				$markup = $this->charsetAppendToXML($markup, $charset);
			}
			// see http://pl2.php.net/manual/en/book.dom.php#78929
			// LIBXML_DTDLOAD (>= PHP 5.1)
			// does XML ctalogues works with LIBXML_NONET
	//		$this->document->resolveExternals = true;
			// TODO test LIBXML_COMPACT for performance improvement
			// create document
			$this->documentCreate($charset);
			if (phpversion() < 5.1) {
				$this->document->resolveExternals = true;
				$return = phpQuery::$debug === 2
					? $this->document->loadXML($markup)
					: @$this->document->loadXML($markup);
			} else {
				/** @link http://pl2.php.net/manual/en/libxml.constants.php */
				$libxmlStatic = phpQuery::$debug === 2
					? LIBXML_DTDLOAD|LIBXML_DTDATTR|LIBXML_NONET
					: LIBXML_DTDLOAD|LIBXML_DTDATTR|LIBXML_NONET|LIBXML_NOWARNING|LIBXML_NOERROR;
				$return = $this->document->loadXML($markup, $libxmlStatic);
// 				if (! $return)
// 					$return = $this->document->loadHTML($markup);
			}
			if ($return)
				$this->root = $this->document;
		}
		if ($return) {
			if (! $this->contentType) {
				if ($this->isXHTML)
					$this->contentType = 'application/xhtml+xml';
				else
					$this->contentType = 'text/xml';
			}
			return $return;
		} else {
			throw new Exception("Error loading XML markup");
		}
	}
	protected function isXHTML($markup = null) {
		if (! isset($markup)) {
			return strpos($this->contentType, 'xhtml') !== false;
		}
		// XXX ok ?
		return strpos($markup, "<!DOCTYPE html") !== false;
//		return stripos($doctype, 'xhtml') !== false;
//		$doctype = isset($dom->doctype) && is_object($dom->doctype)
//			? $dom->doctype->publicId
//			: self::$defaultDoctype;
	}
	protected function isXML($markup) {
//		return strpos($markup, '<?xml') !== false && stripos($markup, 'xhtml') === false;
		return strpos(substr($markup, 0, 100), '<'.'?xml') !== false;
	}
	protected function contentTypeToArray($contentType) {
		$matches = explode(';', trim(strtolower($contentType)));
		if (isset($matches[1])) {
			$matches[1] = explode('=', $matches[1]);
			// strip 'charset='
			$matches[1] = isset($matches[1][1]) && trim($matches[1][1])
				? $matches[1][1]
				: $matches[1][0];
		} else
			$matches[1] = null;
		return $matches;
	}
	/**
	 *
	 * @param $markup
	 * @return array contentType, charset
	 */
	protected function contentTypeFromHTML($markup) {
		$matches = array();
		// find meta tag
		preg_match('@<meta[^>]+http-equiv\\s*=\\s*(["|\'])Content-Type\\1([^>]+?)>@i',
			$markup, $matches
		);
		if (! isset($matches[0]))
			return array(null, null);
		// get attr 'content'
		preg_match('@content\\s*=\\s*(["|\'])(.+?)\\1@', $matches[0], $matches);
		if (! isset($matches[0]))
			return array(null, null);
		return $this->contentTypeToArray($matches[2]);
	}
	protected function charsetFromHTML($markup) {
		$contentType = $this->contentTypeFromHTML($markup);
		return $contentType[1];
	}
	protected function charsetFromXML($markup) {
		$matches;
		// find declaration
		preg_match('@<'.'?xml[^>]+encoding\\s*=\\s*(["|\'])(.*?)\\1@i',
			$markup, $matches
		);
		return isset($matches[2])
			? strtolower($matches[2])
			: null;
	}
	/**
	 * Repositions meta[type=charset] at the start of head. Bypasses DOMDocument bug.
	 *
	 * @link http://code.google.com/p/phpquery/issues/detail?id=80
	 * @param $html
	 */
	protected function charsetFixHTML($markup) {
		$matches = array();
		// find meta tag
		preg_match('@\s*<meta[^>]+http-equiv\\s*=\\s*(["|\'])Content-Type\\1([^>]+?)>@i',
			$markup, $matches, PREG_OFFSET_CAPTURE
		);
		if (! isset($matches[0]))
			return;
		$metaContentType = $matches[0][0];
		$markup = substr($markup, 0, $matches[0][1])
			.substr($markup, $matches[0][1]+strlen($metaContentType));
		$headStart = stripos($markup, '<head>');
		$markup = substr($markup, 0, $headStart+6).$metaContentType
			.substr($markup, $headStart+6);
		return $markup;
	}
	protected function charsetAppendToHTML($html, $charset, $xhtml = false) {
		// remove existing meta[type=content-type]
		$html = preg_replace('@\s*<meta[^>]+http-equiv\\s*=\\s*(["|\'])Content-Type\\1([^>]+?)>@i', '', $html);
		$meta = '<meta http-equiv="Content-Type" content="text/html;charset='
			.$charset.'" '
			.($xhtml ? '/' : '')
			.'>';
		if (strpos($html, '<head') === false) {
			if (strpos($hltml, '<html') === false) {
				return $meta.$html;
			} else {
				return preg_replace(
					'@<html(.*?)(?(?<!\?)>)@s',
					"<html\\1><head>{$meta}</head>",
					$html
				);
			}
		} else {
			return preg_replace(
				'@<head(.*?)(?(?<!\?)>)@s',
				'<head\\1>'.$meta,
				$html
			);
		}
	}
	protected function charsetAppendToXML($markup, $charset) {
		$declaration = '<'.'?xml version="1.0" encoding="'.$charset.'"?'.'>';
		return $declaration.$markup;
	}
	public static function isDocumentFragmentHTML($markup) {
		return stripos($markup, '<html') === false && stripos($markup, '<!doctype') === false;
	}
	public static function isDocumentFragmentXML($markup) {
		return stripos($markup, '<'.'?xml') === false;
	}
	public static function isDocumentFragmentXHTML($markup) {
		return self::isDocumentFragmentHTML($markup);
	}
	public function importAttr($value) {
		// TODO
	}
	/**
	 *
	 * @param $source
	 * @param $target
	 * @param $sourceCharset
	 * @return array Array of imported nodes.
	 */
	public function import($source, $sourceCharset = null) {
		// TODO charset conversions
		$return = array();
		if ($source instanceof DOMNODE && !($source instanceof DOMNODELIST))
			$source = array($source);
//		if (is_array($source)) {
//			foreach($source as $node) {
//				if (is_string($node)) {
//					// string markup
//					$fake = $this->documentFragmentCreate($node, $sourceCharset);
//					if ($fake === false)
//						throw new Exception("Error loading documentFragment markup");
//					else
//						$return = array_merge($return, 
//							$this->import($fake->root->childNodes)
//						);
//				} else {
//					$return[] = $this->document->importNode($node, true);
//				}
//			}
//			return $return;
//		} else {
//			// string markup
//			$fake = $this->documentFragmentCreate($source, $sourceCharset);
//			if ($fake === false)
//				throw new Exception("Error loading documentFragment markup");
//			else
//				return $this->import($fake->root->childNodes);
//		}
		if (is_array($source) || $source instanceof DOMNODELIST) {
			// dom nodes
			self::debug('Importing nodes to document');
			foreach($source as $node)
				$return[] = $this->document->importNode($node, true);
		} else {
			// string markup
			$fake = $this->documentFragmentCreate($source, $sourceCharset);
			if ($fake === false)
				throw new Exception("Error loading documentFragment markup");
			else
				return $this->import($fake->root->childNodes);
		}
		return $return;
	}
	/**
	 * Creates new document fragment.
	 *
	 * @param $source
	 * @return DOMDocumentWrapper
	 */
	protected function documentFragmentCreate($source, $charset = null) {
		$fake = new DOMDocumentWrapper();
		$fake->contentType = $this->contentType;
		$fake->isXML = $this->isXML;
		$fake->isHTML = $this->isHTML;
		$fake->isXHTML = $this->isXHTML;
		$fake->root = $fake->document;
		if (! $charset)
			$charset = $this->charset;
//	$fake->documentCreate($this->charset);
		if ($source instanceof DOMNODE && !($source instanceof DOMNODELIST))
			$source = array($source);
		if (is_array($source) || $source instanceof DOMNODELIST) {
			// dom nodes
			// load fake document
			if (! $this->documentFragmentLoadMarkup($fake, $charset))
				return false;
			$nodes = $fake->import($source);
			foreach($nodes as $node)
				$fake->root->appendChild($node);
		} else {
			// string markup
			$this->documentFragmentLoadMarkup($fake, $charset, $source);
		}
		return $fake;
	}
	/**
	 *
	 * @param $document DOMDocumentWrapper
	 * @param $markup
	 * @return $document
	 */
	private function documentFragmentLoadMarkup($fragment, $charset, $markup = null) {
		// TODO error handling
		// TODO copy doctype
		// tempolary turn off
		$fragment->isDocumentFragment = false;
		if ($fragment->isXML) {
			if ($fragment->isXHTML) {
				// add FAKE element to set default namespace
				$fragment->loadMarkupXML('<?xml version="1.0" encoding="'.$charset.'"?>'
					.'<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" '
					.'"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">'
					.'<fake xmlns="http://www.w3.org/1999/xhtml">'.$markup.'</fake>');
				$fragment->root = $fragment->document->firstChild->nextSibling;
			} else {
				$fragment->loadMarkupXML('<?xml version="1.0" encoding="'.$charset.'"?><fake>'.$markup.'</fake>');
				$fragment->root = $fragment->document->firstChild;
			}
		} else {
			$markup2 = phpQuery::$defaultDoctype.'<html><head><meta http-equiv="Content-Type" content="text/html;charset='
				.$charset.'"></head>';
			$noBody = strpos($markup, '<body') === false;
			if ($noBody)
				$markup2 .= '<body>';
			$markup2 .= $markup;
			if ($noBody)
				$markup2 .= '</body>';
			$markup2 .= '</html>';
			$fragment->loadMarkupHTML($markup2);
			// TODO resolv body tag merging issue
			$fragment->root = $noBody
				? $fragment->document->firstChild->nextSibling->firstChild->nextSibling
				: $fragment->document->firstChild->nextSibling->firstChild->nextSibling;
		}
		if (! $fragment->root)
			return false;
		$fragment->isDocumentFragment = true;
		return true;
	}
	protected function documentFragmentToMarkup($fragment) {
		phpQuery::debug('documentFragmentToMarkup');
		$tmp = $fragment->isDocumentFragment;
		$fragment->isDocumentFragment = false;
		$markup = $fragment->markup();
		if ($fragment->isXML) {
			$markup = substr($markup, 0, strrpos($markup, '</fake>'));
			if ($fragment->isXHTML) {
				$markup = substr($markup, strpos($markup, '<fake')+43);
			} else {
				$markup = substr($markup, strpos($markup, '<fake>')+6);
			}
		} else {
				$markup = substr($markup, strpos($markup, '<body>')+6);
				$markup = substr($markup, 0, strrpos($markup, '</body>'));
		}
		$fragment->isDocumentFragment = $tmp;
		if (phpQuery::$debug)
			phpQuery::debug('documentFragmentToMarkup: '.substr($markup, 0, 150));
		return $markup;
	}
	/**
	 * Return document markup, starting with optional $nodes as root.
	 *
	 * @param $nodes	DOMNode|DOMNodeList
	 * @return string
	 */
	public function markup($nodes = null, $innerMarkup = false) {
		if (isset($nodes) && count($nodes) == 1 && $nodes[0] instanceof DOMDOCUMENT)
			$nodes = null;
		if (isset($nodes)) {
			$markup = '';
			if (!is_array($nodes) && !($nodes instanceof DOMNODELIST) )
				$nodes = array($nodes);
			if ($this->isDocumentFragment && ! $innerMarkup)
				foreach($nodes as $i => $node)
					if ($node->isSameNode($this->root)) {
					//	var_dump($node);
						$nodes = array_slice($nodes, 0, $i)
							+ phpQuery::DOMNodeListToArray($node->childNodes)
							+ array_slice($nodes, $i+1);
						}
			if ($this->isXML && ! $innerMarkup) {
				self::debug("Getting outerXML with charset '{$this->charset}'");
				// we need outerXML, so we can benefit from
				// $node param support in saveXML()
				foreach($nodes as $node)
					$markup .= $this->document->saveXML($node);
			} else {
				$loop = array();
				if ($innerMarkup)
					foreach($nodes as $node) {
						if ($node->childNodes)
							foreach($node->childNodes as $child)
								$loop[] = $child;
						else
							$loop[] = $node;
					}
				else
					$loop = $nodes;
				self::debug("Getting markup, moving selected nodes (".count($loop).") to new DocumentFragment");
				$fake = $this->documentFragmentCreate($loop);
				$markup = $this->documentFragmentToMarkup($fake);
			}
			if ($this->isXHTML) {
				self::debug("Fixing XHTML");
				$markup = self::markupFixXHTML($markup);
			}
			self::debug("Markup: ".substr($markup, 0, 250));
			return $markup;
		} else {
			if ($this->isDocumentFragment) {
				// documentFragment, html only...
				self::debug("Getting markup, DocumentFragment detected");
//				return $this->markup(
////					$this->document->getElementsByTagName('body')->item(0)
//					$this->document->root, true
//				);
				$markup = $this->documentFragmentToMarkup($this);
				// no need for markupFixXHTML, as it's done thought markup($nodes) method
				return $markup;
			} else {
				self::debug("Getting markup (".($this->isXML?'XML':'HTML')."), final with charset '{$this->charset}'");
				$markup = $this->isXML
					? $this->document->saveXML()
					: $this->document->saveHTML();
				if ($this->isXHTML) {
					self::debug("Fixing XHTML");
					$markup = self::markupFixXHTML($markup);
				}
				self::debug("Markup: ".substr($markup, 0, 250));
				return $markup;
			}
		}
	}
	protected static function markupFixXHTML($markup) {
		$markup = self::expandEmptyTag('script', $markup);
		$markup = self::expandEmptyTag('select', $markup);
		$markup = self::expandEmptyTag('textarea', $markup);
		return $markup;
	}
	public static function debug($text) {
		phpQuery::debug($text);
	}
	/**
	 * expandEmptyTag
	 *
	 * @param $tag
	 * @param $xml
	 * @return unknown_type
	 * @author mjaque at ilkebenson dot com
	 * @link http://php.net/manual/en/domdocument.savehtml.php#81256
	 */
	public static function expandEmptyTag($tag, $xml){
        $indice = 0;
        while ($indice< strlen($xml)){
            $pos = strpos($xml, "<$tag ", $indice);
            if ($pos){
                $posCierre = strpos($xml, ">", $pos);
                if ($xml[$posCierre-1] == "/"){
                    $xml = substr_replace($xml, "></$tag>", $posCierre-1, 2);
                }
                $indice = $posCierre;
            }
            else break;
        }
        return $xml;
	}
}

/**
 * Event handling class.
 *
 * @author Tobiasz Cudnik
 * @package phpQuery
 * @static
 */
abstract class phpQueryEvents {
	/**
	 * Trigger a type of event on every matched element.
	 *
	 * @param DOMNode|phpQueryObject|string $document
	 * @param unknown_type $type
	 * @param unknown_type $data
	 *
	 * @TODO exclusive events (with !)
	 * @TODO global events (test)
	 * @TODO support more than event in $type (space-separated)
	 */
	public static function trigger($document, $type, $data = array(), $node = null) {
		// trigger: function(type, data, elem, donative, extra) {
		$documentID = phpQuery::getDocumentID($document);
		$namespace = null;
		if (strpos($type, '.') !== false)
			list($name, $namespace) = explode('.', $type);
		else
			$name = $type;
		if (! $node) {
			if (self::issetGlobal($documentID, $type)) {
				$pq = phpQuery::getDocument($documentID);
				// TODO check add($pq->document)
				$pq->find('*')->add($pq->document)
					->trigger($type, $data);
			}
		} else {
			if (isset($data[0]) && $data[0] instanceof DOMEvent) {
				$event = $data[0];
				$event->relatedTarget = $event->target;
				$event->target = $node;
				$data = array_slice($data, 1);
			} else {
				$event = new DOMEvent(array(
					'type' => $type,
					'target' => $node,
					'timeStamp' => time(),
				));
			}
			$i = 0;
			while($node) {
				// TODO whois
				phpQuery::debug("Triggering ".($i?"bubbled ":'')."event '{$type}' on "
					."node \n");//.phpQueryObject::whois($node)."\n");
				$event->currentTarget = $node;
				$eventNode = self::getNode($documentID, $node);
				if (isset($eventNode->eventHandlers)) {
					foreach($eventNode->eventHandlers as $eventType => $handlers) {
						$eventNamespace = null;
						if (strpos($type, '.') !== false)
							list($eventName, $eventNamespace) = explode('.', $eventType);
						else
							$eventName = $eventType;
						if ($name != $eventName)
							continue;
						if ($namespace && $eventNamespace && $namespace != $eventNamespace)
							continue;
						foreach($handlers as $handler) {
							phpQuery::debug("Calling event handler\n");
							$event->data = $handler['data']
								? $handler['data']
								: null;
							$params = array_merge(array($event), $data);
							$return = phpQuery::callbackRun($handler['callback'], $params);
							if ($return === false) {
								$event->bubbles = false;
							}
						}
					}
				}
				// to bubble or not to bubble...
				if (! $event->bubbles)
					break;
				$node = $node->parentNode;
				$i++;
			}
		}
	}
	/**
	 * Binds a handler to one or more events (like click) for each matched element.
	 * Can also bind custom events.
	 *
	 * @param DOMNode|phpQueryObject|string $document
	 * @param unknown_type $type
	 * @param unknown_type $data Optional
	 * @param unknown_type $callback
	 *
	 * @TODO support '!' (exclusive) events
	 * @TODO support more than event in $type (space-separated)
	 * @TODO support binding to global events
	 */
	public static function add($document, $node, $type, $data, $callback = null) {
		phpQuery::debug("Binding '$type' event");
		$documentID = phpQuery::getDocumentID($document);
//		if (is_null($callback) && is_callable($data)) {
//			$callback = $data;
//			$data = null;
//		}
		$eventNode = self::getNode($documentID, $node);
		if (! $eventNode)
			$eventNode = self::setNode($documentID, $node);
		if (!isset($eventNode->eventHandlers[$type]))
			$eventNode->eventHandlers[$type] = array();
		$eventNode->eventHandlers[$type][] = array(
			'callback' => $callback,
			'data' => $data,
		);
	}
	/**
	 * Enter description here...
	 *
	 * @param DOMNode|phpQueryObject|string $document
	 * @param unknown_type $type
	 * @param unknown_type $callback
	 *
	 * @TODO namespace events
	 * @TODO support more than event in $type (space-separated)
	 */
	public static function remove($document, $node, $type = null, $callback = null) {
		$documentID = phpQuery::getDocumentID($document);
		$eventNode = self::getNode($documentID, $node);
		if (is_object($eventNode) && isset($eventNode->eventHandlers[$type])) {
			if ($callback) {
				foreach($eventNode->eventHandlers[$type] as $k => $handler)
					if ($handler['callback'] == $callback)
						unset($eventNode->eventHandlers[$type][$k]);
			} else {
				unset($eventNode->eventHandlers[$type]);
			}
		}
	}
	protected static function getNode($documentID, $node) {
		foreach(phpQuery::$documents[$documentID]->eventsNodes as $eventNode) {
			if ($node->isSameNode($eventNode))
				return $eventNode;
		}
	}
	protected static function setNode($documentID, $node) {
		phpQuery::$documents[$documentID]->eventsNodes[] = $node;
		return phpQuery::$documents[$documentID]->eventsNodes[
			count(phpQuery::$documents[$documentID]->eventsNodes)-1
		];
	}
	protected static function issetGlobal($documentID, $type) {
		return isset(phpQuery::$documents[$documentID])
			? in_array($type, phpQuery::$documents[$documentID]->eventsGlobal)
			: false;
	}
}


interface ICallbackNamed {
	function hasName();
	function getName();
}
/**
 * Callback class introduces currying-like pattern.
 * 
 * Example:
 * function foo($param1, $param2, $param3) {
 *   var_dump($param1, $param2, $param3);
 * }
 * $fooCurried = new Callback('foo', 
 *   'param1 is now statically set', 
 *   new CallbackParam, new CallbackParam
 * );
 * phpQuery::callbackRun($fooCurried,
 * 	array('param2 value', 'param3 value'
 * );
 * 
 * Callback class is supported in all phpQuery methods which accepts callbacks. 
 *
 * @link http://code.google.com/p/phpquery/wiki/Callbacks#Param_Structures
 * @author Tobiasz Cudnik <tobiasz.cudnik/gmail.com>
 * 
 * @TODO??? return fake forwarding function created via create_function
 * @TODO honor paramStructure
 */
class Callback
	implements ICallbackNamed {
	public $callback = null;
	public $params = null;
	protected $name;
	public function __construct($callback, $param1 = null, $param2 = null, 
			$param3 = null) {
		$params = func_get_args();
		$params = array_slice($params, 1);
		if ($callback instanceof Callback) {
			// TODO implement recurention
		} else {
			$this->callback = $callback;
			$this->params = $params;
		}
	}
	public function getName() {
		return 'Callback: '.$this->name;
	}
	public function hasName() {
		return isset($this->name) && $this->name;
	}
	public function setName($name) {
		$this->name = $name;
		return $this;
	}
	// TODO test me
//	public function addParams() {
//		$params = func_get_args();
//		return new Callback($this->callback, $this->params+$params);
//	}
}
/**
 * Shorthand for new Callback(create_function(...), ...);
 * 
 * @author Tobiasz Cudnik <tobiasz.cudnik/gmail.com>
 */
class CallbackBody extends Callback {
	public function __construct($paramList, $code, $param1 = null, $param2 = null, 
			$param3 = null) {
		$params = func_get_args();
		$params = array_slice($params, 2);
		$this->callback = create_function($paramList, $code);
		$this->params = $params;
	}
}
/**
 * Callback type which on execution returns reference passed during creation.
 * 
 * @author Tobiasz Cudnik <tobiasz.cudnik/gmail.com>
 */
class CallbackReturnReference extends Callback
	implements ICallbackNamed {
	protected $reference;
	public function __construct(&$reference, $name = null){
		$this->reference =& $reference;
		$this->callback = array($this, 'callback');
	}
	public function callback() {
		return $this->reference;
	}
	public function getName() {
		return 'Callback: '.$this->name;
	}
	public function hasName() {
		return isset($this->name) && $this->name;
	}
}
/**
 * Callback type which on execution returns value passed during creation.
 * 
 * @author Tobiasz Cudnik <tobiasz.cudnik/gmail.com>
 */
class CallbackReturnValue extends Callback
	implements ICallbackNamed {
	protected $value;
	protected $name;
	public function __construct($value, $name = null){
		$this->value =& $value;
		$this->name = $name;
		$this->callback = array($this, 'callback');
	}
	public function callback() {
		return $this->value;
	}
	public function __toString() {
		return $this->getName();
	}
	public function getName() {
		return 'Callback: '.$this->name;
	}
	public function hasName() {
		return isset($this->name) && $this->name;
	}
}
/**
 * CallbackParameterToReference can be used when we don't really want a callback,
 * only parameter passed to it. CallbackParameterToReference takes first 
 * parameter's value and passes it to reference.
 *
 * @author Tobiasz Cudnik <tobiasz.cudnik/gmail.com>
 */
class CallbackParameterToReference extends Callback {
	/**
	 * @param $reference
	 * @TODO implement $paramIndex; 
	 * param index choose which callback param will be passed to reference
	 */
	public function __construct(&$reference){
		$this->callback =& $reference;
	}
}
//class CallbackReference extends Callback {
//	/**
//	 *
//	 * @param $reference
//	 * @param $paramIndex
//	 * @todo implement $paramIndex; param index choose which callback param will be passed to reference
//	 */
//	public function __construct(&$reference, $name = null){
//		$this->callback =& $reference;
//	}
//}
class CallbackParam {}

/**
 * Class representing phpQuery objects.
 *
 * @author Tobiasz Cudnik <tobiasz.cudnik/gmail.com>
 * @package phpQuery
 * @method phpQueryObject clone() clone()
 * @method phpQueryObject empty() empty()
 * @method phpQueryObject next() next($selector = null)
 * @method phpQueryObject prev() prev($selector = null)
 * @property Int $length
 */
class phpQueryObject
	implements Iterator, Countable, ArrayAccess {
	public $documentID = null;
	/**
	 * DOMDocument class.
	 *
	 * @var DOMDocument
	 */
	public $document = null;
	public $charset = null;
	/**
	 *
	 * @var DOMDocumentWrapper
	 */
	public $documentWrapper = null;
	/**
	 * XPath interface.
	 *
	 * @var DOMXPath
	 */
	public $xpath = null;
	/**
	 * Stack of selected elements.
	 * @TODO refactor to ->nodes
	 * @var array
	 */
	public $elements = array();
	/**
	 * @access private
	 */
	protected $elementsBackup = array();
	/**
	 * @access private
	 */
	protected $previous = null;
	/**
	 * @access private
	 * @TODO deprecate
	 */
	protected $root = array();
	/**
	 * Indicated if doument is just a fragment (no <html> tag).
	 *
	 * Every document is realy a full document, so even documentFragments can
	 * be queried against <html>, but getDocument(id)->htmlOuter() will return
	 * only contents of <body>.
	 *
	 * @var bool
	 */
	public $documentFragment = true;
	/**
	 * Iterator interface helper
	 * @access private
	 */
	protected $elementsInterator = array();
	/**
	 * Iterator interface helper
	 * @access private
	 */
	protected $valid = false;
	/**
	 * Iterator interface helper
	 * @access private
	 */
	protected $current = null;
	/**
	 * Enter description here...
	 *
	 * @return phpQueryObject|QueryTemplatesSource|QueryTemplatesParse|QueryTemplatesSourceQuery
	 */
	public function __construct($documentID) {
//		if ($documentID instanceof self)
//			var_dump($documentID->getDocumentID());
		$id = $documentID instanceof self
			? $documentID->getDocumentID()
			: $documentID;
//		var_dump($id);
		if (! isset(phpQuery::$documents[$id] )) {
//			var_dump(phpQuery::$documents);
			throw new Exception("Document with ID '{$id}' isn't loaded. Use phpQuery::newDocument(\$html) or phpQuery::newDocumentFile(\$file) first.");
		}
		$this->documentID = $id;
		$this->documentWrapper =& phpQuery::$documents[$id];
		$this->document =& $this->documentWrapper->document;
		$this->xpath =& $this->documentWrapper->xpath;
		$this->charset =& $this->documentWrapper->charset;
		$this->documentFragment =& $this->documentWrapper->isDocumentFragment;
		// TODO check $this->DOM->documentElement;
//		$this->root = $this->document->documentElement;
		$this->root =& $this->documentWrapper->root;
//		$this->toRoot();
		$this->elements = array($this->root);
	}
	/**
	 *
	 * @access private
	 * @param $attr
	 * @return unknown_type
	 */
	public function __get($attr) {
		switch($attr) {
			// FIXME doesnt work at all ?
			case 'length':
				return $this->size();
			break;
			default:
				return $this->$attr;
		}
	}
	/**
	 * Saves actual object to $var by reference.
	 * Useful when need to break chain.
	 * @param phpQueryObject $var
	 * @return phpQueryObject|QueryTemplatesSource|QueryTemplatesParse|QueryTemplatesSourceQuery
	 */
	public function toReference(&$var) {
		return $var = $this;
	}
	public function documentFragment($state = null) {
		if ($state) {
			phpQuery::$documents[$this->getDocumentID()]['documentFragment'] = $state;
			return $this;
		}
		return $this->documentFragment;
	}
	/**
   * @access private
   * @TODO documentWrapper
	 */
	protected function isRoot( $node) {
//		return $node instanceof DOMDOCUMENT || $node->tagName == 'html';
		return $node instanceof DOMDOCUMENT
			|| ($node instanceof DOMELEMENT && $node->tagName == 'html')
			|| $this->root->isSameNode($node);
	}
	/**
   * @access private
	 */
	protected function stackIsRoot() {
		return $this->size() == 1 && $this->isRoot($this->elements[0]);
	}
	/**
	 * Enter description here...
	 * NON JQUERY METHOD
	 *
	 * Watch out, it doesn't creates new instance, can be reverted with end().
	 *
	 * @return phpQueryObject|QueryTemplatesSource|QueryTemplatesParse|QueryTemplatesSourceQuery
	 */
	public function toRoot() {
		$this->elements = array($this->root);
		return $this;
//		return $this->newInstance(array($this->root));
	}
	/**
	 * Saves object's DocumentID to $var by reference.
	 * <code>
	 * $myDocumentId;
	 * phpQuery::newDocument('<div/>')
	 *     ->getDocumentIDRef($myDocumentId)
	 *     ->find('div')->...
	 * </code>
	 *
	 * @param unknown_type $domId
	 * @see phpQuery::newDocument
	 * @see phpQuery::newDocumentFile
	 * @return phpQueryObject|QueryTemplatesSource|QueryTemplatesParse|QueryTemplatesSourceQuery
	 */
	public function getDocumentIDRef(&$documentID) {
		$documentID = $this->getDocumentID();
		return $this;
	}
	/**
	 * Returns object with stack set to document root.
	 *
	 * @return phpQueryObject|QueryTemplatesSource|QueryTemplatesParse|QueryTemplatesSourceQuery
	 */
	public function getDocument() {
		return phpQuery::getDocument($this->getDocumentID());
	}
	/**
	 *
	 * @return DOMDocument
	 */
	public function getDOMDocument() {
		return $this->document;
	}
	/**
	 * Get object's Document ID.
	 *
	 * @return phpQueryObject|QueryTemplatesSource|QueryTemplatesParse|QueryTemplatesSourceQuery
	 */
	public function getDocumentID() {
		return $this->documentID;
	}
	/**
	 * Unloads whole document from memory.
	 * CAUTION! None further operations will be possible on this document.
	 * All objects refering to it will be useless.
	 *
	 * @return phpQueryObject|QueryTemplatesSource|QueryTemplatesParse|QueryTemplatesSourceQuery
	 */
	public function unloadDocument() {
		phpQuery::unloadDocuments($this->getDocumentID());
	}
	public function isHTML() {
		return $this->documentWrapper->isHTML;
	}
	public function isXHTML() {
		return $this->documentWrapper->isXHTML;
	}
	public function isXML() {
		return $this->documentWrapper->isXML;
	}
	/**
	 * Enter description here...
	 *
	 * @link http://docs.jquery.com/Ajax/serialize
	 * @return string
	 */
	public function serialize() {
		return phpQuery::param($this->serializeArray());
	}
	/**
	 * Enter description here...
	 *
	 * @link http://docs.jquery.com/Ajax/serializeArray
	 * @return array
	 */
	public function serializeArray($submit = null) {
		$source = $this->filter('form, input, select, textarea')
			->find('input, select, textarea')
			->andSelf()
			->not('form');
		$return = array();
//		$source->dumpDie();
		foreach($source as $input) {
			$input = phpQuery::pq($input);
			if ($input->is('[disabled]'))
				continue;
			if (!$input->is('[name]'))
				continue;
			if ($input->is('[type=checkbox]') && !$input->is('[checked]'))
				continue;
			// jquery diff
			if ($submit && $input->is('[type=submit]')) {
				if ($submit instanceof DOMELEMENT && ! $input->elements[0]->isSameNode($submit))
					continue;
				else if (is_string($submit) && $input->attr('name') != $submit)
					continue;
			}
			$return[] = array(
				'name' => $input->attr('name'),
				'value' => $input->val(),
			);
		}
		return $return;
	}
	/**
	 * @access private
	 */
	protected function debug($in) {
		if (! phpQuery::$debug )
			return;
		print('<pre>');
		print_r($in);
		// file debug
//		file_put_contents(dirname(__FILE__).'/phpQuery.log', print_r($in, true)."\n", FILE_APPEND);
		// quite handy debug trace
//		if ( is_array($in))
//			print_r(array_slice(debug_backtrace(), 3));
		print("</pre>\n");
	}
	/**
	 * @access private
	 */
	protected function isRegexp($pattern) {
		return in_array(
			$pattern[ mb_strlen($pattern)-1 ],
			array('^','*','$')
		);
	}
	/**
	 * Determines if $char is really a char.
	 *
	 * @param string $char
	 * @return bool
	 * @todo rewrite me to charcode range ! ;)
	 * @access private
	 */
	protected function isChar($char) {
		return extension_loaded('mbstring') && phpQuery::$mbstringSupport
			? mb_eregi('\w', $char)
			: preg_match('@\w@', $char);
	}
	/**
	 * @access private
	 */
	protected function parseSelector($query) {
		// clean spaces
		// TODO include this inside parsing ?
		$query = trim(
			preg_replace('@\s+@', ' ',
				preg_replace('@\s*(>|\\+|~)\s*@', '\\1', $query)
			)
		);
		$queries = array(array());
		if (! $query)
			return $queries;
		$return =& $queries[0];
		$specialChars = array('>',' ');
//		$specialCharsMapping = array('/' => '>');
		$specialCharsMapping = array();
		$strlen = mb_strlen($query);
		$classChars = array('.', '-');
		$pseudoChars = array('-');
		$tagChars = array('*', '|', '-');
		// split multibyte string
		// http://code.google.com/p/phpquery/issues/detail?id=76
		$_query = array();
		for ($i=0; $i<$strlen; $i++)
			$_query[] = mb_substr($query, $i, 1);
		$query = $_query;
		// it works, but i dont like it...
		$i = 0;
		while( $i < $strlen) {
			$c = $query[$i];
			$tmp = '';
			// TAG
			if ($this->isChar($c) || in_array($c, $tagChars)) {
				while(isset($query[$i])
					&& ($this->isChar($query[$i]) || in_array($query[$i], $tagChars))) {
					$tmp .= $query[$i];
					$i++;
				}
				$return[] = $tmp;
			// IDs
			} else if ( $c == '#') {
				$i++;
				while( isset($query[$i]) && ($this->isChar($query[$i]) || $query[$i] == '-')) {
					$tmp .= $query[$i];
					$i++;
				}
				$return[] = '#'.$tmp;
			// SPECIAL CHARS
			} else if (in_array($c, $specialChars)) {
				$return[] = $c;
				$i++;
			// MAPPED SPECIAL MULTICHARS
//			} else if ( $c.$query[$i+1] == '//') {
//				$return[] = ' ';
//				$i = $i+2;
			// MAPPED SPECIAL CHARS
			} else if ( isset($specialCharsMapping[$c])) {
				$return[] = $specialCharsMapping[$c];
				$i++;
			// COMMA
			} else if ( $c == ',') {
				$queries[] = array();
				$return =& $queries[ count($queries)-1 ];
				$i++;
				while( isset($query[$i]) && $query[$i] == ' ')
					$i++;
			// CLASSES
			} else if ($c == '.') {
				while( isset($query[$i]) && ($this->isChar($query[$i]) || in_array($query[$i], $classChars))) {
					$tmp .= $query[$i];
					$i++;
				}
				$return[] = $tmp;
			// ~ General Sibling Selector
			} else if ($c == '~') {
				$spaceAllowed = true;
				$tmp .= $query[$i++];
				while( isset($query[$i])
					&& ($this->isChar($query[$i])
						|| in_array($query[$i], $classChars)
						|| $query[$i] == '*'
						|| ($query[$i] == ' ' && $spaceAllowed)
					)) {
					if ($query[$i] != ' ')
						$spaceAllowed = false;
					$tmp .= $query[$i];
					$i++;
				}
				$return[] = $tmp;
			// + Adjacent sibling selectors
			} else if ($c == '+') {
				$spaceAllowed = true;
				$tmp .= $query[$i++];
				while( isset($query[$i])
					&& ($this->isChar($query[$i])
						|| in_array($query[$i], $classChars)
						|| $query[$i] == '*'
						|| ($spaceAllowed && $query[$i] == ' ')
					)) {
					if ($query[$i] != ' ')
						$spaceAllowed = false;
					$tmp .= $query[$i];
					$i++;
				}
				$return[] = $tmp;
			// ATTRS
			} else if ($c == '[') {
				$stack = 1;
				$tmp .= $c;
				while( isset($query[++$i])) {
					$tmp .= $query[$i];
					if ( $query[$i] == '[') {
						$stack++;
					} else if ( $query[$i] == ']') {
						$stack--;
						if (! $stack )
							break;
					}
				}
				$return[] = $tmp;
				$i++;
			// PSEUDO CLASSES
			} else if ($c == ':') {
				$stack = 1;
				$tmp .= $query[$i++];
				while( isset($query[$i]) && ($this->isChar($query[$i]) || in_array($query[$i], $pseudoChars))) {
					$tmp .= $query[$i];
					$i++;
				}
				// with arguments ?
				if ( isset($query[$i]) && $query[$i] == '(') {
					$tmp .= $query[$i];
					$stack = 1;
					while( isset($query[++$i])) {
						$tmp .= $query[$i];
						if ( $query[$i] == '(') {
							$stack++;
						} else if ( $query[$i] == ')') {
							$stack--;
							if (! $stack )
								break;
						}
					}
					$return[] = $tmp;
					$i++;
				} else {
					$return[] = $tmp;
				}
			} else {
				$i++;
			}
		}
		foreach($queries as $k => $q) {
			if (isset($q[0])) {
				if (isset($q[0][0]) && $q[0][0] == ':')
					array_unshift($queries[$k], '*');
				if ($q[0] != '>')
					array_unshift($queries[$k], ' ');
			}
		}
		return $queries;
	}
	/**
	 * Return matched DOM nodes.
	 *
	 * @param int $index
	 * @return array|DOMElement Single DOMElement or array of DOMElement.
	 */
	public function get($index = null, $callback1 = null, $callback2 = null, $callback3 = null) {
		$return = isset($index)
			? (isset($this->elements[$index]) ? $this->elements[$index] : null)
			: $this->elements;
		// pass thou callbacks
		$args = func_get_args();
		$args = array_slice($args, 1);
		foreach($args as $callback) {
			if (is_array($return))
				foreach($return as $k => $v)
					$return[$k] = phpQuery::callbackRun($callback, array($v));
			else
				$return = phpQuery::callbackRun($callback, array($return));
		}
		return $return;
	}
	/**
	 * Return matched DOM nodes.
	 * jQuery difference.
	 *
	 * @param int $index
	 * @return array|string Returns string if $index != null
	 * @todo implement callbacks
	 * @todo return only arrays ?
	 * @todo maybe other name...
	 */
	public function getString($index = null, $callback1 = null, $callback2 = null, $callback3 = null) {
		if ($index)
			$return = $this->eq($index)->text();
		else {
			$return = array();
			for($i = 0; $i < $this->size(); $i++) {
				$return[] = $this->eq($i)->text();
			}
		}
		// pass thou callbacks
		$args = func_get_args();
		$args = array_slice($args, 1);
		foreach($args as $callback) {
			$return = phpQuery::callbackRun($callback, array($return));
		}
		return $return;
	}
	/**
	 * Return matched DOM nodes.
	 * jQuery difference.
	 *
	 * @param int $index
	 * @return array|string Returns string if $index != null
	 * @todo implement callbacks
	 * @todo return only arrays ?
	 * @todo maybe other name...
	 */
	public function getStrings($index = null, $callback1 = null, $callback2 = null, $callback3 = null) {
		if ($index)
			$return = $this->eq($index)->text();
		else {
			$return = array();
			for($i = 0; $i < $this->size(); $i++) {
				$return[] = $this->eq($i)->text();
			}
			// pass thou callbacks
			$args = func_get_args();
			$args = array_slice($args, 1);
		}
		foreach($args as $callback) {
			if (is_array($return))
				foreach($return as $k => $v)
					$return[$k] = phpQuery::callbackRun($callback, array($v));
			else
				$return = phpQuery::callbackRun($callback, array($return));
		}
		return $return;
	}
	/**
	 * Returns new instance of actual class.
	 *
	 * @param array $newStack Optional. Will replace old stack with new and move old one to history.c
	 */
	public function newInstance($newStack = null) {
		$class = get_class($this);
		// support inheritance by passing old object to overloaded constructor
		$new = $class != 'phpQuery'
			? new $class($this, $this->getDocumentID())
			: new phpQueryObject($this->getDocumentID());
		$new->previous = $this;
		if (is_null($newStack)) {
			$new->elements = $this->elements;
			if ($this->elementsBackup)
				$this->elements = $this->elementsBackup;
		} else if (is_string($newStack)) {
			$new->elements = phpQuery::pq($newStack, $this->getDocumentID())->stack();
		} else {
			$new->elements = $newStack;
		}
		return $new;
	}
	/**
	 * Enter description here...
	 *
	 * In the future, when PHP will support XLS 2.0, then we would do that this way:
	 * contains(tokenize(@class, '\s'), "something")
	 * @param unknown_type $class
	 * @param unknown_type $node
	 * @return boolean
	 * @access private
	 */
	protected function matchClasses($class, $node) {
		// multi-class
		if ( mb_strpos($class, '.', 1)) {
			$classes = explode('.', substr($class, 1));
			$classesCount = count( $classes );
			$nodeClasses = explode(' ', $node->getAttribute('class') );
			$nodeClassesCount = count( $nodeClasses );
			if ( $classesCount > $nodeClassesCount )
				return false;
			$diff = count(
				array_diff(
					$classes,
					$nodeClasses
				)
			);
			if (! $diff )
				return true;
		// single-class
		} else {
			return in_array(
				// strip leading dot from class name
				substr($class, 1),
				// get classes for element as array
				explode(' ', $node->getAttribute('class') )
			);
		}
	}
	/**
	 * @access private
	 */
	protected function runQuery($XQuery, $selector = null, $compare = null) {
		if ($compare && ! method_exists($this, $compare))
			return false;
		$stack = array();
		if (! $this->elements)
			$this->debug('Stack empty, skipping...');
//		var_dump($this->elements[0]->nodeType);
		// element, document
		foreach($this->stack(array(1, 9, 13)) as $k => $stackNode) {
			$detachAfter = false;
			// to work on detached nodes we need temporary place them somewhere
			// thats because context xpath queries sucks ;]
			$testNode = $stackNode;
			while ($testNode) {
				if (! $testNode->parentNode && ! $this->isRoot($testNode)) {
					$this->root->appendChild($testNode);
					$detachAfter = $testNode;
					break;
				}
				$testNode = isset($testNode->parentNode)
					? $testNode->parentNode
					: null;
			}
			// XXX tmp ?
			$xpath = $this->documentWrapper->isXHTML
				? $this->getNodeXpath($stackNode, 'html')
				: $this->getNodeXpath($stackNode);
			// FIXME pseudoclasses-only query, support XML
			$query = $XQuery == '//' && $xpath == '/html[1]'
				? '//*'
				: $xpath.$XQuery;
			$this->debug("XPATH: {$query}");
			// run query, get elements
			$nodes = $this->xpath->query($query);
			$this->debug("QUERY FETCHED");
			if (! $nodes->length )
				$this->debug('Nothing found');
			$debug = array();
			foreach($nodes as $node) {
				$matched = false;
				if ( $compare) {
					phpQuery::$debug ?
						$this->debug("Found: ".$this->whois( $node ).", comparing with {$compare}()")
						: null;
					$phpQueryDebug = phpQuery::$debug;
					phpQuery::$debug = false;
					// TODO ??? use phpQuery::callbackRun()
					if (call_user_func_array(array($this, $compare), array($selector, $node)))
						$matched = true;
					phpQuery::$debug = $phpQueryDebug;
				} else {
					$matched = true;
				}
				if ( $matched) {
					if (phpQuery::$debug)
						$debug[] = $this->whois( $node );
					$stack[] = $node;
				}
			}
			if (phpQuery::$debug) {
				$this->debug("Matched ".count($debug).": ".implode(', ', $debug));
			}
			if ($detachAfter)
				$this->root->removeChild($detachAfter);
		}
		$this->elements = $stack;
	}
	/**
	 * Enter description here...
	 *
	 * @return phpQueryObject|QueryTemplatesSource|QueryTemplatesParse|QueryTemplatesSourceQuery
	 */
	public function find($selectors, $context = null, $noHistory = false) {
		if (!$noHistory)
			// backup last stack /for end()/
			$this->elementsBackup = $this->elements;
		// allow to define context
		// TODO combine code below with phpQuery::pq() context guessing code
		//   as generic function
		if ($context) {
			if (! is_array($context) && $context instanceof DOMELEMENT)
				$this->elements = array($context);
			else if (is_array($context)) {
				$this->elements = array();
				foreach ($context as $c)
					if ($c instanceof DOMELEMENT)
						$this->elements[] = $c;
			} else if ( $context instanceof self )
				$this->elements = $context->elements;
		}
		$queries = $this->parseSelector($selectors);
		$this->debug(array('FIND', $selectors, $queries));
		$XQuery = '';
		// remember stack state because of multi-queries
		$oldStack = $this->elements;
		// here we will be keeping found elements
		$stack = array();
		foreach($queries as $selector) {
			$this->elements = $oldStack;
			$delimiterBefore = false;
			foreach($selector as $s) {
				// TAG
				$isTag = extension_loaded('mbstring') && phpQuery::$mbstringSupport
					? mb_ereg_match('^[\w|\||-]+$', $s) || $s == '*'
					: preg_match('@^[\w|\||-]+$@', $s) || $s == '*';
				if ($isTag) {
					if ($this->isXML()) {
						// namespace support
						if (mb_strpos($s, '|') !== false) {
							$ns = $tag = null;
							list($ns, $tag) = explode('|', $s);
							$XQuery .= "$ns:$tag";
						} else if ($s == '*') {
							$XQuery .= "*";
						} else {
							$XQuery .= "*[local-name()='$s']";
						}
					} else {
						$XQuery .= $s;
					}
				// ID
				} else if ($s[0] == '#') {
					if ($delimiterBefore)
						$XQuery .= '*';
					$XQuery .= "[@id='".substr($s, 1)."']";
				// ATTRIBUTES
				} else if ($s[0] == '[') {
					if ($delimiterBefore)
						$XQuery .= '*';
					// strip side brackets
					$attr = trim($s, '][');
					$execute = false;
					// attr with specifed value
					if (mb_strpos($s, '=')) {
						$value = null;
						list($attr, $value) = explode('=', $attr);
						$value = trim($value, "'\"");
						if ($this->isRegexp($attr)) {
							// cut regexp character
							$attr = substr($attr, 0, -1);
							$execute = true;
							$XQuery .= "[@{$attr}]";
						} else {
							$XQuery .= "[@{$attr}='{$value}']";
						}
					// attr without specified value
					} else {
						$XQuery .= "[@{$attr}]";
					}
					if ($execute) {
						$this->runQuery($XQuery, $s, 'is');
						$XQuery = '';
						if (! $this->length())
							break;
					}
				// CLASSES
				} else if ($s[0] == '.') {
					// TODO use return $this->find("./self::*[contains(concat(\" \",@class,\" \"), \" $class \")]");
					// thx wizDom ;)
					if ($delimiterBefore)
						$XQuery .= '*';
					$XQuery .= '[@class]';
					$this->runQuery($XQuery, $s, 'matchClasses');
					$XQuery = '';
					if (! $this->length() )
						break;
				// ~ General Sibling Selector
				} else if ($s[0] == '~') {
					$this->runQuery($XQuery);
					$XQuery = '';
					$this->elements = $this
						->siblings(
							substr($s, 1)
						)->elements;
					if (! $this->length() )
						break;
				// + Adjacent sibling selectors
				} else if ($s[0] == '+') {
					// TODO /following-sibling::
					$this->runQuery($XQuery);
					$XQuery = '';
					$subSelector = substr($s, 1);
					$subElements = $this->elements;
					$this->elements = array();
					foreach($subElements as $node) {
						// search first DOMElement sibling
						$test = $node->nextSibling;
						while($test && ! ($test instanceof DOMELEMENT))
							$test = $test->nextSibling;
						if ($test && $this->is($subSelector, $test))
							$this->elements[] = $test;
					}
					if (! $this->length() )
						break;
				// PSEUDO CLASSES
				} else if ($s[0] == ':') {
					// TODO optimization for :first :last
					if ($XQuery) {
						$this->runQuery($XQuery);
						$XQuery = '';
					}
					if (! $this->length())
						break;
					$this->pseudoClasses($s);
					if (! $this->length())
						break;
				// DIRECT DESCENDANDS
				} else if ($s == '>') {
					$XQuery .= '/';
					$delimiterBefore = 2;
				// ALL DESCENDANDS
				} else if ($s == ' ') {
					$XQuery .= '//';
					$delimiterBefore = 2;
				// ERRORS
				} else {
					phpQuery::debug("Unrecognized token '$s'");
				}
				$delimiterBefore = $delimiterBefore === 2;
			}
			// run query if any
			if ($XQuery && $XQuery != '//') {
				$this->runQuery($XQuery);
				$XQuery = '';
			}
			foreach($this->elements as $node)
				if (! $this->elementsContainsNode($node, $stack))
					$stack[] = $node;
		}
		$this->elements = $stack;
		return $this->newInstance();
	}
	/**
	 * @todo create API for classes with pseudoselectors
	 * @access private
	 */
	protected function pseudoClasses($class) {
		// TODO clean args parsing ?
		$class = ltrim($class, ':');
		$haveArgs = mb_strpos($class, '(');
		if ($haveArgs !== false) {
			$args = substr($class, $haveArgs+1, -1);
			$class = substr($class, 0, $haveArgs);
		}
		switch($class) {
			case 'even':
			case 'odd':
				$stack = array();
				foreach($this->elements as $i => $node) {
					if ($class == 'even' && ($i%2) == 0)
						$stack[] = $node;
					else if ( $class == 'odd' && $i % 2 )
						$stack[] = $node;
				}
				$this->elements = $stack;
				break;
			case 'eq':
				$k = intval($args);
				$this->elements = isset( $this->elements[$k] )
					? array( $this->elements[$k] )
					: array();
				break;
			case 'gt':
				$this->elements = array_slice($this->elements, $args+1);
				break;
			case 'lt':
				$this->elements = array_slice($this->elements, 0, $args+1);
				break;
			case 'first':
				if (isset($this->elements[0]))
					$this->elements = array($this->elements[0]);
				break;
			case 'last':
				if ($this->elements)
					$this->elements = array($this->elements[count($this->elements)-1]);
				break;
			/*case 'parent':
				$stack = array();
				foreach($this->elements as $node) {
					if ( $node->childNodes->length )
						$stack[] = $node;
				}
				$this->elements = $stack;
				break;*/
			case 'contains':
				$text = trim($args, "\"'");
				$stack = array();
				foreach($this->elements as $node) {
					if (mb_stripos($node->textContent, $text) === false)
						continue;
					$stack[] = $node;
				}
				$this->elements = $stack;
				break;
			case 'not':
				$selector = self::unQuote($args);
				$this->elements = $this->not($selector)->stack();
				break;
			case 'slice':
				// TODO jQuery difference ?
				$args = explode(',',
					str_replace(', ', ',', trim($args, "\"'"))
				);
				$start = $args[0];
				$end = isset($args[1])
					? $args[1]
					: null;
				if ($end > 0)
					$end = $end-$start;
				$this->elements = array_slice($this->elements, $start, $end);
				break;
			case 'has':
				$selector = trim($args, "\"'");
				$stack = array();
				foreach($this->stack(1) as $el) {
					if ($this->find($selector, $el, true)->length)
						$stack[] = $el;
				}
				$this->elements = $stack;
				break;
			case 'submit':
			case 'reset':
				$this->elements = phpQuery::merge(
					$this->map(array($this, 'is'),
						"input[type=$class]", new CallbackParam()
					),
					$this->map(array($this, 'is'),
						"button[type=$class]", new CallbackParam()
					)
				);
			break;
//				$stack = array();
//				foreach($this->elements as $node)
//					if ($node->is('input[type=submit]') || $node->is('button[type=submit]'))
//						$stack[] = $el;
//				$this->elements = $stack;
			case 'input':
				$this->elements = $this->map(
					array($this, 'is'),
					'input', new CallbackParam()
				)->elements;
			break;
			case 'password':
			case 'checkbox':
			case 'radio':
			case 'hidden':
			case 'image':
			case 'file':
				$this->elements = $this->map(
					array($this, 'is'),
					"input[type=$class]", new CallbackParam()
				)->elements;
			break;
			case 'parent':
				$this->elements = $this->map(
					create_function('$node', '
						return $node instanceof DOMELEMENT && $node->childNodes->length
							? $node : null;')
				)->elements;
			break;
			case 'empty':
				$this->elements = $this->map(
					create_function('$node', '
						return $node instanceof DOMELEMENT && $node->childNodes->length
							? null : $node;')
				)->elements;
			break;
			case 'disabled':
			case 'selected':
			case 'checked':
				$this->elements = $this->map(
					array($this, 'is'),
					"[$class]", new CallbackParam()
				)->elements;
			break;
			case 'enabled':
				$this->elements = $this->map(
					create_function('$node', '
						return pq($node)->not(":disabled") ? $node : null;')
				)->elements;
			break;
			case 'header':
				$this->elements = $this->map(
					create_function('$node',
						'$isHeader = isset($node->tagName) && in_array($node->tagName, array(
							"h1", "h2", "h3", "h4", "h5", "h6", "h7"
						));
						return $isHeader
							? $node
							: null;')
				)->elements;
//				$this->elements = $this->map(
//					create_function('$node', '$node = pq($node);
//						return $node->is("h1")
//							|| $node->is("h2")
//							|| $node->is("h3")
//							|| $node->is("h4")
//							|| $node->is("h5")
//							|| $node->is("h6")
//							|| $node->is("h7")
//							? $node
//							: null;')
//				)->elements;
			break;
			case 'only-child':
				$this->elements = $this->map(
					create_function('$node',
						'return pq($node)->siblings()->size() == 0 ? $node : null;')
				)->elements;
			break;
			case 'first-child':
				$this->elements = $this->map(
					create_function('$node', 'return pq($node)->prevAll()->size() == 0 ? $node : null;')
				)->elements;
			break;
			case 'last-child':
				$this->elements = $this->map(
					create_function('$node', 'return pq($node)->nextAll()->size() == 0 ? $node : null;')
				)->elements;
			break;
			case 'nth-child':
				$param = trim($args, "\"'");
				if (! $param)
					break;
					// nth-child(n+b) to nth-child(1n+b)
				if ($param{0} == 'n')
					$param = '1'.$param;
				// :nth-child(index/even/odd/equation)
				if ($param == 'even' || $param == 'odd')
					$mapped = $this->map(
						create_function('$node, $param',
							'$index = pq($node)->prevAll()->size()+1;
							if ($param == "even" && ($index%2) == 0)
								return $node;
							else if ($param == "odd" && $index%2 == 1)
								return $node;
							else
								return null;'),
						new CallbackParam(), $param
					);
				else if (mb_strlen($param) > 1 && $param{1} == 'n')
					// an+b
					$mapped = $this->map(
						create_function('$node, $param',
							'$prevs = pq($node)->prevAll()->size();
							$index = 1+$prevs;
							$b = mb_strlen($param) > 3
								? $param{3}
								: 0;
							$a = $param{0};
							if ($b && $param{2} == "-")
								$b = -$b;
							if ($a > 0) {
								return ($index-$b)%$a == 0
									? $node
									: null;
								phpQuery::debug($a."*".floor($index/$a)."+$b-1 == ".($a*floor($index/$a)+$b-1)." ?= $prevs");
								return $a*floor($index/$a)+$b-1 == $prevs
										? $node
										: null;
							} else if ($a == 0)
								return $index == $b
										? $node
										: null;
							else
								// negative value
								return $index <= $b
										? $node
										: null;
//							if (! $b)
//								return $index%$a == 0
//									? $node
//									: null;
//							else
//								return ($index-$b)%$a == 0
//									? $node
//									: null;
							'),
						new CallbackParam(), $param
					);
				else
					// index
					$mapped = $this->map(
						create_function('$node, $index',
							'$prevs = pq($node)->prevAll()->size();
							if ($prevs && $prevs == $index-1)
								return $node;
							else if (! $prevs && $index == 1)
								return $node;
							else
								return null;'),
						new CallbackParam(), $param
					);
				$this->elements = $mapped->elements;
			break;
			default:
				$this->debug("Unknown pseudoclass '{$class}', skipping...");
		}
	}
	/**
	 * @access private
	 */
	protected function __pseudoClassParam($paramsString) {
		// TODO;
	}
	/**
	 * Enter description here...
	 *
	 * @return phpQueryObject|QueryTemplatesSource|QueryTemplatesParse|QueryTemplatesSourceQuery
	 */
	public function is($selector, $nodes = null) {
		phpQuery::debug(array("Is:", $selector));
		if (! $selector)
			return false;
		$oldStack = $this->elements;
		$returnArray = false;
		if ($nodes && is_array($nodes)) {
			$this->elements = $nodes;
		} else if ($nodes)
			$this->elements = array($nodes);
		$this->filter($selector, true);
		$stack = $this->elements;
		$this->elements = $oldStack;
		if ($nodes)
			return $stack ? $stack : null;
		return (bool)count($stack);
	}
	/**
	 * Enter description here...
	 * jQuery difference.
	 *
	 * Callback:
	 * - $index int
	 * - $node DOMNode
	 *
	 * @return phpQueryObject|QueryTemplatesSource|QueryTemplatesParse|QueryTemplatesSourceQuery
	 * @link http://docs.jquery.com/Traversing/filter
	 */
	public function filterCallback($callback, $_skipHistory = false) {
		if (! $_skipHistory) {
			$this->elementsBackup = $this->elements;
			$this->debug("Filtering by callback");
		}
		$newStack = array();
		foreach($this->elements as $index => $node) {
			$result = phpQuery::callbackRun($callback, array($index, $node));
			if (is_null($result) || (! is_null($result) && $result))
				$newStack[] = $node;
		}
		$this->elements = $newStack;
		return $_skipHistory
			? $this
			: $this->newInstance();
	}
	/**
	 * Enter description here...
	 *
	 * @return phpQueryObject|QueryTemplatesSource|QueryTemplatesParse|QueryTemplatesSourceQuery
	 * @link http://docs.jquery.com/Traversing/filter
	 */
	public function filter($selectors, $_skipHistory = false) {
		if ($selectors instanceof Callback OR $selectors instanceof Closure)
			return $this->filterCallback($selectors, $_skipHistory);
		if (! $_skipHistory)
			$this->elementsBackup = $this->elements;
		$notSimpleSelector = array(' ', '>', '~', '+', '/');
		if (! is_array($selectors))
			$selectors = $this->parseSelector($selectors);
		if (! $_skipHistory)
			$this->debug(array("Filtering:", $selectors));
		$finalStack = array();
		foreach($selectors as $selector) {
			$stack = array();
			if (! $selector)
				break;
			// avoid first space or /
			if (in_array($selector[0], $notSimpleSelector))
				$selector = array_slice($selector, 1);
			// PER NODE selector chunks
			foreach($this->stack() as $node) {
				$break = false;
				foreach($selector as $s) {
					if (!($node instanceof DOMELEMENT)) {
						// all besides DOMElement
						if ( $s[0] == '[') {
							$attr = trim($s, '[]');
							if ( mb_strpos($attr, '=')) {
								list( $attr, $val ) = explode('=', $attr);
								if ($attr == 'nodeType' && $node->nodeType != $val)
									$break = true;
							}
						} else
							$break = true;
					} else {
						// DOMElement only
						// ID
						if ( $s[0] == '#') {
							if ( $node->getAttribute('id') != substr($s, 1) )
								$break = true;
						// CLASSES
						} else if ( $s[0] == '.') {
							if (! $this->matchClasses( $s, $node ) )
								$break = true;
						// ATTRS
						} else if ( $s[0] == '[') {
							// strip side brackets
							$attr = trim($s, '[]');
							if (mb_strpos($attr, '=')) {
								list($attr, $val) = explode('=', $attr);
								$val = self::unQuote($val);
								if ($attr == 'nodeType') {
									if ($val != $node->nodeType)
										$break = true;
								} else if ($this->isRegexp($attr)) {
									$val = extension_loaded('mbstring') && phpQuery::$mbstringSupport
										? quotemeta(trim($val, '"\''))
										: preg_quote(trim($val, '"\''), '@');
									// switch last character
									switch( substr($attr, -1)) {
										// quotemeta used insted of preg_quote
										// http://code.google.com/p/phpquery/issues/detail?id=76
										case '^':
											$pattern = '^'.$val;
											break;
										case '*':
											$pattern = '.*'.$val.'.*';
											break;
										case '$':
											$pattern = '.*'.$val.'$';
											break;
									}
									// cut last character
									$attr = substr($attr, 0, -1);
									$isMatch = extension_loaded('mbstring') && phpQuery::$mbstringSupport
										? mb_ereg_match($pattern, $node->getAttribute($attr))
										: preg_match("@{$pattern}@", $node->getAttribute($attr));
									if (! $isMatch)
										$break = true;
								} else if ($node->getAttribute($attr) != $val)
									$break = true;
							} else if (! $node->hasAttribute($attr))
								$break = true;
						// PSEUDO CLASSES
						} else if ( $s[0] == ':') {
							// skip
						// TAG
						} else if (trim($s)) {
							if ($s != '*') {
								// TODO namespaces
								if (isset($node->tagName)) {
									if ($node->tagName != $s)
										$break = true;
								} else if ($s == 'html' && ! $this->isRoot($node))
									$break = true;
							}
						// AVOID NON-SIMPLE SELECTORS
						} else if (in_array($s, $notSimpleSelector)) {
							$break = true;
							$this->debug(array('Skipping non simple selector', $selector));
						}
					}
					if ($break)
						break;
				}
				// if element passed all chunks of selector - add it to new stack
				if (! $break )
					$stack[] = $node;
			}
			$tmpStack = $this->elements;
			$this->elements = $stack;
			// PER ALL NODES selector chunks
			foreach($selector as $s)
				// PSEUDO CLASSES
				if ($s[0] == ':')
					$this->pseudoClasses($s);
			foreach($this->elements as $node)
				// XXX it should be merged without duplicates
				// but jQuery doesnt do that
				$finalStack[] = $node;
			$this->elements = $tmpStack;
		}
		$this->elements = $finalStack;
		if ($_skipHistory) {
			return $this;
		} else {
			$this->debug("Stack length after filter(): ".count($finalStack));
			return $this->newInstance();
		}
	}
	/**
	 *
	 * @param $value
	 * @return unknown_type
	 * @TODO implement in all methods using passed parameters
	 */
	protected static function unQuote($value) {
		return $value[0] == '\'' || $value[0] == '"'
			? substr($value, 1, -1)
			: $value;
	}
	/**
	 * Enter description here...
	 *
	 * @link http://docs.jquery.com/Ajax/load
	 * @return phpQuery|QueryTemplatesSource|QueryTemplatesParse|QueryTemplatesSourceQuery
	 * @todo Support $selector
	 */
	public function load($url, $data = null, $callback = null) {
		if ($data && ! is_array($data)) {
			$callback = $data;
			$data = null;
		}
		if (mb_strpos($url, ' ') !== false) {
			$matches = null;
			if (extension_loaded('mbstring') && phpQuery::$mbstringSupport)
				mb_ereg('^([^ ]+) (.*)$', $url, $matches);
			else
				preg_match('^([^ ]+) (.*)$', $url, $matches);
			$url = $matches[1];
			$selector = $matches[2];
			// FIXME this sucks, pass as callback param
			$this->_loadSelector = $selector;
		}
		$ajax = array(
			'url' => $url,
			'type' => $data ? 'POST' : 'GET',
			'data' => $data,
			'complete' => $callback,
			'success' => array($this, '__loadSuccess')
		);
		phpQuery::ajax($ajax);
		return $this;
	}
	/**
	 * @access private
	 * @param $html
	 * @return unknown_type
	 */
	public function __loadSuccess($html) {
		if ($this->_loadSelector) {
			$html = phpQuery::newDocument($html)->find($this->_loadSelector);
			unset($this->_loadSelector);
		}
		foreach($this->stack(1) as $node) {
			phpQuery::pq($node, $this->getDocumentID())
				->markup($html);
		}
	}
	/**
	 * Enter description here...
	 *
	 * @return phpQuery|QueryTemplatesSource|QueryTemplatesParse|QueryTemplatesSourceQuery
	 * @todo
	 */
	public function css() {
		// TODO
		return $this;
	}
	/**
	 * @todo
	 *
	 */
	public function show(){
		// TODO
		return $this;
	}
	/**
	 * @todo
	 *
	 */
	public function hide(){
		// TODO
		return $this;
	}
	/**
	 * Trigger a type of event on every matched element.
	 *
	 * @param unknown_type $type
	 * @param unknown_type $data
	 * @return phpQueryObject|QueryTemplatesSource|QueryTemplatesParse|QueryTemplatesSourceQuery
	 * @TODO support more than event in $type (space-separated)
	 */
	public function trigger($type, $data = array()) {
		foreach($this->elements as $node)
			phpQueryEvents::trigger($this->getDocumentID(), $type, $data, $node);
		return $this;
	}
	/**
	 * This particular method triggers all bound event handlers on an element (for a specific event type) WITHOUT executing the browsers default actions.
	 *
	 * @param unknown_type $type
	 * @param unknown_type $data
	 * @return phpQueryObject|QueryTemplatesSource|QueryTemplatesParse|QueryTemplatesSourceQuery
	 * @TODO
	 */
	public function triggerHandler($type, $data = array()) {
		// TODO;
	}
	/**
	 * Binds a handler to one or more events (like click) for each matched element.
	 * Can also bind custom events.
	 *
	 * @param unknown_type $type
	 * @param unknown_type $data Optional
	 * @param unknown_type $callback
	 * @return phpQueryObject|QueryTemplatesSource|QueryTemplatesParse|QueryTemplatesSourceQuery
	 * @TODO support '!' (exclusive) events
	 * @TODO support more than event in $type (space-separated)
	 */
	public function bind($type, $data, $callback = null) {
		// TODO check if $data is callable, not using is_callable
		if (! isset($callback)) {
			$callback = $data;
			$data = null;
		}
		foreach($this->elements as $node)
			phpQueryEvents::add($this->getDocumentID(), $node, $type, $data, $callback);
		return $this;
	}
	/**
	 * Enter description here...
	 *
	 * @param unknown_type $type
	 * @param unknown_type $callback
	 * @return unknown
	 * @TODO namespace events
	 * @TODO support more than event in $type (space-separated)
	 */
	public function unbind($type = null, $callback = null) {
		foreach($this->elements as $node)
			phpQueryEvents::remove($this->getDocumentID(), $node, $type, $callback);
		return $this;
	}
	/**
	 * Enter description here...
	 *
	 * @return phpQueryObject|QueryTemplatesSource|QueryTemplatesParse|QueryTemplatesSourceQuery
	 */
	public function change($callback = null) {
		if ($callback)
			return $this->bind('change', $callback);
		return $this->trigger('change');
	}
	/**
	 * Enter description here...
	 *
	 * @return phpQueryObject|QueryTemplatesSource|QueryTemplatesParse|QueryTemplatesSourceQuery
	 */
	public function submit($callback = null) {
		if ($callback)
			return $this->bind('submit', $callback);
		return $this->trigger('submit');
	}
	/**
	 * Enter description here...
	 *
	 * @return phpQueryObject|QueryTemplatesSource|QueryTemplatesParse|QueryTemplatesSourceQuery
	 */
	public function click($callback = null) {
		if ($callback)
			return $this->bind('click', $callback);
		return $this->trigger('click');
	}
	/**
	 * Enter description here...
	 *
	 * @param String|phpQuery
	 * @return phpQueryObject|QueryTemplatesSource|QueryTemplatesParse|QueryTemplatesSourceQuery
	 */
	public function wrapAllOld($wrapper) {
		$wrapper = pq($wrapper)->_clone();
		if (! $wrapper->length() || ! $this->length() )
			return $this;
		$wrapper->insertBefore($this->elements[0]);
		$deepest = $wrapper->elements[0];
		while($deepest->firstChild && $deepest->firstChild instanceof DOMELEMENT)
			$deepest = $deepest->firstChild;
		pq($deepest)->append($this);
		return $this;
	}
	/**
	 * Enter description here...
	 *
	 * TODO testme...
	 * @param String|phpQuery
	 * @return phpQueryObject|QueryTemplatesSource|QueryTemplatesParse|QueryTemplatesSourceQuery
	 */
	public function wrapAll($wrapper) {
		if (! $this->length())
			return $this;
		return phpQuery::pq($wrapper, $this->getDocumentID())
			->clone()
			->insertBefore($this->get(0))
			->map(array($this, '___wrapAllCallback'))
			->append($this);
	}
  /**
   *
	 * @param $node
	 * @return unknown_type
	 * @access private
   */
	public function ___wrapAllCallback($node) {
		$deepest = $node;
		while($deepest->firstChild && $deepest->firstChild instanceof DOMELEMENT)
			$deepest = $deepest->firstChild;
		return $deepest;
	}
	/**
	 * Enter description here...
	 * NON JQUERY METHOD
	 *
	 * @param String|phpQuery
	 * @return phpQueryObject|QueryTemplatesSource|QueryTemplatesParse|QueryTemplatesSourceQuery
	 */
	public function wrapAllPHP($codeBefore, $codeAfter) {
		return $this
			->slice(0, 1)
				->beforePHP($codeBefore)
			->end()
			->slice(-1)
				->afterPHP($codeAfter)
			->end();
	}
	/**
	 * Enter description here...
	 *
	 * @param String|phpQuery
	 * @return phpQueryObject|QueryTemplatesSource|QueryTemplatesParse|QueryTemplatesSourceQuery
	 */
	public function wrap($wrapper) {
		foreach($this->stack() as $node)
			phpQuery::pq($node, $this->getDocumentID())->wrapAll($wrapper);
		return $this;
	}
	/**
	 * Enter description here...
	 *
	 * @param String|phpQuery
	 * @return phpQueryObject|QueryTemplatesSource|QueryTemplatesParse|QueryTemplatesSourceQuery
	 */
	public function wrapPHP($codeBefore, $codeAfter) {
		foreach($this->stack() as $node)
			phpQuery::pq($node, $this->getDocumentID())->wrapAllPHP($codeBefore, $codeAfter);
		return $this;
	}
	/**
	 * Enter description here...
	 *
	 * @param String|phpQuery
	 * @return phpQueryObject|QueryTemplatesSource|QueryTemplatesParse|QueryTemplatesSourceQuery
	 */
	public function wrapInner($wrapper) {
		foreach($this->stack() as $node)
			phpQuery::pq($node, $this->getDocumentID())->contents()->wrapAll($wrapper);
		return $this;
	}
	/**
	 * Enter description here...
	 *
	 * @param String|phpQuery
	 * @return phpQueryObject|QueryTemplatesSource|QueryTemplatesParse|QueryTemplatesSourceQuery
	 */
	public function wrapInnerPHP($codeBefore, $codeAfter) {
		foreach($this->stack(1) as $node)
			phpQuery::pq($node, $this->getDocumentID())->contents()
				->wrapAllPHP($codeBefore, $codeAfter);
		return $this;
	}
	/**
	 * Enter description here...
	 *
	 * @return phpQueryObject|QueryTemplatesSource|QueryTemplatesParse|QueryTemplatesSourceQuery
	 * @testme Support for text nodes
	 */
	public function contents() {
		$stack = array();
		foreach($this->stack(1) as $el) {
			// FIXME (fixed) http://code.google.com/p/phpquery/issues/detail?id=56
//			if (! isset($el->childNodes))
//				continue;
			foreach($el->childNodes as $node) {
				$stack[] = $node;
			}
		}
		return $this->newInstance($stack);
	}
	/**
	 * Enter description here...
	 *
	 * jQuery difference.
	 *
	 * @return phpQueryObject|QueryTemplatesSource|QueryTemplatesParse|QueryTemplatesSourceQuery
	 */
	public function contentsUnwrap() {
		foreach($this->stack(1) as $node) {
			if (! $node->parentNode )
				continue;
			$childNodes = array();
			// any modification in DOM tree breaks childNodes iteration, so cache them first
			foreach($node->childNodes as $chNode )
				$childNodes[] = $chNode;
			foreach($childNodes as $chNode )
//				$node->parentNode->appendChild($chNode);
				$node->parentNode->insertBefore($chNode, $node);
			$node->parentNode->removeChild($node);
		}
		return $this;
	}
	/**
	 * Enter description here...
	 *
	 * jQuery difference.
	 */
	public function switchWith($markup) {
		$markup = pq($markup, $this->getDocumentID());
		$content = null;
		foreach($this->stack(1) as $node) {
			pq($node)
				->contents()->toReference($content)->end()
				->replaceWith($markup->clone()->append($content));
		}
		return $this;
	}
	/**
	 * Enter description here...
	 *
	 * @return phpQueryObject|QueryTemplatesSource|QueryTemplatesParse|QueryTemplatesSourceQuery
	 */
	public function eq($num) {
		$oldStack = $this->elements;
		$this->elementsBackup = $this->elements;
		$this->elements = array();
		if ( isset($oldStack[$num]) )
			$this->elements[] = $oldStack[$num];
		return $this->newInstance();
	}
	/**
	 * Enter description here...
	 *
	 * @return phpQueryObject|QueryTemplatesSource|QueryTemplatesParse|QueryTemplatesSourceQuery
	 */
	public function size() {
		return count($this->elements);
	}
	/**
	 * Enter description here...
	 *
	 * @return phpQueryObject|QueryTemplatesSource|QueryTemplatesParse|QueryTemplatesSourceQuery
	 * @deprecated Use length as attribute
	 */
	public function length() {
		return $this->size();
	}
	public function count() {
		return $this->size();
	}
	/**
	 * Enter description here...
	 *
	 * @return phpQueryObject|QueryTemplatesSource|QueryTemplatesParse|QueryTemplatesSourceQuery
	 * @todo $level
	 */
	public function end($level = 1) {
//		$this->elements = array_pop( $this->history );
//		return $this;
//		$this->previous->DOM = $this->DOM;
//		$this->previous->XPath = $this->XPath;
		return $this->previous
			? $this->previous
			: $this;
	}
	/**
	 * Enter description here...
	 * Normal use ->clone() .
	 *
	 * @return phpQueryObject|QueryTemplatesSource|QueryTemplatesParse|QueryTemplatesSourceQuery
	 * @access private
	 */
	public function _clone() {
		$newStack = array();
		//pr(array('copy... ', $this->whois()));
		//$this->dumpHistory('copy');
		$this->elementsBackup = $this->elements;
		foreach($this->elements as $node) {
			$newStack[] = $node->cloneNode(true);
		}
		$this->elements = $newStack;
		return $this->newInstance();
	}
	/**
	 * Enter description here...
	 *
	 * @return phpQueryObject|QueryTemplatesSource|QueryTemplatesParse|QueryTemplatesSourceQuery
	 */
	public function replaceWithPHP($code) {
		return $this->replaceWith(phpQuery::php($code));
	}
	/**
	 * Enter description here...
	 *
	 * @param String|phpQuery $content
	 * @link http://docs.jquery.com/Manipulation/replaceWith#content
	 * @return phpQueryObject|QueryTemplatesSource|QueryTemplatesParse|QueryTemplatesSourceQuery
	 */
	public function replaceWith($content) {
		return $this->after($content)->remove();
	}
	/**
	 * Enter description here...
	 *
	 * @param String $selector
	 * @return phpQueryObject|QueryTemplatesSource|QueryTemplatesParse|QueryTemplatesSourceQuery
	 * @todo this works ?
	 */
	public function replaceAll($selector) {
		foreach(phpQuery::pq($selector, $this->getDocumentID()) as $node)
			phpQuery::pq($node, $this->getDocumentID())
				->after($this->_clone())
				->remove();
		return $this;
	}
	/**
	 * Enter description here...
	 *
	 * @return phpQueryObject|QueryTemplatesSource|QueryTemplatesParse|QueryTemplatesSourceQuery
	 */
	public function remove($selector = null) {
		$loop = $selector
			? $this->filter($selector)->elements
			: $this->elements;
		foreach($loop as $node) {
			if (! $node->parentNode )
				continue;
			if (isset($node->tagName))
				$this->debug("Removing '{$node->tagName}'");
			$node->parentNode->removeChild($node);
			// Mutation event
			$event = new DOMEvent(array(
				'target' => $node,
				'type' => 'DOMNodeRemoved'
			));
			phpQueryEvents::trigger($this->getDocumentID(),
				$event->type, array($event), $node
			);
		}
		return $this;
	}
	protected function markupEvents($newMarkup, $oldMarkup, $node) {
		if ($node->tagName == 'textarea' && $newMarkup != $oldMarkup) {
			$event = new DOMEvent(array(
				'target' => $node,
				'type' => 'change'
			));
			phpQueryEvents::trigger($this->getDocumentID(),
				$event->type, array($event), $node
			);
		}
	}
	/**
	 * jQuey difference
	 *
	 * @param $markup
	 * @return unknown_type
	 * @TODO trigger change event for textarea
	 */
	public function markup($markup = null, $callback1 = null, $callback2 = null, $callback3 = null) {
		$args = func_get_args();
		if ($this->documentWrapper->isXML)
			return call_user_func_array(array($this, 'xml'), $args);
		else
			return call_user_func_array(array($this, 'html'), $args);
	}
	/**
	 * jQuey difference
	 *
	 * @param $markup
	 * @return unknown_type
	 */
	public function markupOuter($callback1 = null, $callback2 = null, $callback3 = null) {
		$args = func_get_args();
		if ($this->documentWrapper->isXML)
			return call_user_func_array(array($this, 'xmlOuter'), $args);
		else
			return call_user_func_array(array($this, 'htmlOuter'), $args);
	}
	/**
	 * Enter description here...
	 *
	 * @param unknown_type $html
	 * @return string|phpQuery|QueryTemplatesSource|QueryTemplatesParse|QueryTemplatesSourceQuery
	 * @TODO force html result
	 */
	public function html($html = null, $callback1 = null, $callback2 = null, $callback3 = null) {
		if (isset($html)) {
			// INSERT
			$nodes = $this->documentWrapper->import($html);
			$this->empty();
			foreach($this->stack(1) as $alreadyAdded => $node) {
				// for now, limit events for textarea
				if (($this->isXHTML() || $this->isHTML()) && $node->tagName == 'textarea')
					$oldHtml = pq($node, $this->getDocumentID())->markup();
				foreach($nodes as $newNode) {
					$node->appendChild($alreadyAdded
						? $newNode->cloneNode(true)
						: $newNode
					);
				}
				// for now, limit events for textarea
				if (($this->isXHTML() || $this->isHTML()) && $node->tagName == 'textarea')
					$this->markupEvents($html, $oldHtml, $node);
			}
			return $this;
		} else {
			// FETCH
			$return = $this->documentWrapper->markup($this->elements, true);
			$args = func_get_args();
			foreach(array_slice($args, 1) as $callback) {
				$return = phpQuery::callbackRun($callback, array($return));
			}
			return $return;
		}
	}
	/**
	 * @TODO force xml result
	 */
	public function xml($xml = null, $callback1 = null, $callback2 = null, $callback3 = null) {
		$args = func_get_args();
		return call_user_func_array(array($this, 'html'), $args);
	}
	/**
	 * Enter description here...
	 * @TODO force html result
	 *
	 * @return String
	 */
	public function htmlOuter($callback1 = null, $callback2 = null, $callback3 = null) {
		$markup = $this->documentWrapper->markup($this->elements);
		// pass thou callbacks
		$args = func_get_args();
		foreach($args as $callback) {
			$markup = phpQuery::callbackRun($callback, array($markup));
		}
		return $markup;
	}
	/**
	 * @TODO force xml result
	 */
	public function xmlOuter($callback1 = null, $callback2 = null, $callback3 = null) {
		$args = func_get_args();
		return call_user_func_array(array($this, 'htmlOuter'), $args);
	}
	public function __toString() {
		return $this->markupOuter();
	}
	/**
	 * Just like html(), but returns markup with VALID (dangerous) PHP tags.
	 *
	 * @return phpQueryObject|QueryTemplatesSource|QueryTemplatesParse|QueryTemplatesSourceQuery
	 * @todo support returning markup with PHP tags when called without param
	 */
	public function php($code = null) {
		return $this->markupPHP($code);
	}
	/**
	 * Enter description here...
	 * 
	 * @param $code
	 * @return unknown_type
	 */
	public function markupPHP($code = null) {
		return isset($code)
			? $this->markup(phpQuery::php($code))
			: phpQuery::markupToPHP($this->markup());
	}
	/**
	 * Enter description here...
	 * 
	 * @param $code
	 * @return unknown_type
	 */
	public function markupOuterPHP() {
		return phpQuery::markupToPHP($this->markupOuter());
	}
	/**
	 * Enter description here...
	 *
	 * @return phpQueryObject|QueryTemplatesSource|QueryTemplatesParse|QueryTemplatesSourceQuery
	 */
	public function children($selector = null) {
		$stack = array();
		foreach($this->stack(1) as $node) {
//			foreach($node->getElementsByTagName('*') as $newNode) {
			foreach($node->childNodes as $newNode) {
				if ($newNode->nodeType != 1)
					continue;
				if ($selector && ! $this->is($selector, $newNode))
					continue;
				if ($this->elementsContainsNode($newNode, $stack))
					continue;
				$stack[] = $newNode;
			}
		}
		$this->elementsBackup = $this->elements;
		$this->elements = $stack;
		return $this->newInstance();
	}
	/**
	 * Enter description here...
	 *
	 * @return phpQueryObject|QueryTemplatesSource|QueryTemplatesParse|QueryTemplatesSourceQuery
	 */
	public function ancestors($selector = null) {
		return $this->children( $selector );
	}
	/**
	 * Enter description here...
	 *
	 * @return phpQueryObject|QueryTemplatesSource|QueryTemplatesParse|QueryTemplatesSourceQuery
	 */
	public function append( $content) {
		return $this->insert($content, __FUNCTION__);
	}
	/**
	 * Enter description here...
	 *
	 * @return phpQueryObject|QueryTemplatesSource|QueryTemplatesParse|QueryTemplatesSourceQuery
	 */
	public function appendPHP( $content) {
		return $this->insert("<php><!-- {$content} --></php>", 'append');
	}
	/**
	 * Enter description here...
	 *
	 * @return phpQueryObject|QueryTemplatesSource|QueryTemplatesParse|QueryTemplatesSourceQuery
	 */
	public function appendTo( $seletor) {
		return $this->insert($seletor, __FUNCTION__);
	}
	/**
	 * Enter description here...
	 *
	 * @return phpQueryObject|QueryTemplatesSource|QueryTemplatesParse|QueryTemplatesSourceQuery
	 */
	public function prepend( $content) {
		return $this->insert($content, __FUNCTION__);
	}
	/**
	 * Enter description here...
	 *
	 * @todo accept many arguments, which are joined, arrays maybe also
	 * @return phpQueryObject|QueryTemplatesSource|QueryTemplatesParse|QueryTemplatesSourceQuery
	 */
	public function prependPHP( $content) {
		return $this->insert("<php><!-- {$content} --></php>", 'prepend');
	}
	/**
	 * Enter description here...
	 *
	 * @return phpQueryObject|QueryTemplatesSource|QueryTemplatesParse|QueryTemplatesSourceQuery
	 */
	public function prependTo( $seletor) {
		return $this->insert($seletor, __FUNCTION__);
	}
	/**
	 * Enter description here...
	 *
	 * @return phpQueryObject|QueryTemplatesSource|QueryTemplatesParse|QueryTemplatesSourceQuery
	 */
	public function before($content) {
		return $this->insert($content, __FUNCTION__);
	}
	/**
	 * Enter description here...
	 *
	 * @return phpQueryObject|QueryTemplatesSource|QueryTemplatesParse|QueryTemplatesSourceQuery
	 */
	public function beforePHP( $content) {
		return $this->insert("<php><!-- {$content} --></php>", 'before');
	}
	/**
	 * Enter description here...
	 *
	 * @param String|phpQuery
	 * @return phpQueryObject|QueryTemplatesSource|QueryTemplatesParse|QueryTemplatesSourceQuery
	 */
	public function insertBefore( $seletor) {
		return $this->insert($seletor, __FUNCTION__);
	}
	/**
	 * Enter description here...
	 *
	 * @return phpQueryObject|QueryTemplatesSource|QueryTemplatesParse|QueryTemplatesSourceQuery
	 */
	public function after( $content) {
		return $this->insert($content, __FUNCTION__);
	}
	/**
	 * Enter description here...
	 *
	 * @return phpQueryObject|QueryTemplatesSource|QueryTemplatesParse|QueryTemplatesSourceQuery
	 */
	public function afterPHP( $content) {
		return $this->insert("<php><!-- {$content} --></php>", 'after');
	}
	/**
	 * Enter description here...
	 *
	 * @return phpQueryObject|QueryTemplatesSource|QueryTemplatesParse|QueryTemplatesSourceQuery
	 */
	public function insertAfter( $seletor) {
		return $this->insert($seletor, __FUNCTION__);
	}
	/**
	 * Internal insert method. Don't use it.
	 *
	 * @param unknown_type $target
	 * @param unknown_type $type
	 * @return phpQueryObject|QueryTemplatesSource|QueryTemplatesParse|QueryTemplatesSourceQuery
	 * @access private
	 */
	public function insert($target, $type) {
		$this->debug("Inserting data with '{$type}'");
		$to = false;
		switch( $type) {
			case 'appendTo':
			case 'prependTo':
			case 'insertBefore':
			case 'insertAfter':
				$to = true;
		}
		switch(gettype($target)) {
			case 'string':
				$insertFrom = $insertTo = array();
				if ($to) {
					// INSERT TO
					$insertFrom = $this->elements;
					if (phpQuery::isMarkup($target)) {
						// $target is new markup, import it
						$insertTo = $this->documentWrapper->import($target);
					// insert into selected element
					} else {
						// $tagret is a selector
						$thisStack = $this->elements;
						$this->toRoot();
						$insertTo = $this->find($target)->elements;
						$this->elements = $thisStack;
					}
				} else {
					// INSERT FROM
					$insertTo = $this->elements;
					$insertFrom = $this->documentWrapper->import($target);
				}
				break;
			case 'object':
				$insertFrom = $insertTo = array();
				// phpQuery
				if ($target instanceof self) {
					if ($to) {
						$insertTo = $target->elements;
						if ($this->documentFragment && $this->stackIsRoot())
							// get all body children
//							$loop = $this->find('body > *')->elements;
							// TODO test it, test it hard...
//							$loop = $this->newInstance($this->root)->find('> *')->elements;
							$loop = $this->root->childNodes;
						else
							$loop = $this->elements;
						// import nodes if needed
						$insertFrom = $this->getDocumentID() == $target->getDocumentID()
							? $loop
							: $target->documentWrapper->import($loop);
					} else {
						$insertTo = $this->elements;
						if ( $target->documentFragment && $target->stackIsRoot() )
							// get all body children
//							$loop = $target->find('body > *')->elements;
							$loop = $target->root->childNodes;
						else
							$loop = $target->elements;
						// import nodes if needed
						$insertFrom = $this->getDocumentID() == $target->getDocumentID()
							? $loop
							: $this->documentWrapper->import($loop);
					}
				// DOMNODE
				} elseif ($target instanceof DOMNODE) {
					// import node if needed
//					if ( $target->ownerDocument != $this->DOM )
//						$target = $this->DOM->importNode($target, true);
					if ( $to) {
						$insertTo = array($target);
						if ($this->documentFragment && $this->stackIsRoot())
							// get all body children
							$loop = $this->root->childNodes;
//							$loop = $this->find('body > *')->elements;
						else
							$loop = $this->elements;
						foreach($loop as $fromNode)
							// import nodes if needed
							$insertFrom[] = ! $fromNode->ownerDocument->isSameNode($target->ownerDocument)
								? $target->ownerDocument->importNode($fromNode, true)
								: $fromNode;
					} else {
						// import node if needed
						if (! $target->ownerDocument->isSameNode($this->document))
							$target = $this->document->importNode($target, true);
						$insertTo = $this->elements;
						$insertFrom[] = $target;
					}
				}
				break;
		}
		phpQuery::debug("From ".count($insertFrom)."; To ".count($insertTo)." nodes");
		foreach($insertTo as $insertNumber => $toNode) {
			// we need static relative elements in some cases
			switch( $type) {
				case 'prependTo':
				case 'prepend':
					$firstChild = $toNode->firstChild;
					break;
				case 'insertAfter':
				case 'after':
					$nextSibling = $toNode->nextSibling;
					break;
			}
			foreach($insertFrom as $fromNode) {
				// clone if inserted already before
				$insert = $insertNumber
					? $fromNode->cloneNode(true)
					: $fromNode;
				switch($type) {
					case 'appendTo':
					case 'append':
//						$toNode->insertBefore(
//							$fromNode,
//							$toNode->lastChild->nextSibling
//						);
						$toNode->appendChild($insert);
						$eventTarget = $insert;
						break;
					case 'prependTo':
					case 'prepend':
						$toNode->insertBefore(
							$insert,
							$firstChild
						);
						break;
					case 'insertBefore':
					case 'before':
						if (! $toNode->parentNode)
							throw new Exception("No parentNode, can't do {$type}()");
						else
							$toNode->parentNode->insertBefore(
								$insert,
								$toNode
							);
						break;
					case 'insertAfter':
					case 'after':
						if (! $toNode->parentNode)
							throw new Exception("No parentNode, can't do {$type}()");
						else
							$toNode->parentNode->insertBefore(
								$insert,
								$nextSibling
							);
						break;
				}
				// Mutation event
				$event = new DOMEvent(array(
					'target' => $insert,
					'type' => 'DOMNodeInserted'
				));
				phpQueryEvents::trigger($this->getDocumentID(),
					$event->type, array($event), $insert
				);
			}
		}
		return $this;
	}
	/**
	 * Enter description here...
	 *
	 * @return Int
	 */
	public function index($subject) {
		$index = -1;
		$subject = $subject instanceof phpQueryObject
			? $subject->elements[0]
			: $subject;
		foreach($this->newInstance() as $k => $node) {
			if ($node->isSameNode($subject))
				$index = $k;
		}
		return $index;
	}
	/**
	 * Enter description here...
	 *
	 * @param unknown_type $start
	 * @param unknown_type $end
	 *
	 * @return phpQueryObject|QueryTemplatesSource|QueryTemplatesParse|QueryTemplatesSourceQuery
	 * @testme
	 */
	public function slice($start, $end = null) {
//		$last = count($this->elements)-1;
//		$end = $end
//			? min($end, $last)
//			: $last;
//		if ($start < 0)
//			$start = $last+$start;
//		if ($start > $last)
//			return array();
		if ($end > 0)
			$end = $end-$start;
		return $this->newInstance(
			array_slice($this->elements, $start, $end)
		);
	}
	/**
	 * Enter description here...
	 *
	 * @return phpQueryObject|QueryTemplatesSource|QueryTemplatesParse|QueryTemplatesSourceQuery
	 */
	public function reverse() {
		$this->elementsBackup = $this->elements;
		$this->elements = array_reverse($this->elements);
		return $this->newInstance();
	}
	/**
	 * Return joined text content.
	 * @return String
	 */
	public function text($text = null, $callback1 = null, $callback2 = null, $callback3 = null) {
		if (isset($text))
			return $this->html(htmlspecialchars($text));
		$args = func_get_args();
		$args = array_slice($args, 1);
		$return = '';
		foreach($this->elements as $node) {
			$text = $node->textContent;
			if (count($this->elements) > 1 && $text)
				$text .= "\n";
			foreach($args as $callback) {
				$text = phpQuery::callbackRun($callback, array($text));
			}
			$return .= $text;
		}
		return $return;
	}
	/**
	 * Enter description here...
	 *
	 * @return phpQueryObject|QueryTemplatesSource|QueryTemplatesParse|QueryTemplatesSourceQuery
	 */
	public function plugin($class, $file = null) {
		phpQuery::plugin($class, $file);
		return $this;
	}
	/**
	 * Deprecated, use $pq->plugin() instead.
	 *
	 * @deprecated
	 * @param $class
	 * @param $file
	 * @return unknown_type
	 */
	public static function extend($class, $file = null) {
		return $this->plugin($class, $file);
	}
	/**
	 *
	 * @access private
	 * @param $method
	 * @param $args
	 * @return unknown_type
	 */
	public function __call($method, $args) {
		$aliasMethods = array('clone', 'empty');
		if (isset(phpQuery::$extendMethods[$method])) {
			array_unshift($args, $this);
			return phpQuery::callbackRun(
				phpQuery::$extendMethods[$method], $args
			);
		} else if (isset(phpQuery::$pluginsMethods[$method])) {
			array_unshift($args, $this);
			$class = phpQuery::$pluginsMethods[$method];
			$realClass = "phpQueryObjectPlugin_$class";
			$return = call_user_func_array(
				array($realClass, $method),
				$args
			);
			// XXX deprecate ?
			return is_null($return)
				? $this
				: $return;
		} else if (in_array($method, $aliasMethods)) {
			return call_user_func_array(array($this, '_'.$method), $args);
		} else
			throw new Exception("Method '{$method}' doesnt exist");
	}
	/**
	 * Safe rename of next().
	 *
	 * Use it ONLY when need to call next() on an iterated object (in same time).
	 * Normaly there is no need to do such thing ;)
	 *
	 * @return phpQueryObject|QueryTemplatesSource|QueryTemplatesParse|QueryTemplatesSourceQuery
	 * @access private
	 */
	public function _next($selector = null) {
		return $this->newInstance(
			$this->getElementSiblings('nextSibling', $selector, true)
		);
	}
	/**
	 * Use prev() and next().
	 *
	 * @deprecated
	 * @return phpQueryObject|QueryTemplatesSource|QueryTemplatesParse|QueryTemplatesSourceQuery
	 * @access private
	 */
	public function _prev($selector = null) {
		return $this->prev($selector);
	}
	/**
	 * Enter description here...
	 *
	 * @return phpQueryObject|QueryTemplatesSource|QueryTemplatesParse|QueryTemplatesSourceQuery
	 */
	public function prev($selector = null) {
		return $this->newInstance(
			$this->getElementSiblings('previousSibling', $selector, true)
		);
	}
	/**
	 * @return phpQueryObject|QueryTemplatesSource|QueryTemplatesParse|QueryTemplatesSourceQuery
	 * @todo
	 */
	public function prevAll($selector = null) {
		return $this->newInstance(
			$this->getElementSiblings('previousSibling', $selector)
		);
	}
	/**
	 * @return phpQueryObject|QueryTemplatesSource|QueryTemplatesParse|QueryTemplatesSourceQuery
	 * @todo FIXME: returns source elements insted of next siblings
	 */
	public function nextAll($selector = null) {
		return $this->newInstance(
			$this->getElementSiblings('nextSibling', $selector)
		);
	}
	/**
	 * @access private
	 */
	protected function getElementSiblings($direction, $selector = null, $limitToOne = false) {
		$stack = array();
		$count = 0;
		foreach($this->stack() as $node) {
			$test = $node;
			while( isset($test->{$direction}) && $test->{$direction}) {
				$test = $test->{$direction};
				if (! $test instanceof DOMELEMENT)
					continue;
				$stack[] = $test;
				if ($limitToOne)
					break;
			}
		}
		if ($selector) {
			$stackOld = $this->elements;
			$this->elements = $stack;
			$stack = $this->filter($selector, true)->stack();
			$this->elements = $stackOld;
		}
		return $stack;
	}
	/**
	 * Enter description here...
	 *
	 * @return phpQueryObject|QueryTemplatesSource|QueryTemplatesParse|QueryTemplatesSourceQuery
	 */
	public function siblings($selector = null) {
		$stack = array();
		$siblings = array_merge(
			$this->getElementSiblings('previousSibling', $selector),
			$this->getElementSiblings('nextSibling', $selector)
		);
		foreach($siblings as $node) {
			if (! $this->elementsContainsNode($node, $stack))
				$stack[] = $node;
		}
		return $this->newInstance($stack);
	}
	/**
	 * Enter description here...
	 *
	 * @return phpQueryObject|QueryTemplatesSource|QueryTemplatesParse|QueryTemplatesSourceQuery
	 */
	public function not($selector = null) {
		if (is_string($selector))
			phpQuery::debug(array('not', $selector));
		else
			phpQuery::debug('not');
		$stack = array();
		if ($selector instanceof self || $selector instanceof DOMNODE) {
			foreach($this->stack() as $node) {
				if ($selector instanceof self) {
					$matchFound = false;
					foreach($selector->stack() as $notNode) {
						if ($notNode->isSameNode($node))
							$matchFound = true;
					}
					if (! $matchFound)
						$stack[] = $node;
				} else if ($selector instanceof DOMNODE) {
					if (! $selector->isSameNode($node))
						$stack[] = $node;
				} else {
					if (! $this->is($selector))
						$stack[] = $node;
				}
			}
		} else {
			$orgStack = $this->stack();
			$matched = $this->filter($selector, true)->stack();
//			$matched = array();
//			// simulate OR in filter() instead of AND 5y
//			foreach($this->parseSelector($selector) as $s) {
//				$matched = array_merge($matched,
//					$this->filter(array($s))->stack()
//				);
//			}
			foreach($orgStack as $node)
				if (! $this->elementsContainsNode($node, $matched))
					$stack[] = $node;
		}
		return $this->newInstance($stack);
	}
	/**
	 * Enter description here...
	 *
	 * @param string|phpQueryObject
	 * @return phpQueryObject|QueryTemplatesSource|QueryTemplatesParse|QueryTemplatesSourceQuery
	 */
	public function add($selector = null) {
		if (! $selector)
			return $this;
		$stack = array();
		$this->elementsBackup = $this->elements;
		$found = phpQuery::pq($selector, $this->getDocumentID());
		$this->merge($found->elements);
		return $this->newInstance();
	}
	/**
	 * @access private
	 */
	protected function merge() {
		foreach(func_get_args() as $nodes)
			foreach($nodes as $newNode )
				if (! $this->elementsContainsNode($newNode) )
					$this->elements[] = $newNode;
	}
	/**
	 * @access private
	 * TODO refactor to stackContainsNode
	 */
	protected function elementsContainsNode($nodeToCheck, $elementsStack = null) {
		$loop = ! is_null($elementsStack)
			? $elementsStack
			: $this->elements;
		foreach($loop as $node) {
			if ( $node->isSameNode( $nodeToCheck ) )
				return true;
		}
		return false;
	}
	/**
	 * Enter description here...
	 *
	 * @return phpQueryObject|QueryTemplatesSource|QueryTemplatesParse|QueryTemplatesSourceQuery
	 */
	public function parent($selector = null) {
		$stack = array();
		foreach($this->elements as $node )
			if ( $node->parentNode && ! $this->elementsContainsNode($node->parentNode, $stack) )
				$stack[] = $node->parentNode;
		$this->elementsBackup = $this->elements;
		$this->elements = $stack;
		if ( $selector )
			$this->filter($selector, true);
		return $this->newInstance();
	}
	/**
	 * Enter description here...
	 *
	 * @return phpQueryObject|QueryTemplatesSource|QueryTemplatesParse|QueryTemplatesSourceQuery
	 */
	public function parents($selector = null) {
		$stack = array();
		if (! $this->elements )
			$this->debug('parents() - stack empty');
		foreach($this->elements as $node) {
			$test = $node;
			while( $test->parentNode) {
				$test = $test->parentNode;
				if ($this->isRoot($test))
					break;
				if (! $this->elementsContainsNode($test, $stack)) {
					$stack[] = $test;
					continue;
				}
			}
		}
		$this->elementsBackup = $this->elements;
		$this->elements = $stack;
		if ( $selector )
			$this->filter($selector, true);
		return $this->newInstance();
	}
	/**
	 * Internal stack iterator.
	 *
	 * @access private
	 */
	public function stack($nodeTypes = null) {
		if (!isset($nodeTypes))
			return $this->elements;
		if (!is_array($nodeTypes))
			$nodeTypes = array($nodeTypes);
		$return = array();
		foreach($this->elements as $node) {
			if (in_array($node->nodeType, $nodeTypes))
				$return[] = $node;
		}
		return $return;
	}
	// TODO phpdoc; $oldAttr is result of hasAttribute, before any changes
	protected function attrEvents($attr, $oldAttr, $oldValue, $node) {
		// skip events for XML documents
		if (! $this->isXHTML() && ! $this->isHTML())
			return;
		$event = null;
		// identify
		$isInputValue = $node->tagName == 'input'
			&& (
				in_array($node->getAttribute('type'),
					array('text', 'password', 'hidden'))
				|| !$node->getAttribute('type')
				 );
		$isRadio = $node->tagName == 'input'
			&& $node->getAttribute('type') == 'radio';
		$isCheckbox = $node->tagName == 'input'
			&& $node->getAttribute('type') == 'checkbox';
		$isOption = $node->tagName == 'option';
		if ($isInputValue && $attr == 'value' && $oldValue != $node->getAttribute($attr)) {
			$event = new DOMEvent(array(
				'target' => $node,
				'type' => 'change'
			));
		} else if (($isRadio || $isCheckbox) && $attr == 'checked' && (
				// check
				(! $oldAttr && $node->hasAttribute($attr))
				// un-check
				|| (! $node->hasAttribute($attr) && $oldAttr)
			)) {
			$event = new DOMEvent(array(
				'target' => $node,
				'type' => 'change'
			));
		} else if ($isOption && $node->parentNode && $attr == 'selected' && (
				// select
				(! $oldAttr && $node->hasAttribute($attr))
				// un-select
				|| (! $node->hasAttribute($attr) && $oldAttr)
			)) {
			$event = new DOMEvent(array(
				'target' => $node->parentNode,
				'type' => 'change'
			));
		}
		if ($event) {
			phpQueryEvents::trigger($this->getDocumentID(),
				$event->type, array($event), $node
			);
		}
	}
	public function attr($attr = null, $value = null) {
		foreach($this->stack(1) as $node) {
			if (! is_null($value)) {
				$loop = $attr == '*'
					? $this->getNodeAttrs($node)
					: array($attr);
				foreach($loop as $a) {
					$oldValue = $node->getAttribute($a);
					$oldAttr = $node->hasAttribute($a);
					// TODO raises an error when charset other than UTF-8
					// while document's charset is also not UTF-8
					@$node->setAttribute($a, $value);
					$this->attrEvents($a, $oldAttr, $oldValue, $node);
				}
			} else if ($attr == '*') {
				// jQuery difference
				$return = array();
				foreach($node->attributes as $n => $v)
					$return[$n] = $v->value;
				return $return;
			} else
				return $node->hasAttribute($attr)
					? $node->getAttribute($attr)
					: null;
		}
		return is_null($value)
			? '' : $this;
	}
	/**
	 * @access private
	 */
	protected function getNodeAttrs($node) {
		$return = array();
		foreach($node->attributes as $n => $o)
			$return[] = $n;
		return $return;
	}
	/**
	 * Enter description here...
	 *
	 * @return phpQueryObject|QueryTemplatesSource|QueryTemplatesParse|QueryTemplatesSourceQuery
	 * @todo check CDATA ???
	 */
	public function attrPHP($attr, $code) {
		if (! is_null($code)) {
			$value = '<'.'?php '.$code.' ?'.'>';
			// TODO tempolary solution
			// http://code.google.com/p/phpquery/issues/detail?id=17
//			if (function_exists('mb_detect_encoding') && mb_detect_encoding($value) == 'ASCII')
//				$value	= mb_convert_encoding($value, 'UTF-8', 'HTML-ENTITIES');
		}
		foreach($this->stack(1) as $node) {
			if (! is_null($code)) {
//				$attrNode = $this->DOM->createAttribute($attr);
				$node->setAttribute($attr, $value);
//				$attrNode->value = $value;
//				$node->appendChild($attrNode);
			} else if ( $attr == '*') {
				// jQuery diff
				$return = array();
				foreach($node->attributes as $n => $v)
					$return[$n] = $v->value;
				return $return;
			} else
				return $node->getAttribute($attr);
		}
		return $this;
	}
	/**
	 * Enter description here...
	 *
	 * @return phpQueryObject|QueryTemplatesSource|QueryTemplatesParse|QueryTemplatesSourceQuery
	 */
	public function removeAttr($attr) {
		foreach($this->stack(1) as $node) {
			$loop = $attr == '*'
				? $this->getNodeAttrs($node)
				: array($attr);
			foreach($loop as $a) {
				$oldValue = $node->getAttribute($a);
				$node->removeAttribute($a);
				$this->attrEvents($a, $oldValue, null, $node);
			}
		}
		return $this;
	}
	/**
	 * Return form element value.
	 *
	 * @return String Fields value.
	 */
	public function val($val = null) {
		if (! isset($val)) {
			if ($this->eq(0)->is('select')) {
					$selected = $this->eq(0)->find('option[selected=selected]');
					if ($selected->is('[value]'))
						return $selected->attr('value');
					else
						return $selected->text();
			} else if ($this->eq(0)->is('textarea'))
					return $this->eq(0)->markup();
				else
					return $this->eq(0)->attr('value');
		} else {
			$_val = null;
			foreach($this->stack(1) as $node) {
				$node = pq($node, $this->getDocumentID());
				if (is_array($val) && in_array($node->attr('type'), array('checkbox', 'radio'))) {
					$isChecked = in_array($node->attr('value'), $val)
							|| in_array($node->attr('name'), $val);
					if ($isChecked)
						$node->attr('checked', 'checked');
					else
						$node->removeAttr('checked');
				} else if ($node->get(0)->tagName == 'select') {
					if (! isset($_val)) {
						$_val = array();
						if (! is_array($val))
							$_val = array((string)$val);
						else
							foreach($val as $v)
								$_val[] = $v;
					}
					foreach($node['option']->stack(1) as $option) {
						$option = pq($option, $this->getDocumentID());
						$selected = false;
						// XXX: workaround for string comparsion, see issue #96
						// http://code.google.com/p/phpquery/issues/detail?id=96
						$selected = is_null($option->attr('value'))
							? in_array($option->markup(), $_val)
							: in_array($option->attr('value'), $_val);
//						$optionValue = $option->attr('value');
//						$optionText = $option->text();
//						$optionTextLenght = mb_strlen($optionText);
//						foreach($_val as $v)
//							if ($optionValue == $v)
//								$selected = true;
//							else if ($optionText == $v && $optionTextLenght == mb_strlen($v))
//								$selected = true;
						if ($selected)
							$option->attr('selected', 'selected');
						else
							$option->removeAttr('selected');
					}
				} else if ($node->get(0)->tagName == 'textarea')
					$node->markup($val);
				else
					$node->attr('value', $val);
			}
		}
		return $this;
	}
	/**
	 * Enter description here...
	 *
	 * @return phpQueryObject|QueryTemplatesSource|QueryTemplatesParse|QueryTemplatesSourceQuery
	 */
	public function andSelf() {
		if ( $this->previous )
			$this->elements = array_merge($this->elements, $this->previous->elements);
		return $this;
	}
	/**
	 * Enter description here...
	 *
	 * @return phpQueryObject|QueryTemplatesSource|QueryTemplatesParse|QueryTemplatesSourceQuery
	 */
	public function addClass( $className) {
		if (! $className)
			return $this;
		foreach($this->stack(1) as $node) {
			if (! $this->is(".$className", $node))
				$node->setAttribute(
					'class',
					trim($node->getAttribute('class').' '.$className)
				);
		}
		return $this;
	}
	/**
	 * Enter description here...
	 *
	 * @return phpQueryObject|QueryTemplatesSource|QueryTemplatesParse|QueryTemplatesSourceQuery
	 */
	public function addClassPHP( $className) {
		foreach($this->stack(1) as $node) {
				$classes = $node->getAttribute('class');
				$newValue = $classes
					? $classes.' <'.'?php '.$className.' ?'.'>'
					: '<'.'?php '.$className.' ?'.'>';
				$node->setAttribute('class', $newValue);
		}
		return $this;
	}
	/**
	 * Enter description here...
	 *
	 * @param	string	$className
	 * @return	bool
	 */
	public function hasClass($className) {
		foreach($this->stack(1) as $node) {
			if ( $this->is(".$className", $node))
				return true;
		}
		return false;
	}
	/**
	 * Enter description here...
	 *
	 * @return phpQueryObject|QueryTemplatesSource|QueryTemplatesParse|QueryTemplatesSourceQuery
	 */
	public function removeClass($className) {
		foreach($this->stack(1) as $node) {
			$classes = explode( ' ', $node->getAttribute('class'));
			if ( in_array($className, $classes)) {
				$classes = array_diff($classes, array($className));
				if ( $classes )
					$node->setAttribute('class', implode(' ', $classes));
				else
					$node->removeAttribute('class');
			}
		}
		return $this;
	}
	/**
	 * Enter description here...
	 *
	 * @return phpQueryObject|QueryTemplatesSource|QueryTemplatesParse|QueryTemplatesSourceQuery
	 */
	public function toggleClass($className) {
		foreach($this->stack(1) as $node) {
			if ( $this->is( $node, '.'.$className ))
				$this->removeClass($className);
			else
				$this->addClass($className);
		}
		return $this;
	}
	/**
	 * Proper name without underscore (just ->empty()) also works.
	 *
	 * Removes all child nodes from the set of matched elements.
	 *
	 * Example:
	 * pq("p")._empty()
	 *
	 * HTML:
	 * <p>Hello, <span>Person</span> <a href="#">and person</a></p>
	 *
	 * Result:
	 * [ <p></p> ]
	 *
	 * @return phpQueryObject|QueryTemplatesSource|QueryTemplatesParse|QueryTemplatesSourceQuery
	 * @access private
	 */
	public function _empty() {
		foreach($this->stack(1) as $node) {
			// thx to 'dave at dgx dot cz'
			$node->nodeValue = '';
		}
		return $this;
	}
	/**
	 * Enter description here...
	 *
	 * @param array|string $callback Expects $node as first param, $index as second
	 * @param array $scope External variables passed to callback. Use compact('varName1', 'varName2'...) and extract($scope)
	 * @param array $arg1 Will ba passed as third and futher args to callback.
	 * @param array $arg2 Will ba passed as fourth and futher args to callback, and so on...
	 * @return phpQueryObject|QueryTemplatesSource|QueryTemplatesParse|QueryTemplatesSourceQuery
	 */
	public function each($callback, $param1 = null, $param2 = null, $param3 = null) {
		$paramStructure = null;
		if (func_num_args() > 1) {
			$paramStructure = func_get_args();
			$paramStructure = array_slice($paramStructure, 1);
		}
		foreach($this->elements as $v)
			phpQuery::callbackRun($callback, array($v), $paramStructure);
		return $this;
	}
	/**
	 * Run callback on actual object.
	 *
	 * @return phpQueryObject|QueryTemplatesSource|QueryTemplatesParse|QueryTemplatesSourceQuery
	 */
	public function callback($callback, $param1 = null, $param2 = null, $param3 = null) {
		$params = func_get_args();
		$params[0] = $this;
		phpQuery::callbackRun($callback, $params);
		return $this;
	}
	/**
	 * Enter description here...
	 *
	 * @return phpQueryObject|QueryTemplatesSource|QueryTemplatesParse|QueryTemplatesSourceQuery
	 * @todo add $scope and $args as in each() ???
	 */
	public function map($callback, $param1 = null, $param2 = null, $param3 = null) {
//		$stack = array();
////		foreach($this->newInstance() as $node) {
//		foreach($this->newInstance() as $node) {
//			$result = call_user_func($callback, $node);
//			if ($result)
//				$stack[] = $result;
//		}
		$params = func_get_args();
		array_unshift($params, $this->elements);
		return $this->newInstance(
			call_user_func_array(array('phpQuery', 'map'), $params)
//			phpQuery::map($this->elements, $callback)
		);
	}
	/**
	 * Enter description here...
	 * 
	 * @param <type> $key
	 * @param <type> $value
	 */
	public function data($key, $value = null) {
		if (! isset($value)) {
			// TODO? implement specific jQuery behavior od returning parent values
			// is child which we look up doesn't exist
			return phpQuery::data($this->get(0), $key, $value, $this->getDocumentID());
		} else {
			foreach($this as $node)
				phpQuery::data($node, $key, $value, $this->getDocumentID());
			return $this;
		}
	}
	/**
	 * Enter description here...
	 * 
	 * @param <type> $key
	 */
	public function removeData($key) {
		foreach($this as $node)
			phpQuery::removeData($node, $key, $this->getDocumentID());
		return $this;
	}
	// INTERFACE IMPLEMENTATIONS

	// ITERATOR INTERFACE
	/**
   * @access private
	 */
	public function rewind(){
		$this->debug('iterating foreach');
//		phpQuery::selectDocument($this->getDocumentID());
		$this->elementsBackup = $this->elements;
		$this->elementsInterator = $this->elements;
		$this->valid = isset( $this->elements[0] )
			? 1 : 0;
// 		$this->elements = $this->valid
// 			? array($this->elements[0])
// 			: array();
		$this->current = 0;
	}
	/**
   * @access private
	 */
	public function current(){
		return $this->elementsInterator[ $this->current ];
	}
	/**
   * @access private
	 */
	public function key(){
		return $this->current;
	}
	/**
	 * Double-function method.
	 *
	 * First: main iterator interface method.
	 * Second: Returning next sibling, alias for _next().
	 *
	 * Proper functionality is choosed automagicaly.
	 *
	 * @see phpQueryObject::_next()
	 * @return phpQueryObject|QueryTemplatesSource|QueryTemplatesParse|QueryTemplatesSourceQuery
	 */
	public function next($cssSelector = null){
//		if ($cssSelector || $this->valid)
//			return $this->_next($cssSelector);
		$this->valid = isset( $this->elementsInterator[ $this->current+1 ] )
			? true
			: false;
		if (! $this->valid && $this->elementsInterator) {
			$this->elementsInterator = null;
		} else if ($this->valid) {
			$this->current++;
		} else {
			return $this->_next($cssSelector);
		}
	}
	/**
   * @access private
	 */
	public function valid(){
		return $this->valid;
	}
	// ITERATOR INTERFACE END
	// ARRAYACCESS INTERFACE
	/**
   * @access private
	 */
	public function offsetExists($offset) {
		return $this->find($offset)->size() > 0;
	}
	/**
   * @access private
	 */
	public function offsetGet($offset) {
		return $this->find($offset);
	}
	/**
   * @access private
	 */
	public function offsetSet($offset, $value) {
//		$this->find($offset)->replaceWith($value);
		$this->find($offset)->html($value);
	}
	/**
   * @access private
	 */
	public function offsetUnset($offset) {
		// empty
		throw new Exception("Can't do unset, use array interface only for calling queries and replacing HTML.");
	}
	// ARRAYACCESS INTERFACE END
	/**
	 * Returns node's XPath.
	 *
	 * @param unknown_type $oneNode
	 * @return string
	 * @TODO use native getNodePath is avaible
	 * @access private
	 */
	protected function getNodeXpath($oneNode = null, $namespace = null) {
		$return = array();
		$loop = $oneNode
			? array($oneNode)
			: $this->elements;
//		if ($namespace)
//			$namespace .= ':';
		foreach($loop as $node) {
			if ($node instanceof DOMDOCUMENT) {
				$return[] = '';
				continue;
			}
			$xpath = array();
			while(! ($node instanceof DOMDOCUMENT)) {
				$i = 1;
				$sibling = $node;
				while($sibling->previousSibling) {
					$sibling = $sibling->previousSibling;
					$isElement = $sibling instanceof DOMELEMENT;
					if ($isElement && $sibling->tagName == $node->tagName)
						$i++;
				}
				$xpath[] = $this->isXML()
					? "*[local-name()='{$node->tagName}'][{$i}]"
					: "{$node->tagName}[{$i}]";
				$node = $node->parentNode;
			}
			$xpath = join('/', array_reverse($xpath));
			$return[] = '/'.$xpath;
		}
		return $oneNode
			? $return[0]
			: $return;
	}
	// HELPERS
	public function whois($oneNode = null) {
		$return = array();
		$loop = $oneNode
			? array( $oneNode )
			: $this->elements;
		foreach($loop as $node) {
			if (isset($node->tagName)) {
				$tag = in_array($node->tagName, array('php', 'js'))
					? strtoupper($node->tagName)
					: $node->tagName;
				$return[] = $tag
					.($node->getAttribute('id')
						? '#'.$node->getAttribute('id'):'')
					.($node->getAttribute('class')
						? '.'.join('.', split(' ', $node->getAttribute('class'))):'')
					.($node->getAttribute('name')
						? '[name="'.$node->getAttribute('name').'"]':'')
					.($node->getAttribute('value') && strpos($node->getAttribute('value'), '<'.'?php') === false
						? '[value="'.substr(str_replace("\n", '', $node->getAttribute('value')), 0, 15).'"]':'')
					.($node->getAttribute('value') && strpos($node->getAttribute('value'), '<'.'?php') !== false
						? '[value=PHP]':'')
					.($node->getAttribute('selected')
						? '[selected]':'')
					.($node->getAttribute('checked')
						? '[checked]':'')
				;
			} else if ($node instanceof DOMTEXT) {
				if (trim($node->textContent))
					$return[] = 'Text:'.substr(str_replace("\n", ' ', $node->textContent), 0, 15);
			} else {

			}
		}
		return $oneNode && isset($return[0])
			? $return[0]
			: $return;
	}
	/**
	 * Dump htmlOuter and preserve chain. Usefull for debugging.
	 *
	 * @return phpQueryObject|QueryTemplatesSource|QueryTemplatesParse|QueryTemplatesSourceQuery
	 *
	 */
	public function dump() {
		print 'DUMP #'.(phpQuery::$dumpCount++).' ';
		$debug = phpQuery::$debug;
		phpQuery::$debug = false;
//		print __FILE__.':'.__LINE__."\n";
		var_dump($this->htmlOuter());
		return $this;
	}
	public function dumpWhois() {
		print 'DUMP #'.(phpQuery::$dumpCount++).' ';
		$debug = phpQuery::$debug;
		phpQuery::$debug = false;
//		print __FILE__.':'.__LINE__."\n";
		var_dump('whois', $this->whois());
		phpQuery::$debug = $debug;
		return $this;
	}
	public function dumpLength() {
		print 'DUMP #'.(phpQuery::$dumpCount++).' ';
		$debug = phpQuery::$debug;
		phpQuery::$debug = false;
//		print __FILE__.':'.__LINE__."\n";
		var_dump('length', $this->length());
		phpQuery::$debug = $debug;
		return $this;
	}
	public function dumpTree($html = true, $title = true) {
		$output = $title
			? 'DUMP #'.(phpQuery::$dumpCount++)." \n" : '';
		$debug = phpQuery::$debug;
		phpQuery::$debug = false;
		foreach($this->stack() as $node)
			$output .= $this->__dumpTree($node);
		phpQuery::$debug = $debug;
		print $html
			? nl2br(str_replace(' ', '&nbsp;', $output))
			: $output;
		return $this;
	}
	private function __dumpTree($node, $intend = 0) {
		$whois = $this->whois($node);
		$return = '';
		if ($whois)
			$return .= str_repeat(' - ', $intend).$whois."\n";
		if (isset($node->childNodes))
			foreach($node->childNodes as $chNode)
				$return .= $this->__dumpTree($chNode, $intend+1);
		return $return;
	}
	/**
	 * Dump htmlOuter and stop script execution. Usefull for debugging.
	 *
	 */
	public function dumpDie() {
		print __FILE__.':'.__LINE__;
		var_dump($this->htmlOuter());
		die();
	}
}


// -- Multibyte Compatibility functions ---------------------------------------
// http://svn.iphonewebdev.com/lace/lib/mb_compat.php

/**
 *  mb_internal_encoding()
 *
 *  Included for mbstring pseudo-compatability.
 */
if (!function_exists('mb_internal_encoding'))
{
	function mb_internal_encoding($enc) {return true; }
}

/**
 *  mb_regex_encoding()
 *
 *  Included for mbstring pseudo-compatability.
 */
if (!function_exists('mb_regex_encoding'))
{
	function mb_regex_encoding($enc) {return true; }
}

/**
 *  mb_strlen()
 *
 *  Included for mbstring pseudo-compatability.
 */
if (!function_exists('mb_strlen'))
{
	function mb_strlen($str)
	{
		return strlen($str);
	}
}

/**
 *  mb_strpos()
 *
 *  Included for mbstring pseudo-compatability.
 */
if (!function_exists('mb_strpos'))
{
	function mb_strpos($haystack, $needle, $offset=0)
	{
		return strpos($haystack, $needle, $offset);
	}
}
/**
 *  mb_stripos()
 *
 *  Included for mbstring pseudo-compatability.
 */
if (!function_exists('mb_stripos'))
{
	function mb_stripos($haystack, $needle, $offset=0)
	{
		return stripos($haystack, $needle, $offset);
	}
}

/**
 *  mb_substr()
 *
 *  Included for mbstring pseudo-compatability.
 */
if (!function_exists('mb_substr'))
{
	function mb_substr($str, $start, $length=0)
	{
		return substr($str, $start, $length);
	}
}

/**
 *  mb_substr_count()
 *
 *  Included for mbstring pseudo-compatability.
 */
if (!function_exists('mb_substr_count'))
{
	function mb_substr_count($haystack, $needle)
	{
		return substr_count($haystack, $needle);
	}
}


/**
 * Static namespace for phpQuery functions.
 *
 * @author Tobiasz Cudnik <tobiasz.cudnik/gmail.com>
 * @package phpQuery
 */
abstract class phpQuery {
	/**
	 * XXX: Workaround for mbstring problems 
	 * 
	 * @var bool
	 */
	public static $mbstringSupport = true;
	public static $debug = false;
	public static $documents = array();
	public static $defaultDocumentID = null;
//	public static $defaultDoctype = 'html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"';
	/**
	 * Applies only to HTML.
	 *
	 * @var unknown_type
	 */
	public static $defaultDoctype = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">';
	public static $defaultCharset = 'UTF-8';
	/**
	 * Static namespace for plugins.
	 *
	 * @var object
	 */
	public static $plugins = array();
	/**
	 * List of loaded plugins.
	 *
	 * @var unknown_type
	 */
	public static $pluginsLoaded = array();
	public static $pluginsMethods = array();
	public static $pluginsStaticMethods = array();
	public static $extendMethods = array();
	/**
	 * @TODO implement
	 */
	public static $extendStaticMethods = array();
	/**
	 * Hosts allowed for AJAX connections.
	 * Dot '.' means $_SERVER['HTTP_HOST'] (if any).
	 *
	 * @var array
	 */
	public static $ajaxAllowedHosts = array(
		'.'
	);
	/**
	 * AJAX settings.
	 *
	 * @var array
	 * XXX should it be static or not ?
	 */
	public static $ajaxSettings = array(
		'url' => '',//TODO
		'global' => true,
		'type' => "GET",
		'timeout' => null,
		'contentType' => "application/x-www-form-urlencoded",
		'processData' => true,
//		'async' => true,
		'data' => null,
		'username' => null,
		'password' => null,
		'accepts' => array(
			'xml' => "application/xml, text/xml",
			'html' => "text/html",
			'script' => "text/javascript, application/javascript",
			'json' => "application/json, text/javascript",
			'text' => "text/plain",
			'_default' => "*/*"
		)
	);
	public static $lastModified = null;
	public static $active = 0;
	public static $dumpCount = 0;
	/**
	 * Multi-purpose function.
	 * Use pq() as shortcut.
	 *
	 * In below examples, $pq is any result of pq(); function.
	 *
	 * 1. Import markup into existing document (without any attaching):
	 * - Import into selected document:
	 *   pq('<div/>')				// DOESNT accept text nodes at beginning of input string !
	 * - Import into document with ID from $pq->getDocumentID():
	 *   pq('<div/>', $pq->getDocumentID())
	 * - Import into same document as DOMNode belongs to:
	 *   pq('<div/>', DOMNode)
	 * - Import into document from phpQuery object:
	 *   pq('<div/>', $pq)
	 *
	 * 2. Run query:
	 * - Run query on last selected document:
	 *   pq('div.myClass')
	 * - Run query on document with ID from $pq->getDocumentID():
	 *   pq('div.myClass', $pq->getDocumentID())
	 * - Run query on same document as DOMNode belongs to and use node(s)as root for query:
	 *   pq('div.myClass', DOMNode)
	 * - Run query on document from phpQuery object
	 *   and use object's stack as root node(s) for query:
	 *   pq('div.myClass', $pq)
	 *
	 * @param string|DOMNode|DOMNodeList|array	$arg1	HTML markup, CSS Selector, DOMNode or array of DOMNodes
	 * @param string|phpQueryObject|DOMNode	$context	DOM ID from $pq->getDocumentID(), phpQuery object (determines also query root) or DOMNode (determines also query root)
	 *
	 * @return phpQueryObject|QueryTemplatesSource|QueryTemplatesParse|QueryTemplatesSourceQuery|QueryTemplatesPhpQuery|false
   * phpQuery object or false in case of error.
	 */
	public static function pq($arg1, $context = null) {
		if ($arg1 instanceof DOMNODE && ! isset($context)) {
			foreach(phpQuery::$documents as $documentWrapper) {
				$compare = $arg1 instanceof DOMDocument
					? $arg1 : $arg1->ownerDocument;
				if ($documentWrapper->document->isSameNode($compare))
					$context = $documentWrapper->id;
			}
		}
		if (! $context) {
			$domId = self::$defaultDocumentID;
			if (! $domId)
				throw new Exception("Can't use last created DOM, because there isn't any. Use phpQuery::newDocument() first.");
//		} else if (is_object($context) && ($context instanceof PHPQUERY || is_subclass_of($context, 'phpQueryObject')))
		} else if (is_object($context) && $context instanceof phpQueryObject)
			$domId = $context->getDocumentID();
		else if ($context instanceof DOMDOCUMENT) {
			$domId = self::getDocumentID($context);
			if (! $domId) {
				//throw new Exception('Orphaned DOMDocument');
				$domId = self::newDocument($context)->getDocumentID();
			}
		} else if ($context instanceof DOMNODE) {
			$domId = self::getDocumentID($context);
			if (! $domId) {
				throw new Exception('Orphaned DOMNode');
//				$domId = self::newDocument($context->ownerDocument);
			}
		} else
			$domId = $context;
		if ($arg1 instanceof phpQueryObject) {
//		if (is_object($arg1) && (get_class($arg1) == 'phpQueryObject' || $arg1 instanceof PHPQUERY || is_subclass_of($arg1, 'phpQueryObject'))) {
			/**
			 * Return $arg1 or import $arg1 stack if document differs:
			 * pq(pq('<div/>'))
			 */
			if ($arg1->getDocumentID() == $domId)
				return $arg1;
			$class = get_class($arg1);
			// support inheritance by passing old object to overloaded constructor
			$phpQuery = $class != 'phpQuery'
				? new $class($arg1, $domId)
				: new phpQueryObject($domId);
			$phpQuery->elements = array();
			foreach($arg1->elements as $node)
				$phpQuery->elements[] = $phpQuery->document->importNode($node, true);
			return $phpQuery;
		} else if ($arg1 instanceof DOMNODE || (is_array($arg1) && isset($arg1[0]) && $arg1[0] instanceof DOMNODE)) {
			/*
			 * Wrap DOM nodes with phpQuery object, import into document when needed:
			 * pq(array($domNode1, $domNode2))
			 */
			$phpQuery = new phpQueryObject($domId);
			if (!($arg1 instanceof DOMNODELIST) && ! is_array($arg1))
				$arg1 = array($arg1);
			$phpQuery->elements = array();
			foreach($arg1 as $node) {
				$sameDocument = $node->ownerDocument instanceof DOMDOCUMENT
					&& ! $node->ownerDocument->isSameNode($phpQuery->document);
				$phpQuery->elements[] = $sameDocument
					? $phpQuery->document->importNode($node, true)
					: $node;
			}
			return $phpQuery;
		} else if (self::isMarkup($arg1)) {
			/**
			 * Import HTML:
			 * pq('<div/>')
			 */
			$phpQuery = new phpQueryObject($domId);
			return $phpQuery->newInstance(
				$phpQuery->documentWrapper->import($arg1)
			);
		} else {
			/**
			 * Run CSS query:
			 * pq('div.myClass')
			 */
			$phpQuery = new phpQueryObject($domId);
//			if ($context && ($context instanceof PHPQUERY || is_subclass_of($context, 'phpQueryObject')))
			if ($context && $context instanceof phpQueryObject)
				$phpQuery->elements = $context->elements;
			else if ($context && $context instanceof DOMNODELIST) {
				$phpQuery->elements = array();
				foreach($context as $node)
					$phpQuery->elements[] = $node;
			} else if ($context && $context instanceof DOMNODE)
				$phpQuery->elements = array($context);
			return $phpQuery->find($arg1);
		}
	}
	/**
	 * Sets default document to $id. Document has to be loaded prior
	 * to using this method.
	 * $id can be retrived via getDocumentID() or getDocumentIDRef().
	 *
	 * @param unknown_type $id
	 */
	public static function selectDocument($id) {
		$id = self::getDocumentID($id);
		self::debug("Selecting document '$id' as default one");
		self::$defaultDocumentID = self::getDocumentID($id);
	}
	/**
	 * Returns document with id $id or last used as phpQueryObject.
	 * $id can be retrived via getDocumentID() or getDocumentIDRef().
	 * Chainable.
	 *
	 * @see phpQuery::selectDocument()
	 * @param unknown_type $id
	 * @return phpQueryObject|QueryTemplatesSource|QueryTemplatesParse|QueryTemplatesSourceQuery
	 */
	public static function getDocument($id = null) {
		if ($id)
			phpQuery::selectDocument($id);
		else
			$id = phpQuery::$defaultDocumentID;
		return new phpQueryObject($id);
	}
	/**
	 * Creates new document from markup.
	 * Chainable.
	 *
	 * @param unknown_type $markup
	 * @return phpQueryObject|QueryTemplatesSource|QueryTemplatesParse|QueryTemplatesSourceQuery
	 */
	public static function newDocument($markup = null, $contentType = null) {
		if (! $markup)
			$markup = '';
		$documentID = phpQuery::createDocumentWrapper($markup, $contentType);
		return new phpQueryObject($documentID);
	}
	/**
	 * Creates new document from markup.
	 * Chainable.
	 *
	 * @param unknown_type $markup
	 * @return phpQueryObject|QueryTemplatesSource|QueryTemplatesParse|QueryTemplatesSourceQuery
	 */
	public static function newDocumentHTML($markup = null, $charset = null) {
		$contentType = $charset
			? ";charset=$charset"
			: '';
		return self::newDocument($markup, "text/html{$contentType}");
	}
	/**
	 * Creates new document from markup.
	 * Chainable.
	 *
	 * @param unknown_type $markup
	 * @return phpQueryObject|QueryTemplatesSource|QueryTemplatesParse|QueryTemplatesSourceQuery
	 */
	public static function newDocumentXML($markup = null, $charset = null) {
		$contentType = $charset
			? ";charset=$charset"
			: '';
		return self::newDocument($markup, "text/xml{$contentType}");
	}
	/**
	 * Creates new document from markup.
	 * Chainable.
	 *
	 * @param unknown_type $markup
	 * @return phpQueryObject|QueryTemplatesSource|QueryTemplatesParse|QueryTemplatesSourceQuery
	 */
	public static function newDocumentXHTML($markup = null, $charset = null) {
		$contentType = $charset
			? ";charset=$charset"
			: '';
		return self::newDocument($markup, "application/xhtml+xml{$contentType}");
	}
	/**
	 * Creates new document from markup.
	 * Chainable.
	 *
	 * @param unknown_type $markup
	 * @return phpQueryObject|QueryTemplatesSource|QueryTemplatesParse|QueryTemplatesSourceQuery
	 */
	public static function newDocumentPHP($markup = null, $contentType = "text/html") {
		// TODO pass charset to phpToMarkup if possible (use DOMDocumentWrapper function)
		$markup = phpQuery::phpToMarkup($markup, self::$defaultCharset);
		return self::newDocument($markup, $contentType);
	}
	public static function phpToMarkup($php, $charset = 'utf-8') {
		$regexes = array(
			'@(<(?!\\?)(?:[^>]|\\?>)+\\w+\\s*=\\s*)(\')([^\']*)<'.'?php?(.*?)(?:\\?>)([^\']*)\'@s',
			'@(<(?!\\?)(?:[^>]|\\?>)+\\w+\\s*=\\s*)(")([^"]*)<'.'?php?(.*?)(?:\\?>)([^"]*)"@s',
		);
		foreach($regexes as $regex)
			while (preg_match($regex, $php, $matches)) {
				$php = preg_replace_callback(
					$regex,
//					create_function('$m, $charset = "'.$charset.'"',
//						'return $m[1].$m[2]
//							.htmlspecialchars("<"."?php".$m[4]."?".">", ENT_QUOTES|ENT_NOQUOTES, $charset)
//							.$m[5].$m[2];'
//					),
					array('phpQuery', '_phpToMarkupCallback'),
					$php
				);
			}
		$regex = '@(^|>[^<]*)+?(<\?php(.*?)(\?>))@s';
//preg_match_all($regex, $php, $matches);
//var_dump($matches);
		$php = preg_replace($regex, '\\1<php><!-- \\3 --></php>', $php);
		return $php;
	}
	public static function _phpToMarkupCallback($php, $charset = 'utf-8') {
		return $m[1].$m[2]
			.htmlspecialchars("<"."?php".$m[4]."?".">", ENT_QUOTES|ENT_NOQUOTES, $charset)
			.$m[5].$m[2];
	}
	public static function _markupToPHPCallback($m) {
		return "<"."?php ".htmlspecialchars_decode($m[1])." ?".">";
	}
	/**
	 * Converts document markup containing PHP code generated by phpQuery::php()
	 * into valid (executable) PHP code syntax.
	 *
	 * @param string|phpQueryObject $content
	 * @return string PHP code.
	 */
	public static function markupToPHP($content) {
		if ($content instanceof phpQueryObject)
			$content = $content->markupOuter();
		/* <php>...</php> to <?php...? > */
		$content = preg_replace_callback(
			'@<php>\s*<!--(.*?)-->\s*</php>@s',
//			create_function('$m',
//				'return "<'.'?php ".htmlspecialchars_decode($m[1])." ?'.'>";'
//			),
			array('phpQuery', '_markupToPHPCallback'),
			$content
		);
		/* <node attr='< ?php ? >'> extra space added to save highlighters */
		$regexes = array(
			'@(<(?!\\?)(?:[^>]|\\?>)+\\w+\\s*=\\s*)(\')([^\']*)(?:&lt;|%3C)\\?(?:php)?(.*?)(?:\\?(?:&gt;|%3E))([^\']*)\'@s',
			'@(<(?!\\?)(?:[^>]|\\?>)+\\w+\\s*=\\s*)(")([^"]*)(?:&lt;|%3C)\\?(?:php)?(.*?)(?:\\?(?:&gt;|%3E))([^"]*)"@s',
		);
		foreach($regexes as $regex)
			while (preg_match($regex, $content))
				$content = preg_replace_callback(
					$regex,
					create_function('$m',
						'return $m[1].$m[2].$m[3]."<?php "
							.str_replace(
								array("%20", "%3E", "%09", "&#10;", "&#9;", "%7B", "%24", "%7D", "%22", "%5B", "%5D"),
								array(" ", ">", "	", "\n", "	", "{", "$", "}", \'"\', "[", "]"),
								htmlspecialchars_decode($m[4])
							)
							." ?>".$m[5].$m[2];'
					),
					$content
				);
		return $content;
	}
	/**
	 * Creates new document from file $file.
	 * Chainable.
	 *
	 * @param string $file URLs allowed. See File wrapper page at php.net for more supported sources.
	 * @return phpQueryObject|QueryTemplatesSource|QueryTemplatesParse|QueryTemplatesSourceQuery
	 */
	public static function newDocumentFile($file, $contentType = null) {
		$documentID = self::createDocumentWrapper(
			file_get_contents($file), $contentType
		);
		return new phpQueryObject($documentID);
	}
	/**
	 * Creates new document from markup.
	 * Chainable.
	 *
	 * @param unknown_type $markup
	 * @return phpQueryObject|QueryTemplatesSource|QueryTemplatesParse|QueryTemplatesSourceQuery
	 */
	public static function newDocumentFileHTML($file, $charset = null) {
		$contentType = $charset
			? ";charset=$charset"
			: '';
		return self::newDocumentFile($file, "text/html{$contentType}");
	}
	/**
	 * Creates new document from markup.
	 * Chainable.
	 *
	 * @param unknown_type $markup
	 * @return phpQueryObject|QueryTemplatesSource|QueryTemplatesParse|QueryTemplatesSourceQuery
	 */
	public static function newDocumentFileXML($file, $charset = null) {
		$contentType = $charset
			? ";charset=$charset"
			: '';
		return self::newDocumentFile($file, "text/xml{$contentType}");
	}
	/**
	 * Creates new document from markup.
	 * Chainable.
	 *
	 * @param unknown_type $markup
	 * @return phpQueryObject|QueryTemplatesSource|QueryTemplatesParse|QueryTemplatesSourceQuery
	 */
	public static function newDocumentFileXHTML($file, $charset = null) {
		$contentType = $charset
			? ";charset=$charset"
			: '';
		return self::newDocumentFile($file, "application/xhtml+xml{$contentType}");
	}
	/**
	 * Creates new document from markup.
	 * Chainable.
	 *
	 * @param unknown_type $markup
	 * @return phpQueryObject|QueryTemplatesSource|QueryTemplatesParse|QueryTemplatesSourceQuery
	 */
	public static function newDocumentFilePHP($file, $contentType = null) {
		return self::newDocumentPHP(file_get_contents($file), $contentType);
	}
	/**
	 * Reuses existing DOMDocument object.
	 * Chainable.
	 *
	 * @param $document DOMDocument
	 * @return phpQueryObject|QueryTemplatesSource|QueryTemplatesParse|QueryTemplatesSourceQuery
	 * @TODO support DOMDocument
	 */
	public static function loadDocument($document) {
		// TODO
		die('TODO loadDocument');
	}
	/**
	 * Enter description here...
	 *
	 * @param unknown_type $html
	 * @param unknown_type $domId
	 * @return unknown New DOM ID
	 * @todo support PHP tags in input
	 * @todo support passing DOMDocument object from self::loadDocument
	 */
	protected static function createDocumentWrapper($html, $contentType = null, $documentID = null) {
		if (function_exists('domxml_open_mem'))
			throw new Exception("Old PHP4 DOM XML extension detected. phpQuery won't work until this extension is enabled.");
//		$id = $documentID
//			? $documentID
//			: md5(microtime());
		$document = null;
		if ($html instanceof DOMDOCUMENT) {
			if (self::getDocumentID($html)) {
				// document already exists in phpQuery::$documents, make a copy
				$document = clone $html;
			} else {
				// new document, add it to phpQuery::$documents
				$wrapper = new DOMDocumentWrapper($html, $contentType, $documentID);
			}
		} else {
			$wrapper = new DOMDocumentWrapper($html, $contentType, $documentID);
		}
//		$wrapper->id = $id;
		// bind document
		phpQuery::$documents[$wrapper->id] = $wrapper;
		// remember last loaded document
		phpQuery::selectDocument($wrapper->id);
		return $wrapper->id;
	}
	/**
	 * Extend class namespace.
	 *
	 * @param string|array $target
	 * @param array $source
	 * @TODO support string $source
	 * @return unknown_type
	 */
	public static function extend($target, $source) {
		switch($target) {
			case 'phpQueryObject':
				$targetRef = &self::$extendMethods;
				$targetRef2 = &self::$pluginsMethods;
				break;
			case 'phpQuery':
				$targetRef = &self::$extendStaticMethods;
				$targetRef2 = &self::$pluginsStaticMethods;
				break;
			default:
				throw new Exception("Unsupported \$target type");
		}
		if (is_string($source))
			$source = array($source => $source);
		foreach($source as $method => $callback) {
			if (isset($targetRef[$method])) {
//				throw new Exception
				self::debug("Duplicate method '{$method}', can\'t extend '{$target}'");
				continue;
			}
			if (isset($targetRef2[$method])) {
//				throw new Exception
				self::debug("Duplicate method '{$method}' from plugin '{$targetRef2[$method]}',"
					." can\'t extend '{$target}'");
				continue;
			}
			$targetRef[$method] = $callback;
		}
		return true;
	}
	/**
	 * Extend phpQuery with $class from $file.
	 *
	 * @param string $class Extending class name. Real class name can be prepended phpQuery_.
	 * @param string $file Filename to include. Defaults to "{$class}.php".
	 */
	public static function plugin($class, $file = null) {
		// TODO $class checked agains phpQuery_$class
//		if (strpos($class, 'phpQuery') === 0)
//			$class = substr($class, 8);
		if (in_array($class, self::$pluginsLoaded))
			return true;
		if (! $file)
			$file = $class.'.php';
		$objectClassExists = class_exists('phpQueryObjectPlugin_'.$class);
		$staticClassExists = class_exists('phpQueryPlugin_'.$class);
		if (! $objectClassExists && ! $staticClassExists)
			require_once($file);
		self::$pluginsLoaded[] = $class;
		// static methods
		if (class_exists('phpQueryPlugin_'.$class)) {
			$realClass = 'phpQueryPlugin_'.$class;
			$vars = get_class_vars($realClass);
			$loop = isset($vars['phpQueryMethods'])
				&& ! is_null($vars['phpQueryMethods'])
				? $vars['phpQueryMethods']
				: get_class_methods($realClass);
			foreach($loop as $method) {
				if ($method == '__initialize')
					continue;
				if (! is_callable(array($realClass, $method)))
					continue;
				if (isset(self::$pluginsStaticMethods[$method])) {
					throw new Exception("Duplicate method '{$method}' from plugin '{$c}' conflicts with same method from plugin '".self::$pluginsStaticMethods[$method]."'");
					return;
				}
				self::$pluginsStaticMethods[$method] = $class;
			}
			if (method_exists($realClass, '__initialize'))
				call_user_func_array(array($realClass, '__initialize'), array());
		}
		// object methods
		if (class_exists('phpQueryObjectPlugin_'.$class)) {
			$realClass = 'phpQueryObjectPlugin_'.$class;
			$vars = get_class_vars($realClass);
			$loop = isset($vars['phpQueryMethods'])
				&& ! is_null($vars['phpQueryMethods'])
				? $vars['phpQueryMethods']
				: get_class_methods($realClass);
			foreach($loop as $method) {
				if (! is_callable(array($realClass, $method)))
					continue;
				if (isset(self::$pluginsMethods[$method])) {
					throw new Exception("Duplicate method '{$method}' from plugin '{$c}' conflicts with same method from plugin '".self::$pluginsMethods[$method]."'");
					continue;
				}
				self::$pluginsMethods[$method] = $class;
			}
		}
		return true;
	}
	/**
	 * Unloades all or specified document from memory.
	 *
	 * @param mixed $documentID @see phpQuery::getDocumentID() for supported types.
	 */
	public static function unloadDocuments($id = null) {
		if (isset($id)) {
			if ($id = self::getDocumentID($id))
				unset(phpQuery::$documents[$id]);
		} else {
			foreach(phpQuery::$documents as $k => $v) {
				unset(phpQuery::$documents[$k]);
			}
		}
	}
	/**
	 * Parses phpQuery object or HTML result against PHP tags and makes them active.
	 *
	 * @param phpQuery|string $content
	 * @deprecated
	 * @return string
	 */
	public static function unsafePHPTags($content) {
		return self::markupToPHP($content);
	}
	public static function DOMNodeListToArray($DOMNodeList) {
		$array = array();
		if (! $DOMNodeList)
			return $array;
		foreach($DOMNodeList as $node)
			$array[] = $node;
		return $array;
	}
	/**
	 * Checks if $input is HTML string, which has to start with '<'.
	 *
	 * @deprecated
	 * @param String $input
	 * @return Bool
	 * @todo still used ?
	 */
	public static function isMarkup($input) {
		return ! is_array($input) && substr(trim($input), 0, 1) == '<';
	}
	public static function debug($text) {
		if (self::$debug)
			print var_dump($text);
	}
	/**
	 * Make an AJAX request.
	 *
	 * @param array See $options http://docs.jquery.com/Ajax/jQuery.ajax#toptions
	 * Additional options are:
	 * 'document' - document for global events, @see phpQuery::getDocumentID()
	 * 'referer' - implemented
	 * 'requested_with' - TODO; not implemented (X-Requested-With)
	 * @return Zend_Http_Client
	 * @link http://docs.jquery.com/Ajax/jQuery.ajax
	 *
	 * @TODO $options['cache']
	 * @TODO $options['processData']
	 * @TODO $options['xhr']
	 * @TODO $options['data'] as string
	 * @TODO XHR interface
	 */
	public static function ajax($options = array(), $xhr = null) {
		$options = array_merge(
			self::$ajaxSettings, $options
		);
		$documentID = isset($options['document'])
			? self::getDocumentID($options['document'])
			: null;
		if ($xhr) {
			// reuse existing XHR object, but clean it up
			$client = $xhr;
//			$client->setParameterPost(null);
//			$client->setParameterGet(null);
			$client->setAuth(false);
			$client->setHeaders("If-Modified-Since", null);
			$client->setHeaders("Referer", null);
			$client->resetParameters();
		} else {
			// create new XHR object
			require_once('Zend/Http/Client.php');
			$client = new Zend_Http_Client();
			$client->setCookieJar();
		}
		if (isset($options['timeout']))
			$client->setConfig(array(
				'timeout'      => $options['timeout'],
			));
//			'maxredirects' => 0,
		foreach(self::$ajaxAllowedHosts as $k => $host)
			if ($host == '.' && isset($_SERVER['HTTP_HOST']))
				self::$ajaxAllowedHosts[$k] = $_SERVER['HTTP_HOST'];
		$host = parse_url($options['url'], PHP_URL_HOST);
		if (! in_array($host, self::$ajaxAllowedHosts)) {
			throw new Exception("Request not permitted, host '$host' not present in "
				."phpQuery::\$ajaxAllowedHosts");
		}
		// JSONP
		$jsre = "/=\\?(&|$)/";
		if (isset($options['dataType']) && $options['dataType'] == 'jsonp') {
			$jsonpCallbackParam = $options['jsonp']
				? $options['jsonp'] : 'callback';
			if (strtolower($options['type']) == 'get') {
				if (! preg_match($jsre, $options['url'])) {
					$sep = strpos($options['url'], '?')
						? '&' : '?';
					$options['url'] .= "$sep$jsonpCallbackParam=?";
				}
			} else if ($options['data']) {
				$jsonp = false;
				foreach($options['data'] as $n => $v) {
					if ($v == '?')
						$jsonp = true;
				}
				if (! $jsonp) {
					$options['data'][$jsonpCallbackParam] = '?';
				}
			}
			$options['dataType'] = 'json';
		}
		if (isset($options['dataType']) && $options['dataType'] == 'json') {
			$jsonpCallback = 'json_'.md5(microtime());
			$jsonpData = $jsonpUrl = false;
			if ($options['data']) {
				foreach($options['data'] as $n => $v) {
					if ($v == '?')
						$jsonpData = $n;
				}
			}
			if (preg_match($jsre, $options['url']))
				$jsonpUrl = true;
			if ($jsonpData !== false || $jsonpUrl) {
				// remember callback name for httpData()
				$options['_jsonp'] = $jsonpCallback;
				if ($jsonpData !== false)
					$options['data'][$jsonpData] = $jsonpCallback;
				if ($jsonpUrl)
					$options['url'] = preg_replace($jsre, "=$jsonpCallback\\1", $options['url']);
			}
		}
		$client->setUri($options['url']);
		$client->setMethod(strtoupper($options['type']));
		if (isset($options['referer']) && $options['referer'])
			$client->setHeaders('Referer', $options['referer']);
		$client->setHeaders(array(
//			'content-type' => $options['contentType'],
			'User-Agent' => 'Mozilla/5.0 (X11; U; Linux x86; en-US; rv:1.9.0.5) Gecko'
				 .'/2008122010 Firefox/3.0.5',
	 		// TODO custom charset
			'Accept-Charset' => 'ISO-8859-1,utf-8;q=0.7,*;q=0.7',
// 	 		'Connection' => 'keep-alive',
// 			'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
	 		'Accept-Language' => 'en-us,en;q=0.5',
		));
		if ($options['username'])
			$client->setAuth($options['username'], $options['password']);
		if (isset($options['ifModified']) && $options['ifModified'])
			$client->setHeaders("If-Modified-Since",
				self::$lastModified
					? self::$lastModified
					: "Thu, 01 Jan 1970 00:00:00 GMT"
			);
		$client->setHeaders("Accept",
			isset($options['dataType'])
			&& isset(self::$ajaxSettings['accepts'][ $options['dataType'] ])
				? self::$ajaxSettings['accepts'][ $options['dataType'] ].", */*"
				: self::$ajaxSettings['accepts']['_default']
		);
		// TODO $options['processData']
		if ($options['data'] instanceof phpQueryObject) {
			$serialized = $options['data']->serializeArray($options['data']);
			$options['data'] = array();
			foreach($serialized as $r)
				$options['data'][ $r['name'] ] = $r['value'];
		}
		if (strtolower($options['type']) == 'get') {
			$client->setParameterGet($options['data']);
		} else if (strtolower($options['type']) == 'post') {
			$client->setEncType($options['contentType']);
			$client->setParameterPost($options['data']);
		}
		if (self::$active == 0 && $options['global'])
			phpQueryEvents::trigger($documentID, 'ajaxStart');
		self::$active++;
		// beforeSend callback
		if (isset($options['beforeSend']) && $options['beforeSend'])
			phpQuery::callbackRun($options['beforeSend'], array($client));
		// ajaxSend event
		if ($options['global'])
			phpQueryEvents::trigger($documentID, 'ajaxSend', array($client, $options));
		if (phpQuery::$debug) {
			self::debug("{$options['type']}: {$options['url']}\n");
			self::debug("Options: <pre>".var_export($options, true)."</pre>\n");
//			if ($client->getCookieJar())
//				self::debug("Cookies: <pre>".var_export($client->getCookieJar()->getMatchingCookies($options['url']), true)."</pre>\n");
		}
		// request
		$response = $client->request();
		if (phpQuery::$debug) {
			self::debug('Status: '.$response->getStatus().' / '.$response->getMessage());
			self::debug($client->getLastRequest());
			self::debug($response->getHeaders());
		}
		if ($response->isSuccessful()) {
			// XXX tempolary
			self::$lastModified = $response->getHeader('Last-Modified');
			$data = self::httpData($response->getBody(), $options['dataType'], $options);
			if (isset($options['success']) && $options['success'])
				phpQuery::callbackRun($options['success'], array($data, $response->getStatus(), $options));
			if ($options['global'])
				phpQueryEvents::trigger($documentID, 'ajaxSuccess', array($client, $options));
		} else {
			if (isset($options['error']) && $options['error'])
				phpQuery::callbackRun($options['error'], array($client, $response->getStatus(), $response->getMessage()));
			if ($options['global'])
				phpQueryEvents::trigger($documentID, 'ajaxError', array($client, /*$response->getStatus(),*/$response->getMessage(), $options));
		}
		if (isset($options['complete']) && $options['complete'])
			phpQuery::callbackRun($options['complete'], array($client, $response->getStatus()));
		if ($options['global'])
			phpQueryEvents::trigger($documentID, 'ajaxComplete', array($client, $options));
		if ($options['global'] && ! --self::$active)
			phpQueryEvents::trigger($documentID, 'ajaxStop');
		return $client;
//		if (is_null($domId))
//			$domId = self::$defaultDocumentID ? self::$defaultDocumentID : false;
//		return new phpQueryAjaxResponse($response, $domId);
	}
	protected static function httpData($data, $type, $options) {
		if (isset($options['dataFilter']) && $options['dataFilter'])
			$data = self::callbackRun($options['dataFilter'], array($data, $type));
		if (is_string($data)) {
			if ($type == "json") {
				if (isset($options['_jsonp']) && $options['_jsonp']) {
					$data = preg_replace('/^\s*\w+\((.*)\)\s*$/s', '$1', $data);
				}
				$data = self::parseJSON($data);
			}
		}
		return $data;
	}
	/**
	 * Enter description here...
	 *
	 * @param array|phpQuery $data
	 *
	 */
	public static function param($data) {
		return http_build_query($data, null, '&');
	}
	public static function get($url, $data = null, $callback = null, $type = null) {
		if (!is_array($data)) {
			$callback = $data;
			$data = null;
		}
		// TODO some array_values on this shit
		return phpQuery::ajax(array(
			'type' => 'GET',
			'url' => $url,
			'data' => $data,
			'success' => $callback,
			'dataType' => $type,
		));
	}
	public static function post($url, $data = null, $callback = null, $type = null) {
		if (!is_array($data)) {
			$callback = $data;
			$data = null;
		}
		return phpQuery::ajax(array(
			'type' => 'POST',
			'url' => $url,
			'data' => $data,
			'success' => $callback,
			'dataType' => $type,
		));
	}
	public static function getJSON($url, $data = null, $callback = null) {
		if (!is_array($data)) {
			$callback = $data;
			$data = null;
		}
		// TODO some array_values on this shit
		return phpQuery::ajax(array(
			'type' => 'GET',
			'url' => $url,
			'data' => $data,
			'success' => $callback,
			'dataType' => 'json',
		));
	}
	public static function ajaxSetup($options) {
		self::$ajaxSettings = array_merge(
			self::$ajaxSettings,
			$options
		);
	}
	public static function ajaxAllowHost($host1, $host2 = null, $host3 = null) {
		$loop = is_array($host1)
			? $host1
			: func_get_args();
		foreach($loop as $host) {
			if ($host && ! in_array($host, phpQuery::$ajaxAllowedHosts)) {
				phpQuery::$ajaxAllowedHosts[] = $host;
			}
		}
	}
	public static function ajaxAllowURL($url1, $url2 = null, $url3 = null) {
		$loop = is_array($url1)
			? $url1
			: func_get_args();
		foreach($loop as $url)
			phpQuery::ajaxAllowHost(parse_url($url, PHP_URL_HOST));
	}
	/**
	 * Returns JSON representation of $data.
	 *
	 * @static
	 * @param mixed $data
	 * @return string
	 */
	public static function toJSON($data) {
		if (function_exists('json_encode'))
			return json_encode($data);
		require_once('Zend/Json/Encoder.php');
		return Zend_Json_Encoder::encode($data);
	}
	/**
	 * Parses JSON into proper PHP type.
	 *
	 * @static
	 * @param string $json
	 * @return mixed
	 */
	public static function parseJSON($json) {
		if (function_exists('json_decode')) {
			$return = json_decode(trim($json), true);
			// json_decode and UTF8 issues
			if (isset($return))
				return $return;
		}
		require_once('Zend/Json/Decoder.php');
		return Zend_Json_Decoder::decode($json);
	}
	/**
	 * Returns source's document ID.
	 *
	 * @param $source DOMNode|phpQueryObject
	 * @return string
	 */
	public static function getDocumentID($source) {
		if ($source instanceof DOMDOCUMENT) {
			foreach(phpQuery::$documents as $id => $document) {
				if ($source->isSameNode($document->document))
					return $id;
			}
		} else if ($source instanceof DOMNODE) {
			foreach(phpQuery::$documents as $id => $document) {
				if ($source->ownerDocument->isSameNode($document->document))
					return $id;
			}
		} else if ($source instanceof phpQueryObject)
			return $source->getDocumentID();
		else if (is_string($source) && isset(phpQuery::$documents[$source]))
			return $source;
	}
	/**
	 * Get DOMDocument object related to $source.
	 * Returns null if such document doesn't exist.
	 *
	 * @param $source DOMNode|phpQueryObject|string
	 * @return string
	 */
	public static function getDOMDocument($source) {
		if ($source instanceof DOMDOCUMENT)
			return $source;
		$source = self::getDocumentID($source);
		return $source
			? self::$documents[$id]['document']
			: null;
	}

	// UTILITIES
	// http://docs.jquery.com/Utilities

	/**
	 *
	 * @return unknown_type
	 * @link http://docs.jquery.com/Utilities/jQuery.makeArray
	 */
	public static function makeArray($obj) {
		$array = array();
		if (is_object($object) && $object instanceof DOMNODELIST) {
			foreach($object as $value)
				$array[] = $value;
		} else if (is_object($object) && ! ($object instanceof Iterator)) {
			foreach(get_object_vars($object) as $name => $value)
				$array[0][$name] = $value;
		} else {
			foreach($object as $name => $value)
				$array[0][$name] = $value;
		}
		return $array;
	}
	public static function inArray($value, $array) {
		return in_array($value, $array);
	}
	/**
	 *
	 * @param $object
	 * @param $callback
	 * @return unknown_type
	 * @link http://docs.jquery.com/Utilities/jQuery.each
	 */
	public static function each($object, $callback, $param1 = null, $param2 = null, $param3 = null) {
		$paramStructure = null;
		if (func_num_args() > 2) {
			$paramStructure = func_get_args();
			$paramStructure = array_slice($paramStructure, 2);
		}
		if (is_object($object) && ! ($object instanceof Iterator)) {
			foreach(get_object_vars($object) as $name => $value)
				phpQuery::callbackRun($callback, array($name, $value), $paramStructure);
		} else {
			foreach($object as $name => $value)
				phpQuery::callbackRun($callback, array($name, $value), $paramStructure);
		}
	}
	/**
	 *
	 * @link http://docs.jquery.com/Utilities/jQuery.map
	 */
	public static function map($array, $callback, $param1 = null, $param2 = null, $param3 = null) {
		$result = array();
		$paramStructure = null;
		if (func_num_args() > 2) {
			$paramStructure = func_get_args();
			$paramStructure = array_slice($paramStructure, 2);
		}
		foreach($array as $v) {
			$vv = phpQuery::callbackRun($callback, array($v), $paramStructure);
//			$callbackArgs = $args;
//			foreach($args as $i => $arg) {
//				$callbackArgs[$i] = $arg instanceof CallbackParam
//					? $v
//					: $arg;
//			}
//			$vv = call_user_func_array($callback, $callbackArgs);
			if (is_array($vv))  {
				foreach($vv as $vvv)
					$result[] = $vvv;
			} else if ($vv !== null) {
				$result[] = $vv;
			}
		}
		return $result;
	}
	/**
	 *
	 * @param $callback Callback
	 * @param $params
	 * @param $paramStructure
	 * @return unknown_type
	 */
	public static function callbackRun($callback, $params = array(), $paramStructure = null) {
		if (! $callback)
			return;
		if ($callback instanceof CallbackParameterToReference) {
			// TODO support ParamStructure to select which $param push to reference
			if (isset($params[0]))
				$callback->callback = $params[0];
			return true;
		}
		if ($callback instanceof Callback) {
			$paramStructure = $callback->params;
			$callback = $callback->callback;
		}
		if (! $paramStructure)
			return call_user_func_array($callback, $params);
		$p = 0;
		foreach($paramStructure as $i => $v) {
			$paramStructure[$i] = $v instanceof CallbackParam
				? $params[$p++]
				: $v;
		}
		return call_user_func_array($callback, $paramStructure);
	}
	/**
	 * Merge 2 phpQuery objects.
	 * @param array $one
	 * @param array $two
	 * @protected
	 * @todo node lists, phpQueryObject
	 */
	public static function merge($one, $two) {
		$elements = $one->elements;
		foreach($two->elements as $node) {
			$exists = false;
			foreach($elements as $node2) {
				if ($node2->isSameNode($node))
					$exists = true;
			}
			if (! $exists)
				$elements[] = $node;
		}
		return $elements;
//		$one = $one->newInstance();
//		$one->elements = $elements;
//		return $one;
	}
	/**
	 *
	 * @param $array
	 * @param $callback
	 * @param $invert
	 * @return unknown_type
	 * @link http://docs.jquery.com/Utilities/jQuery.grep
	 */
	public static function grep($array, $callback, $invert = false) {
		$result = array();
		foreach($array as $k => $v) {
			$r = call_user_func_array($callback, array($v, $k));
			if ($r === !(bool)$invert)
				$result[] = $v;
		}
		return $result;
	}
	public static function unique($array) {
		return array_unique($array);
	}
	/**
	 *
	 * @param $function
	 * @return unknown_type
	 * @TODO there are problems with non-static methods, second parameter pass it
	 * 	but doesnt verify is method is really callable
	 */
	public static function isFunction($function) {
		return is_callable($function);
	}
	public static function trim($str) {
		return trim($str);
	}
	/* PLUGINS NAMESPACE */
	/**
	 *
	 * @param $url
	 * @param $callback
	 * @param $param1
	 * @param $param2
	 * @param $param3
	 * @return phpQueryObject
	 */
	public static function browserGet($url, $callback, $param1 = null, $param2 = null, $param3 = null) {
		if (self::plugin('WebBrowser')) {
			$params = func_get_args();
			return self::callbackRun(array(self::$plugins, 'browserGet'), $params);
		} else {
			self::debug('WebBrowser plugin not available...');
		}
	}
	/**
	 *
	 * @param $url
	 * @param $data
	 * @param $callback
	 * @param $param1
	 * @param $param2
	 * @param $param3
	 * @return phpQueryObject
	 */
	public static function browserPost($url, $data, $callback, $param1 = null, $param2 = null, $param3 = null) {
		if (self::plugin('WebBrowser')) {
			$params = func_get_args();
			return self::callbackRun(array(self::$plugins, 'browserPost'), $params);
		} else {
			self::debug('WebBrowser plugin not available...');
		}
	}
	/**
	 *
	 * @param $ajaxSettings
	 * @param $callback
	 * @param $param1
	 * @param $param2
	 * @param $param3
	 * @return phpQueryObject
	 */
	public static function browser($ajaxSettings, $callback, $param1 = null, $param2 = null, $param3 = null) {
		if (self::plugin('WebBrowser')) {
			$params = func_get_args();
			return self::callbackRun(array(self::$plugins, 'browser'), $params);
		} else {
			self::debug('WebBrowser plugin not available...');
		}
	}
	/**
	 *
	 * @param $code
	 * @return string
	 */
	public static function php($code) {
		return self::code('php', $code);
	}
	/**
	 *
	 * @param $type
	 * @param $code
	 * @return string
	 */
	public static function code($type, $code) {
		return "<$type><!-- ".trim($code)." --></$type>";
	}

	public static function __callStatic($method, $params) {
		return call_user_func_array(
			array(phpQuery::$plugins, $method),
			$params
		);
	}
	protected static function dataSetupNode($node, $documentID) {
		// search are return if alredy exists
		foreach(phpQuery::$documents[$documentID]->dataNodes as $dataNode) {
			if ($node->isSameNode($dataNode))
				return $dataNode;
		}
		// if doesn't, add it
		phpQuery::$documents[$documentID]->dataNodes[] = $node;
		return $node;
	}
	protected static function dataRemoveNode($node, $documentID) {
		// search are return if alredy exists
		foreach(phpQuery::$documents[$documentID]->dataNodes as $k => $dataNode) {
			if ($node->isSameNode($dataNode)) {
				unset(self::$documents[$documentID]->dataNodes[$k]);
				unset(self::$documents[$documentID]->data[ $dataNode->dataID ]);
			}
		}
	}
	public static function data($node, $name, $data, $documentID = null) {
		if (! $documentID)
			// TODO check if this works
			$documentID = self::getDocumentID($node);
		$document = phpQuery::$documents[$documentID];
		$node = self::dataSetupNode($node, $documentID);
		if (! isset($node->dataID))
			$node->dataID = ++phpQuery::$documents[$documentID]->uuid;
		$id = $node->dataID;
		if (! isset($document->data[$id]))
			$document->data[$id] = array();
		if (! is_null($data))
			$document->data[$id][$name] = $data;
		if ($name) {
			if (isset($document->data[$id][$name]))
				return $document->data[$id][$name];
		} else
			return $id;
	}
	public static function removeData($node, $name, $documentID) {
		if (! $documentID)
			// TODO check if this works
			$documentID = self::getDocumentID($node);
		$document = phpQuery::$documents[$documentID];
		$node = self::dataSetupNode($node, $documentID);
		$id = $node->dataID;
		if ($name) {
			if (isset($document->data[$id][$name]))
				unset($document->data[$id][$name]);
			$name = null;
			foreach($document->data[$id] as $name)
				break;
			if (! $name)
				self::removeData($node, $name, $documentID);
		} else {
			self::dataRemoveNode($node, $documentID);
		}
	}
}
/**
 * Plugins static namespace class.
 *
 * @author Tobiasz Cudnik <tobiasz.cudnik/gmail.com>
 * @package phpQuery
 * @todo move plugin methods here (as statics)
 */
class phpQueryPlugins {
	public function __call($method, $args) {
		if (isset(phpQuery::$extendStaticMethods[$method])) {
			$return = call_user_func_array(
				phpQuery::$extendStaticMethods[$method],
				$args
			);
		} else if (isset(phpQuery::$pluginsStaticMethods[$method])) {
			$class = phpQuery::$pluginsStaticMethods[$method];
			$realClass = "phpQueryPlugin_$class";
			$return = call_user_func_array(
				array($realClass, $method),
				$args
			);
			return isset($return)
				? $return
				: $this;
		} else
			throw new Exception("Method '{$method}' doesnt exist");
	}
}
/**
 * Shortcut to phpQuery::pq($arg1, $context)
 * Chainable.
 *
 * @see phpQuery::pq()
 * @return phpQueryObject|QueryTemplatesSource|QueryTemplatesParse|QueryTemplatesSourceQuery
 * @author Tobiasz Cudnik <tobiasz.cudnik/gmail.com>
 * @package phpQuery
 */
function pq($arg1, $context = null) {
	$args = func_get_args();
	return call_user_func_array(
		array('phpQuery', 'pq'),
		$args
	);
}
// add plugins dir and Zend framework to include path
set_include_path(
	get_include_path()
		.PATH_SEPARATOR.dirname(__FILE__).'/phpQuery/'
		.PATH_SEPARATOR.dirname(__FILE__).'/phpQuery/plugins/'
);
// why ? no __call nor __get for statics in php...
// XXX __callStatic will be available in PHP 5.3
phpQuery::$plugins = new phpQueryPlugins();
// include bootstrap file (personal library config)
if (file_exists(dirname(__FILE__).'/phpQuery/bootstrap.php'))
	require_once dirname(__FILE__).'/phpQuery/bootstrap.php';
?><?php

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