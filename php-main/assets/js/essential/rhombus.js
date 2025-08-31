"use strict";

/*!
 * Rhombus JavaScript Library
 *
 * Copyright 2019, Sumit Kalia
 * Date: Mon Nov 21 21:11:03 2011 -0500
 */

function initTheme() {
  let darkThemeSelected =
    localStorage.getItem("darkSwitch") !== null &&
    rb_en_de_fixed("de",localStorage.getItem("darkSwitch")) === "dark";
  // darkSwitch.checked = darkThemeSelected;
  darkThemeSelected
    ? document.body.setAttribute("data-theme", "dark")
    : document.body.removeAttribute("data-theme");
    darkThemeSelected ? $('html').addClass('dark') : $('html').removeClass('dark')
}

//
// Sumit Created 21 September 2019
//
let darkSwitch = document.getElementById("darkSwitch");
initTheme();
if (darkSwitch) {
  darkSwitch.addEventListener("change", function (event) {
    resetTheme();
  });
}


function resetTheme() {
  if (darkSwitch.checked) {
    document.body.setAttribute("data-theme", "dark");
    localStorage.setItem("darkSwitch", rb_en_de_fixed("en","dark"));
  } else {
    document.body.removeAttribute("data-theme");
    localStorage.removeItem("darkSwitch");
  }
}
//
// Sumit Created 17 October 2019
//
function rhombus_dark_mode(mode, switch_visibility) {
  let temp_elm = document.getElementsByClassName("custom-control custom-switch nav-link");
  let temp_id = document.getElementById("darkSwitch");

  if (switch_visibility == "switch_true") {
    if (temp_elm.length > 0) {
      temp_elm[0].classList.remove("d-none");
    }
  }
  if (switch_visibility == "switch_false") {
    if (temp_elm.length > 0) {
      temp_elm[0].classList.add("d-none");
    }
  }
  if (mode == "dark") {
    if (temp_id) {
      temp_id.checked = true;
    }
    document.body.setAttribute("data-theme", "dark");
  }
  if (mode == "light") {
    resetTheme();
  }
}


//
// Sumit Created 16 September 2019
//
function rhombuscookie() {
  let name = 'rhombus_token_cookie';
  let value = "; " + document.cookie;
  let parts = value.split("; " + name + "=");
  if (parts.length == 2) return parts.pop().split(";").shift();
}

//
// Sumit Created 23 October 2019
//
let xhr_gb_var = new XMLHttpRequest();
xhr_gb_var.open("POST", "/js/vars", true);
xhr_gb_var.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
xhr_gb_var.onreadystatechange = function () {
  if (xhr_gb_var.readyState == 4 && xhr_gb_var.status == 200) {
    //
    // Empty local storage
    //
    let dsdv = localStorage.getItem('darkSwitch'), dsd = "light";
    console.log(dsdv);
    if (dsdv !== null) {
      dsd = rb_en_de_fixed("de", dsdv);
    }
    // localStorage.clear();
    let data = JSON.parse(xhr_gb_var.responseText);
    // Note: Order is important, any environmental variable such as rhombus_console should be after the local storage.
    //
    // Sumit Created 12 September 2019
    //
    for (let key in data) {
      if (key === 'darkSwitch') {
        window[key] = dsd;
        localStorage.setItem(key, rb_en_de_fixed("en", dsd));
        if (typeof homeDs === 'function') {
          homeDs();
        }
        continue;
      }
      window[key] = data[key];
      localStorage.setItem(key, rb_en_de_fixed("en", data[key]));
    }
  }
}
xhr_gb_var.send("info=get&rhombus_token=" + rhombuscookie());
//
// Fetch previous storage if exits
//
let constantsFromPhp = ['RHOMBUS_MAPBOX_LIGHT','RHOMBUS_DEBUG','RHOMBUS_CONSOLE'];
for (let key in localStorage) {
  if(constantsFromPhp.includes(key)){
    window[key] = rb_en_de_fixed("de", localStorage[key]);
  }
}

//
// Sumit Created 17 September 2019
//
function action_button(id, type) {
  let temp_spinner = '<i class="fas fa-spinner fa-pulse mr-3"></i>';
  if (type == "add") {
    $("#" + id).prepend(temp_spinner);
    $("#" + id).prop('disabled', true);
  }
  if (type == "remove") {
    $("#" + id + " .fa-spinner").remove();
    $("#" + id).prop('disabled', false);
  }
}


//
// Sumit Created 17 September 2019
// Updated 3rd September 2020
//
function clear_form(form_id) {
  $("#" + form_id).find("input").each(function () {
    let temp = $(this).attr("type");
    if ((temp == "email") || (temp == "text") || (temp == "password") || (temp == "number")) {
      $(this).val(null);
    }
    if ((temp == "radio") || (temp == "checkbox")) {
      $(this).prop('checked', false);
    }
    if ((temp == "select")) {
      $(this).val(null).trigger("change");
    }
  });
  $("#" + form_id).find("textarea").each(function () {
    $(this).val(null);
  });
}


$('[data-toggle="popover"]').popover();
$('[data-toggle="tooltip"]').tooltip();


//
//  Sumit Created 4 Oct 2019
//
let rhombus_rb_cp = JSON.stringify(new Date());

