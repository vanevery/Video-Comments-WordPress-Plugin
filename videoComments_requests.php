<?php

/* --------------------------------------------------------------------------*/
/*                                                                           */
/* QuickTime Video Comments WordPress Plugin								 */   
/* Written by Shawn Van Every <vanevery@walking-productions.com>			 */
/*	and John Schimmel														 */		
/*                                                                           */
/* Copyright (c) 2006-2007 Shawn Van Every <vanevery@walking-productions.com>*/
/* 	and John Schimmel              											 */
/*                                                                           */
/* This program is free software; you can redistribute it and/or             */
/* modify it under the terms of the GNU General Public License               */
/* as published by the Free Software Foundation; either version 2            */
/* of the License, or (at your option) any later version.                    */
/*                                                                           */
/* See file LICENSE for further informations on licensing terms.             */
/*                                                                           */
/* This program is distributed in the hope that it will be useful,           */
/* but WITHOUT ANY WARRANTY; without even the implied warranty of            */
/* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the             */
/* GNU General Public License for more details.                              */
/*                                                                           */
/* You should have received a copy of the GNU General Public License         */
/* along with this program; if not, write to the Free Software Foundation,   */
/* Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.           */
/*                                                                           */
/* --------------------------------------------------------------------------*/

include_once("../../../wp-config.php");
include_once("videoCommentsLib.php");

ini_set('display_errors', false);
ini_set('display_startup_errors', false);
// You could also log them:
//ini_set('log_errors', true);
//ini_set('error_log', '/home/netid/php_errors.log');
//error_reporting(E_ALL);
error_reporting(E_NONE);

