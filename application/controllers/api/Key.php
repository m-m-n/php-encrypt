<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Key extends CI_Controller {

    private static $LOCKFILE = '/dev/shm/_pkey_lock_';
    private static $PUBLIC_KEY_PATH = APPPATH.'cache/public.key';
    private static $PRIVATE_KEY_PATH = APPPATH.'cache/private.key';
    private static $EXPIRE_PATH = APPPATH.'cache/expire';
    private static $KEYTYPE = OPENSSL_KEYTYPE_RSA;

    public function get()
    {
        while(self::_locked()) {
            sleep(1);
        }
        if (!self::_checkKeyFiles()) {
            self::_createKey();
        }

        $key = file_get_contents(self::$PUBLIC_KEY_PATH);
        $this->output->set_content_type('application/json', 'utf8')->set_output(json_encode([
            'type' => self::$KEYTYPE,
            'key' => $key,
        ]));
    }

    private static function _checkKeyFiles()
    {
        self::_lock();
        $r = file_exists(self::$PUBLIC_KEY_PATH) &&
            file_exists(self::$PRIVATE_KEY_PATH) &&
            file_exists(self::$EXPIRE_PATH);
        if ($r) {
            self::_unlock();
        }
        return $r;
    }

    private static function _createKey()
    {
        $key = openssl_pkey_new([
            'digest_alg' => 'sha512',
            'private_key_bits' => 8192,
            'private_key_type' => self::$KEYTYPE,
        ]);

        openssl_pkey_export($key, $privateKey);
        file_put_contents(self::$PRIVATE_KEY_PATH, $privateKey, LOCK_EX);
        $details = openssl_pkey_get_details($key);
        $publicKey = $details['key'];
        file_put_contents(self::$PUBLIC_KEY_PATH, $publicKey, LOCK_EX);
        file_put_contents(self::$EXPIRE_PATH, time() + (60*60*24*7), LOCK_EX);
        self::_unlock();
    }

    private static function _lock()
    {
        file_put_contents(self::$LOCKFILE, '');
    }

    private static function _unlock()
    {
        unlink(self::$LOCKFILE);
    }

    private static function _locked()
    {
        return file_exists(self::$LOCKFILE);
    }

}
