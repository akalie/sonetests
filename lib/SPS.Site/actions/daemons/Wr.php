<?php
    Package::Load( 'SPS.Articles' );
    Package::Load( 'SPS.Site' );



    class Wr extends wrapper
    {
    const TESTING = false;
    const BASE_RENEW = true;
    public $id; // id паблика

    public function Execute()
    {
            $this->db_wrap('connect');

            if (self::BASE_RENEW)
                $this->clear_chk();
            while($this->publ_selector()) {
        //        die('Нету норм пабликов');

                $offset = $this->get_offset();
                $offset  = +$offset ? +$offset : 0;

                while( $this->get_posts($offset)) {
                    $offset += 100;
                    $this->set_offset($offset);
                    echo '<br>'.'посылаем оффсет ' . $offset . '<br>';
                }

                $this->set_offset(0);
                $this->set_checktime();
            }
        }

    public function get_posts($offset = 0)
    {
        echo '<br>вемя = ' . (time()-86400) . '<br>';
        $sql = 'SELECT MAX(time_st) FROM posts_for_likes where vk_id=' . $this->id;
        $res = $this->db_wrap('query', $sql);

        $row = $this->db_wrap('get_row');
        if (!$row or self::BASE_RENEW)
            $date_from = 0;
        else
            $date_from = $row['max_date'];
        //

        $date_to = time() - 86400;
        echo 'мониторим с ' . date('d-m-Y',$date_from) . ' по ' . date('d-m-Y',$date_to);
        if ($date_from - $date_to > 0) {
            echo "<br>$date_from > $date_to <br>";
            echo 'date from > date to <br>';
            return false;
        }
        $params  = array(   'owner_id'       =>        '-' . $this->id,
                            'offset'         =>        $offset,
                            'count'          =>        100,
                            'filter'         =>        'owner'
        );

        $res = $this->vk_api_wrap('wall.get', $params);
        sleep(0.3);
        if (count($res) < 3) {
            echo 'abyrvalg';
            return false;
        }
        unset($res[0]);
        echo '<br>'.'offset = '.$offset.'<br>';

        $ids = array();
        $end_trig = 0;
        foreach($res as $rr) {
            if ($rr->date > $date_to) {

                echo date('H:i d:m:Y', $rr->date).' > '. date('H:i d:m:Y', $date_to) . '<br>';
                continue;
            }
            if ($rr->date < $date_from) {
                echo date('H:i d:m:Y', $rr->date).' < '. date('H:i d:m:Y', $date_from) . '<br>';
                print_r($rr);
                echo 'End trigger  = 1<br>';
                $end_trig = 1;
                break;
            }

            $values .= "'" . $this->id . '_' . $rr->id . "'," .$rr->date . "," . $rr->comments->count. "," .
                    $rr->likes->count. "," . $rr->reposts->count . '),(';
            $ids[] = $rr->id;
        }

        $values = trim($values, ',(');
        echo '<br>values = ' . $values . '<br>';
        if (strlen($values) > 5) {
            $sql = 'INSERT IGNORE posts_for_likes(vk_id,time_st,comments,likes,reposts)VALUES (' . $values;
            $res = $this->db_wrap('query', $sql);
        }

        if (!$end_trig) {
            echo 'now must be second circle<br>';
            return true;
        }
        else {
            echo 'wtf&<br>';
            return false;

        }

    }

    public function clear_chk() {
        $sql = 'UPDATE publics SET check_time=0';
        $this->db_wrap('query', $sql);
    }

    //выбирает паблик, для которого будут обновлятся данные
    public function publ_selector()
    {
        $sql = 'SELECT vk_id FROM publics WHERE check_time<' . (time() - 86400) . ' AND active=1 order by id' ;
        $res = $this->db_wrap('query', $sql);

        //$res->fetch_assoc(
        while($row = $this->db_wrap('get_row')) {
            if (self::TESTING)
                echo '<br>' . $row['vk_id'] . '<br>';
            $this->id = $row['vk_id'];
            return true;
        }
        return false;
    }

    public function set_checktime()
    {
        $sql = 'UPDATE publics SET check_time=' . time() . ' WHERE vk_id=' . $this->id;
        if (self::TESTING)
            echo '<br>'.$sql.'<br>';
        $this->db_wrap('query', $sql);
    }

    public function set_offset($offset = 0)
    {
        $sql = 'UPDATE publics SET offset=' . $offset . ' WHERE vk_id=' . $this->id;
        if (self::TESTING)
            echo '<br>'.$sql.'<br>';
        $this->db_wrap('query', $sql);
    }

    public function get_offset()
    {
        $sql = 'SELECT "offset" FROM publics WHERE vk_id=' . $this->id;
        if (self::TESTING)
            echo '<br>'.$sql.'<br>';
        if ( $this->db_wrap('query', $sql))
        $a = $this->db_wrap('get_row');

        return $a['offset'];
    }
}

?>