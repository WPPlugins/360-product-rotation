/**
 * Javascript for managing the add 360 view media page
 *
 */


/**
 * Namespace for yofla upload related functions
 *
 */
var yupload = {};

//reference to jQuery
yupload.$ = $ || jQuery;

/**
 *
 *
 * Based on: http://www.sanwebe.com/2012/06/ajax-file-upload-with-php-and-jquery
 *
 * @returns {boolean}
 */
yupload.beforeSubmit = function(){
	//check whether client browser fully supports all File API
	if (window.File && window.FileReader && window.FileList && window.Blob) {

		if(!yupload.$('#FileInput')[0].files[0]){
			yupload.$("#output").html("Please select a file!");
			return false;
		}

		var ftype = yupload.$('#FileInput')[0].files[0].type; // get file type
		var fsize = yupload.$('#FileInput')[0].files[0].size; //get file size

		//allow file types
		/* not working reliable... check disabled
		switch(ftype) {
			case 'application/x-zip-compressed':
			case 'application/zip':
				//all ok
				break;
			default:
				yupload.$("#output").html("<b>"+ftype+"</b> Unsupported file type!");
				return false
		}
		*/

		maxAllowedUploadSize = maxAllowedUploadSize || (10*1048576); //defined variable from php script or fallback

		//Allowed file size is less than 5 MB (1048576)
		if(fsize > maxAllowedUploadSize )
		{
			yupload.$("#output").html("<b>"+bytesToSize(fsize) +"</b> Too big file! <br />File is too big, it should be less than " + bytesToSize(maxAllowedUploadSize) +".");
			return false
		}

		yupload.$('#submit-btn').hide(); //hide submit button
		yupload.$('#loading-img').show(); //hide submit button
		yupload.$("#output").html("");
	}
	else {
		alert("Please upgrade your browser, because your current browser lacks some new features needed for this file upload!");
	}
};

yupload.afterSuccess = function(){
	yupload.$('#submit-btn').show(); //show submit button
	yupload.$('#loading-img').hide(); //hide submit button
	yupload.$('#progressbox').delay( 1000 ).fadeOut(); //hide progress bar
	yupload.$('#statustxt').text('Upload successful.'); //update status text
	yupload.load360ViewsList();
};

yupload.onProgress = function(event, position, total, percentComplete){
	//Progress bar
	yupload.$('#progressbox').show();
	yupload.$('#progressbar').width(percentComplete + '%') //update progressbar percent complete
	yupload.$('#statustxt').html(percentComplete + '%'); //update status text
	if(percentComplete>50)
	{
		yupload.$('#statustxt').css('color','#000'); //change status text to white after 50%
	}
};


yupload.initUploader = function(){
	var options = {
		target:   '#output',   // target element(s) to be updated with server response
		beforeSubmit:  yupload.beforeSubmit,  // pre-submit callback
		success:       yupload.afterSuccess,  // post-submit callback
		uploadProgress: yupload.onProgress, //upload progress callback
		resetForm: true        // reset the form after successful submit
	};

	yupload.$('#MyUploadForm').submit(function() {
		yupload.$(this).ajaxSubmit(options);
		return false;
	});

	yupload.$('#FileInput').on("change", function(){
		yupload.$('#MyUploadForm').ajaxSubmit(options);
	});

};


/**
 * Listeners lists the files in the 360 folder
 */
yupload.load360ViewsList = function(){

	yupload.$('ul.products_list').text('Loading...');

	var rnd = Math.round(Math.random()*1000);

	var utilsUrl = yofla360PluginUrl +  "includes/ajax-controller.php?action=y360list&rnd="+rnd ;

	var jqxhr = yupload.$.ajax(utilsUrl)
		.done(function(data) {
			data = JSON.parse(data);
			if( data && data.length > 0 ){
				yupload.display360ViewsList(data);
				yupload.addCopyEmbedCodeActions();
			}
			else{
				yupload.$('ul.products_list').html('No 360&deg; views were uploaded yet.');
			}
		})
		.fail(function() {
			yupload.$('ul.products_list').text( "Failed loading the list of 360 views!" );
		})
		.always(function() {

		});
};

/**
 * Renders the html code that lists the 360 views and updates the 
 * HTML of the page
 * 
 * @param data
 */
yupload.display360ViewsList = function(data) {
	var output = '', cssClass, action, name, valid, dataPath, item, invalid_text, action_text;

	for(var i=0; i<data.length; i++){
		item      = data[i];
		cssClass  = '';
		action    = '';
		name      = item.name;
		valid     = (item.data && item.data.images && item.data.images.length > 0);
		dataPath  = name;

		invalid_text = '<span class="invalid">(not a 360&deg; view folder)</span>';
		action_text  = '<span class="action action_embed" data-path="'+dataPath+'">embed it</span>, <span class="action action_trash" data-path="'+dataPath+'">trash it</span>';

		cssClass += (valid) ? 'valid' : 'invalid';
		action   += (valid) ? action_text : invalid_text;
		output   += "<li class='"+cssClass+"'>"+name+"  "+action+"</li>\n";
	}

	yupload.$('ul.products_list').html(output);
}

yupload.addCopyEmbedCodeActions = function(){
	yupload.$('span.action_embed').click(function(){
		var data = yupload.$(this).data();
		var path = 'yofla360/'+data.path;

		var text = '[360 width="100%" height="400px" src="'+path+'"]';

		//copy to clipboard
		var msg = "Copy the shortcode to clipboard and then paste into any page.\n";
		msg += "(you can modify the width/height parameter as you like)";
		window.prompt(msg, text);

	});

	yupload.$('span.action_trash').click(function(){
		var data = yupload.$(this).data();
		var path = data.path;

		var doTrash = confirm("Move \""+path+"\" to trash?\n\nYou can recover it from trash or delete permanently using FTP.")
		if(doTrash){
			yupload.moveToTrash(path);
		}
	});
}

/**
 * Moves 360 view folder to trash
 *
 * @param path
 */
yupload.moveToTrash = function(path){

	var url = yofla360PluginUrl +  "includes/ajax-controller.php?action=trash&path="+encodeURIComponent(path);

	yupload.$('ul.products_list').text('Processing...');

	var jqxhr = yupload.$.ajax(url)
		.done(function(data) {
		})
		.fail(function() {
		})
		.always(function() {
			yupload.load360ViewsList();
		});
}


/**
 * function to format bites bit.ly/19yoIPO
 *
 * @param bytes
 * @returns {string}
 */
function bytesToSize(bytes) {
	var sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
	if (bytes == 0) return '0 Bytes';
	var i = parseInt(Math.floor(Math.log(bytes) / Math.log(1024)));
	return Math.round(bytes / Math.pow(1024, i), 2) + ' ' + sizes[i];
}

/**
 * Entry point
 */
yupload.$(document).ready(function() {
	yupload.initUploader();
	yupload.load360ViewsList();
});

