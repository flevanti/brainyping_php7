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
$current_dir = realpath(dirname(__FILE__));
$agent = "Mozilla/5.0 (iPhone; U; CPU iPhone OS 4_3_3 like Mac OS X; en-us) AppleWebKit/533.17.9 (KHTML, like Gecko) Version/5.0.2 Mobile/8J2 Safari/6533.18.5";
$cookiefile = uniqid("cookiefile_", true);
$cookiejar = uniqid("cookiejar_", true);

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

$ch = curl_init();
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_NOBODY, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
//curl_setopt($ch,CURLOPT_COOKIESESSION,true);
//curl_setopt($ch, CURLOPT_COOKIEFILE, $current_dir."/cookies/$cookiefile");
//curl_setopt($ch, CURLOPT_COOKIEJAR, $current_dir."/cookies/$cookiejar");
curl_setopt($ch, CURLOPT_USERAGENT, $agent);


foreach ($hosts_list as $host_record) {

    curl_setopt($ch, CURLOPT_URL, $host_record->url);
    curl_exec($ch);
    $curl_info = curl_getinfo($ch);

    $total_time = $curl_info['total_time'];
    $http_code = $curl_info['http_code'];
    $host = parse_url($curl_info['url']);
    $host = $host['scheme']."://".$host['host']."/";

    $curl_info = null;
    unset($curl_info);

    DB::update(
        "update hosts set host=?, time_spent=?, http_code=? where id_ai__=?;",
        [$host, $total_time, $http_code, $host_record->id_ai__]
    );

    $this->e($host_record->url." ---> $http_code   $total_time   $host");

}

curl_close($ch);
