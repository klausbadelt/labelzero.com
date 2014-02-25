<?php
function release_cmp($a, $b)
{
    if ($a["release_date"] == $b["release_date"]) {
        return 0;
    }
    return ($a["release_date"] > $b["release_date"]) ? -1 : 1;
}
/* FIRST get all artists' bandcamp URLs on our site */
$band_urls = array();

/* 
* the following we would do if don't want to hard code the cck table name:
* $field = content_database_info(content_fields('field_bc_url'));
* $table = $field["table"];
* $result = db_query('SELECT field_bc_url_url FROM {%s}',$table);
*
* TODO: only get published artists
*/

$result = db_query('SELECT field_bc_url_url FROM {content_type_artist}');
while ($row = db_fetch_object($result)) {
  $band_urls[] = $row->field_bc_url_url;
}

/* SECOND convert all band URLs into band IDs via Bandcamp API calls */
/*
$band_ids = array();
$bc_api_url = 'http://api.bandcamp.com/api/url/1/info?key=razitillagvafudrmannfornoldr&url=';
foreach ($band_urls as $band_url) {
  $querystring = $bc_api_url . $band_url;
  $http_result = drupal_http_request($querystring );
  $http_result->code == 200 or die();
  $response = json_decode($http_result->data, TRUE);
  if (!array_key_exists("error",$response)) {
    $band_ids[] = $response["band_id"];
    watchdog("bc-covers", "Bandcamp API call result: %result, band URL %bandurl -> band ID %bandid", array( "%bandurl" => $band_url, "%result" => $http_result->code, "%bandid"=>$response["band_id"]), WATCHDOG_INFO, $querystring);
  }
  else {
    watchdog("bc-covers", "Bandcamp API call FAILED result: %result, error %error", array("%result" => $http_result->code,"%error" => $band_id["error_message"]), WATCHDOG_ERROR, $band_url);
  }
}

FOR NOW: HACK to save on API calls - hard code band IDs *** TODO 
*/
$band_ids = array(2983633648, 1477992086, 2989315057, 3310037202);
/* now we have all band IDs of all artists on our website in $band_ids[] */
if (count($band_ids) == 0) {
  watchdog("bc-covers", "No band IDs found", array(), WATCHDOG_ERROR);
  exit();
}

/* THIRD retrieve all releases of all bands via a single Bandcamp API call */

$bc_api_url = 'http://api.bandcamp.com/api/band/3/discography?key=razitillagvafudrmannfornoldr&band_id=';
$band_ids_querystring = implode(",",$band_ids);
$querystring = $bc_api_url . $band_ids_querystring;
$http_result = drupal_http_request($querystring);
if ($http_result->code == 200) {
  watchdog("bc-covers", "Bandcamp API call result: %result", array("%result" => $http_result->code),WATCHDOG_INFO, $querystring);

  $disco = json_decode($http_result->data, TRUE);

  /* consolidate all releases of all artists into one single array */
  if ($disco) {
    foreach ($disco as $artist) {
      foreach ($artist["discography"] as $album) {
        $releases[] = $album;
      }
    }

  /* sort by reverse release date */
    usort($releases,"release_cmp");
    $markup = '<div class="cover">';
    foreach ($releases as $release) {
      $markup .= '<a href="' . $release["url"] . '">' .
              '<img src="' . $release["small_art_url"] . '"></a>';
    }
    $markup .= '</div>';
    print $markup;
  }
}
else {
  watchdog("bc-covers", "Bandcamp API call FAILED result: %result", array("%result" => $http_result->code),WATCHDOG_ERROR, $querystring);
}

?>