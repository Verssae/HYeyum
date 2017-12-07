jQuery(document).ready(function($) {
	function hanaboard_form_alert_html(type, typemsg, msg) {
		var html = '<div class="alert alert-' + type + '"><strong>' + typemsg + '!</strong> ' + msg + '</div>';
		return html;
	}
	
	$("#hanaboard_list_admin_action_all").change(function () {
	    $(".hanaboard_list_admin_action_checkbox:checkbox").prop('checked', $(this).prop("checked"));
	});
	$("select[name=hanaboard_list_admin_action]").change(function () {
		if( $("select[name=hanaboard_list_admin_action]").val() == 'move') {
		$("#hanaboard_list_admin_action_target_wrapper").show();
		} else {
			$("#hanaboard_list_admin_action_target_wrapper").hide();			
		}
	});

	$("#hanaboard-post-form #cat").change(function () {
		$("#sub_cat_wrap").html('<i class="fa fa-spinner fa-pulse"></i>');
		var param = {
			'nonce' : hanaboard.nonce,
			'action' : 'hanaboard_get_sub_categories',
			'term_id' : $(this).val(),
			'target' : $("#hanaboard_list_admin_action_target").val()
		};
		$.ajax({
			type : "POST",
			url : hanaboard.ajaxurl,
			data : param,
			success : function(data) {
				$("#sub_cat_wrap").html(data);
			},
			dataType : 'html'
		});
	});

	
	$("#hanaboard_list_admin_action_button").click(function() {
		var values = new Array();
		$.each($("input[name='hanaboard_list_admin_action[]']:checked"), function() {
		  values.push($(this).val());
		});		
		var param = {
		    'nonce' : hanaboard.nonce,
		    'action' : 'hanaboard_list_admin_action',
		    'term_id' : $(this).data('termId'),
		    'list_admin_action' : $("select[name=hanaboard_list_admin_action]").val(),
		    'posts_id' : values,
		    'target' : $("#hanaboard_list_admin_action_target").val()
		};
		
		$("#hanaboard_list_admin_action_spinner").show();
		$.ajax({
		    type : "POST",
		    url : hanaboard.ajaxurl,
		    data : param,
		    success : function(data) {
			    if (data.error != 0) {
				    var error_html = hanaboard_form_alert_html('danger', hanaboard.messages.error, hanaboard.messages.list_admin_action_error);
			    } else {
			    	alert(hanaboard.messages.list_admin_action_success);
			    	location.reload();
			    }
		    },
		    dataType : 'json'
		});
	});
	$('#hanaboard-post-form').on('submit', function(e) {
		$('#hanaboardErrors').html('');
		var form = $(this);
		var hasError = false;
		if (typeof tinyMCE != 'undefined')
			tinyMCE.triggerSave();

		form.find('.requiredField').each(function() {
			if ($(this).hasClass('invalid')) {
				if (!$(this).hasClass('captcha') && !$(this).hasClass('guest-password'))
					$(this).removeClass('invalid');
			}
		});
		$("#hanaboard-richtext").removeClass('invalid');
		$(this).find('.requiredField').each(function() {
			var el = $(this), val = el.val();

			if (typeof tinyMCE != 'undefined') {
				val = $.trim(tinymce.activeEditor.getContent());
			} else {
				val = $("#post_content").val();
			}
			if ($.trim(val).length < 3) {
				if (typeof tinyMCE != 'undefined') {
					$("#hanaboard-richtext").addClass('invalid');
					$("#hanaboard-richtext").focus();
				} else {
					$("#post_content").addClass('invalid');
					$("#post_content").focus();
				}
				hasError = true;
				displayErrors({
					empty_content : hanaboard.messages.empty_content
				});
				$(window).scrollTop(0);

			}

			if (el.hasClass('title') && !el.val()) {
				displayErrors({
					empty_title : hanaboard.messages['empty_title']
				});
				$(window).scrollTop(0);
				el.focus();
				el.addClass('invalid');
				hasError = true;
			} else if ($.trim(val) == '') {
				if (typeof hanaboard.messages[el.attr('id')] != "undefined")
					displayErrors({
						empty : hanaboard.messages[el.attr('id')]
					});
				$(window).scrollTop(0);

				el.focus();
				el.addClass('invalid');
				hasError = true;
			} else if (el.hasClass('guest-author')) {
				if (el.val().length < 2) {
					el.focus();
					el.addClass('invalid');
					hasError = true;
				}
			} else if (el.hasClass('guest-password')) {
				if (el.val().length < 4) {
					el.focus();
					el.addClass('invalid');
					hasError = true;
				}
			} else if (el.hasClass('email')) {
				var emailReg = /^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/;
				if (!emailReg.test($.trim(el.val()))) {
					el.focus();
					el.addClass('invalid');
					hasError = true;
				}
			} else if (el.hasClass('cat')) {
				if (el.val() == '-1') {
					el.focus();
					el.addClass('invalid');
					hasError = true;
				}
			} else if (el.hasClass('captcha')) {
				if (el.val() == '') {
					el.focus();
					el.addClass('invalid');
					hasError = true;
				}
			} else if (el.hasClass('guest-password')) {
				if (el.val() == '') {
					el.focus();
					el.addClass('invalid');
					hasError = true;
				}
			}
		});

		if (!hasError) {
			return true;
		}
		e.preventDefault();
	});

	insertVideoDialog = $("#insert-video-dialog-form").dialog({
	    autoOpen : false,
	    width : 300,
	    height : 'auto',
	    resize : 'auto',
	    modal : true,
	    buttons : [ {
	        text : hanaboard.messages.insert_to_content,
	        click : insertVideoToEditor
	    }, {
	        text : hanaboard.messages.cancel,
	        click : function() {
		        insertVideoDialog.dialog("close");
		        $("#insert_video_html").html('');
	        }
	    }
	    ],
	    close : function() {
		    // form[ 0 ].reset();
		    $("#insert_video_html").html('');
		    // allFields.removeClass( "ui-state-error" );
	    }
	});

	function noscript(strHTML) {
		var div = document.createElement("div");
		div.innerHTML = strHTML;
		var scripts = div.getElementsByTagName("script");

		for (var i = scripts.length; i--;) {
			scripts[i].parentNode.removeChild(scripts[i]);
		}

		return div.innerHTML;
	}

	function insertVideoToEditor() {
		var valid = true;
		// allFields.removeClass( "ui-state-error" );

		// var SCRIPT_REGEX =
		// /<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/gi;
		if (valid) {
			window.send_to_editor(noscript($("#insert_video_html").val()));
			insertVideoDialog.dialog("close");
		}
		return valid;
	}

	$("#hanaboard-insert-video-button").on("click", function() {
		insertVideoDialog.dialog("open");
	});


	deletePostDialog = $("#hanaboard-guest-password-dialog").dialog({
	    autoOpen : false,
	    width : 300,
	    height : 'auto',
	    resize : 'auto',
	    modal : true,
	    buttons : [ {
	        text : hanaboard.messages.submit,
	        click : function() {
	    		var el_password = $('#hanaboard-guest-password-input');
	    		var param = {
	    		    'nonce' : hanaboard.nonce,
	    		    'action' : 'hanaboard_check_guest_password',
	    		    'hanaboard_guest_password' : el_password.val(),
	    		    'post_id' : el_password.data('postId')
	    		};
	    		$.ajax({
	    		    type : "POST",
	    		    url : hanaboard.ajaxurl,
	    		    data : param,
	    		    success : function(data) {
	    			    if (data.error != 0) {
	    				    var error_html = hanaboard_form_alert_html('danger', hanaboard.messages.error, hanaboard.messages.wrong_guest_password);
	    				    $('#guest_password_alert_message').html(error_html);
	    				    el_password.addClass('invalid');
	    				    el_password.val('').focus();
	    			    } else {
	    				    el_password.removeClass('invalid');
	    				    $('#guest_password_alert_message').html('');
	    		        	//$(this).dialog("close");
	    				    $('#hanaboard-delete-post-form').submit();
	    			    }
	    		    },
	    		    dataType : 'json'
	    		});
	        }
	    }, {
	        text : hanaboard.messages.cancel,
	        click : function() {
		        $('#hanaboard-guest-password-input').val('');
	        	 $(this).dialog("close");
	        }
	    }
	    ],
	    close : function() {
		    $('#hanaboard-guest-password-input').val('');
		    $(this).dialog("close");
	    }
	});
	if(hanaboard.page_now =='view' || hanaboard.page_now =='edit') {
	$('#hanaboard-guest-password-input').change(function() {
		var el_password = $('#hanaboard-guest-password-input');
		var param = {
		    'nonce' : hanaboard.nonce,
		    'action' : 'hanaboard_check_guest_password',
		    'hanaboard_guest_password' : el_password.val(),
		    'post_id' : el_password.data('postId')
		};
		$.ajax({
		    type : "POST",
		    url : hanaboard.ajaxurl,
		    data : param,
		    success : function(data) {
			    if (data.error != 0) {
				    var error_html = hanaboard_form_alert_html('danger', hanaboard.messages.error, hanaboard.messages.wrong_guest_password);
				    $('#guest_password_alert_message').html(error_html);
				    el_password.addClass('invalid');
				    el_password.val('').focus();

			    } else {
				    el_password.removeClass('invalid');
				    $('#guest_password_alert_message').html('');
			    }
		    },
		    dataType : 'json'
		});
	});
		
	
	}
	$('#delete_post_button').on('click', function(e) {
		if ($(this).data('isAuthorGuest')) {
			deletePostDialog.dialog('open');
		} else {
			if (confirm(hanaboard.messages.confirmDelete)) {
				$('#hanaboard-delete-post-form').submit();
			}
		}
	});
	
	function clearErrors() {
		$('#hanaboardErrors').html('');
	}
	function displayErrors(errors) {
		return;
		$.each(errors, function(key, value) {
			$('#hanaboardErrors').append('<div class="hanaboard-error">' + value + '</div>');
		});
	}
	function reloadCaptcha() {
		$('#siimage').prop('src', hanaboard_secure_image.path + 'securimage_show.php?sid=' + Math.random());
		$('#hanaboard_secure_image_input').val('');
	}

	$.widget("artistan.loading", $.ui.dialog, {
	    options : {
	        // your options
	        spinnerClassSuffix : 'spinner',
	        spinnerHtml : 'Loading',// allow for spans with callback for
	        // timeout...
	        maxHeight : false,
	        maxWidth : false,
	        minHeight : 80,
	        minWidth : 220,
	        height : 80,
	        width : 220,
	        modal : true
	    },

	    _create : function() {
		    $.ui.dialog.prototype._create.apply(this);
		    // constructor
		    $(this.uiDialog).children('*').hide();
		    var self = this, options = self.options;
		    self.uiDialogSpinner = $('.ui-dialog-content', self.uiDialog).html(options.spinnerHtml).addClass('ui-dialog-' + options.spinnerClassSuffix);
	    },
	    _setOption : function(key, value) {
		    var original = value;
		    $.ui.dialog.prototype._setOption.apply(this, arguments);
		    // process the setting of options
		    var self = this;

		    switch (key) {
			    case "innerHeight":
				    // remove old class and add the new one.
				    self.uiDialogSpinner.height(value);
				    break;
			    case "spinnerClassSuffix":
				    // remove old class and add the new one.
				    self.uiDialogSpinner.removeClass('ui-dialog-' + original).addClass('ui-dialog-' + value);
				    break;
			    case "spinnerHtml":
				    // convert whatever was passed in to a string, for html() to
				    // not
				    // throw up
				    self.uiDialogSpinner.html("" + (value || '&#160;'));
				    break;
		    }
	    },
	    _size : function() {
		    $.ui.dialog.prototype._size.apply(this, arguments);
	    },
	    // other methods
	    loadStart : function(newHtml) {
		    if (typeof (newHtml) != 'undefined') {
			    this._setOption('spinnerHtml', newHtml);
		    }
		    this.open();
	    },
	    loadStop : function() {
		    this._setOption('spinnerHtml', this.options.spinnerHtml);
		    this.close();
	    }
	});

	var HanaBoard_Obj = {
	    init : function() {

		    $('.hanaboard-post-form').on('click', 'a.hanaboard-del-ft-image', this.removeFeatImg);

		    // editprofile password strength
		    $('#pass1').val('').keyup(this.passStrength);
		    $('#pass2').val('').keyup(this.passStrength);
		    $('#pass-strength-result').show();

		    // initialize the featured image uploader
		    this.featImgUploader();
		    this.ajaxCategory();
	    },

	    passStrength : function() {
		    var pass1 = $('#pass1').val(), user = $('#user_login1').val(), pass2 = $('#pass2').val(), strength;

		    $('#pass-strength-result').removeClass('short bad good strong');
		    if (!pass1) {
			    $('#pass-strength-result').html(pwsL10n.empty);
			    return;
		    }

		    strength = passwordStrength(pass1, user, pass2);

		    switch (strength) {
			    case 2:
				    $('#pass-strength-result').addClass('bad').html(pwsL10n['bad']);
				    break;
			    case 3:
				    $('#pass-strength-result').addClass('good').html(pwsL10n['good']);
				    break;
			    case 4:
				    $('#pass-strength-result').addClass('strong').html(pwsL10n['strong']);
				    break;
			    case 5:
				    $('#pass-strength-result').addClass('short').html(pwsL10n['mismatch']);
				    break;
			    default:
				    $('#pass-strength-result').addClass('short').html(pwsL10n['short']);
		    }
	    },

	    featImgUploader : function() {
		    if (typeof plupload === 'undefined') {
			    return;
		    }

		    if (hanaboard.featEnabled !== '1') {
			    return;
		    }

		    if ($('#jB-ft-upload-pickfiles').length < 1) {
			    return;
		    }

		    var uploader = new plupload.Uploader(hanaboard.plupload);

		    uploader.bind('Init', function(up, params) {
			    // $('#cpm-upload-filelist').html("<div>Current runtime: "
			    // +
			    // params.runtime + "</div>");
		    });

		    $('#hanaboard-ft-upload-pickfiles').click(function(e) {
			    uploader.start();
			    e.preventDefault();
		    });

		    uploader.init();

		    uploader.bind('FilesAdded', function(up, files) {
			    $.each(files, function(i, file) {
				    $('#jB-ft-upload-filelist').append('<div id="' + file.id + '">' + file.name + ' (' + plupload.formatSize(file.size) + ') <b></b>' + '</div>');
			    });

			    up.refresh(); // Reposition Flash/Silverlight
			    uploader.start();
		    });

		    uploader.bind('UploadProgress', function(up, file) {
			    $('#' + file.id + " b").html(file.percent + "%");
		    });

		    uploader.bind('Error', function(up, err) {
			    $('#jB-ft-upload-filelist').append("<div>Error: " + err.code + ", Message: " + err.message + (err.file ? ", File: " + err.file.name : "") + "</div>");

			    up.refresh(); // Reposition Flash/Silverlight
		    });

		    uploader.bind('FileUploaded', function(up, file, response) {
			    var resp = $.parseJSON(response.response);
			    // $('#' + file.id + " b").html("100%");
			    $('#' + file.id).remove();
			    if (resp.success) {
				    $('#jB-ft-upload-filelist').append(resp.html);
				    $('#jB-ft-upload-pickfiles').hide();
			    }
		    });
	    },

	    removeFeatImg : function(e) {
		    e.preventDefault();

		    if (confirm(hanaboard.messages.confirm)) {
			    var el = $(this), data = {
			        'attach_id' : el.data('id'),
			        'nonce' : hanaboard.nonce,
			        'action' : 'hanaboard_feat_img_del'
			    }

			    $.post(hanaboard.ajaxurl, data, function() {
				    $('#jB-ft-upload-pickfiles').show();
				    el.parent().remove();
			    });
		    }
	    },

	    ajaxCategory : function() {
		    var el = '#cat-ajax', wrap = '.category-wrap';

		    $(el).parent().attr('level', 0);
		    if ($(wrap + ' ' + el).val() > 0) {
			    HanaBoard_Obj.getChildCats($(el), 'lvl', 1, wrap, 'category');
		    }

		    $(wrap).on('change', el, function() {
			    currentLevel = parseInt($(this).parent().attr('level'));
			    HanaBoard_Obj.getChildCats($(this), 'lvl', currentLevel + 1, wrap, 'category');
		    });
	    },

	    getChildCats : function(dropdown, result_div, level, wrap_div, taxonomy) {
		    cat = $(dropdown).val();
		    results_div = result_div + level;
		    taxonomy = typeof taxonomy !== 'undefined' ? taxonomy : 'category';

		    $.ajax({
		        type : 'post',
		        url : hanaboard.ajaxurl,
		        data : {
		            action : 'hanaboard_get_child_cats',
		            catID : cat,
		            nonce : hanaboard.nonce
		        },
		        beforeSend : function() {
			        $(dropdown).parent().parent().next('.loading').addClass('hanaboard-loading');
		        },
		        complete : function() {
			        $(dropdown).parent().parent().next('.loading').removeClass('hanaboard-loading');
		        },
		        success : function(html) {

			        $(dropdown).parent().nextAll().each(function() {
				        $(this).remove();
			        });

			        if (html != "") {
				        $(dropdown).parent().addClass('hasChild').parent().append('<div id="' + result_div + level + '" level="' + level + '"></div>');
				        dropdown.parent().parent().find('#' + results_div).html(html).slideDown('fast');
			        }
		        }
		    });
	    }
	};

	// run the bootstrap
	HanaBoard_Obj.init();

});
