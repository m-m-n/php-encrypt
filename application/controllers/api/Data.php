<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Data extends CI_Controller {

    private static $PRIVATE_KEY_PATH = APPPATH.'cache/private.key';

    public function echo()
    {
        $json = file_get_contents('php://input');
        $obj = json_decode($json);

        $private_key = file_get_contents(self::$PRIVATE_KEY_PATH);

        $c_pass = base64_decode($obj->key);
        $algo = $obj->algo;
        $data = base64_decode($obj->data);
        $iv = base64_decode($obj->iv);

        openssl_private_decrypt($c_pass, $pass, $private_key);
        $plain = openssl_decrypt($data, $algo, $pass, OPENSSL_RAW_DATA, $iv);

        $this->output->set_content_type('text/plain')->set_output($plain);
    }

}
