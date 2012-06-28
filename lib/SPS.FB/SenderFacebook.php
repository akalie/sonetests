<?php
class SenderFacebook 
{
    const TESTING = 0;
    private $data_array;
    const METH = 'https://graph.facebook.com/';

    public  function __construct($data_array)
    {
        //file_put_contents('tmp.txt',$data_array);
        $data_array = json_decode($data_array);
        $data_array->targeting = json_encode($data_array->targeting);
        $data_array->message = $this->remove_tags($data_array->message);
        $this->album = $data_array->album;
        $url = 'https://graph.facebook.com/me/accounts?access_token='   . $data_array->access_token;
        $res = $this->qurl_request($url);
        if (self::TESTING)
            echo '<br>' . $res . '<br>';
        foreach (json_decode($res)->data as $page) {
            if ($page->name == 'Topface Girls') {
                $data_array->access_token = $page->access_token;
                $this->data_array = $data_array;
		//file_put_contents('tmp2.txt',$page->access_token);
                return;
            }
        }
    }

    private function qurl_request($url, $arr_of_fields = 0, $headers = '', $uagent = '')
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
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


        curl_setopt($ch, CURLOPT_HEADER, 0);
        if (is_array($arr_of_fields)) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $arr_of_fields);

        } ;#else return false;

        $result = curl_exec($ch);
        if (curl_errno($ch)){
             throw new exception("error in curl: ". curl_error($ch) );
        }

        curl_close($ch);
        return $result;
    }

    private function remove_tags($text)
    {
         $text = str_replace( '<br>', "\r\n", $text );
         $text = htmlspecialchars_decode($text);
         $text = html_entity_decode($text);
         $text = strip_tags( $text );
         return $text;
    }

    //пока мимо, ссылка на сторонний ресурс
    public  function send_post()
    {
        $url = self::METH . $this->data_array->page . '/feed/';
//        echo $url . '<br>';
        unset($this->data_array->page);
        $this->data_array = (array)$this->data_array;
        $result = $this->qurl_request($url, $this->data_array);
        $result = json_decode($result);

        if (isset($result->error)){
            throw new exception($result->error->message);
        }

        return $result->id;
    }

    public  function send_photo()
    {
        $url = self::METH . $this->album . '/photos/';
        echo $url . '<br>';
        unset($this->data_array->page);
        unset($this->data_array->album);
        $this->data_array = (array) $this->data_array;
        $result = $this->qurl_request($url, $this->data_array);
        $result = json_decode($result);

        if (isset($result->error)) {
            throw new exception($result->error->message);
        }

        return $result->id;
    }

    public  function send_album($album)
    {
        $url = self::METH . $album . '/photos/';
        echo $url . '<br>';
        unset($this->data_array->page);
        $this->data_array = (array)  $this->data_array;
        $result = $this->qurl_request($url, $this->data_array);
        $result = json_decode($result);

        if (isset($result->error)) {
            throw new exception($result->error->message);
        }

        return $result->id;
    }
}
?>