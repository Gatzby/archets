<?php

add_action('pre_get_posts','display_concerts');

function display_concerts($query){

	if($query->is_front_page() && $query->is_main_query()){
	
		$query->set('post_type',array('concert'));
		
		// 10 dernières années
		$query->set('date_query',array('year'=>getdate()['year']-10,'compare'=>'>='));
		
		// le lieu n'est pas spécifié
		//$query->set('meta_query',array(array('key'=>'wpcf-lieu','value'=>false,'type'=>BOOLEAN)));
		
		// qui possède une image à la une
		//$query->set('meta_query',array(array('key'=>'_thumbnail_id','compare'=>'EXISTS')));
		return;
	}
}

function getMarkerList($post_type = "concert") {
	$results = getPostWithLatLon($post_type);
	$array = array();
	foreach($results as $result) {
		$array[] = "var marker_$result->ID =  L.marker([$result->lat, $result->lng]).addTo(map);";
		$array[] = "var popup_$result->ID = L.popup().setContent('".$result->post_title."');";
		$array[] = "marker_$result->ID.bindPopup(popup_$result->ID);";

		$array[] = "marker_$result->ID.post_id = $result->ID;";
		
		
	} return implode( PHP_EOL, $array );
}

function getPostWithLatLon($post_type="concert")
{
	global$wpdb;
	$query=" 
	SELECT
	ID, post_title, p1.meta_value as lat, p2.meta_value as lng
	FROM wp_archetsposts, wp_archetspostmeta as p1, wp_archetspostmeta as p2
	WHERE wp_archetsposts.post_type = 'concert'
	AND p1.post_id = wp_archetsposts.ID
	AND p2.post_id = wp_archetsposts.ID
	AND p1.meta_key = 'lat' 
	AND p2.meta_key='lng'";
	return $wpdb->get_results ($query);
}

function display_actions($query){

	$query->set('post_type',array('actions'));
	
	/** remplace ligne ci-dessous** 
		
		if($query->is_front_page() && $query->is_main_query()){
	
		$query->set('post_type',array('action'));
		
		// le pays n'est pas spécifié
		$query->set('meta_query',array(array('key'=>'wpcf-pays','value'=>false,'type'=>BOOLEAN)));
		return;
	}*/
}


function dashboard_widget_function(){
	echo "Hello World, this is my first Dashboard Widget !";
}


function add_dashboard_widgets() {
	wp_add_dashboard_widget('dashboard_widget','Example Dashboard Widget','dashboard_widget_function');
}

add_action('wp_dashboard_setup','add_dashboard_widgets');

function geolocalize($post_id) {
	
	if (wp_is_post_revision($post_id)) return;

	$post = get_post($post_id);

	
	if (!in_array($post->post_type,array('concert'))) return;
	$lieu = get_post_meta($post_id,'wpcf-lieu',true);
	
	if (empty($lieu)) return;
	
	$lat = get_post_meta($post_id,'lat',true);
	
	if (empty($lat)) {
		$address = $lieu.',France';
		$result = doGeolocation($address);
		
		if (false === $result) return;
		
		try {
			$location = $result[0]['geometry']['location'];
			add_post_meta($post_id,'lat',$location["lat"]);
			add_post_meta($post_id,'lng',$location["lng"]);
		} catch(Exception $e) {
			return;
		}
	}
}

/*function doGeolocation($address) {
	$opts = array('http'=>array('proxy' => 'wwwcache.univ-orleans.fr:3128', 'request_fulluri' => true));
	$context = stream_context_create($opts);
	
	
	$url = "http://maps.google.com/maps/api/geocode/json?sensor=false"."&address=".urlencode($address);
	
	if ($json = file_get_contents($url)) {
		$data = json_decode($json,TRUE);
		if ($data['status'] == "OK") return $data['results'];
	} return false;
}*/

/*function doGeolocation($address)
{
    $url = "http://maps.google.com/maps/api/geocode/json?sensor=false" . "&address=" . urlencode($address);
    $proxy='wwwcache.univ-orleans.fr:3128';
    $ctx = stream_context_create(array(
        'http' => array(
            'timeout' => 30,
            'proxy' => $proxy,
            'request_fulluri' => true,
            )
        )
    );
    if($json = file_get_contents($url,0,$ctx)){
				
        $data = json_decode($json, TRUE);

        if($data['status']=="OK"){

            return $data['results'];
        }
    }
  
    return false;
}*/

function doGeolocation($address) {
	$opts = array('http'=>array('proxy' => 'wwwcache.univ-orleans.fr:3128', 'request_fulluri' => true));
	$context = stream_context_create($opts);

	$url = "http://maps.google.com/maps/api/geocode/json?sensor=false"."&address=".urlencode($address);
	
	if ($json = file_get_contents($url, false, $context)){
				
		$data = json_decode($json,TRUE);
		if ($data['status'] == "OK") {
			return $data['results'];
		}
	} return false;
}


add_action('save_post','geolocalize');

function load_scripts() {
	if(!is_post_type_archive('concert')&&!is_post_type_archive('action'))return;
	wp_register_script('leaflet-js','http://cdn.leafletjs.com/leaflet-0.7.1/leaflet.js');
	wp_enqueue_script('leaflet-js');
	
	wp_register_style('leaflet','http://cdn.leafletjs.com/leaflet-0.7.2/leaflet.css');
	wp_enqueue_style('leaflet');
}
add_action('wp_enqueue_scripts','load_scripts');

?>
