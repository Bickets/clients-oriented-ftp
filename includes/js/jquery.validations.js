var error_count = 0;
var error_count_options = 0;

$(document).ready(function() {
	$('input:first').focus();
});

function clean_form(this_form) {
	$(this_form).find(':input').each(function() {
		if($(this).hasClass('field_error')) {
			$(this).removeClass('field_error');
		}
	});
	$(this_form).find('.field_error_msg').each(function() {
		$(this).remove();
	});
}


function is_complete_all_options(this_form,error) {
	var error_count_options = 0;
	$(this_form).find(':input').each(function() {
		if ($(this).hasClass('textboxlist-bit-editable-input')) {
			// Exclude every Textboxlist generated input
		}
		else {
			if ($(this).val().length == 0) {
				$(this).addClass('field_error');
				error_count_options++;
			}
		}
	});
	if(error_count_options > 0) {
		error_count++;
	}
}

function add_error_to_field(field, error) {
	error_count++;
	$(field).addClass('field_error');
	$(field).after('<div class="field_error_msg"><p>'+error+'</p></div>');
}

function is_complete(field,error) {
	if ($(field).val().length == 0) {
		add_error_to_field(field, error);
	}
}

function is_selected(field,error) {
	if ($(field).val() == 'ps_empty_value') {
		add_error_to_field(field, error);
	}
}

function is_length(field,minsize,maxsize,error) {
	if ($(field).val().length < minsize || $(field).val().length > maxsize) {
		add_error_to_field(field, error);
	}
}

function is_email(field,error) {
	var reg = /^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/;
	var address = field.value;
	if (reg.test(address) == false) {
		add_error_to_field(field, error);
	}
}


function is_alpha(field,error) {
	var checkme = field.value;
	if (!(checkme.match(/^[a-zA-Z0-9]+$/))) {
		add_error_to_field(field, error);
	}
}

function is_password(field,error) {
	var checkme = field.value;
	if (!(checkme.match(/^[0-9a-zA-Z`!"?$%\^&*()_\-+={\[}\]:;@~#|<,>.'\/\\]+$/))) {
		add_error_to_field(field, error);
	}
}

function is_match(field,field2,error) {
	if ($(field).val() != $(field2).val()) {
		add_error_to_field(field, error);
		add_error_to_field(field2, error);
	}
}

function show_form_errors() {
	if (error_count > 0) {
		error_count = 0;
		return false;
	}
}