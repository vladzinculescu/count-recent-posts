<?php
/*
Plugin Name: Count Recent Posts
Plugin URI: http://github.com/vladzinculescu/count-recent-posts
Description: Counts the recents posts in a given category from the last visit on a domain.
			 If first visit counts all posts published within the last 30 days.
			 If no new posts are found you can pass a default number ( default is 0 )
Version: 0.1
Author: Vlad Zinculescu
Author URI: http://about.me/vladzinculescu
License: GPL
*/
function crp_init(){
	if( !crp_check_visit() ){ //if this is the first visit
		crp_set_visit(true);
	}
}
add_action('plugins_loaded','crp_init');//this is to trigger the init function before output starts


function crp_set_visit($first_visit=false){
	if($first_visit) {		
		//set last visit to 30days back
		setcookie("crp_last_visit_", date('Y-m-d H:i:s', strtotime('-30 days')), time()+ 31536000, "/"); //it expires after one year
		//hack in order to get the new value of the cookie
		$_COOKIE["crp_last_visit_"] = date('Y-m-d H:i:s', strtotime('-30 days'));
	} else {
		//set last visit to now
		setcookie("crp_last_visit_", date('Y-m-d H:i:s') , time() + 31536000, "/"); //it expires after one year
		//hack in order to get the new value of the cookie
		$_COOKIE["crp_last_visit_"] = date('Y-m-d H:i:s');
	}	
}
function crp_get_visit(){
	if( crp_check_visit() ){
		return $_COOKIE["crp_last_visit_"];
	}
	return false;
}
function crp_check_visit(){
	if( isset( $_COOKIE["crp_last_visit_"] ) ) {
		return true;
	}
	return false;
}

function crp_filter_where($where = '') {
    $where .= " AND post_date > '" . crp_get_visit() . "'";
    return $where;
}

function crp_count_posts($category, $nr=0) {
	if( crp_check_visit()){
		add_filter('posts_where', 'crp_filter_where');
		$posts = query_posts(array ( 'cat' => $category, 'posts_per_page' => -1 ));
		remove_filter('posts_where', 'crp_filter_where');
		wp_reset_query();
		if(count($posts)>0) {
			return count($posts);
		}
	}

	return $nr; 
}
?>