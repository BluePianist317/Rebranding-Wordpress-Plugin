function confirmDelete(frm) {
	if(confirm("Are you sure?")) {
		frm.del.value=1;
		frm.submit();
	}
}

// thanks to http://stackoverflow.com/questions/210717/using-jquery-to-center-a-div-on-the-screen
(function($){
    $.fn.extend({
        center: function () {
            return this.each(function() {
                var top = ($(window).height() - $(this).outerHeight()) / 2;
                var left = ($(window).width() - $(this).outerWidth()) / 2;
                $(this).css({position:'absolute', margin:0, top: (top > 0 ? top : 0)+'px', left: (left > 0 ? left : 0)+'px'});
            });
        }
    }); 
})(jQuery);

// basic JS validation
function validateBFTProUser(frm, requireName) {
	requireName = requireName || false;
	
	if(requireName && frm.bftpro_name.value=="") {
		alert(bftpro_i18n.name_required);
		frm.bftpro_name.focus();
		return false;
	}
	
	if(frm.email.value=="" || frm.email.value.indexOf("@")<1 || frm.email.value.indexOf(".")<1) {
		alert(bftpro_i18n.email_required);
		frm.email.focus();
		return false;
	}
	
	// check custom fields
	var req_cnt = frm.elements["required_fields[]"].length; // there's always at least 1
	if(req_cnt > 1) {
		for(i = 0; i<req_cnt; i++) {
			var fieldName = frm.elements["required_fields[]"][i].value;
			
			if(fieldName !='') {
				var isFilled = false;
				// ignore radios
				if(frm.elements[fieldName].type == 'radio') continue;
				
				// checkbox
				if(frm.elements[fieldName].type == 'checkbox' && !frm.elements[fieldName].checked) {
					alert(bftpro_i18n.required_field);
					frm.elements[fieldName].focus();
					return false;
				}		
				
				// all other fields
				if(frm.elements[fieldName].value=="") {
					alert(bftpro_i18n.required_field);
					frm.elements[fieldName].focus();
					return false;
				}
			}
		}
	}
	
	// text captcha?
	if(frm.bftpro_text_captcha_answer && frm.bftpro_text_captcha_answer.value == '') {
		alert(bftpro_i18n.missed_text_captcha);
		frm.bftpro_text_captcha_answer.focus();
		return false;
	}
		
	return true;
}

function stripslashes(str) {
	str=str.replace(/\\'/g,'\'');
	str=str.replace(/\\"/g,'"');
	str=str.replace(/\\0/g,'\0');
	str=str.replace(/\\\\/g,'\\');
	return str;
}

// set session var when closing a Gozaimasu form
function gozHideForm(designID) {
	var url = bftpro_i18n.ajax_url;
	var data = {'action' : 'bftpro_ajax', 'type' : 'goz_close', 'design_id' : designID}
	
	jQuery.post(url, data);
}

// create object to hold most functions and global vars
BFTPRO={};