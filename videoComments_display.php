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

$vcstag = "[QT_COMMENTS ";
$vcetag = "]";
$movie_height = "";
$movie_width = "";
$movie_file = "";


$popup_url = "";

if (dirname($_SERVER['PHP_SELF']) != "/")
{        
    $popup_url = dirname($_SERVER['PHP_SELF']) . "/wp-content/plugins/videoComments/videoCommentsPop.php";
}
else
{
	$popup_url = "/wp-content/plugins/videoComments/videoCommentsPop.php";
}


/* The main display function called by the_content filter */
function quicktime_comments_post($the_content)
{
    GLOBAL $vcstag, $vcetag, $movie_width, $movie_height, $movie_file;

	// See if there is data in the meta table
	$videoData = videoComment_Entry_inPostMeta(); //check post_meta table for videoComment entry
	$customVCData = videoComment_Entry_inCustomTable();  // custom table for videoComment entry
	
	// See if they used the manual method
	$spos = strpos($the_content, $vcstag);
    if ($spos !== false)
    {
        $epos = strpos($the_content, $vcetag, $spos);
        $spose = $spos + strlen($vcstag);
        $slen = $epos - $spose;
        $tagargs = substr($the_content, $spose, $slen);
        list($movie_file ,$movie_width,$movie_height) = explode(" ", $tagargs);

        $tags = vc_generate_tags();
        
        $new_content = substr($the_content,0,$spos);
        $new_content .= $tags;
        $new_content .= substr($the_content,($epos+1));

        if ($epos+1 < strlen($the_content))
        {
            $new_content = quicktime_comments_post($new_content);
        }
        return $new_content;
        
        //************************************************//
        //		display from the video comments 
        //		admin interface, using info from postmeta
        //************************************************//
    } 
    else if ($videoData) // If there is data in the meta table
    {
    	if ($videoData['videoComments_video_url'] != "") 
    	{    		
    		$movie_file = $videoData['videoComments_video_url'];
    		$movie_width = $videoData['videoComments_video_width'];
    		$movie_height = $videoData['videoComments_video_height'];
    		
    		$new_content = vc_generate_tags($videoData['videoComments_video_text'],$videoData['videoComments_video_image']);
    		
    		if ($videoData['videoComments_video_display'] == "top") 
    		{
    			$new_content = $new_content . $the_content;
    		} 
    		else 
    		{
    			$new_content = $the_content . $new_content;
    		}
    		
    		return $new_content;
    	} 
    	else 
    	{ 
    		return $the_content; 
    	}
    }
    else if (!empty($customVCData))
    {
    	if ($customVCData->video_url != "") 
    	{    		
    		$movie_file = $customVCData->video_url;
    		$movie_width = $customVCData->video_width;
    		$movie_height = $customVCData->video_height;
    		
    		$new_content = vc_generate_tags($customVCData->video_text,$customVCData->video_image);
    		
    		if ($customVCData->video_display == "top") 
    		{
    			$new_content = $new_content . $the_content;
    		} 
    		else 
    		{
    			$new_content = $the_content . $new_content;
    		}
    		
    		return $new_content;
    	}
    	else
    	{
    		return $the_content;
    	}
    }
    else
    {
        return $the_content;
    }
}

/* Get data out of post meta table - This is the old format */
function videoComment_Entry_inPostMeta() {
	global $wpdb,$post;
	
	$sql ="SELECT meta_key, meta_value FROM $wpdb->postmeta where post_id=$post->ID and left(meta_key,20) = 'videoComments_video_'";
	$results = $wpdb->get_results($sql);
	
	if ($results) {
		foreach($results as $result) {
			$videoArray[$result->meta_key] = $result->meta_value;
		}
    	return $videoArray;
		
	} else {
		return false;
	}
}

function videoComment_Entry_inCustomTable() 
{
	global $wpdb,$post;
	$results = videoComments_getVideoCommentsPost($post->ID);
	return $results;	
}

