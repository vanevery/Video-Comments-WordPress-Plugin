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

/* Variables Specific to Video Comments */

var formstate = "display"; // form state is a variable that keeps tabs for the one button interface. Possible values are "display", "comment"
var postid = "";

//**********************************************************
// comment array for movie
//**********************************************************
//var divCounter = 0;
var startFrame = 0;
var startTimecode = "00:00:00";
var title = '';
//var getCommentsInterval;
var getVideoCommentsTimeout;
var videoReadyTimeout;
var vReady = false;


/* Logging utility */
function log(message) {
/*
    if (!log.window_ || log.window_.closed) {
        var win = window.open("", null, "width=400,height=200," +
                              "scrollbars=yes,resizable=yes,status=no," +
                              "location=no,menubar=no,toolbar=no");
        if (!win) return;
        var doc = win.document;
        doc.write("<html><head><title>Debug Log</title></head>" +
                  "<body></body></html>");
        doc.close();
        log.window_ = win;
    }
    var logLine = log.window_.document.createElement("div");
    logLine.appendChild(log.window_.document.createTextNode(message));
    log.window_.document.body.appendChild(logLine);
*/
}


/* Basic AJAX Call */
/* THANK YOU DAVID NOLAN */
var xmlHTTPReq = false;
function initAjaxObject() 
{
	var aobject;
	var msxmlhttp = new Array('Msxml2.XMLHTTP.5.0','Msxml2.XMLHTTP.4.0','Msxml2.XMLHTTP.3.0','Msxml2.XMLHTTP','Microsoft.XMLHTTP');
	for (var i = 0; i < msxmlhttp.length; i++) 
	{
		try 
		{
			log(msxmlhttp[i]);
			aobject = new ActiveXObject(msxmlhttp[i]);
			break;
		} 
		catch (err) 
		{
			log(err);
			aobject = null;
		}
	}
	
	if (!aobject && (typeof(XMLHttpRequest) != "undefined"))
	{
		aobject = new XMLHttpRequest();
	}

	return aobject;
}

/* Create the request, default method is "POST" */
/* This stuff needs more work */
function createAjaxRequest(url,responseFunction,method) 
{   
	if (!xmlHTTPReq)
	{
		xmlHTTPReq = initAjaxObject();
	}

	if (xmlHTTPReq)
	{
		log(url);
		
		//xmlHTTPReq.overrideMimeType('text/html');
		xmlHTTPReq.onreadystatechange = function() 
		{
			if (xmlHTTPReq.readyState == 1)
			{
						
			}
			else if (xmlHTTPReq.readyState == 4)
			{
				// Do something with the data		
				log("initial response:" + xmlHTTPReq.responseText);
				responseFunction(xmlHTTPReq.responseText);
			}
		}
		
		xmlHTTPReq.open('GET', url, true); 
		xmlHTTPReq.send(null);
	}
	else
	{
		log("Ajax not working");
	}
}

/* Initialize the video comments system */
function initvideo(id,post_title,startTime) 
{
	/* Populate global vars */
	postid = id;
	title = post_title;
	if (startTime.length == 8)
	{
		startTimecode = startTime;
	}
	
	//getVideoComments(postid); // get comments and populate the side

	//oneButton(); // oneButton keep track of the one button interface
	
	/* Get the comments every so often */
	//getCommentsInterval = window.setInterval('getVideoComments(postid)',10000); //set timer to request comments every 10 seconds.

	/* Display time for chat */
	window.setInterval('displayTime()',1000); 

	/* Wait a little bit to see if the video is ready before we start playing at any particular point */
	videoReadyTimeout = window.setTimeout('videoReady()',500);		
}

/* Check to see if the video is ready to go */
function videoReady() 
{
	if (videoReadyTimeout != null && videoReadyTimeout != false && videoReadyTimeout > -1)
	{
		window.clearTimeout(videoReadyTimeout);
	}
	
	if (document.movieplayer.GetMaxTimeLoaded() > startFrame) 
	{
		navigate(startTimecode);		
		vReady = true;
	} 
	else 
	{
		window.setTimeout('videoReady()',500);
	}
}

function oneButton() 
{
	//get the button
	var button = document.getElementById("button");
	
	if (formstate == "display") 
	{
		//Controls making a comment. The playing movie will be stopped. 
		//a comment form displayed. 

		//hide the save button
		var saveButton = document.getElementById("save_Button");
		saveButton.style.display = "none"; 
		
		//show the auto scroll
		var auto_scroll = document.getElementById("scrolling_div");
		auto_scroll.style.display = "block"
		
		//display the comment button
		var makeButton = document.getElementById("make_Button");
		makeButton.style.display = "block"; 
		
	} 
	else if (formstate == "comment") 
	{
		//hide the comment button
		var makeButton = document.getElementById("make_Button");
		makeButton.style.display = "none"; 

		// hide the auto scroll
		var auto_scroll = document.getElementById("scrolling_div");
		auto_scroll.style.display = "none"
		
		//display the save button
		var saveButton = document.getElementById("save_Button");
		saveButton.style.display = "block"; 			
	}

	makeButton.blur();
	saveButton.blur();
}