if (isset($_GET['postid']) && isset($_GET['mode'])) 
{
	$postid = $_GET['postid'];
	$mode = $_GET['mode'];
	
	$insert_ok = false;
	
	if ($mode =="GET") 
	{
		$movieRate = $_GET['movieRate'];
	
		$comments = get_timecode_comments($postid); //get the comments with timecodes from the WP database
		format_timecode_comments($comments,$movieRate);		//format the comments so they are in DIVs and using styles.		
	}	
} 
else if (isset($_GET['comment_post_ID']))
{
	$return_message = "";
	$passed_tests = true;
	
	$comment_post_ID = (int) $_GET['comment_post_ID'];
	
	$status = $wpdb->get_row("SELECT post_status, comment_status FROM $wpdb->posts WHERE ID = '$comment_post_ID'");
	
	if (empty($status->comment_status)) 
	{
		$return_message = 'Sorry, can not find this post';
		//echo $return_message;		
		$passed_tests = false;
	} 
	elseif ('closed' ==  $status->comment_status) 
	{
		$return_message = 'Sorry, comments are closed for this item.';
		//echo $return_message;
		$passed_tests = false;
	} 
	elseif ('draft' == $status->post_status) 
	{
		$return_message = 'Sorry, this is a draft item, no comments.';
		//echo $return_message;		
		$passed_tests = false;
	}
	else
	{
		$comment_author = "";
		$comment_author_email = "";
		$comment_author_url = "";
		$comment_content = "";
		if (isset($_GET['author'])) {
			$comment_author       = trim($_GET['author']);
		}
		if (isset($_GET['email'])) {
			$comment_author_email = trim($_GET['email']);
		}
		if (isset($_GET['url'])) {
			$comment_author_url   = trim($_GET['url']);
		}
		if (isset($_GET['comment']))
		{
			$comment_content      = trim($_GET['comment']);
		}
	
		// If the user is logged in
		get_currentuserinfo();

		if ( $user_ID )
		{
			$comment_author       = $wpdb->escape($user_identity);
			$comment_author_email = $wpdb->escape($user_email);
			$comment_author_url   = $wpdb->escape($user_url);
		}
		else 
		{
			if (get_option('comment_registration'))
			{
				$return_message = 'Sorry, you must be logged in to post a comment.';
				//echo $return_message;
				$passed_tests = false;	
			}
		}
	
		$comment_type = '';
	
		if (get_settings('require_name_email') && !$user_ID) 
		{
			if ( 6 > strlen($comment_author_email) || '' == $comment_author )
			{
				$return_message = 'Error: please fill the required fields (name, email).';
				//echo $return_message;
				$passed_tests = false;	
			}
			elseif (!is_email($comment_author_email))
			{
				$return_message = 'Error: please enter a valid email address.';
				//echo $return_message;
				$passed_tests = false;	
			}
		}
	
		if ('' == $comment_content)
		{
			$return_message = 'Error: please type a comment.';
			//echo $return_message;			
			$passed_tests = false;	
		}
	}
	
	if ($passed_tests)
	{
		$commentdata = compact('comment_post_ID', 'comment_author', 'comment_author_email', 'comment_author_url', 'comment_content', 'comment_type', 'user_ID');
	
		//$comment_id = wp_new_comment( $commentdata );
		// The following lines are from wp_new_comment
		
			// This might need some work
			$commentdata = apply_filters('preprocess_comment', $commentdata);
			
			$commentdata['comment_post_ID'] = (int) $commentdata['comment_post_ID'];
			$commentdata['user_ID']         = (int) $commentdata['user_ID'];
			$commentdata['comment_author_IP'] = preg_replace( '/[^0-9., ]/', '',$_SERVER['REMOTE_ADDR'] );
			$commentdata['comment_agent']     = $_SERVER['HTTP_USER_AGENT'];
			$commentdata['comment_date']     = current_time('mysql');
			$commentdata['comment_date_gmt'] = current_time('mysql', 1);
			
			// This might need some work
			$commentdata = wp_filter_comment($commentdata);			
			
			//$commentdata['comment_approved'] = wp_allow_comment($commentdata);
			// This might need some more work...
			
			// The following lines are from wp_filter_comment
			
				extract($commentdata);

				// Simple duplicate check
				$dupe = "SELECT comment_ID FROM $wpdb->comments WHERE comment_post_ID = '$comment_post_ID' AND ( comment_author = '$comment_author' ";
				if ( $comment_author_email )
					$dupe .= "OR comment_author_email = '$comment_author_email' ";
				$dupe .= ") AND comment_content = '$comment_content' LIMIT 1";
				if ( $wpdb->get_var($dupe) )
					//die( __('Duplicate comment detected; it looks as though you\'ve already said that!') );
					$return_message = 'Duplicate comment detected; it looks as though you\'ve already said that!';
					$passed_tests = false;
			
				// Simple flood-protection
				/* Removing flood-protection 
				if ( $lasttime = $wpdb->get_var("SELECT comment_date_gmt FROM $wpdb->comments WHERE comment_author_IP = '$comment_author_IP' OR comment_author_email = '$comment_author_email' ORDER BY comment_date DESC LIMIT 1") ) {
					$time_lastcomment = mysql2date('U', $lasttime);
					$time_newcomment  = mysql2date('U', $comment_date_gmt);
					if ( ($time_newcomment - $time_lastcomment) < 15 ) {
						do_action('comment_flood_trigger', $time_lastcomment, $time_newcomment);
						die( __('Sorry, you can only post a new comment once every 15 seconds. Slow down cowboy.') );
					}
				}
				*/
				
				if ( $user_id ) {
					$userdata = get_userdata($user_id);
					$user = new WP_User($user_id);
					$post_author = $wpdb->get_var("SELECT post_author FROM $wpdb->posts WHERE ID = '$comment_post_ID' LIMIT 1");
				}
			
				// The author and the admins get respect.
				if ( $userdata && ( $user_id == $post_author || $user->has_cap('level_9') ) ) {
					$approved = 1;
				}
			
				// Everyone else's comments will be checked.
				else {
					if ( check_comment($comment_author, $comment_author_email, $comment_author_url, $comment_content, $comment_author_IP, $comment_agent, $comment_type) )
						$approved = 1;
					else
						$approved = 0;
					if ( wp_blacklist_check($comment_author, $comment_author_email, $comment_author_url, $comment_content, $comment_author_IP, $comment_agent) )
						$approved = 'spam';
				}
			
				$commentdata['comment_approved'] = apply_filters('pre_comment_approved', $approved);
				
	
		// End wp_filter_comment
			
			
			//$commentdata['comment_approved'] = 1;

			$comment_id = wp_insert_comment($commentdata);
			$return_message = "Comment Inserted";

			// This probably needs some work...
			do_action('comment_post', $comment_id, $commentdata['comment_approved']);

	if ( 'spam' !== $commentdata['comment_approved'] ) { // If it's spam save it silently for later crunching
		if ( '0' == $commentdata['comment_approved'] ) {
			wp_notify_moderator($comment_id);
			$return_message = "This comment is being held for approval";
		}
		
		$post = &get_post($commentdata['comment_post_ID']); // Don't notify if it's your own comment

		if ( get_option('comments_notify') && $commentdata['comment_approved'] && $post->post_author != $commentdata['user_ID'] )
			wp_notify_postauthor($comment_id, $commentdata['comment_type']);
		}		
		
		
		if ( !$user_ID ) 
		{
			$comment = get_comment($comment_id);
			setcookie('comment_author_' . COOKIEHASH, $comment->comment_author, time() + 30000000, COOKIEPATH, COOKIE_DOMAIN);
			setcookie('comment_author_email_' . COOKIEHASH, $comment->comment_author_email, time() + 30000000, COOKIEPATH, COOKIE_DOMAIN);
			setcookie('comment_author_url_' . COOKIEHASH, clean_url($comment->comment_author_url), time() + 30000000, COOKIEPATH, COOKIE_DOMAIN);
		}
		
	}
	else
	{
		$return_message = "This comment has been flagged as spam!";
	}
	echo("Result:" . $return_message);
}
?>
