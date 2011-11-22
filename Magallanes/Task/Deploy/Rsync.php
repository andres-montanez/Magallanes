<?php
class Magallanes_Task_Deploy_Rsync
{
    public static function exec($user, $host, $from, $to)
    {
        $output = array();
        $command = 'rsync -avz ' . $from . ' ' . $user . '@' . $host . ':' . $to;
        exec($command . ' 2>&1 ', $command, $result);
        return $result;
    }
}