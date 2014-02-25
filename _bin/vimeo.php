<?php
$url = 'http://vimeo.com/api/v2/labelzero/videos.json';
$http_result = drupal_http_request($url);
watchdog("vimeo-videos", "Vimeo API call result: %result", array( "%result" => $http_result->code), WATCHDOG_INFO, $url);
if ($http_result->code == 200) {
  $videos = json_decode($http_result->data, TRUE);
  if ($videos) {
    print '<div class="vimeo-thumb">';
    $i = 0;
    foreach ($videos as $vid) if (++$i < 7) {
      print '<a href="' . $vid[url] . '">' . '<img src="' . $vid[thumbnail_medium] . '" ></a>';
    }
    print "</div>";
  }
}
?>