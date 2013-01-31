<?

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
include_once("../../../wp-config.php");
include_once("videoCommentsLib.php");


if ((!isset($_GET['movie'])) || (!isset($_GET['height'])) || (!isset($_GET['width'])) || (!isset($_GET['postid']))) {
	exit;
} else {
	$movie = $_GET['movie'];
	$height = $_GET['height'];
	$width = $_GET['width'];
	
	if ($width < 240) {
		$width = 240;
	}
	
	$postid = $_GET['postid'];
	$timecode = $_GET['timecode'];
	$title = $_GET['title'];
}

?>

<html>
	<head>
		<script language="JavaScript" src="qtobject.js"></script>
		<script language="JavaScript" src="videoComments.js"></script>	
		<link rel="stylesheet" href="style.css" type="text/css"/>
	</head>
	
	<body>
	
		<div id="header"><?= stripslashes($title); ?></div>
	
		<div id="movie_container">
			<script type="text/javascript">
			<!--
				myQTObject = new QTObject("<?= $movie; ?>", "movieplayer", "<?= $width; ?>", "<?= $height; ?>");
				myQTObject.altTxt = "This requires QuickTime: http://www.apple.com/quicktime/";
				myQTObject.addParam("controller", "true");
				myQTObject.addParam("AUTOPLAY","TRUE");
				myQTObject.addParam("SCALE","ASPECT");
				myQTObject.write();
				
				// The init onload isn't getting called in FF on Mac
				initvideo(<?= $postid; ?>,'<?= $title; ?>','<?= $timecode; ?>');				
			// -->	
			</script>
			<noscript>
				<OBJECT CLASSID="clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B" WIDTH="<?= $width; ?>" HEIGHT="<?= $height; ?>" CODEBASE="http://www.apple.com/qtactivex/qtplugin.cab">
				<PARAM name="SRC" VALUE="<?= $movie; ?>" />
				<PARAM name="CONTROLLER" VALUE="true" />
				<PARAM name="AUTOPLAY" VALUE="false" />
				<PARAM name="SCALE" VALUE="ASPECT" />
				<EMBED SRC="<?= $movie; ?>" CONTROLLER="true" WIDTH="<?= $width; ?>" HEIGHT="<?= $height; ?>" AUTOPLAY="false" SCALE="ASPECT" PLUGINSPAGE="http://www.apple.com/quicktime/download/"></EMBED>
				</OBJECT>
			</noscript>
		</div>
	
		<div id="comments_container" style="height:<?= $height-30; ?>;width:<?= $width; ?>">
		<?
			$comments = get_timecode_comments($postid); //get the comments with timecodes from the WP database
			format_timecode_comments($comments);		//format the comments so they are in DIVs and using styles.	
		?>
		</div>
		
		
		<div id="comment_form" style="display:none;">	
			<form method="post" id="commentform">
			<? if ( $user_ID ) : ?>		
				Logged in as <a href="<? echo get_option('siteurl'); ?>/wp-admin/profile.php"><?= $user_identity; ?></a>. 
				<a href="<?= get_option('siteurl'); ?>/wp-login.php?action=logout" title="<? _e('Log out of this account') ?>" target="parent">Logout &raquo;</a>
			<? else : ?>		
				<input type="text" name="author" id="author" class="videocomments_form" value="<? echo $comment_author; ?>" size="22" tabindex="1" />
				<label for="author"><small>Name <? if ($req) _e('(required)'); ?></small></label>
				<br>
				<input type="text" name="email" id="email" class="videocomments_form" value="<? echo $comment_author_email; ?>" size="22" tabindex="2" />
				<label for="email"><small>Mail (will not be published) <? if ($req) _e('(required)'); ?></small></label>			
				<br>
				<input type="text" name="url" id="url" class="videocomments_form" value="<? echo $comment_author_url; ?>" size="22" tabindex="3" />
				<label for="url"><small>Website</small></label>
			<? endif; ?>
			<br>
			<textarea name="comment" id="comment" class="videocomments_form" tabindex="4"></textarea>
			<input type="hidden" name="comment_post_ID" id="comment_post_ID" value="<?= $postid; ?>" />
			<br><small>(new users may have to be approved)</small>
			</form>
		</div>
		
		
		<div id="oneButton">
			<input type="button" id="make_Button" class="videocomments_buttons" value="Make Comment" onclick="comment()">
			<div id="scrolling_div">Auto-Scroll <input type="checkbox" id="scrolling_enabled" checked></div>
			<input type="button" id="save_Button" class="videocomments_buttons" style="display:none;float:left;" value="Save & Continue" onclick="save()">
			<input type="button" id="cancel_Button" class="videocomments_buttons" style="display:none;" value="Cancel" onclick="">
		</div>
	
	
		<div id="footer">
			<a href="http://itp.nyu.edu/research/vc" target="_blank">video comments</a> from <a target="_blank" href="http://itp.nyu.edu/research/">ITP Research</a>
		</div>

	</body>
</html>
