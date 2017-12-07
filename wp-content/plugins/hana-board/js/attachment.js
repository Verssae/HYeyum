jQuery(document).ready(function() {

	if (typeof hanaboard_attachment.use_media_library != 'undefined' && hanaboard_attachment.use_media_library == "true") {

		jQuery('#hanaboard-attachment-upload-pickfiles').css('display', 'none');
		jQuery('#insert-media-button').css('display', 'inline');
	}

	window.hanaboard_attachment_list_check = function(src) {
		var flag = true;
		jQuery("input[name^='hanaboard_attach_url']").each(function() {
			if (jQuery(this).val() == src)
				flag = false;
		});
		return flag;
	};
	window.hanaboard_attachment_list_html = function(idx, src) {
		var filename = src.split('/').pop();
		var item_html = '<li>';
		item_html += '<input type="hidden" name="hanaboard_attach_file_id[]" value="" />';
		item_html += '<input type="hidden" name="hanaboard_attach_file_url[]" value="' + src + '" />';
		item_html += '<span class="attachment-filename">' + filename + '</span>';
		item_html += '</li>';
		return item_html;
	};

	var HANA_BOARD_Attachment = {
	init : function() {
		this.maxFiles = parseInt(hanaboard_attachment.number);

		jQuery('#hanaboard-attachment-upload-filelist').on('click', 'a.attachment-delete', this.removeAttachment);

		if (jQuery('#hanaboard-attachment-upload-filelist').length) {
			jQuery('#hanaboard-attachment-upload-filelist ul.hanaboard-attachment-list').sortable({
			cursor : 'crosshair',
			handle : '.handle'
			});
		}

		if (hanaboard_attachment.use_media_library) {
			// jQuery('#insert-media-button').css('display', 'inline-block');
			// jQuery('#hanaboard-attachment-upload-pickfiles').hide();
		}

		jQuery('.attachment-media-to-content').on('click', function() {
			// jQuery(this).data( 'attach_id' );
		});

		HANA_BOARD_Attachment.attachUploader();
		HANA_BOARD_Attachment.hideUploadBtn();
	},
	hideUploadBtn : function() {
	},
	attachUploader : function() {
		
		if (jQuery('#hanaboard-attachment-upload-pickfiles').length < 1) {
			return;
		}
		attachUploader = new plupload.Uploader(hanaboard_attachment.plupload);

		attachUploader.init();
		attachUploader.refresh();
		// Hook in the second button
		/*
		 * plupload.addEvent(document.getElementById('hanaboard-attachment-upload-pickfiles'),
		 * 'click', function(e) { var input =
		 * document.getElementById(attachUploader.id + '_html5'); if (input &&
		 * !input.disabled) { input.click(); } // if jQuery('div.moxie-shim
		 * input[type=file]').trigger('click'); e.preventDefault(); });
		 */
		/*
		 * jQuery('#hanaboard-attachment-upload-pickfiles').click(function(e) { //
		 * attachUploader.start(); e.preventDefault(); e.stopPropagation();
		 * jQuery('div.moxie-shim input[type=file]').trigger('click'); });
		 * 
		 */

		attachUploader.bind('FilesAdded', function(up, files) {
			jQuery.each(files, function(i, file) {
				jQuery('#hanaboard-attachment-upload-filelist').append('<div id="' + file.id + '">' + file.name + ' (' + plupload.formatSize(file.size) + ') <b></b>' + '</div>');
			});
			attachUploader.start();
			up.refresh(); // Reposition Flash/Silverlight
		});

		attachUploader.bind('UploadProgress', function(up, file) {
			var processing_message = "";
			processing_message = '<i class="fa fa-spinner fa-pulse"></i>';
			if (file.percent == 100)
				processing_message += 'Processing.. ';
			jQuery('#' + file.id + " b").html(file.percent + "%" + processing_message);
		});

		attachUploader.bind('Error', function(up, err) {
			HANA_BOARD_Attachment.uploadErrorOutput(err);

			up.refresh(); // Reposition Flash/Silverlight
		});

		attachUploader.bind('FileUploaded', function(up, file, response) {
			try {
				var resp = jQuery.parseJSON(response.response);

				jQuery('#' + file.id).remove();
				if (resp.success) {
					if (resp.attach_data.is_image) {

					}
					var fileitem_display = '';
					var html_send_to_content = '';
					if (resp.attach_data.is_image === true) {
						fileitem_display = ' style="display:none;"';

						html_send_to_content += '<p><a href="' + resp.attach_data.url_full + '" data-mce-href="' + resp.attach_data.url_full + '" rel="alinnone attachment wp-att-' + resp.attach_data.attach_id + '">';
						html_send_to_content += '<img class="alignnone wp-image-' + resp.attach_data.attach_id + ' lazy-loaded" alt="' + resp.attach_data.filename + '" src="' + resp.attach_data.url + '" data-lazy-type="image" data-src="' + resp.attach_data.url + '">';
						html_send_to_content += "</a></p><br />";

						window.send_to_editor(html_send_to_content);
						if (typeof tinyMCE != 'undefined')
							tinyMCE.triggerSave();		
						
					}

					var html_append_filelist = '<li class="hanaboard-attachment" ' + fileitem_display + '>';
					html_append_filelist += '<span class="handle"></span>';
					html_append_filelist += '<input type="hidden" name="hanaboard_attach_title[]" value="' + resp.attach_data.filename + '" />';
					html_append_filelist += '<span class="attachment-name">' + resp.attach_data.filename + '</span>';
					html_append_filelist += '<span class="attachment-size">(' + resp.attach_data.filesize + ')</span>';
					html_append_filelist += '<span class="attachment-actions"><a href="#" class="attachment-delete hanaboard-button" data-attach_id="' + resp.attach_data.attach_id + '">' + hanaboard_attachment.msg.Delete + '</a></span>';
					html_append_filelist += '<input type="hidden" name="hanaboard_attach_id[]" value="' + resp.attach_data.attach_id + '" /></li>';
					jQuery('#hanaboard-attachment-upload-filelist ul').append(html_append_filelist);

					HANA_BOARD_Attachment.hideUploadBtn();
				} else {
					alert(resp.errorMsg);
				}
			} catch (e) {
				var resStr = response.response + "";
				var errorType = "";
				if (resStr.indexOf("Unable to open") >= 0)
					errorType = "permission";
				else
					errorType = resStr;
				HANA_BOARD_Attachment.uploadErrorHandler(errorType);
			}

		});
	},
	uploadErrorHandler : function(errorType) {
		var data = {
			'errorType' : errorType,
		};
		jQuery.post(hanaboard_attachment.error_handler.ajaxurl, data, function(res) {
			var resp = jQuery.parseJSON(res);
			HANA_BOARD_Attachment.uploadErrorOutput(resp);
		});

	},
	uploadErrorOutput : function(err) {
		jQuery('#hanaboard-attachment-upload-filelist').append("<li>" + err.code + " Error : " + err.message + (err.file ? ", File: " + err.file.name : "") + "</li>");
	},
	removeAttachment : function(e) {
		e.preventDefault();

		if (confirm(hanaboard_attachment.msg.deleteConfirmMsg)) {
			var el = jQuery(this), data = {
			'attach_id' : el.data('attach_id'),
			'nonce' : hanaboard_attachment.nonce,
			'action' : 'hanaboard_attach_del'
			};

			jQuery.post(hanaboard.ajaxurl, data, function() {
				el.parent().parent().remove();

			});
		}
	}
	};
	HANA_BOARD_Attachment.init();
});