function comment() 
{
	//window.clearInterval(getCommentsInterval)

	document.movieplayer.Stop(); //stop movie
	formstate = "comment";	
	oneButton();
	
	//display the form 
	commentForm("show");
}

function play() 
{
	if (vReady)
	{
		document.movieplayer.Play();
	}
}

function stop() 
{
	if (vReady)
	{
		document.movieplayer.Stop();
	}
}

var videoCommentsResponse = function(responseText)
{
	// I don't seem to get the response ..
	log("Got a response" + responseText);
	
	var sbegin = responseText.substring(0,7);
	if (sbegin == 'Result:')
	{
		// From the insert command
		alert(responseText.substring(7));
		log(responseText.substring(7));

		// Update the side, after the post has been made
		getVideoComments(postid);
	}
	else
	{
		var comments_container = document.getElementById("comments_container");
		log(responseText);
		comments_container.innerHTML = responseText;
		changeLinkTargets();
	}
}

function getVideoComments(postid) 
{
	if (getVideoCommentsTimeout != null && getVideoCommentsTimeout != false && getVideoCommentsTimeout > -1)
	{
		window.clearTimeout(getVideoCommentsTimeout);
	}
	
	document.title = "loading video and comments...";

	if (document.movieplayer.GetPluginStatus() == "Waiting") 
	{
		getVideoCommentsTimeout = setTimeout('getVideoComments(postid)',1000);	// Wait some more..
	} 
	else 
	{
		var url = 'videoComments_requests.php?mode=GET&postid='+postid+'&movieRate='+getMovieTimeScale();
		
		createAjaxRequest(url,videoCommentsResponse,"GET");
	}
}

function reset() 
{
	commentForm("hide");
	
	var cancelButton = document.getElementById("cancel_Button");
	cancelButton.style.display = "none"; //display the button
	
	formstate = "display";
	oneButton();
	
	play();
	
	/* Get the comments every so often */
	//getCommentsInterval = window.setInterval('getVideoComments(postid)',10000); //set timer to request comments every 10 seconds.
}

function validateForm() 
{
	if (document.getElementById("comment").value != "") 
	{
		return true;
	} 
	else 
	{
		return false;
	}
}

function getCommentParams()
{
	var commentForm = document.forms["commentform"];
	var params = "";
	
	var oElement, elName, elValue;
	for (var i = 0; i < commentForm.elements.length; i++)
	{
		oElement = commentForm.elements[i];
		elName = commentForm.elements[i].name;
		elValue = commentForm.elements[i].value;
	
		switch (oElement.type)
		{
			case 'select-multiple':
				for(var j=0; j<oElement.options.length; j++)
				{
					if (oElement.options[j].selected && oElement.options[j].value != "")
					{
						params += encodeURIComponent(elName) + '=' + encodeURIComponent(oElement.options[j].value) + '&';
					}
				}
				break;
			case 'radio':
				// Not implemented yet
			case 'checkbox':
				if (oElement.checked && elValue != "") 
				{
					params += encodeURIComponent(elName) + '=' + encodeURIComponent(elValue) + '&';
				}
				break;
			case 'file':
				// Won't work with javascript
				break;
			case undefined:
				// hmmmn
				break;
			default:
				if (elValue != "")
				{
					params += encodeURIComponent(elName) + '=' + encodeURIComponent(elValue)+ '&';
				}
				break;
		}
	}
	params = params.substring(0,params.length-1);	                    
    return params;
}

function save() 
{
	//verify the form is valid
	if (validateForm() && (formstate == "comment")) 
	{ 
		var curTime = getCurTime();
		//send the contents of the form to a script to insert into the db.
		var comment = document.getElementById("comment").value;
		
		document.getElementById("comment").value = "[" + formatTime(curTime) + "] " + comment;
		//send the contents of the form to a script to insert into the db.

		var parameters = getCommentParams();

		var url = 'videoComments_requests.php';
		createAjaxRequest(url + "?" + parameters,videoCommentsResponse,"GET");
		
		//change the form state
		formstate = "display";
		oneButton();
		
		//close the comment form
		commentForm("hide"); 
		
		//clear the comment area
		var commentArea = document.getElementById("comment");
		commentArea.value = "";
		
		//start playing the movie again.
		play();
	} 
	else 
	{
		alert("Please enter a comment");
	}

	/* Get the updated comments */
	//getCommentsInterval = window.setTimeout('getVideoComments(postid)',5000); 	
	//getVideoCommentsTimeout = window.setTimeout('getVideoComments(postid)',5000);
}