//
//  Encrypting all the text which has data-clipboard-text
//
function rb_cp_clipboard() {
  $(".rb_cp").each(function (index) {
    let temp = $(this).attr("data-clipboard-text");
    if (temp) {
      $(this).attr("data-clipboard-text", rb_en_de("en", temp))
    }
  });
}

//
//  Encrypting Decrypting String
//
function rb_en_de(type, string) {
  if (type == "en") {
    return CryptoJS.AES.encrypt(string, rhombus_rb_cp);
  }
  if (type == "de") {
    return CryptoJS.AES.decrypt(string, rhombus_rb_cp).toString(CryptoJS.enc.Utf8);
  }
}
//
// fixed is important for the local storage decryption
//
function rb_en_de_fixed(type, string) {
  if (type == "en") {
    return CryptoJS.AES.encrypt(string, "fixed_key").toString();
  }
  if (type == "de") {
    return CryptoJS.AES.decrypt(string, "fixed_key").toString(CryptoJS.enc.Utf8);
  }
}

rb_cp_clipboard();


//
//  Sumit Created 4 Oct 2019
//
//  Copy to clipboard action
//

let rb_clipboard = new ClipboardJS(".rb_cp", {
  text: function (trigger) {
    let temp = $(trigger).attr("data-clipboard-text");
    if (temp) {
      trigger.setAttribute("data-clipboard-text-en", rb_en_de("de", trigger.getAttribute("data-clipboard-text")))
      return trigger.getAttribute("data-clipboard-text-en");
    }
  }
});

rb_clipboard.on('success', function (e) {
  $(e.trigger).after('<span class="bg-dark text-white small mx-3 px-2 position-absolute rounded rb_cp_hide">Copied!</span>');
  $(".rb_cp_hide").fadeOut(1000, function () { $(".rb_cp_hide").remove(); });
  e.clearSelection();

});


// Sai June 2020
function copyButton(data) {
  if (data == "") {
    return ""
  }
  //style="float: right;"
  return data + '<i class=" btn far fa-copy fa-xs copy" data-clipboard-text="' + data + '" style="background-color: Transparent; opacity: 0" alt="Copy to clipboard" data-toggle="tooltip" title="copied"></i> ';
}

$(".show-password").on('click', function (event) {
  event.preventDefault();
  let field = $(this).prev();
  let eye = $(this).find("i");
  if (field.attr("type") == "text") {
    field.attr('type', 'password');
    eye.addClass("fa-eye-slash");
    eye.removeClass("fa-eye");
  } else if (field.attr("type") == "password") {
    field.attr('type', 'text');
    eye.removeClass("fa-eye-slash");
    eye.addClass("fa-eye");
  }
});

const $$ = (id) => {
  return window.document.getElementById(id);
};

function mode(arr) {
  return arr.sort((a, b) =>
    arr.filter(v => v === a).length
    - arr.filter(v => v === b).length
  ).pop();
}

function reinitialize_dropdown(id, width=null) {
  $('#' + id).select2('destroy');

  let options = {
    placeholder: "Select an option",
    allowClear: true,
  }

  if (width != null) {
    options['width'] = width;
  }

  $('#' + id).val([]).select2(options);
}

const rb_input_submit_form_cookie = () => {
  $('input[name="rhombus_token"]').val(rhombuscookie());
}

const loadPageData = (target_element, path, post = {}, callback = () => null) => {
  post['rhombus_token'] = rhombuscookie();
  $(target_element).load(path, post, (response, status, xhr) => callback(response, status, xhr));
}

function capitalize(str) {
  return str.charAt(0).toUpperCase() + str.slice(1);
}

function ingest_role(app_name,feature_name,roles){
  $.post("/rb_kc/ingest_roles",
  {
      rhombus_token: rhombuscookie(),
      app_name,
      feature_name,
      roles
  }, function(data){
    if(data){
      alert('Entry added successfully')
    }
  })
}

function delete_role(app_name,feature_name){
  $.post("/rb_kc/delete_roles",
  {
      rhombus_token: rhombuscookie(),
      app_name,
      feature_name,
  }, function(data){
    if(data){
      alert('Entry deleted successfully')
    }
  })
}


// Expose functions in window in order to make it reachable in Jest + jsdom
if (!window._rb) window._rb = {}

window._rb.initTheme = initTheme;
window._rb.resetTheme = resetTheme;
window._rb.rhombus_dark_mode = rhombus_dark_mode;
window._rb.rhombuscookie = rhombuscookie;
window._rb.action_button = action_button;
window._rb.clear_form = clear_form;
window._rb.rb_cp_clipboard = rb_cp_clipboard;
window._rb.rb_en_de = rb_en_de;
window._rb.rb_en_de_fixed = rb_en_de_fixed;
window._rb.copyButton = copyButton;
window._rb.delete_role = delete_role;
window._rb.ingest_role = ingest_role;
window._rb.capitalize = capitalize;
window._rb.reinitialize_dropdown = reinitialize_dropdown;
window._rb.loadPageData = loadPageData;
window._rb.rb_input_submit_form_cookie = rb_input_submit_form_cookie;
window._rb.mode = mode;
window._rb.$$ = $$;

