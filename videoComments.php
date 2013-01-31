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

/*
Plugin Name: QuickTime Video Comments
Plugin URI: http://www.itp.nyu.edu/research/vc/
Description: Enable comments on your videos
In as: [QT_COMMENTS file width height]
Author: John Schimmel & Shawn Van Every
Version: 2.0.1b
Author URI: http://www.itp.nyu.edu/research/
Instructions:

This is an all new version of the video comments plugin.  Many portions of it are rewritten but the concept and the look and feel do remain the same.  A little bit of reverse compatability has been lost.  In particular, the administrative interface no longer displays a preview of the video in the form for previous entries.  New entries should work fine and the previous entries should still work as normal on the front end.

Many of the javascript functions have been written to remove dependencies on external libraries and the comment saving portions have been made more wordpress like.

If you are installing this over top of a previous version you should first deactivate the previous version, move the folder elsewhere (keep a back-up just in case) and then install this one in the normal way (upload the folder as well as activate it).
*/ 

include_once('videoComments_admin_interface.php'); //all code for the wp admin interface
include_once('videoComments_display.php'); //code for displaying videocomments in the viewable WP blog
include_once('videoCommentsLib.php'); // Library of functions, including database functions

// Display side
add_filter('the_content', 'quicktime_comments_post'); // inside videoComments_display.php
add_filter('the_excerpt', 'quicktime_comments_post'); // inside videoComments_display.php
add_action('wp_head','insert_qt_comments_javascript'); // inside videoComments_display.php


//below statements referenced in videoComments_admin_interface.php or in the lib file
add_action('save_post','save_videoComments_form'); //insert videoComment data 
add_action('delete_post','videoComments_deleteVideoCommentsPost'); // delete videoComments data
//add_action('edit_post','edit_videoComments_form'); // is this nessecary?
//add_action('publish_post','edit_videoComments_form'); // is this nessecary?

add_filter('admin_head','insert_videoComment_js'); //add javascript to the top of the admin section
add_filter('admin_footer','insert_videoComment_jsFooter'); //add javascript to the bottom of admin section, used to auto load in movies 
add_filter('edit_form_advanced','videoComments_form'); //display WYSIWYG editors videocomments admin form 
add_filter('simple_form','videoComments_form'); //displays simple editor videocomments form
add_filter('comment_text','videocomments_append_to_wpComments'); //add videocomments popup link to timecode comments.

add_action('activate_videoComments/videoComments.php','videoComments_install');  // installs the database table


?>
