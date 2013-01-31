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

/* Include the wp-config.php file */
//include("../../../wp-config.php"); // to include access to wp database

/*
 *	Function : get_timecode_comments(postid)
 *	Returns a comment filled object from the wordpress database containing timecoded comments.
 *	
 *	accepts 1 parameter, the post id number
 */
function get_timecode_comments($postid) 
{
	global $wpdb;

	// The regex for [00:00:00]Comment here....
	$regexpression = "^\\\[[[:digit:]]{2}\\\:[[:digit:]]{2}\\\:[[:digit:]]{2}";
		
	// SQL statement, including the regular expression
	$sql = "SELECT * FROM $wpdb->comments WHERE comment_post_ID = '$postid' and comment_approved='1' and comment_content REGEXP '$regexpression' order by comment_content";

	//return the results
	$comments = $wpdb->get_results($sql);
	return $comments;
}

/*
 *	Function : format_timecode_comments(postid)
 *  Formats the comments into appropriate divs and timecode links
 *	
 *	Accepts 1 parameter, the comments object (from get_timecode_comments) above
 */
function format_timecode_comments($comments,$movieRate=0) 
{
	$current_timecode = "";
	if (count($comments) != 0) 
	{	
		foreach ($comments as $curRow) 
		{	
			$timecodeAndComment = str_replace("[","",$curRow->comment_content);
			
			$values = explode("]",$timecodeAndComment);
			$timecode = $values[0];
			$comment = $values[1];
			$username= $curRow->comment_author;
			$tempTimeCode = explode(":",$timecode);
			$timecodeInSeconds = ($tempTimeCode[0] * 3600) + ($tempTimeCode[1] *60) + $tempTimeCode[2];
	
			// to organize the comments by timecode each unique timecode will be in it's own DIV with id=$timecode. 
			// in this div will contain all the comments for that given timecode
				
			if ($current_timecode == "") {
				//this is the first timecode for this video's comments
				echo "<div id=\"" . $timecode . "\" class=\"timecode_div\">" . chr(13);
			} else if ($current_timecode != $timecode) {
				echo "</div>" . chr(13) . "<div id=\"" . $timecode . "\" class=\"timecode_div\">" . chr(13);
			} else {
			
			}
			$current_timecode = $timecode; //store the timecode for next check loop*/
			?>
				<div class="non_live_comment" style="cursor:pointer;" onClick="navigate('<?php echo $timecode ?>');">
					<br>
					<?php 
						$comment_text = apply_filters('comment_text', $comment); 
						// Remove Paragraph Tags - They mess up our formatting
						$pattern[0] = '/<p>/';
						$pattern[1] = '/<\/p>/';
						$replace[0] = "";
						$replace[1] = "";
						echo preg_replace($pattern, $replace, $comment_text);
					?>  
					<br>
					<b class="username"><?php echo $username ?></b> <span class="timecode"><?php echo $timecode ?></span> 
					<br>
				</div>
			<?
			} //end of for loop
		} //end of array count
	echo "</div>" . chr(13);
}

// Quote variable to make safe for database.
// http://us3.php.net/manual/en/function.mysql-real-escape-string.php
function quote_smart($value)
{
   // Stripslashes
   if (get_magic_quotes_gpc()) {
       $value = stripslashes($value);
   }
   // Quote if not integer
   if (!is_numeric($value)) {
       $value = "'" . mysql_real_escape_string($value) . "'";
   }
   return $value;
}

function videocomments_append_to_wpComments($comment) 
{
	global $post,$popup_url,$wpdb,$movie_file,$movie_width,$movie_height;
	
	$subject = $comment;
	$pattern = "/^\[\d{2}\:\d{2}\:\d{2}\]/";
	
	$result =""; 
	if (preg_match($pattern, $subject, $reg, PREG_OFFSET_CAPTURE, 0)) {
		$result = $reg[0][0];
		$timecode = str_replace("[","",$result);  //strip out left and right brackets [ & ]
		$timecode = str_replace("]","",$timecode);
		$adjusted_comment = str_replace($result,"",$comment);
		//create link
		$link .= "[<a href=\"" . $movie_file . "\" onclick=\"return videoCommentWindow($post->ID,'$movie_file ','" .addslashes($post->post_title) . "',$movie_height,$movie_width,'" . $timecode . "')\">$timecode</a>]";
		return "$adjusted_comment<br>$link";
	 }
	return $comment;

}

function videoComments_install() 
{
   global $wpdb;

   $table_name = $wpdb->prefix . "videocomments";
   //$table_name = "wp_videocomments";
   $existing_table = $wpdb->get_var("show tables like '$table_name'");
   if (empty($existing_table)) 
   {   
      $sql = "CREATE TABLE ".$table_name." (      
		  post_id bigint(20) not null,
		  video_url text null,
		  video_width int(10) null,
		  video_height int(10) null,
		  video_text text null,
		  video_image text null,
		  video_display text null
	     );";

      require_once(ABSPATH . 'wp-admin/upgrade-functions.php');
      dbDelta($sql);
   }
}

function videoComments_getVideoCommentsPost($postid)
{
	global $wpdb;
	$table_name = $wpdb->prefix . "videocomments";	

	$vc = $wpdb->get_row("select post_id, video_url, video_width, video_height, video_text, video_image, video_display from $table_name where post_id = $postid");
	return $vc;
}

//delete_post hook
function videoComments_deleteVideoCommentsPost($postid)
{
	global $wpdb;
	$table_name = $wpdb->prefix . "videocomments";
	$success = $wpdb->query("delete from $table_name where post_id = $postid");
	return $success;
}

function videoComments_insertVideoCommentsPost($postid, $video_url, $video_width, $video_height, $video_text, $video_image, $video_display)
{
	global $wpdb;
	$table_name = $wpdb->prefix . "videocomments";	

	$video_url = $wpdb->escape($video_url);
	$video_width = $wpdb->escape($video_width);
	$video_height = $wpdb->escape($video_height);
	$video_text = $wpdb->escape($video_text);
	$video_image = $wpdb->escape($video_image);
	$video_display = $wpdb->escape($video_display);
		
	$existing_postid = $wpdb->get_var("select post_id from $table_name where post_id = $postid");
	$success = FALSE;
	if (!empty($existing_postid) && $existing_postid == $postid)
	{
		$success = $wpdb->query("
			UPDATE $table_name
			SET video_url = '$video_url',
				video_width = '$video_width',
				video_height = '$video_height',
				video_text = '$video_text',
				video_image = '$video_image',
				video_display = '$video_display'
			WHERE post_id = $postid");			
	}
	else
	{
		$success = $wpdb->query("
			INSERT INTO $table_name
			(post_id,video_url,video_width,video_height,video_text,video_image,video_display)
			VALUES ($postid,'$video_url', '$video_width', '$video_height', '$video_text', '$video_image', '$video_display')");			
	}
		
	return $success;
}

?>