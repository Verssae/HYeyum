jQuery(document).ready(function($) {
	/*$('div.moxie-shim input[type=file]').click(function(event) {
		event.stopPropagation();
	});*/

	// Register plugin
	tinymce.PluginManager.add('wptuts', function(ed, url) {
		ed.addButton('hanaboard_upload_image', {
		    title : hanaboard.messages.upload_image,
		    cmd : 'hanaboard_upload_image',
		    icon : 'image'
		});

		ed.addButton('hanaboard_upload_video', {
		    title : hanaboard.messages.insert_video,
		    cmd : 'hanaboard_upload_video',
		    icon : 'media'
		});

		ed.addCommand('hanaboard_upload_image', function() {
			var ie = (function() {
				var undef, v = 3, div = document.createElement('div'), all = div.getElementsByTagName('i');
				while (div.innerHTML = '<!--[if gt IE ' + (++v) + ']><i></i><![endif]-->', all[0])
					;
				return v > 4 ? v : undef;
			}());
			var el = $('div.moxie-shim input[type=file]').get(0);

			if (ie < 9) {
				console.log($('#hanaboard-attachment-upload-pickfiles'));
				$('#hanaboard-attachment-upload-pickfiles').click(function(){
					alert('Internet Explorer 8 is not supported.');
				});
				$('#hanaboard-attachment-upload-pickfiles').click();
			} else {
				//var link = $( 'div.moxie-shim input[type=file]' );
			    event = document.createEvent( 'HTMLEvents' );
			    	
			event.initEvent( 'click', true, true );
			//$('div.moxie-shim input[type=file]').trigger('click');
			var node_list = document.getElementsByTagName('input');
			 
			 for (var i = 0; i < node_list.length; i++) {
			     var node = node_list[i];
			 
			     if (node.getAttribute('type') == 'file') {
			         // do something here with a <input type="text" .../>
			         // we alert its value here
			    	 node.dispatchEvent( event );
			     }
			 }
			
//			link.dispatchEvent( event );
			/*
				var evt = document.createEvent("MouseEvents");
				evt.initMouseEvent("click", true, true, window, 0, 0, 0, 0, 0, false, false, false, false, 0, null);
				console.log(evt);
				el.dispatchEvent(evt);
				$('div.moxie-shim input[type=file]').trigger('click');
				*/
			}
			// .live('click').attr('onclick','');
		});

		ed.addCommand('hanaboard_upload_video', function() {
			$('#insert-video-dialog-form').dialog("open");
			// var html = prompt("Paste html source code.", '');
			// if (html !== null) {
			// ed.execCommand('mceInsertContent', 0, html);
			// }

		});
	});
});