<?php
/*
Plugin Name: Flickr Shortcode
Plugin URI: http://andrewferguson.net/
Description: Provides quick access a la short codes for inserting flickr photos
Version: 0.3
Author: Andrew Ferguson
Author URI: http://andrewferguson.net


Flickr Shortcode - Provides quick access a la short codes for inserting flickr photos
Copyright (c) 2009-2011 Andrew Ferguson

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
*/

global $fergcorp_flickrShortcode_apiKey, $fergcorp_flickrShortcode_f;

/**
 * Processes the wppd shortcode
 *
 * @param $atts Attributes provided in the shortcode
 * @param $content Content provided between the opening and closing elements
 * @since 0.1
 * @access public
 * @author Andrew Ferguson
 * @return string
*/

function fergcorp_flickrShortcode_init(){
	global $fergcorp_flickrShortcode_apiKey, $fergcorp_flickrShortcode_f;
	require_once("fergcorp_flickr-shortcode_key.php");
	require_once("phpFlickr/phpFlickr.php");
	$fergcorp_flickrShortcode_f = new phpFlickr($fergcorp_flickrShortcode_apiKey);
	$fergcorp_flickrShortcode_f->enableCache("fs", dirname(__FILE__)."/phpFlickrCache", 15552000);
	return $fergcorp_flickrShortcode_f;
}

fergcorp_flickrShortcode_init();

function fergcorp_flickrShortcode_set($setID, $size = "medium"){
	global $fergcorp_flickrShortcode_f;

	$setList = $fergcorp_flickrShortcode_f->photosets_getPhotos($setID);
	
	$toReturn = '';

	if($setList){
		foreach($setList['photoset']['photo'] as $photo){
			$toReturn .= '<p>' . fergcorp_flickrShortcode($photo['id'], $size) . '</p>';
		}
	}

	return $toReturn;
}

function fergcorp_flickrShortcode($photoID, $size = "medium"){
	global $fergcorp_flickrShortcode_f;

	//API calls
	$photoInfo = $fergcorp_flickrShortcode_f->photos_getInfo($photoID);
	$photoSizes = $fergcorp_flickrShortcode_f->photos_getSizes($photoID);
	$photoExif = $fergcorp_flickrShortcode_f->photos_getExif($photoID);
	$photoLocation = $fergcorp_flickrShortcode_f->photos_geo_getLocation($photoID);

	foreach($photoExif['exif'] as $photo){
		$exif[$photo['tag']] = $photo['raw'];
	}

	//Process the sizes, grab the medium one
	$photoSizesArrIt = new RecursiveIteratorIterator(new RecursiveArrayIterator($photoSizes));

	 foreach ($photoSizesArrIt as $sub) {
	    $subArray = $photoSizesArrIt->getSubIterator();
	    if (strtolower($subArray['label']) === $size) {
	        $photoSize[] = iterator_to_array($subArray);
	    }
	}
	
	//Grab the URL, width, and height for the medium size
	$imageURL = $photoSize[0]["source"];
	$imageWidth = $photoSize[0]["width"];
	$imageHeight = $photoSize[0]["height"];
	
	$linkURL = $photoInfo["photo"]["urls"]["url"][0]["_content"];
	$title = $photoInfo["photo"]["title"]['_content'];
	$author = $photoInfo["photo"]["owner"]["realname"];
	
	$toReturn = "<a href='$linkURL' title='$title by $author'>";
	$toReturn .= "<img src='$imageURL' width='$imageWidth' height='$imageHeight' alt='$title' />"; //style='border:solid black 1px !important'
	$toReturn .= "</a>\n";

	//return "<p>" . $toReturn . "</p>";

	$exifData = "";
	
	
	if(isset($exif['FocalLength'])){
		$exifData .= $exif['FocalLength']['_content'] . " || ";
	}
	if(isset($exif['ExposureTime'])){
		$exifData .= $exif['ExposureTime']['_content'] . " ". (floatval($exif['ExposureTime']['_content'])>1?"sec ":NULL) ."|| ";
	}
	if(isset($exif['FNumber'])){
		$exifData .= "f/" . $exif['FNumber']['_content'] . " || ";
	}
	if(isset($exif['ISO'])){
		$exifData .= "ISO" . $exif['ISO']['_content'] . " || ";
	}
	if(isset($exif['Model'])){
		$exifData .= $exif['Model']['_content'] . " || ";
	}

	$exifData = rtrim($exifData, " || ");


	if(isset($photoLocation['location'])){
		if($exifData != ""){
			$exifData .= "<br />";
		}
		//$GPS = str_replace(' deg', '&deg;', $exif['GPSPosition']);
		//$exifData .= "<small><a href=\"http://maps.google.com/maps?q=" . urlencode($GPS) . "\">$GPS</a></small>";
		//print_r($photoLocation);
		$exifData .= "" . $photoLocation['location']['locality']['_content']. ', ' . $photoLocation['location']['region']['_content']. ', ' . $photoLocation['location']['country']['_content'];
	}

	$toReturn .= "<br /><small>" . $exifData . "</small>\n";
	
	//$toReturn .= "<p class='wp-caption-text'>" . $exifData . "</p></div>"; 
	
	return $toReturn;

		
}