function getMovieTimeScale() 
{
	if (vReady)
	{
		return document.movieplayer.GetTimeScale();
	}
	else
	{
		return 0;
	}
}

function getCurTime() 
{
	if (vReady)
	{
		var curTime = Math.round(document.movieplayer.GetTime() / document.movieplayer.GetTimeScale());
		return curTime;
	}
	else
	{
		return 0;
	}
}

function formatTime(curTime) 
{
	if (curTime > 60*60)
	{
		hours = Math.floor(curTime/(60*60));
		curTime = curTime - hours*60*60;
		
		if (hours < 10) {
			hours = "0" + hours;
		}
	}
	else
	{
		hours = "00";
	}

	//format minutes
	if (curTime > 60) 
	{
		minutes = Math.floor(curTime/60);
		curTime = curTime -minutes*60;
		
		if (minutes < 10) 
		{
			minutes = "0" + minutes;
		}		
	} else 
	{
		minutes = "00";
	}
	
	//format seconds
	seconds = Math.floor(curTime%60);
	if (seconds < 10) 
	{ 
		seconds = "0" + seconds; 
	}
	
	//prepare new time
	newTime = hours + ":" + minutes + ":" + seconds;
	return newTime;
}

var previous_div = 0;
function displayTime() 
{
	//alert("displayTime");
	var theTime = formatTime(getCurTime());
	var comments_container = document.getElementById("comments_container");
	var timecode_div = document.getElementById(theTime);
	
	//this is where the scrolling actually happens
	if (timecode_div != null) 
	{
		//scroll the comment area to display the current timecode_div

		var scrolling_enabled = document.getElementById("scrolling_enabled");
		if (scrolling_enabled.checked)
		{
			comments_container.scrollTop = timecode_div.offsetTop - 60;
		}

		//log(comments_container.scrollTop);
		
		if (previous_div != 0)
		{
			previous_div.style.background = "#FFFFFF";
		}
		timecode_div.style.background = "#A5EEA7"; //change background color of the current timecode_div			
		previous_div = timecode_div;
		
		//var theNodes = comments_container.getElementsByTagName("div");
		//log("Length:" + theNodes.length);
		/*for (var x = 0; x < theNodes.length; x++)	
		{
			if (theNodes[x] != timecode_div && theNodes[x].hasAttribute("style"))
			{
				theNodes[x].style.background = "#FFFFFF";
			}
			else if (theNodes[x] == timecode_div && theNodes[x].hasAttribute("style"))
			{
				log("here");
				theNodes[x].style.background = "#A5EEA7";
			}
			
			log("time:" + theTime);
			log("id:" + theNodes[x].id);
			theNodes[x].style.background="#FFFFFFF";
		}*/						
			
		//timecode_div.style.background = "#A5EEA7"; //change background color of the current timecode_div			
	}
	document.title = title + " " + theTime ;
}

function navigate(timecode) 
{
	if (timecode.length == 8)
	{
		var frame = convertToFrames(timecode)
		if (frame != "") 
		{
			reset();
		
			if (vReady)
			{
				document.movieplayer.Stop();
				document.movieplayer.SetTime(frame);
				document.movieplayer.Play();
			}
		}
	}
}

function convertToFrames(timecode) 
{
	var timeunits = new Array();
	timeunits = timecode.split(':');
	
	var hours = Number(timeunits[0]);
	var minutes = Number(timeunits[1]);
	var seconds = Number(timeunits[2]);
	
	var frames = (((hours*3600) + (minutes*60) + seconds) * document.movieplayer.GetTimeScale());
	return frames;
}

//toggle the list and the comment form
function commentForm(mode) 
{ 
	var theForm = document.getElementById("comment_form"); // the form to make a new comment
	var theList = document.getElementById("comments_container"); //the list of comments
	
	if (mode == "show") 
	{
		theList.style.display = "none"; //hide comment list
		theForm.style.display = "block"; //show the form, change it's display style
		
		var cancelButton = document.getElementById("cancel_Button");
		cancelButton.style.display = "block"; //display the button
			
		cancelButton.onclick = reset; //set click to execute reset() function
		
	} 
	else if (mode == "hide") 
	{
	
		theForm.style.display = "none";  //hide the form
			
		var cancelButton = document.getElementById("cancel_Button");
		cancelButton.style.display = "none"; //display the button
		
		//clear the comment area
		var commentArea = document.getElementById("comment");
		commentArea.value = "";
		
		theList.style.display = "block"; //show the comments list
	}
}

function changeLinkTargets() 
{
	var links = document.getElementsByTagName("a");
	
	for (var i=0;i<links.length;i++) {
		links[i].target = "_blank";
	}
}