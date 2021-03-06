<?php
/**
 * Created by PhpStorm.
 * User: maps_red
 * Date: 17/11/16
 * Time: 12:10
 */

namespace UserBundle\Security;


class Generator
{
    /**
     * @param $length
     * @param string $keyspace
     * @return string
     * @throws \Exception
     */
    public function random_str(
        $length,
        $keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'
    ) {
        $str = '';
        $max = mb_strlen($keyspace, '8bit') - 1;
        if ($max < 1) {
            throw new \Exception('$keyspace must be at least two characters long');
        }
        for ($i = 0; $i < $length; ++$i) {
            $str .= $keyspace[random_int(0, $max)];
        }

        return $str;
    }

    /**
     * @return string
     */
    public function generateAPIKey()
    {
        return md5(microtime().rand());
    }
}