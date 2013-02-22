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

/****************************************************************************************
					interface for wordpress admin here 
****************************************************************************************/
$manage_entry_path = get_settings('siteurl')."/wp-content/plugins/videoComments"; //for the wordpress admin interface

/* The form for video comments in the admin interface for making a new post */
/*
add_filter('edit_form_advanced','videoComments_form');
add_filter('simple_form','videoComments_form'); 
*/
function videoComments_form($thing) 
{
	global $post_ID;

	if (!empty($post_ID))
	{
		$result = videoComments_getVideoCommentsPost($post_ID);
	}
	else
	{
		$result = "";
	}
	
?>
	<div id="videoComment_admin" >
		<h3 class="dbx-handle">Video Comments</h3>
		<div id="videoComment_body" style="height:300px;background:#F2F3F5;width:100%;float:top;padding:5px;margin-top:3px;margin-right:2px;">
			<div id="videoComment_form_container" style="float:left;width:48%;">
				<b>Add Video Comments to this post.</b>
				<br>
				<table border="0" width="80%">
					<tr>
						<td nowrap>Video URL</td>
						<td>
						<input type="textbox" id="videoComments_form_video_url" name="videoComments_form_video_url" style="width:100%;" onBlur="videoComments_loadVideo();"<?php if (!empty($result->video_url)) { echo " value=\"$result->video_url\"";}?>>
						</td>
					</tr>
					<tr>
						<td><!--empty cell under video url--></td>
						<td>
							<table>
								<tr>
									<td width="50%">Width <input type="textbox" id="videoComments_form_video_width" name="videoComments_form_video_width" onBlur="videoComments_loadVideo();" size="4"<?php if (!empty($result->video_width)) { echo " value=\"$result->video_width\"";}?>></td>					
									<td width="50%">Height <input type="textbox" id="videoComments_form_video_height" name="videoComments_form_video_height" onBlur="videoComments_loadVideo();" size="4"<?php if (!empty($result->video_height)) { echo " value=\"$result->video_height\"";}?>></td>
								</tr>
							</table>
						</td>				
					</tr>
				</table>
				<div style="border-bottom:1px dotted grey; width:95%;height:10px;margin-bottom:10px;"></div>
				<b>Blog Display Options</b> <small>(define appearance in blog post)</small><br>
				<div id="videoComments_form_display_container">
					Text link: <input type="textbox" id="videoComments_form_display_text" name="videoComments_form_display_text" value="<?php if (!empty($result->video_text)) { echo $result->video_text; } else { echo "Click to Watch"; }?>" onFocus="this.select()"><br>
					Optional Image URL : <input type="textbox" id="videoComments_form_display_image" name="videoComments_form_display_image"<?php if (!empty($result->video_image)) { echo " value=\"$result->video_image\"";}?>><br>
					<br>
					Place in entry at
					<select id="videoComments_form_display_position" name="videoComments_form_display_position">
						<option value="top" <?php if (!empty($result->video_display) && $result->video_display == "top") { echo " selected";}?>>Top</option>
						<option value="bottom" <?php if (!empty($result->video_display) && $result->video_display == "bottom") { echo " selected";}?>>Bottom</option>
					</select>
				</div>
			</div>
			
			<div id="videoComments_preview" style="float:left;padding:5px;width:320px;height:257px;background:#CCC4C4;"></div>
		
		</div>
		<div style="float:top;" id="videoComment_footer"></div>
	</div>	
<?php
}

/* Inserts javascript into the header */
/* add_filter('admin_head','insert_videoComment_js'); */
function insert_videoComment_js() 
{
	global $manage_entry_path, $postid;
?>
	<script language="JavaScript">	
		function videoComments_loadVideo() 
		{
			var video_url = getElement('videoComments_form_video_url').value;
			var video_width = getElement('videoComments_form_video_width').value;
			var video_height = getElement('videoComments_form_video_height').value;
			
			if ((video_url != '') && (video_width != '') && (video_height != '')) 
			{

				myQTObject.write("videoComments_preview");

				getElement('videoComments_preview').innerHTML = '<video id="movieplayer" src="' + video_url '" width="' + video_width + '" height="' + video_height + '" controls></video>';
				getElement('videoComments_preview').style.width = video_width;
				getElement('videoComments_preview').style.height = video_height;
			}
		}
		
		function getElement(elementid) 
		{
			return document.getElementById(elementid);
		}
	</script>
<?php 
}

