/**
 * @author Alan Tygel <alan@eita.org.br>
 *
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

function setLdapusermanagementValue(setting, value) {
	OC.msg.startSaving('#ldapusermanagement_settings_msg');
	$.post(
		OC.generateUrl('/apps/ldapusermanagement/ajax/updateStylesheet'), {'setting' : setting, 'value' : value}
	).done(function(response) {
		OC.msg.finishedSaving('#ldapusermanagement_settings_msg', response);
		hideUndoButton(setting, value);
	}).fail(function(response) {
		OC.msg.finishedSaving('#ldapusermanagement_settings_msg', response);
	});
	preview(setting, value);
}

function hideUndoButton(setting, value) {
	var ldapusermanagementDefaults = {
		host: 'localhost',
		port: '389',
		dn: 'dc=localhost',
		password: '',
		userbase: 'ou=users,dc=localhost',
		groupbase: 'ou=groups,dc=localhost',
	};

	if (value === ldapusermanagementDefaults[setting] || value === '') {
		$('.theme-undo[data-setting=' + setting + ']').hide();
	} else {
		$('.theme-undo[data-setting=' + setting + ']').show();
	}
}

$(document).ready(function () {
	$('#ldapusermanagement [data-toggle="tooltip"]').tooltip();

	$('html > head').append($('<style type="text/css" id="previewStyles"></style>'));

	$('#ldapusermanagement .theme-undo').each(function() {
		var setting = $(this).data('setting');
		var value = $('#ldapusermanagement-'+setting).val();
		if(setting === 'logoMime' || setting === 'backgroundMime') {
			var value = $('#current-'+setting).val();
		}
		hideUndoButton(setting, value);
	});
	var uploadParamsLogo = {
		pasteZone: null,
		dropZone: null,
		done: function (e, response) {
			preview('logoMime', response.result.data.name);
			OC.msg.finishedSaving('#ldapusermanagement_settings_msg', response.result);
			$('label#uploadlogo').addClass('icon-upload').removeClass('icon-loading-small');
			$('.theme-undo[data-setting=logoMime]').show();
		},
		submit: function(e, response) {
			OC.msg.startSaving('#ldapusermanagement_settings_msg');
			$('label#uploadlogo').removeClass('icon-upload').addClass('icon-loading-small');
		},
		fail: function (e, response){
			OC.msg.finishedError('#ldapusermanagement_settings_msg', response._response.jqXHR.responseJSON.data.message);
			$('label#uploadlogo').addClass('icon-upload').removeClass('icon-loading-small');
		}
	};
	var uploadParamsLogin = {
		pasteZone: null,
		dropZone: null,
		done: function (e, response) {
			preview('backgroundMime', response.result.data.name);
			OC.msg.finishedSaving('#ldapusermanagement_settings_msg', response.result);
			$('label#upload-login-background').addClass('icon-upload').removeClass('icon-loading-small');
			$('.theme-undo[data-setting=backgroundMime]').show();
		},
		submit: function(e, response) {
			OC.msg.startSaving('#ldapusermanagement_settings_msg');
			$('label#upload-login-background').removeClass('icon-upload').addClass('icon-loading-small');
		},
		fail: function (e, response){
			$('label#upload-login-background').removeClass('icon-loading-small').addClass('icon-upload');
			OC.msg.finishedError('#ldapusermanagement_settings_msg', response._response.jqXHR.responseJSON.data.message);
		}
	};

	$('#ldapusermanagement-host').change(function(e) {
		var el = $(this);
		$.when(el.focusout()).then(function() {
			setLdapusermanagementValue('host', $(this).val());
		});
		if (e.keyCode == 13) {
			setLdapusermanagementValue('host', $(this).val());
		}
	});

	$('#ldapusermanagement-port').change(function(e) {
		var el = $(this);
		$.when(el.focusout()).then(function() {
			setLdapusermanagementValue('port', $(this).val());
		});
		if (e.keyCode == 13) {
			setLdapusermanagementValue('port', $(this).val());
		}
	});

	$('#ldapusermanagement-dn').change(function(e) {
		var el = $(this);
		$.when(el.focusout()).then(function() {
			setLdapusermanagementValue('dn', $(this).val());
		});
		if (e.keyCode == 13) {
			setLdapusermanagementValue('dn', $(this).val());
		}
	});

	$('#ldapusermanagement-password').change(function(e) {
		var el = $(this);
		$.when(el.focusout()).then(function() {
			setLdapusermanagementValue('password', $(this).val());
		});
		if (e.keyCode == 13) {
			setLdapusermanagementValue('password', $(this).val());
		}
	});

	$('#ldapusermanagement-userbase').change(function(e) {
		var el = $(this);
		$.when(el.focusout()).then(function() {
			setLdapusermanagementValue('userbase', $(this).val());
		});
		if (e.keyCode == 13) {
			setLdapusermanagementValue('userbase', $(this).val());
		}
	});

	$('#ldapusermanagement-groupbase').change(function(e) {
		var el = $(this);
		$.when(el.focusout()).then(function() {
			setLdapusermanagementValue('groupbase', $(this).val());
		});
		if (e.keyCode == 13) {
			setLdapusermanagementValue('groupbase', $(this).val());
		}
	});

	$('.theme-undo').click(function (e) {
		var setting = $(this).data('setting');
		OC.msg.startSaving('#ldapusermanagement_settings_msg');
		$('.theme-undo[data-setting=' + setting + ']').hide();
		$.post(
			OC.generateUrl('/apps/ldapusermanagement/ajax/undoChanges'), {'setting' : setting}
		).done(function(response) {
			var input = document.getElementById('ldapusermanagement-'+setting);
			input.value = response.data.value;

			preview(setting, response.data.value);
			OC.msg.finishedSaving('#ldapusermanagement_settings_msg', response);
		});
	});
});
