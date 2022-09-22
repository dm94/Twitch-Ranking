<?php

/*
Template Name: Listado de streamers
*/

?>
<?php
	$args = array( 
		'numberposts'		=> -1, // -1 is for all
	  'post_type'		=> 'canales', // or 'post', 'page'
	  'orderby' 		=> 'title', // or 'date', 'rand'
	  'order' 		=> 'ASC', // or 'DESC'
	);
	$streamers = array();
	$myposts = get_posts($args);
	foreach ($myposts as $r){
		array_push($streamers, $r -> post_title);
	}
	echo json_encode($streamers);
?>