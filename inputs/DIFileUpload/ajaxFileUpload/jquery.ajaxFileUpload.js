/*********

* Asynchronous file upload with progress info
* Copyright (C) Lukas Berns (http://rand1-365.blogspot.com)

* The contents of this file are subject to the Mozilla Public
* License Version 1.1 (the "License"); you may not use this file
* except in compliance with the License. You may obtain a copy of
* the License at http://www.mozilla.org/MPL/

* The License can be found in the 'licenses' directory

* Requirements:
* - fileprogress.php
* - read_settings.php
* - upload.cgi
* - upload_settings.php

* Original by Tomas Larsson:

	* Javascript for file upload demo
	* Copyright (C) Tomas Larsson 2006
	* http://tomas.epineer.se/
	
	* Licence:
	* The contents of this file are subject to the Mozilla Public
	* License Version 1.1 (the "License"); you may not use this file
	* except in compliance with the License. You may obtain a copy of
	* the License at http://www.mozilla.org/MPL/
	* 
	* Software distributed under this License is distributed on an "AS
	* IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or
	* implied. See the License for the specific language governing
	* rights and limitations under the License.


*/

(function($) {

if ($.validator) {
	$.validator.addMethod("ajaxFileUpload", function(value, element, param) {
		return value != "uploading";
	}, "uploading");
	$.validator.addClassRules({
		ajaxFileUploadHidden: {
			ajaxFileUpload: true
		}
	});
}

$.ajaxFileUpload = {
	options: {
		upload_cgi: "/cgi-bin/upload.cgi",
		fileprogress_php: "fileprogress.php",
		restartAfter: 5, // restart after 5 ajax responses without data
		debug: false,
		beforePopIn: function() { },
		beforePopOut: function() { },
		accept: [],
		notAccepted: 'The file you chose was not accepted.\nAllowed: '
	}//,
//	bucket: $('<div id="ajaxFileUploadBucket"/>').appendTo('body') // to store 
};

$.fn.ajaxFileUpload = function(options) {
	var action = '';
	if (typeof options == 'string') {
		action = options;
		options = {};
	}
	
	if (!$.metadata) {
		options = options || {};
		options = $.extend({}, $.ajaxFileUpload.options, options);
	}
	
	if (action) {
		// should be called on wrapper element
		var info = $.data(this[0], 'ajaxFileUpload');
		if (!info) {
			console.error('$.ajaxFileUpload("'+action+'"): Please run ajaxFileUpload([options]) first (without the string argument)');
		}
		switch (action) {
			case 'value':
			if (arguments[1] !== undefined) {
				if (arguments[1]) {
					info.setValue(arguments[1]);
					info.changeToFinishedStatus();
				}
			}
			else {
				return info.hiddenInput.val();
			}
			break;
			
			
		}
		return this.eq(0);
	}
	
	// if no action => init
	var wrappers; // store the changed elements here
	this.each(function() {
		var w = new ajaxFileUploader($(this), options).wrapper;
		if (!wrappers) {
			wrappers = w;
		}
		else {
			wrappers.add(w);
		}
	});
	return wrappers;
};

var ajaxFileUploader = function(el, options) {
	if ($.metadata) {
		options = options || {};
		var meta = el.metadata().ajaxFileUpload || {};
		options = $.extend({}, $.ajaxFileUpload.options, options, meta);
	}
	
	// will submit a new form at the end of <body>
	// into a hidden iframe when the value of the input changes
	// 
	// then the progress will be observed
	// and displayed as text (can be made to a progress bar)
	// until the upload completes
	
	this.id = el.attr('id') || el.attr('name') || 'ajaxFileUpload'+(new Date().getTime());
	
	this.sid = '';
	this.valid = this.stopped = true;
	this.count = this.errorCount = this.noDataCount = this.lastResponse = 0;
	
	this.previousData = '';
	this.decay = 1; 				// see around line ### (209)
	this.originalDecay = 1.2;		// call server not so many times when progress doesn't change for a while (e.g. big file)
	this.frequency = 0.5;			// in seconds
	this.maxWait = 5;				// maximum seconds to wait (even if very big file)
	
	this.oldFile = ''; // a file that’s already stored on the server
	this.deleteOld = false; // if there’s no new file but the old one should be deleted
	this.uploadedSid = '';
	this.uploadedFileName = '';
	this.uploadingFileName = '';
	
	this.orgEl = el;
	this.options = options;
	
	// delete elements that might have been created before
	$('#'+this.id+'_iframe').add('#'+this.id+'_form').add('#'+this.id+'_wrapper').remove();
	
	// init the elements
	this.initIFrameAndForm();
	this.initWrapperAndContents();
	
	this.changeToNoFileStatus();
	
	$.data(this.wrapper[0], 'ajaxFileUpload', this);
};

ajaxFileUploader.prototype = {
	
	// Methods to ease init
	
	initIFrameAndForm: function() {
		var self = this;
		
		// create iframe to submit into at the end of <body>
		this.iframe = $('<iframe />')
			.attr({
				id: this.id+'_iframe',
				name: this.id+'_iframe',
				src: 'about:blank' // prevent opening last page when doing a broser reload
			})
			.hide()
			.appendTo('body');

		// create the form to submit at the end of <body>
		this.form = $('<form />')
			.attr({
				enctype: 'multipart/form-data',
				method: 'post',
				target: this.id+'_iframe',
				id: this.id+'_form'
			})
			.hide()
			.submit(function() {
				var filename = self.fileInput.val();
				var ext = filename.substr(filename.search(/\.[^\.]+$/) + 1);
				var accept = self.options.accept;
				
				if (accept.length) {
					self.valid = false;
					
					for (var x in accept) {
						if (accept[x].toLowerCase() == ext.toLowerCase()) {
							self.valid = true;
							break;
						}
					}
				}
				else {
					self.valid = true;
				}
				
				if (self.valid) {
					return true;
				}
				else {
					self.changeToNoFileStatus();
					alert(self.options.notAccepted + accept.join(', '));
					return false;
				}
			})
			.appendTo("body");
	},

	initWrapperAndContents: function() {
		var self = this;
		this.emptyFileInput = $('<input/>', {
				type: 'file',
				name: inputName+'_file',
				id: this.id+'_file'
			})
			.css({
				position: 'absolute',
				zIndex: 1,
				opacity: 0
			});
		
		this.fileInputEvents['change'] = function() {
			/*
			var id = $(this).attr('id').replace(/_file$/, '');
			$.data($('#'+id)[0], 'ajaxFileUpload').start();
//			*/
			self.start();
		};
		
		var inputName = $(this.orgEl).attr('name');
		var clas = $(this.orgEl).attr("class");

		// the wrapper to contain everything
		// all ajaxFileUpload('...') methods should be called on this element
		this.wrapper = $('<div/>')
			.attr({
				id: this.id+'_wrapper'
			})
			.css({
				display: 'inline-block'
			});

		// the progress bar
		this.progressContainer = $('<div />')
			.addClass('ajaxFileUploadProgressContainer')
			.css('display', 'inline-block')
			.hide()
			.appendTo(this.wrapper);
		
		this.progressBar = $('<div />')
			.addClass('ajaxFileUploadProgressBar')
			.hide()
			.appendTo(this.progressContainer);
		
		// the container for the filename
		this.fileNameText = $('<input type="text" />')
			.attr('readonly', true)
			.addClass('ajaxFileUploadFileName')
			.appendTo(this.progressContainer);
		
		// the upload button, wrapped together with the confirmator
		
/*		this.uploadButtonAndConfirmatorWrapper = $('<div />')
			.css('display', 'inline-block')
			.appendTo(this.wrapper);
		
		// the confirmator
		this.confirmator = $('<div />')
			.css({
				'padding': '10px',
				'background': '#a00',
				'color': 'white',
				'display': 'none', // later it will be inline-block (see this.confirm(text))
				'opacity': 0
			})
			.click(function() {
				self.hideConfirmator();
			})
			.appendTo(this.uploadButtonAndConfirmatorWrapper);*/
		
		this.segmentedControls = $('<div/>')
			.addClass('segmented')
			.css({
				display: 'inline-block',
				fontSize: '0.8em'
			})
			.appendTo(this.wrapper);
		
		// the button itself
		this.fileInput = this.emptyFileInput.clone(); // has to be done this way, so that $.validate does not try to validate fileInput
		this.uploadButton = this.makeUploadButton('Choose File').appendTo(this.segmentedControls); // uploadButtonAndConfirmatorWrapper);
		this.uploadButton.addClass('ignore'); // don’t apply the first-child styles related to this.segmentedControls
		this.bindFileInputEvents();
		
		// the cancel button (while uploading)
		this.cancelButton = $('<input type="button" />')
			.val('Cancel')
			.addClass('ignore')
			.click(function() {
				if (confirm("Do you really want to cancel the upload?")) {
					self.stop();
					
					self.setValue();
					if (self.uploadedSid || self.oldFile) {
						self.changeToFinishedStatus();
					}
					else {
						self.changeToNoFileStatus();
					}
				}

			})
			.appendTo(this.segmentedControls);
		
		// the revert button (to the original)
		this.revertButton = $('<input type="button" />')
			.val('Revert to Original')
			.click(function() {
				if (confirm("The file you just uploaded will be lost. Revert to original?")) {
					self.uploadedSid = '';
					self.setValue();
					self.uploadedFileName = '';
					self.changeToFinishedStatus();
				}
			})
			.appendTo(this.segmentedControls);
		
		// the remove button
		this.removeButton = $('<input type="button" />')
			.val('Remove')
			.click(function() {
				if (confirm("The file will be lost. Do you really want to remove the file?")) {
					self.uploadedSid = '';
					self.uploadedFileName = '';
					self.uploadingFileName = '';
					self.deleteOld = true;
					
					self.setValue();
					self.changeToNoFileStatus();
				}
			})
			.appendTo(this.segmentedControls);
		
		if (this.options.debug) {
			////// Display status (for debugging) ///////
			this.statusDiv = $('<div/>').appendTo(this.wrapper);
		}
		
		this.hiddenInput = $('<input type="hidden" />')
			.attr({
				name: inputName,
				id: this.id,
				'class': clas
			})
			.addClass('ajaxFileUploadHidden')
			.val('|')
			.appendTo(this.wrapper);
		
		this.orgEl.replaceWith(this.wrapper);
	},
	
	makeUploadButton: function(text) {
		// this function doesn’t insert the element, just creates and returns them
		var self = this;
		
		var wrapper = $('<div/>').appendTo('body')
			.css({
				overflow: 'hidden',
				position: 'relative',
				display: 'inline-block'
			});

		var button = $('<input type="button" />')
			.val(text)
			.focus(function() {
				fileInput.focus(); // FIXME: This breaks backwards-tabbing-through
			})
			.appendTo(wrapper);

		self.fileInput.appendTo(wrapper);
		
		this.fileInputEvents['focus'] = function() {
				button.addClass('focus');
			};
		this.fileInputEvents['blur'] = function() {
				button.removeClass('focus');
			};
		this.fileInputEvents['mousedown'] = function() {
				if (hovering) {
					button.removeClass('hover');
				}
				button.addClass('active');

				$(document).bind('mouseup.ajaxFileUploadButton', function() {
					button.removeClass('active');
					$(document).unbind('mouseup.ajaxFileUploadButton');
				});
			};
		
		var hovering = false;
		this.fileInputEvents['mouseenter'] = function() {
				button.addClass('hover');
				hovering = true;
			};
		this.fileInputEvents['mouseleave'] = function() {
				button.removeClass('hover');
				hovering = true;
			};
		
		wrapper.mousemove(function(e) {
			// the file input will move while mouse is over the button..
			// so that the mouse is always over the button of the file input
			var pos = wrapper.offset();
			var w = self.fileInput.width();
			var h = self.fileInput.height();
			self.fileInput.css({
				top: e.pageY -pos.top -h/2,
				left: e.pageX -pos.left -(w-30)
			});
		});

		return wrapper;
	},
	
	/* TODO: Confirmation stuff, unimplemented */
	
/*	confirming: false,
	
	confirm: function(obj, text) {
		if (!this.confirming) {
			this.showConfirmator(obj, text);
			return false;
		}
		else {
			this.hideConfirmator(obj);
			return true;
		}
	},
	
	showConfirmator: function(obj, text) {
		this.confirming = true;
		var buttonWidth = this.uploadButton.outerWidth();
		this.confirmator
			.text(text)
			.css({
				paddingRight: (10 + buttonWidth)+'px',
				display: 'inline-block'
			})
			.css({
				// this has to be set after paddingRight has been set
				marginRight: -this.confirmator.outerWidth() 
			})
			.animate({
				marginRight: -(5 + buttonWidth)+'px',
				opacity: 1
			}, 'normal');
	},
	
	hideConfirmator: function() {
		this.confirmator
			.animate({
				marginRight: -this.confirmator.outerWidth()+'px',
				opacity: 0
			}, 'normal');
		this.confirming = false;
	},
	
	*/
	
	/* Events for the fileInput */
	
	fileInputEvents: {},
	bindFileInputEvents: function() {
		// the this.fileInput is moved using replaceWith in this.start()
		// this seems to remove its event handlers, so fix them here
		
		this.fileInput.unbind('.ajaxFileUpload');
		
		for (var name in this.fileInputEvents) {
			this.fileInput.bind(name+'.ajaxFileUpload', this.fileInputEvents[name]);
		}
	},

	// Methods to change states (no file, uploading, finished)

	changeToNoFileStatus: function() {
		this.wrapper.children().hide();
		this.segmentedControls.show().children().hide();
		
		this.uploadButton.show().find(':button').removeClass('left').val('Upload');
		this.fileInput.unbind('click.ajaxFileUpload');
		
		if ($.fancybox && $.fancybox.resize) {
			$.fancybox.resize();
		}
	},

	changeToUploadingStatus: function() {
		this.wrapper.children().hide();
		this.segmentedControls.show().children().hide();
		
		this.fileNameText.val(this.uploadingFileName.replace(/^.*[\/\\]([^\/\\]*)$/, '$1'));
		this.progressBar.css('width', 0).show();
		this.progressContainer.addClass('uploading').show();
		
		this.cancelButton.show();
		
		if ($.fancybox && $.fancybox.resize) {
			$.fancybox.resize();
		}
	},

	changeToFinishedStatus: function() {
		var self = this;
		var fileName = this.uploadedFileName || (this.deleteOld ? '' : this.oldFile);
		
		this.wrapper.children().hide();
		this.segmentedControls.show().children().hide();
		
		fileName = fileName.replace(/^.*[\/\\]([^\/\\]*)$/, '$1');
		
		this.fileNameText.val('✓ '+fileName);
		this.progressContainer.removeClass('uploading').show();
		this.progressBar.fadeOut();
		
		this.uploadButton.show().find(':button').addClass('left').val('Change');
		this.removeButton.show();
		
		if (this.uploadedFileName && this.oldFile && !this.deleteOld) {
			this.revertButton.show();
		}
		
		this.fileInput.bind('click.ajaxFileUpload', function() {
			return confirm('The old file will be gone. Do you still want to change the file?');
		});
		
		if ($.fancybox && $.fancybox.resize) {
			$.fancybox.resize();
		}
	},

	start: function() {
		this.stop(); // stop previous upload -- allows for a restart
		
		var now = new Date();
		this.sid = ("" + Math.round(Math.random()*10000000000) + Math.round(Math.random()*1000000000) + now.getTime()).substr(0, 32);
		// a 32 char long unique session id
		this.form.attr("action", this.options.upload_cgi+"?sid="+this.sid);
		
		// reset
		this.count = this.errorCount = this.noDataCount = 0;
		this.stopped = false;
		this.decay = 1;
		this.previousData = "";
		
		var placeholder = $('<span/>');
		var filename = this.fileInput.val();
		this.fileInput.replaceWith(placeholder).appendTo(this.form);
		this.form.submit();
		
		placeholder.replaceWith(this.fileInput);
		this.bindFileInputEvents();
		
		if (this.valid) {
			this.uploadingFileName = filename;
			
			this.changeToUploadingStatus();
			this.getStatus();
		}
	},

	getStatus: function() {
		var self = this;
		$.ajax({
			cache: false,
			data: {sid: this.sid, json: true},
			dataType: "json",
			type: "GET",
			url: this.options.fileprogress_php,
			error: function(XMLHttpRequest, textStatus, errorThrown) {
				if (self.errorCount > 5) {
					if (confirm("Upload failed - "+textStatus+" - "+errorThrown + "\nTry again?")) {
						self.start();
					}
					else {
						self.stop();
					}
					return;
				}

				if (typeof(window.console) != "undefined") {
					window.console.log("Upload failed: "+textStatus+" - "+errorThrown + "\nWill stop after " + (6-self.errorCount) + " more errors");
				}
				
				if (self.options.debug) {
					var table = $('<table/>');
					for (var key in self) {
						table.append('<tr><td>'+key+':</td><td>'+self[key]+'</td></tr>');
					}
					self.statusDiv.html(table);
				}

				self.timer = setTimeout(function() {
					self.getStatus();
				}, self.decay * self.frequency * 1000);
				self.errorCount++;
			},
			success: function(data, textStatus) {
				self.lastResponse = new Date().getTime();

				if (self.options.debug) {
					var table = $('<table/>');
					var key;
					for (key in data) {
						table.append('<tr><td>'+key+':</td><td>'+data[key]+'</td></tr>');
					}
					table.append('<tr><td>---</td><td>---</td></tr>');
					for (key in self) {
						table.append('<tr><td>'+key+':</td><td>'+self[key]+'</td></tr>');
					}
					self.statusDiv.html(table);
				}
				
				if (self.stopped) {
					return;
				}
				
				if (self.hiddenInput.val() == "stopUploading") {
					// you can set the value of the hidden input
					// to "stopUploading" to stop uploading
					self.fileNameText.val("stopped");
					self.stop();
					return;
				}

				if (parseInt(data.percentDone, 10) >= 100 && data.currentSize == data.totalSize) {
					// if uploaded 100%
					
					self.stop();
					self.uploadedSid = self.sid;
					self.uploadedFileName = self.uploadingFileName;
					self.uploadingFileName = '';
					
					var newFileInput = self.emptyFileInput.clone();
					self.fileInput.replaceWith(newFileInput);
					self.fileInput = newFileInput;
					self.bindFileInputEvents();
					
					self.setValue();
					self.changeToFinishedStatus();
					return;
				}
				else if (data.percentDone && data.percentDone != self.previousData.percentDone) {
					// if uploaded percentage changed
					self.decay = 1; // reset decay
					// var w = self.progress.outerWidth();
					//  self.progress.animate({backgroundPosition: (w*parseInt(data.percentDone)/100)+"px top"});
				}
				else if (data.percentDone == self.previousData.percentDone) {
					// if uploaded percentage hasn't changed
					// call server less often,
					// because it could be a big file
					if (self.decay * self.frequency < self.maxWait) {
						self.decay *= self.originalDecay;
					}
				}

				remTime = parseInt(data.remainingTime, 10);
				if (isNaN(remTime))
					remTime = data.remainingTime + "s";
				else if (remTime > 60*120) // if longer than 2 hours
					remTime = Math.round(remTime / 3600) + "h";
				else if (remTime > 120) // if longer than 2 min
					remTime = Math.round(remTime / 60) + "min";
				else
					remTime = Math.round(remTime) + "s";

				if (self.noDataCount > self.options.restartAfter) {
					// restart if it doesn't seem to work
					if (typeof(window.console) != "undefined") {
						window.console.log("Restarted upload because it didn't seem to be working");
					}
					self.start();
					return;
				}

//				self.progress.val(data.percentDone + "%, " + remTime + ", " + data.speed);
				self.progressBar.animate({width: data.percentDone+'%'});
				if (!data.currentSize || data.currentSize == '0') {
					self.noDataCount++;
				}
				self.count++;
				
				self.hiddenInput.val('uploading');
				self.previousData = data;
				
				self.timer = setTimeout(function() {
					self.getStatus();
				}, self.decay * self.frequency * 1000);
			}
		});

		// sometimes it happens that the requested file is responsed only after one file has completed uploading.
		// prevent this by the following line
		// = refetch data if no data was received in 5 seconds

		setTimeout(function() {
			if (!self.stopped && self.lastResponse + 5000 < new Date().getTime()) {
				self.getStatus();
			}
		}, 5000);
	},

	stop: function() {
		if (this.stopped) return;
		
		this.iframe.attr("src", "about:blank");
		this.fileNameText.val("");
		
		this.stopped = true;
		clearTimeout(this.timer);
	},
	
	setValue: function(val) {
		// val is optional
		// if omitted, this function will set the right value according to uploadedSid || oldFile
		
		if (val) {
			this.oldFile = val.replace(/^\|/, '');
			this.deleteOld = false;
			this.uploadedSid = '';
		}
		if (this.uploadedSid) {
			this.hiddenInput.val(this.uploadedSid+'|'+this.oldFile);
		}
		else if (this.deleteOld) {
			this.hiddenInput.val('delete|'+this.oldFile);
		}
		else {
			this.hiddenInput.val('|'+this.oldFile);
		}
	}
};

})(jQuery);