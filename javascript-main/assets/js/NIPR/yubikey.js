/*
MIT License

Copyright (c) 2018 David Earl
Source: https://github.com/davidearl/webauthn/

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
 */

/* 
This is login part of the client (browser) side of webauthn authentication.
This really does little more than fetch the info from the physical key
or fingerprint reader etc, and repackage it in a palatable form for
sending to the server.
When generating the login page on the server, request a challenge from
webauthn->challenge(), and put the result into a hidden field on the
login form (which will also need your means to identify the user,
e.g. email address), probably as well as alternative means to log in
(such as a password login), or perhaps you're using the key as a
second factor, so this will be the second page or step in the login
sequence.
When they submit the form, call:
  webauthnAuthenticate(key, cb)
where key is the contents of the hidden field (or however else you stored
the challenge string). 
The function will ask the browser to get credentials from the user, prompting 
them to plug in the key, or touch finger or whatever.
On completion it will call the callback function cb:
  function cb(success, info)
success is a boolean, true for successful acquisition of info from the key,
in which case put info in the hidden field and continue with the submit
(or do an Ajax POST with the info, or whatever) and when received on the
server side call webauthn->authenticate.
If success is false, then either info is the string 'abort', meaning the
user failed to complete the process, or an error message of whatever else
went wrong.

Modified: Moheb, September 2nd, 2020 to relay browser response to the server.
*/

function webauthnAuthenticate(key, cb){
	var pk = JSON.parse(key);
	var originalChallenge = pk.challenge;
	pk.challenge = new Uint8Array(pk.challenge);
	pk.allowCredentials.forEach(function(k, idx){
		pk.allowCredentials[idx].id = new Uint8Array(k.id);
    });
    
	navigator.credentials.get({publicKey: pk})
		.then(function(aAssertion) {
			var ida = [];
			(new Uint8Array(aAssertion.rawId)).forEach(function(v){ ida.push(v); });
			var cd = JSON.parse(String.fromCharCode.apply(null,
														  new Uint8Array(aAssertion.response.clientDataJSON)));
			var cda = [];
			(new Uint8Array(aAssertion.response.clientDataJSON)).forEach(function(v){ cda.push(v); });
			var ad = [];
			(new Uint8Array(aAssertion.response.authenticatorData)).forEach(function(v){ ad.push(v); });
			var sig = [];
			(new Uint8Array(aAssertion.response.signature)).forEach(function(v){ sig.push(v); });
			var info = {
				type: aAssertion.type,
				originalChallenge: originalChallenge,
				rawId: ida,
				response: {
					authenticatorData: ad,
					clientData: cd,
					clientDataJSONarray: cda,
					signature: sig
				}
			};
			cb(true, JSON.stringify(info));
		})
		.catch(function (aErr) {
			if (("name" in aErr) && (aErr.name == "AbortError" || aErr.name == "NS_ERROR_ABORT" ||
									 aErr.name == "NotAllowedError")) {
                cb(false, 'abort');
			} else {
				cb(false, aErr.toString());
            }
            $.post("/yubikey/navigatorResponse", {
                rhombus_token:rhombuscookie(),
                dummy: 'dummy'
            }, function(data, status) {
                document.getElementById("login_modal_title").innerHTML = '<span class="text-danger">Failure!</span>';
                document.getElementById("login_modal_body").innerHTML = '<span class="text-muted">' + data.message + '</span>';
                $("#login_modal_button1").addClass("d-none");
                $("#login_modal_button2").addClass("d-none");
                $("#login_modal").modal("show");

                if (data.status == 'max_attempts_reached') {
                    setTimeout(function () {
                        window.location.href = '/';
                    }, 1000);
                }
            }, 'json');
		});
}

/*
This is key registration part of the client (browser) side of webauthn
authentication.
This really does little more than fetch the info from the physical key
or fingerprint reader etc, and repackage it in a palatable form for
sending to the server.
When registering a user account, or allowing them to add a key in their profile,
or whatever, request a challenge from $webauthn->challenge() (e.g. using Ajax)
and pass the resulting key string to
  webauthnRegister(key, callback)
where key is the contents of the hidden field (or however else you stored
the challenge string).
The function will ask the browser to identify their key or touch fingerprint
or whatever.
On completion it will call the callback function callback:
  function callback(success, info)
success is a boolean, true for successful acquisition of info from the key,
in which case pass the info back to the server, call $webauth->register to
validate it, and put the resulting string back in the user record for use
in future logins.
If success is false, then either info is the string 'abort', meaning the
user failed to complete the process, or an error message of whatever else
went wrong.
*/

