"use strict"

const result_messages = {
    "null" : [false, "Invalid Email: You have an extra comma in your input"],
    "email" : [false, "Invalid Email: This domain is not allowed by Rhombus Power"],
    "failedToRegister" : [false, "Failed to register"],
    "failedToUpdate" : [false, "Failed to update"],
    "unauthorizedEmailDomains" : [false, "Unauthorized email domain"],
    "usersAdded" : [true, "User successfully added"],
    "usersAlreadyExist" : ["neither", "User already exists"],
    "usersUpdated" : [true, "User successfully updated"],
    "pre_post_success" : [true, "Valid Email: User was not registered. User will be registered after all emails are valid"]
};
let email_list = [];

$("#send_accounts").on("click", function(){
    if($("#input_accounts").val() == "") return;
    let invalid_email = false;
    const accounts_list = process_duplicates(get_input_emails())

    email_list = accounts_list.slice();
    $("#results_div").addClass("d-none");
    $("#info_tip").addClass("d-none");
    $("#results_table").empty();
    $("#confirm_generate_emails").empty();
    $("#confirm_error_emails").empty();
    $("#confirm_error_emails_header").addClass("d-none");
    $("#confirm_error_emails").addClass("d-none");
    $('#confirm_generate_emails_header').removeClass("d-none")
    $('#confirm_generate_emails').removeClass("d-none")
    if(accounts_list){
        for(let i = 0; i < accounts_list.length; i++){
            let note = "";
            if(!validate_email(accounts_list[i])){
                note = accounts_list[i] == ""?result_messages["null"]:result_messages["email"];
                let td = "<td class='text-center'>"+get_icon(note)+"</td>";
                let row = "<tr>";
                row += "<td>" + accounts_list[i] + "</td>";
                row += td + td + td;
                $("#results_table").append(row);
                $("#confirm_error_emails").append(accounts_list[i] + "<br>")
                email_list.splice(email_list.indexOf(accounts_list[i]), 1)
                invalid_email = true;
            } else {
                $("#confirm_generate_emails").append(accounts_list[i] + "<br>")
            }
        }
        if(invalid_email){
            $("#results_div").removeClass("d-none");
            $("#info_tip").removeClass("d-none");
            enable_tooltip();
            $("#confirm_error_emails_header").removeClass("d-none");
            $("#confirm_error_emails").removeClass("d-none");
        }
        if(email_list.length === 0){
            
            $('#confirm_generate_emails').addClass("d-none")
            $('#confirm_generate_emails_header').addClass("d-none")
        }
        $("#confirm_generate").modal();
    }
})

$("#confirm_generate_confirm").on("click", function(){
    action_button("send_accounts", "add");
    let post_data = {
        "rhombus_token":rhombuscookie(),
        "accounts": email_list
    };
    $.post("/sso_users_registration/registerSSOUsers", post_data, function(data, status){
        if(data["result"] == "fail")
            return;
        $("#info_tip").addClass("d-none")
        let new_data = reformat_data(data);
        Object.entries(new_data).forEach(entry => {
            let [email, results] = entry;
            let row = "<tr>";
            row += "<td>" + email + "</td>";
            row += "<td class='text-center'>" + results["entities"] + "</td>";
            row += "<td class='text-center'>" + results["entitiesSSO"] + "</td>";
            row += "<td class='text-center'>" + results["entitiesKeycloak"] + "</td>";
            $("#results_table").append(row);
        })
        $("#info_tip").removeClass("d-none")
        enable_tooltip();
        $("#results_div").removeClass("d-none");
    },"json").always(function() {
        action_button("send_accounts", "remove");
        $("#input_accounts").val("");
    });
})

function validate_email(email) {
    const email_split = email.split("@");
    return (rhombus_email_domain == "FALSE") || (email_split.length == 2 && valid_domains.includes(email_split[1]))? true:false;
}

function get_input_emails(){
    let emails = $("#input_accounts").val().replace(/[^\S\r\n]+/g, "").split(/(?:[,\n]+|,+|\n+)/);
    let flush_emails = []
    for(let i = 0; i<emails.length; i++){
        if(emails[i] != "") flush_emails.push(emails[i]);
    }
    return flush_emails;
}

function enable_tooltip(){
    $('[data-toggle="tooltip"]').tooltip()
}

function get_icon(note_data){
    let icon;
    if(note_data[0] == true) icon = "fa-check text-success"
    else if(note_data[0] == false) icon = "fa-times text-danger"
    else icon = "fa-minus text-gray"

    return "<i class='fas "+icon+"' data-toggle='tooltip' data-placement='top' title='"+note_data[1]+"'></i>"
}

function reformat_data(data){
    let new_data = {};
    let types = ["entities", "entitiesSSO", "entitiesKeycloak"];
    types.forEach(account_type => {
        Object.entries(data[account_type]).forEach(entry => {
            let [result_type, emails] = entry;
            if(emails && Array.isArray(emails) && emails.length !== 0){
                emails.forEach(email => {
                    if(!(email in new_data))
                        new_data[email] = {};
                    new_data[email][account_type] = get_icon(result_messages[result_type]);
                });
            }
        });
    });
    return new_data;
}

function check_account_input(){
    let btn = $("#send_accounts");
    if($("#input_accounts").val()=="") {
        btn.prop("disabled", true)
        btn.css("cursor", "default")
    } else {
        btn.prop("disabled", false)
        btn.css("cursor", "pointer")
    }
}

function process_duplicates(emails){
    const unique_emails = [];
    const duplicate_emails = [];
    $("#confirm_duplicate_emails_header").addClass("d-none");
    $("#confirm_duplicate_emails").addClass("d-none");
    $("#confirm_duplicate_emails").empty();
    $.each(emails, function(i, email){
        if ($.inArray(email, unique_emails) === -1){
            unique_emails.push(email);
        } else {
            $("#confirm_duplicate_emails_header").removeClass("d-none");
            $("#confirm_duplicate_emails").removeClass("d-none");
            if($.inArray(email, duplicate_emails) === -1){
                $("#confirm_duplicate_emails").append(email + "<br>");
                duplicate_emails.push(email);
            }
        }
    });
    return unique_emails;
}

if (!window._rb) window._rb = {}
window._rb.check_account_input = check_account_input;