/* This function inserts the javascript into the head of the page for doing the popup */
/* Called by wp_head action */
function insert_qt_comments_javascript() 
{
	GLOBAL $movie_width,$movie_height, $popup_url,$post;	
?>
<script language="javascript" type="text/javascript">
	function videoCommentWindow(postid,movie,title,movie_height,movie_width,timecode)
	{
		// Set the default height and width
		var windowHeight = 320;	
		if (movie_height > 240)
		{
			windowHeight = movie_height + 80;
		}
		
		var windowWidth = 695;
		if (movie_width > 320) 
		{
			windowWidth = (movie_width*2)+55;
		}
	
		newwindow=window.open('<?= $popup_url; ?>?postid='+postid+'&movie='+movie+'&height='+(movie_height+19)+'&width='+movie_width+'&timecode='+timecode+'&title='+title,'videoCommenter','height='+windowHeight+',width='+windowWidth);
		if (window.focus) {newwindow.focus()}
		//alert("here");
		return false;
	}
	
	
	function getMeta() {
		var meta = '<?= $the_meta; ?>';
		return meta;
	}
</script>
<?php
}


/* This function controls the display of the post that includes video comments */
function vc_generate_tags($text="",$image = "")
{
	GLOBAL $popup_url,$post,$movie_width,$movie_height,$movie_file;

	$movie_file = htmlspecialchars($movie_file ,ENT_QUOTES);
	
	if (empty($text) && empty($image)) 
	{	
		$tags .= "<a href=\"" . $movie_file . "\" onclick=\"return videoCommentWindow($post->ID,'$movie_file ','" .addslashes($post->post_title) . "',$movie_height,$movie_width,'0')\">Watch Video</a><br>";
			
		//display a set timecode
		if ($_GET['timecode'] != "") 
		{
			$tags .= "<a href=\"" . $popup_url . "?movie=" . $movie_file . "\" onclick=\"return videoCommentWindow($post->ID,'$movie_file ','" .addslashes($post->post_title) . "',$movie_height,$movie_width,'" . $_GET['timecode'] . "')\">Flavor " . $_GET['flavor'] . " at " . $_GET['timecode'] . "</a>\n";
		}
   } 
   else if (!empty($text) && empty($image)) 
   { 
   		//text is passed in but not image path
   				
		$tags .= "<a href=\"" . $movie_file . "\" onclick=\"return videoCommentWindow($post->ID,'$movie_file ','" .addslashes($post->post_title) . "',$movie_height,$movie_width,'0')\">" . $text . "</a><br>";
	
		//display a set timecode
		if ($_GET['timecode'] != "") 
		{
			$tags .= "<a href=\"" . $popup_url . "?movie=" . $movie_file . "\" onclick=\"return videoCommentWindow($post->ID,'$movie_file ','" .addslashes($post->post_title) . "',$movie_height,$movie_width,'" . $_GET['timecode'] . "')\">Flavor " . $_GET['flavor'] . " at " . $_GET['timecode'] . "</a>\n";
		}
   } 
   else if (!empty($text) && !empty($image)) 
   { 
	   //both text and image are included
   		$movie_file = htmlspecialchars($movie_file ,ENT_QUOTES);
		
		$tags .= "<a href=\"" . $movie_file . "\" onclick=\"return videoCommentWindow($post->ID,'$movie_file ','" .addslashes($post->post_title) . "',$movie_height,$movie_width,'0')\"><img src=\"" . $image . "\" title=\"" . $text . "\"></a><br><a href=\"" . $movie_file . "\" onclick=\"return videoCommentWindow($post->ID,'$movie_file ','" .addslashes($post->post_title) . "',$movie_height,$movie_width,'0')\">" . $text . "</a><br>";
	
		if ($_GET['timecode'] != "") 
		{
			$tags .= "<a href=\"" . $popup_url . "?movie=" . $movie_file . "\" onclick=\"return videoCommentWindow($post->ID,'$movie_file ','" .addslashes($post->post_title) . "',$movie_height,$movie_width,'" . $_GET['timecode'] . "')\">Flavor " . $_GET['flavor'] . " at " . $_GET['timecode'] . "</a>\n";
		 }
	}

	return $tags;
}



?>