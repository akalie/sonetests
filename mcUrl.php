<?php
    $a  = file_get_contents(urldecode(stripslashes('http:/\/api.vk.com\/captcha.php?sid=467264424848&s=1')));
    file_put_contents('1.jpg', $a)

?>