function fergcorp_flickrShortcode_buildPhotoInfo($photoID){
	$f = fergcorp_flickrShortcode_init();
	$photoExif = $f->photos_getExif($photoID);
	$photoLocation = $f->photos_geo_getLocation($photoID);
	$photoInfo = $f->photos_getInfo($photoID);
	
	foreach($photoExif['exif'] as $photo){
		$exif[$photo['tag']] = $photo['raw'];
	}
	
	
	if(isset($exif['FocalLength'])){
		$exifData .= $exif['FocalLength'] . " || ";
	}
	if(isset($exif['ExposureTime'])){
		$exifData .= $exif['ExposureTime'] . " ". (floatval($exif['ExposureTime'])>1?"sec ":NULL) ."|| ";
	}
	if(isset($exif['FNumber'])){
		$exifData .= "f/" . $exif['FNumber'] . " || ";
	}
	if(isset($exif['ISO'])){
		$exifData .= "ISO" . $exif['ISO'] . " || ";
	}
	if(isset($exif['Model'])){
		$exifData .= $exif['Model'] . " || ";
	}

	$exifData = rtrim($exifData, " || ");


	if(isset($photoLocation['location'])){
		if($exifData != ""){
			$exifData .= "<br />";
		}
		//$GPS = str_replace(' deg', '&deg;', $exif['GPSPosition']);
		//$exifData .= "<small><a href=\"http://maps.google.com/maps?q=" . urlencode($GPS) . "\">$GPS</a></small>";
		//print_r($photoLocation);
		$exifData .= "" . $photoLocation['location']['locality']['_content']. ', ' . $photoLocation['location']['region']['_content']. ', ' . $photoLocation['location']['country']['_content'];
	}

 
	$exifData .= '<br /><a href="' . $photoInfo["photo"]["urls"]["url"][0]["_content"] . '">&#8734</a>';
	
	return $exifData;
	
	
}


function fergcorp_flickrShortcode_buildPhotoURL($photoID, $size = "medium"){
	$f = fergcorp_flickrShortcode_init();
	$photoInfo = $f->photos_getInfo($photoID);
	return $f->buildPhotoURL($photoInfo["photo"], $size);
}






/**
 * Processes the flickr shortcode
 *
 * @param $atts Attributes provided in the shortcode
 * @param $content Content provided between the opening and closing elements
 * @since 0.1
 * @access public
 * @author Andrew Ferguson
 * @return string The result of fergcorp_flickrShortcode
*/
function fergcorp_flickrShortcode_shortcode($atts, $content) {
	extract(shortcode_atts(array(
		"blah" => 'countdown-timer',
	), $atts));
	
	$content = do_shortcode($content);
	
	return "<p>" . fergcorp_flickrShortcode($content) . "</p>";
}
add_shortcode('flickr', 'fergcorp_flickrShortcode_shortcode');

function fergcorp_flickrShortcode_shortcode_set($atts, $content) {
	extract(shortcode_atts(array(
		"blah" => 'countdown-timer',
	), $atts));

	$content = do_shortcode($content);

	return fergcorp_flickrShortcode_set($content);
}
add_shortcode('flickr_set', 'fergcorp_flickrShortcode_shortcode_set');


add_action('admin_print_scripts', 'fergcorp_flickrShortcode_quicktagJS');

function fergcorp_flickrShortcode_quicktagJS() {
	wp_enqueue_script('fergcorp_flickrShortcode_quicktagJS', plugin_dir_url(__FILE__) . 'fergcorp_flickr-shortcode_js.js', array('quicktags')
	);
}


?>
