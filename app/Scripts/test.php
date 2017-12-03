<?php
/**
 * Created by PhpStorm.
 * User: francescolevanti
 * Date: 03/12/2017
 * Time: 13:21
 */

/** @var  $this \App\Console\Commands\scr */
$sql = "select * from hosts where http_code is null ";
$sql_args = [];

if (isset($args['batch_id'])) {
    $sql .= " and batch_id=?";
    $sql_args[] = (int)$args['batch_id'];
    $this->e("Batch id is ".(int)$args['batch_id']);
} else {
    $this->e("No batch id provided");
    $sql .= " limit 10";
}

$hosts_list = DB::select($sql, $sql_args);

$this->e("Records retrieved: ".count($hosts_list));

foreach ($hosts_list as $host_record) {
    //$this->e("processing {$host->url}...");

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $host_record->url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    curl_exec($ch);
    $curl_info = curl_getinfo($ch);

    $total_time = $curl_info['total_time'];
    $http_code = $curl_info['http_code'];
    $host = parse_url($curl_info['url']);
    $host = $host['scheme']."://".$host['host']."/";

    curl_close($ch);
    $ch = $curl_info = null;
    unset($ch, $curl_info);

    DB::update(
        "update hosts set host=?, time_spent=?, http_code=? where id_ai__=?;",
        [$host, $total_time, $http_code, $host_record->id_ai__]
    );

    $this->e($host_record->url." ---> $http_code   $total_time   $host");


}