//this function is used on existing posts to load in any preset videocomment settings.
//add_filter('admin_footer','insert_videoComment_jsFooter');
function insert_videoComment_jsFooter() 
{
	global $post;
	
	if (isset($_GET['post']) && $_GET['post'] != "") 
	{
	?>
		<script>
			window.onload = function() {
				videoComments_loadVideo();
			}
		</script>
	<?php
	}
}

//this function happens when wp saves a post, the id 
// This isn't used..
function edit_videoComments_form($postid) 
{
	global $wpdb;
	
	//if the video url, width and height are provided save the entry
	if (current_user_can('edit_post', $postid)) 
	{
		$video_url = $_POST['videoComments_form_video_url'];
		$video_width = $_POST['videoComments_form_video_width'];
		$video_height = $_POST['videoComments_form_video_height'];
		$video_text = $_POST['videoComments_form_display_text'];
		$video_image = $_POST['videoComments_form_display_image'];
		$video_display = $_POST['videoComments_form_display_position'];
		
		if (($video_url == "")) 
		{
			videoComments_deleteVideoCommentsPost($postid);		
		}
		else
		{
			$success = videoComments_insertVideoCommentsPost($postid, $video_url, $video_width, $video_height, $video_text, $video_image, $video_display);
	
			if ($headers = vc_get_http_headers($video_url)) 
			{
				$len = (int) $headers['content-length'];
				$type = $wpdb->escape($headers['content-type']);
				$allowed_types = array('video', 'audio');
				if (in_array( substr( $type, 0, strpos( $type, "/" ) ), $allowed_types )) 
				{
					$meta_value = "$video_url\n$len\n$type\n";
					$wpdb->query( "INSERT INTO $wpdb->postmeta (post_id , meta_key , meta_value) VALUES ( '$postid', 'enclosure', '$meta_value')");
				}
			}
		}
	}
}

//this function happens when wp saves a post, the id 
function save_videoComments_form($postid) 
{
	global $wpdb;
	
	$video_url = $_POST['videoComments_form_video_url'];
	$video_width = $_POST['videoComments_form_video_width'];
	$video_height = $_POST['videoComments_form_video_height'];
	$video_text = $_POST['videoComments_form_display_text'];
	$video_image = $_POST['videoComments_form_display_image'];
	$video_display = $_POST['videoComments_form_display_position'];
	
	//if the video url, width and height are provided save the entry
	if (($video_url != "") && ($video_width != "") && ($video_height != "")) 
	{
		
		$success = videoComments_insertVideoCommentsPost($postid, $video_url, $video_width, $video_height, $video_text, $video_image, $video_display);

        if ($headers = vc_get_http_headers($video_url)) 
        {
            $len = (int) $headers['content-length'];
            $type = $wpdb->escape($headers['content-type']);
            $allowed_types = array('video', 'audio');
            if (in_array( substr( $type, 0, strpos( $type, "/" ) ), $allowed_types )) 
            {
                $meta_value = "$video_url\n$len\n$type\n";
                $wpdb->query( "INSERT INTO $wpdb->postmeta (post_id , meta_key , meta_value) VALUES ( '$postid', 'enclosure', '$meta_value')");
            }
        }
	}
}

// blatant rip-off of wp_get_http_headers in functions.php
function vc_get_http_headers( $url, $red = 1 ) 
{
	@set_time_limit( 60 );

	if ( $red > 5 )
	   return false;

	$parts = parse_url( $url );
	$file = $parts['path'] . ($parts['query'] ? '?'.$parts['query'] : '');
	$host = $parts['host'];
	if ( !isset( $parts['port'] ) )
			$parts['port'] = 80;

	$head = "HEAD $file HTTP/1.1\r\nHOST: $host\r\nUser-Agent: WPVC/1\r\n\r\n";

	$fp = @fsockopen($host, $parts['port'], $err_num, $err_msg, 3);
	if ( !$fp )
			return false;

	$response = '';
	fputs( $fp, $head );
	while ( !feof( $fp ) && strpos( $response, "\r\n\r\n" ) == false )
			$response .= fgets( $fp, 2048 );
	fclose( $fp );
	preg_match_all('/(.*?): (.*)\r/', $response, $matches);
	$count = count($matches[1]);
	for ( $i = 0; $i < $count; $i++) {
			$key = strtolower($matches[1][$i]);
			$headers["$key"] = $matches[2][$i];
	}

	$code = preg_replace('/.*?(\d{3}).*/i', '$1', $response);

	$headers['status_code'] = $code;

	if ( '302' == $code || '301' == $code )
		return vc_get_http_headers( $url, ++$red );

	preg_match('/.*([0-9]{3}).*/', $response, $return);
	$headers['response'] = $return[1]; // HTTP response code eg 204, 200, 404
	return $headers;
}


?>