function webauthnRegister(key, callback){
	key = JSON.parse(key);
	key.publicKey.attestation = undefined;
	key.publicKey.challenge = new Uint8Array(key.publicKey.challenge);
    key.publicKey.user.id = new Uint8Array(key.publicKey.user.id);
    
	navigator.credentials.create({publicKey: key.publicKey})
		.then(function (aNewCredentialInfo) {
			var cd = JSON.parse(String.fromCharCode.apply(null, new Uint8Array(aNewCredentialInfo.response.clientDataJSON)));
            if (key.b64challenge != cd.challenge) {
				callback(false, 'key returned something unexpected (1)');
            }
            let origin = new URL(cd.origin);
            origin.port = '';
            origin = origin.toString();
			if ('https://'+key.publicKey.rp.name+'/' != origin) {
				return callback(false, 'key returned something unexpected (2)');
			}
			if (! ('type' in cd)) {
				return callback(false, 'key returned something unexpected (3)');
			}
			if (cd.type != 'webauthn.create') {
				return callback(false, 'key returned something unexpected (4)');
			}

			var ao = [];
			(new Uint8Array(aNewCredentialInfo.response.attestationObject)).forEach(function(v){
				ao.push(v);
			});
			var rawId = [];
			(new Uint8Array(aNewCredentialInfo.rawId)).forEach(function(v){
				rawId.push(v);
			});
			var info = {
				rawId: rawId,
				id: aNewCredentialInfo.id,
				type: aNewCredentialInfo.type,
				response: {
					attestationObject: ao,
					clientDataJSON:
					  JSON.parse(String.fromCharCode.apply(null, new Uint8Array(aNewCredentialInfo.response.clientDataJSON)))
				}
			};
			callback(true, JSON.stringify(info));
		})
		.catch(function (aErr) {
			if (
				("name" in aErr) && (aErr.name == "AbortError" || aErr.name == "NS_ERROR_ABORT")
				|| aErr.name == 'NotAllowedError'
			) {
				callback(false, 'abort');
			} else {
				callback(false, aErr.toString());
			}
		});
}

/**
 * @author Moheb, September 2nd, 2020
 * 
 * Registers a yubikey by prompting the user to interact with their yubikey for the first time
 * then registers the received key associated with the user.
 * 
 * @param void
 * @return void
 */
function registerYubikey() {
    $.post("/yubikey/getRegistrationChallenge", {
        rhombus_token:rhombuscookie(),
        cp: 'true'
    }, function(data, status) {
        webauthnRegister(data.challenge, function(success, info) {
            if (success) {
                $.post("/yubikey/finishRegistration", {
                    rhombus_token:rhombuscookie(),
                    register: info
                }, function(data, status) {
                    var yubikey_registration_notification = $('#yubikey_notification');
                    var yubikey_register_btn = $('#yubikey_register');

                    if (data.status == 'success') {
                        yubikey_registration_notification.removeClass('text-danger');
                        yubikey_registration_notification.addClass('text-success');
                        yubikey_register_btn.prop('onclick', null);
                        yubikey_register_btn.click(authenticateYubikey);
                    }
                    yubikey_registration_notification.html(sanitizeHtml(data.message, { allowedAttributes:false, allowedTags:false,}));
                }, 'json');
            }
        });
    }, 'json');
}

/**
 * @author Moheb, September 2nd, 2020
 * 
 * Authenticates a yubikey by prompting the user to interact with their yubikey then attempts
 * to match the received key associated with the user against the user's registered key.
 * 
 * @param void
 * @return void
 */
function authenticateYubikey() {
    $.post("/yubikey/getChallenge", {
        rhombus_token:rhombuscookie(),
        dummy: 'dummy'
    }, function(data, status){
        webauthnAuthenticate(data.challenge, function(success, info){
            if (success) {
                $.post("/yubikey/authenticateKey", {
                    rhombus_token:rhombuscookie(),
                    login: info
                }, function(data, status) {
                    if (data.status == 'success') {
                        document.getElementById("login_modal_title").innerHTML = '<span class="text-success"><i class="fa fa-check-circle pr-1" aria-hidden="true"></i>' + data.message + '</span>';
                        document.getElementById("login_modal_body").innerHTML = '<div class="fa-3x pb-4 text-white"><i class="fas fa-cog fa-spin"></i></div>';
                        $("#login_modal_button1").addClass("d-none");
                        $("#login_modal_button2").addClass("d-none");
                        $("#login_modal").modal("show");
                        setTimeout(function () {
                            window.location.href = '/';
                        }, 2000);
                    } else if (data.status == 'max_attempts_reached') {
                        document.getElementById("login_modal_title").innerHTML = '<span class="text-danger">Failure!</span>';
                        document.getElementById("login_modal_body").innerHTML = '<span class="text-muted">' + data.message + '</span>';
                        $("#login_modal_button1").addClass("d-none");
                        $("#login_modal_button2").addClass("d-none");
                        $("#login_modal").modal("show");
                        setTimeout(function () {
                            window.location.href = '/';
                        }, 1000);
                    } else {
                        document.getElementById("login_modal_title").innerHTML = '<span class="text-danger">Failure!</span>';
                        document.getElementById("login_modal_body").innerHTML = '<span class="text-muted">' + data.message + '</span>';
                        $("#login_modal_button1").addClass("d-none");
                        $("#login_modal_button2").addClass("d-none");
                        $("#login_modal").modal("show");
                    }
                }, 'json');
            }
        });
    }, 'json');
}
