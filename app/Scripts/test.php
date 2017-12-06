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
$agent = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2227.1 Safari/537.36";
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
//curl_setopt($ch, CURLOPT_NOBODY, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
//curl_setopt($ch,CURLOPT_COOKIESESSION,true);
//curl_setopt($ch, CURLOPT_COOKIEFILE, $current_dir."/cookies/$cookiefile");
//curl_setopt($ch, CURLOPT_COOKIEJAR, $current_dir."/cookies/$cookiejar");
curl_setopt($ch, CURLOPT_USERAGENT, $agent);


foreach ($hosts_list as $host_record) {
    if (gethostbyname($host_record->url) == $host_record->url) {
        //domain not found
        DB::update(
            "update hosts set http_code='domainfail' where id_ai__=?;",
            [$host_record->id_ai__]
        );
        $this->e($host_record->url." ---> Domain not found");
        continue;
    }

    curl_setopt($ch, CURLOPT_URL, $host_record->url);
    curl_exec($ch);
    $curl_info = curl_getinfo($ch);

    $total_time = $curl_info['total_time'] ?? 0;
    $http_code = $curl_info['http_code'] ?? 'error';
    if (isset($curl_info['url'])) {
        $host = parse_url($curl_info['url']);
        $host = $host['scheme']."://".$host['host']."/";
    } else {
        $host = null;
    }

    $curl_info = null;
    unset($curl_info);

    DB::update(
        "update hosts set host=?, time_spent=?, http_code=? where id_ai__=?;",
        [$host, $total_time, $http_code, $host_record->id_ai__]
    );

    $this->e($host_record->url." ---> $http_code   $total_time   $host");
}

curl_close($ch);
