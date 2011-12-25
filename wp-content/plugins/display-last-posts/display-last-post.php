<?php
/*
Plugin Name: Display Last Post(s)
Plugin URI: http://www.fb-graphic.com/bonus/display-last-post
Description: Displays the last post(s) from your blog, everywhere you want, by inserting a shortcode or by putting an include php code in a template page of your theme. It can optionnaly display one or more posts (default is one), and the content of the post(s) or the exerpt only. The shortcode is [last-posts] and with options: [last-posts nb="5" cont="excerpt"] (example) /// This plug-in is a customisation of the sc_liste function given here: http://www.webinventif.fr/wordpress-creation-de-shortcode-avance/ 
Version: 1.0
Author: Francois Barret
Author URI: http://www.fb-graphic.com/
License: see readme.txt for details
*/


/**
 * fonction a utiliser dans le code html des templates du theme
 * param $nombre = Number of posts to display (by default = 1)
 * param $typeCont = Type of content to display (whole content or excerpt - by default = the whole content)
 * write the string returned by fb_lastposts() - string = last post(s)
 * author François Barret
**/
function lastPosts($nombre = 1, $typeCont = 'cont') {
	if($nombre == '') $nombre = 1;
	if($typeCont == '') $typeCont = 'cont';
	echo fb_lastposts('', $nombre, $typeCont);
}


/**
 * fonction originale = the_content() (wp-includes > post-template.php)
 * return the whole content of the post
 * author Francois Barret
**/
function fb_the_content($more_link_text = null, $stripteaser = 0) {
	$content = get_the_content($more_link_text, $stripteaser);
	$content = apply_filters('the_content', $content);
	$content = str_replace(']]>', ']]&gt;', $content);
	return $content;
}


/**
 * fonction appelee en direct par le shortcode - dans ce cas seul les attributs $atts sont servis et utilises
 * param $atts = optional attributs given by the calling shortcode
 * param $nombre = given when this function is called from lastPosts() (see above)
 * param $typeCont = given when this function is called from lastPosts() (see above)
 * return string of last post(s)
 * author Francois Barret
**/
function fb_lastposts($atts, $nombre, $typeCont) {

	// si la fonction est appelee par le shortcode
	extract(shortcode_atts(array(
		"nb" => '1',
		"cont" => 'cont',
	), $atts));

	// si la fonction est apelee par lastPosts() (code php dans template du theme)
	if($nombre > 1) $nb = $nombre;
	if($typeCont != 'last-posts') $cont = $typeCont;

	global $post;
	$myposts = get_posts('numberposts='.$nb.'&order=DESC&orderby=post_date');
	$retour= PHP_EOL.'<div class="display-last-posts">'.PHP_EOL;

	foreach($myposts as $post) :
		$cn = get_comments_number();
		if($cn < 1) { $isAre = 'are'; $commentsText = 'no comments'; }
			elseif($cn == 1) { $isAre = 'is'; $commentsText = '1 comment'; }
			else { $isAre = 'are'; $commentsText = $cn.' comments'; }

			setup_postdata($post);
			$retour.='<h3><a href="'.get_permalink().'">'.the_title("","",false).'</a></h3>'.PHP_EOL;
			if ($cont != 'cont') { $retour.= get_the_excerpt().PHP_EOL; } else $retour.= fb_the_content().PHP_EOL;
		endforeach;

		$retour.='</div> ';

		return $retour;
}

add_shortcode("last-posts", "fb_lastposts");
?>