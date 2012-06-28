<?php
//класс стандартных методов

class wrapper
{
    const  ACC_TOK_WRK = '35b9bd2b3dbdfebd3dbdfebd6e3d96a03933dbd3db8c62b879c7877d660642a';
    const VK_API_URL = 'https://api.vk.com/method/';
    const TESTING = false;
    public $db;
    public $id; // id паблика
    public $q_result;

//    public function __construct()
//    {
//        //require_once 'config.inc.php';
//        //if (!$this->db_wrap('connect', $db_config))
//          //      die('bd lost');
//
//    }

    public function vk_api_wrap($method, array $params, $ex = 1)
    {
        $params['access_token']  =  self::ACC_TOK_WRK;
        $url = self::VK_API_URL . $method;
        $res = json_decode($this->qurl_request($url, $params));

        if (isset($res->error))
            if ($ex)
                throw new Exception('Error : ' . $res->error->error_msg . ' on params ' . json_encode($params));
            else
                return $res->error;
        return $res->response;
    }

    //return morning timesamp
    public function morning($timestamp)
    {
        $date = date('m d Y', $timestamp);
        $date = explode(' ', $date);
        return  mktime(0, 0, 0, $date[0], $date[1], $date[2]);
    }

    public function db_wrap($meth, $data='')
    {
        switch ($meth) {
            case 'connect':

//                $connect_line = "host={$data['host']} user={$data['user']}
//                    password={$data['pass']} dbname={$data['name']}";
                $connect_line = "host=localhost user=postgres
                    password=qqqqqq dbname=muspel";
                $this->db = pg_connect($connect_line);
                return true;

            case 'query':
                $res = pg_query($this->db, $data);
                if(!$res) {
                    throw new Exception('Ошибка при работе с бд : '
                        . pg_errormessage($this->db));
                }
//                if (self::TESTING)
                echo '<br>' . $data . '<br>';

                $this->q_result = $res;

                return true;
            case 'get_row':
                $row = pg_fetch_array($this->q_result);

                return $row;
            default:
                echo 'Неправильный метод! <br>';
                break;
        }
    }

    public function get_result() {
        return pg_fetch_assoc($this->q_result);
    }

    public function db_wrap_msql($meth, $data='')
    {
        switch ($meth){
            case 'connect':
                print_r($data);
                $this->db = new mysqli(
                                            $data['host'],
                                            $data['user'],
                                            $data['pass'],
                                            $data['name']
                                        );
                if (mysqli_connect_errno())   throw new Exception('Ошибка при работе с бд : '
                        . mysqli_connect_error());

                $this->db->select_db('muspel');
                 if ($this->db->errno)
                    throw new Exception('Ошибка при работе с бд : '
                        . $this->db->error );
                return true;

            case 'query':
//                if (self::TESTING)
                    echo '<br>' . $data . '<br>';
                $result = $this->db->query($data);
                if ($this->db->errno)
                    throw new Exception('Ошибка при работе с бд : '
                        . $this->db->error . ' on query: ' . $data);

//                if (self::TESTING)
                    echo '<br>строк затронуто: ' . $this->db->affected_rows . '<br>';
                return $result;

            default:
                echo 'Неправильный метод! <br>';
                break;
        }
    }

    public function qurl_request($url, $arr_of_fields, $headers = '', $uagent = '')
    {
            if (empty($url)) {
                return false;
            }
            if (self::TESTING) {
                echo '<br>данные для запроса <br>';
                print_r($arr_of_fields);
                echo '<br>';
            }
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
//            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

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
                return 'error in curl: '. curl_error($ch);
            }

            if (self::TESTING) {
                echo '<br>ответ <br>';
                print_r($result);
                echo '<br>';
            }
            curl_close($ch);
            return $result;
        }

    public function get_publics()
    {
        $sql = "select vk_id,name from publics where active=1";
        $res_sql = $this->db_wrap('query', $sql);
        while ($row = $res_sql->fetch_assoc()) {
            $ids[] = array($row['vk_id'], $row['name']);
        }
        return $ids;
    }

    public function mailer($text, $adress = 'askaslie@gmail.com')
    {
        $text = str_replace("\n.", "\n..", $text);
        $text = wordwrap($text, 70);
        $headers = 'From: askaslie@gmail.com' . "\r\n";
        mail($adress, __CLASS__, $text, $headers);
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
                                'name'       =>     $name[1],
                                'short_name' =>     $short_name
                            );
        }
        return false;
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
                       file_put_contents('1.txt', $a);
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
}
?>
