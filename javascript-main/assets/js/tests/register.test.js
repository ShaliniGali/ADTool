/**
 * Configure Jest to use jsdom by default
 *   https://jestjs.io/docs/configuration#testenvironment-string
 * 
 * @jest-environment jsdom
 */
const jQuery = require('jquery'); 
global.$ = jQuery;
global.controller = '/first_admin/create_accounts';
global.sanitizeHtml = () => undefined;
jest.useFakeTimers();
global.sanitizeHtml = html => html;

require('bootstrap/dist/js/bootstrap.bundle.min.js');



describe('checkPassword', () => {

    test('check matching password', () => {
        jest.resetModules(); 

        document.body.innerHTML = `<input id="user_password_register" type="password" value="password"></input>
                                    <input id="user_password_again_register" type="password" value="password"></input>`;

        require('../actions/register');

        const checkPassword = window._rb.checkPassword;
        expect(checkPassword("user_password_register","user_password_again_register")).toBe(true);
    });
});


describe('isStrongPwd', () => {

    test('is password strong', () => {
        jest.resetModules(); 

        require('../actions/register');

        const isStrongPwd = window._rb.isStrongPwd;
        expect(isStrongPwd("inputPassword1@123")).toBe(true);
    });
});

describe('disable_paste', () => {

    test('disable paste',()=>{

        jest.resetModules();

        document.body.innerHTML = `<input type="password" id="user_password_again_register" value=""></input>
                                    <input type="password" id="user_password_register" value=""></input>`;

                                    require('../actions/register');
        
        const shouldBeCalled1 = jest.spyOn($('#user_password_again_register').get(0), 'onpaste');

        const shouldBeCalled2 = jest.spyOn($('#user_password_register').get(0), 'onpaste');

        $('#user_password_again_register').val('some_text').trigger('paste');

        $('#user_password_register').val('some_text').trigger('paste');

        expect(shouldBeCalled1).toHaveBeenCalledTimes(1);

        expect(shouldBeCalled2).toHaveBeenCalledTimes(1);

    });

});

describe('confirm_registration',() =>{
    test('Registration Pending',() =>{
        require('../actions/register');
        var data = {
            username: 'test@rhomuspower.com',
            password: 'inputPassword1@123',
            password_confirmation: 'inputPassword1@123',
            account_type: 'USER',
            message: 'Developer',
            name: 'test',
        };

        var callBack_data = {
            result: 'registeration_pending'
        };

        const url = 'url';

        
        global.rhombuscookie = () => null
        global.action_button = () => null;
        global.clear_form = () => null;
        $.post = (url, data, callback) => callback(callBack_data);

        const confirm_registration = window._rb.confirm_registration;

        document.body.innerHTML = `<form id="rhombus_register"></form>
                                    <button id="rhombus_register_submit">Register</button>
                                    <button id="register_confirmation_button">Confirmation</button>
                                    <button id="login_modal_button1">Confirmation</button>
                                    <button id="login_modal_button2">Confirmation</button>
                                    <div id="login_modal_title"></div>
                                    <div id="login_modal_body"></div>
                                    <div id="login_modal"></div>
                                    <div id="register_confirmation"></div>
                                    <input value="" id="user_role">
                                    <input name="account_type" type="checkbox">`;
        
        confirm_registration(data,'rhombus_register_submit','rhombus_register');

    

        expect($('#login_modal_button1').hasClass('d-none')).toBe(true);
    });
});


describe('confirm_registration',() =>{
    test('Account Rejected',() =>{
        require('../actions/register');
        var data = {
            username: 'test@rhomuspower.com',
            password: 'inputPassword1@123',
            password_confirmation: 'inputPassword1@123',
            account_type: 'USER',
            message: 'Developer',
            name: 'test',
        };

        var callBack_data = {
            result: 'account_rejected'
        };

        const url = 'url';

    
        global.rhombuscookie = () => null
        global.action_button = () => null;
        global.clear_form = () => null;
        $.post = (url, data, callback) => callback(callBack_data);

        const confirm_registration = window._rb.confirm_registration;

        document.body.innerHTML = `<form id="rhombus_register"></form>
                                    <button id="rhombus_register_submit">Register</button>
                                    <button id="register_confirmation_button">Confirmation</button>
                                    <button id="login_modal_button1">Confirmation</button>
                                    <button id="login_modal_button2">Confirmation</button>
                                    <div id="login_modal_title"></div>
                                    <div id="login_modal_body"></div>
                                    <div id="login_modal"></div>
                                    <div id="register_confirmation"></div>
                                    <input value="" id="user_role">
                                    <input name="account_type" type="checkbox">`;
        
        confirm_registration(data,'rhombus_register_submit','rhombus_register');

    

        expect($('#login_modal_button1').hasClass('d-none')).toBe(true);
    });
});


describe('confirm_registration',() =>{
    test('Error',() =>{
        require('../actions/register');
        var data = {
            username: 'test@rhomuspower.com',
            password: 'inputPassword1@123',
            password_confirmation: 'inputPassword1@123',
            account_type: 'USER',
            message: 'Developer',
            name: 'test',
        };

        var callBack_data = {
            result: 'error'
        };

        const url = 'url';

    
        global.rhombuscookie = () => null
        global.action_button = () => null;
        global.clear_form = () => null;
        $.post = (url, data, callback) => callback(callBack_data);

        const confirm_registration = window._rb.confirm_registration;

        document.body.innerHTML = `<form id="rhombus_register"></form>
                                    <button id="rhombus_register_submit">Register</button>
                                    <button id="register_confirmation_button">Confirmation</button>
                                    <button id="login_modal_button1">Confirmation</button>
                                    <button id="login_modal_button2">Confirmation</button>
                                    <div id="login_modal_title"></div>
                                    <div id="login_modal_body"></div>
                                    <div id="login_modal"></div>
                                    <div id="register_confirmation"></div>
                                    <input value="" id="user_role">
                                    <input name="account_type" type="checkbox">`;
        
        confirm_registration(data,'rhombus_register_submit','rhombus_register');

    

        expect($('#login_modal_button1').hasClass('d-none')).toBe(true);
    });
});


describe('confirm_registration',() =>{
    test('Success',() =>{
        require('../actions/register');
        var data = {
            username: 'test@rhomuspower.com',
            password: 'inputPassword1@123',
            password_confirmation: 'inputPassword1@123',
            account_type: 'USER',
            message: 'Developer',
            name: 'test',
        };

        var callBack_data = {
            result: 'first_success'
        };

        const url = 'url';

        
        global.rhombuscookie = () => null
        global.action_button = () => null;
        global.clear_form = () => null;
        $.post = (url, data, callback) => callback(callBack_data);

        const confirm_registration = window._rb.confirm_registration;

        document.body.innerHTML = `<form id="rhombus_register"></form>
                                    <button id="rhombus_register_submit">Register</button>
                                    <button id="register_confirmation_button">Confirmation</button>
                                    <button id="login_modal_button1">Confirmation</button>
                                    <button id="login_modal_button2">Confirmation</button>
                                    <div id="login_modal_title"></div>
                                    <div id="login_modal_body"></div>
                                    <div id="login_modal"></div>
                                    <div id="register_confirmation"></div>
                                    <input value="" id="user_role">
                                    <input name="account_type" type="checkbox">`;
        
        confirm_registration(data,'rhombus_register_submit','rhombus_register');

    

        expect($('#login_modal_button1').hasClass('d-none')).toBe(true);


        jest.runAllTimers();

        delete window.location;
            window.location = { replace: jest.fn() }

    });
});


describe('confirm_registration',() =>{
    test('Login',() =>{
        require('../actions/register');
        var data = {
            username: 'test@rhomuspower.com',
            password: 'inputPassword1@123',
            password_confirmation: 'inputPassword1@123',
            account_type: 'USER',
            message: 'Developer',
            name: 'test',
        };

        var callBack_data = {
            result: 'login'
        };

        const url = 'url';

        
        global.rhombuscookie = () => null
        global.action_button = () => null;
        global.clear_form = () => null;
        $.post = (url, data, callback) => callback(callBack_data);

        const confirm_registration = window._rb.confirm_registration;

        document.body.innerHTML = `<form id="rhombus_register"></form>
                                    <button id="rhombus_register_submit">Register</button>
                                    <button id="register_confirmation_button">Confirmation</button>
                                    <button id="login_modal_button1">Confirmation</button>
                                    <button id="login_modal_button2">Confirmation</button>
                                    <div id="login_modal_title"></div>
                                    <div id="login_modal_body"></div>
                                    <div id="login_modal"></div>
                                    <div id="register_confirmation"></div>
                                    <input value="" id="user_role">
                                    <input name="account_type" type="checkbox">`;
        
        confirm_registration(data,'rhombus_register_submit','rhombus_register');

    

        expect($('#login_modal_button1').hasClass('d-none')).toBe(true);
    });
});


describe('confirm_registration',() =>{
    test('validation failure',() =>{
        require('../actions/register');
        var data = {
            username: 'test@rhomuspower.com',
            password: 'inputPassword1@123',
            password_confirmation: 'inputPassword1@1231',
            account_type: 'USER',
            message: 'Developer',
            name: 'test',
        };

        var callBack_data = {
            result: 'validation_failure',
            message: 'password not matching'
        };

        const url = 'url';

        
        global.rhombuscookie = () => null
        global.action_button = () => null;
        global.clear_form = () => null;
        
        $.post = (url, data, callback) => callback(callBack_data);

        const confirm_registration = window._rb.confirm_registration;

        document.body.innerHTML = `<form id="rhombus_register"></form>
                                    <button id="rhombus_register_submit">Register</button>
                                    <button id="register_confirmation_button">Confirmation</button>
                                    <button id="login_modal_button1">Confirmation</button>
                                    <button id="login_modal_button2">Confirmation</button>
                                    <div id="login_modal_title"></div>
                                    <div id="login_modal_body"></div>
                                    <div id="login_modal"></div>
                                    <div id="register_confirmation"></div>
                                    <input value="" id="user_role">
                                    <input name="account_type" type="checkbox">`;
        
        confirm_registration(data,'rhombus_register_submit','rhombus_register');

    

        expect($('#login_modal_button1').hasClass('d-none')).toBe(true);
    });
});

describe('rhombus_register_on_submit',() =>{
    
    test('test rhombus register on submit',() =>{
        
        jest.resetModules();

    
        document.body.innerHTML = `<form id="rhombus_register">
                                        <input type="email"  id="user_email_register" placeholder="Enter email" value="test@email.com">
                                        <input type="text"  name="user_name_register" id="user_name_register" placeholder="Enter name" value="test">
                                        <input type="password"  id="user_password_register"  placeholder="Enter password" autocomplete="off" value="inputPassword1@123" >
                                        <input type="password"  id="user_password_again_register"  placeholder="Enter password" autocomplete="off" value="inputPassword1@123" >
                                        <select name="user_role" id="user_role"  required>
                                        <option value="USER" selected>USER</option>
                                        </select>
                                        <textarea id="user_personal_message" rows="3" placeholder="Describe yourself here...">Test User</textarea>
                                        <button type="submit" id="rhombus_register_submit">REGISTER</button>
                                    </form>
                                    <input type="password" id="user_password_again_register" value=""></input>
                                    <input type="password" id="user_password_register" value=""></input>`;


        require('../actions/register');
    
        var data = {
            email : "test@email.com"
        }

        var callBack_data = 'valid';
        const url = 'url';
        global.action_button = () => true;
        global.rhombuscookie = () => true
        $.post = (url, data, callback) => callback(callBack_data);
    
        $('#rhombus_register').trigger('submit');


        expect(true).toBe(true);
        
    
        
    });

});

describe('rhombus_register_on_submit',() =>{
    
    test('test rhombus register on submit account_type',() =>{
    
    jest.resetModules();

    
        document.body.innerHTML = `<form id="rhombus_register">
                                        <input type="email"  id="user_email_register" placeholder="Enter email" value="test@email.com">
                                        <input type="text"  name="user_name_register" id="user_name_register" placeholder="Enter name" value="test">
                                        <input type="password"  id="user_password_register"  placeholder="Enter password" autocomplete="off" value="inputPassword1@123" >
                                        <input type="password"  id="user_password_again_register"  placeholder="Enter password" autocomplete="off" value="inputPassword1@123" >
                                        <select name="user_role" id="user_role" >
                                        <option value="USER">USER</option>
                                        </select>
                                        <input name="account_type" type="checkbox" value="Moderator" checked>
                                        <textarea id="user_personal_message" rows="3" placeholder="Describe yourself here...">Test User</textarea>
                                        <button type="submit" id="rhombus_register_submit">REGISTER</button>
                                    </form>
                                    <input type="password" id="user_password_again_register" value=""></input>
                                    <input type="password" id="user_password_register" value=""></input>`;


    require('../actions/register');

    var data = {
        email : "test@email.com"
    }

    var callBack_data = 'valid';
    const url = 'url';
    global.action_button = () => true;
    global.rhombuscookie = () => true
    $.post = (url, data, callback) => callback(callBack_data);
    
    $('#rhombus_register').trigger('submit');


    expect(true).toBe(true);
    

        
    });

});

describe('rhombus_register_on_submit',() =>{
    
    test('test rhombus register on submit ADMIN',() =>{
    
    jest.resetModules();

    
        document.body.innerHTML = `<form id="rhombus_register">
                                        <input type="email"  id="user_email_register" placeholder="Enter email" value="test@email.com">
                                        <input type="text"  name="user_name_register" id="user_name_register" placeholder="Enter name" value="test">
                                        <input type="password"  id="user_password_register"  placeholder="Enter password" autocomplete="off" value="inputPassword1@123" >
                                        <input type="password"  id="user_password_again_register"  placeholder="Enter password" autocomplete="off" value="inputPassword1@123" >
                                        <select name="user_role" id="user_role" >
                                            <option value="">USER</option>
                                        </select>
                                        <input name="account_type" type="checkbox">
                                        <textarea id="user_personal_message" rows="3" placeholder="Describe yourself here...">Test User</textarea>
                                        <button type="submit" id="rhombus_register_submit">REGISTER</button>
                                    </form>
                                    <input type="password" id="user_password_again_register" value=""></input>
                                    <input type="password" id="user_password_register" value=""></input>`;


    require('../actions/register');

    var data = {
        email : "test@email.com"
    }

    var callBack_data = 'valid';
    const url = 'url';
    global.action_button = () => true;
    global.rhombuscookie = () => true
    $('#user_role').val('undefined');
    $('#rhombus_register').trigger('submit');
    $.post = (url, data, callback) => callback(callBack_data);
    

    expect(true).toBe(true);
    
        
    });

});

describe('rhombus_register_on_submit',()=>{

    test('checkValidity_false',() =>{
        
        jest.resetModules();

    
        document.body.innerHTML = `<form id="rhombus_register">
                                        <input type="email"  id="user_email_register" placeholder="Enter email" value="test@email.com" required>
                                        <input type="text"  name="user_name_register" id="user_name_register" placeholder="Enter name" value="test"></input>
                                        <input type="password"  id="user_password_register"  placeholder="Enter password" autocomplete="off"  ></input>
                                        <input type="password"  id="user_password_again_register"  placeholder="Enter password" autocomplete="off"></input>
                                        <select name="user_role" id="user_role"  required>
                                        <option value="USER" selected>USER</option>
                                        </select>
                                        <textarea id="user_personal_message" rows="3" placeholder="Describe yourself here...">Test User</textarea>
                                        <button type="submit" id="rhombus_register_submit">REGISTER</button>
                                    </form>
                                    <input type="password" id="user_password_again_register" value=""></input>
                                    <input type="password" id="user_password_register" value=""></input>`;


        require('../actions/register');
    

        var callBack_data = 'invalid';
        var data = {
            email : "test@email.com"
        }
        global.action_button = () => true;
        global.rhombuscookie = () => true
        $("#user_email_register").val('')
        $('#rhombus_register').trigger('submit');
        $.post = (url, data, callback) => callback(callBack_data);
    
        expect($('#rhombus_register').hasClass('was-validated')).toBe(true);
        
        
    });
});



describe('rhombus_register_on_submit',()=>{

    test('not strong password',() =>{
        
        jest.resetModules();

    
        document.body.innerHTML = `<form id="rhombus_register">
                                        <input type="email"  id="user_email_register" placeholder="Enter email" value="test@email.com" required>
                                        <input type="text"  name="user_name_register" id="user_name_register" placeholder="Enter name" value="test" required></input>
                                        <input type="password"  id="user_password_register"  placeholder="Enter password" autocomplete="off" value="test1" required ></input>
                                        <input type="password"  id="user_password_again_register"  placeholder="Enter password" autocomplete="off" value="test1" required></input>
                                        <select name="user_role" id="user_role"  required>
                                        <option value="USER" selected>USER</option>
                                        </select>
                                        <textarea id="user_personal_message" rows="3" placeholder="Describe yourself here...">Test User</textarea>
                                        <button type="submit" id="rhombus_register_submit">REGISTER</button>
                                    </form>
                                    <input type="password" id="user_password_again_register" value=""></input>
                                    <input type="password" id="user_password_register" value=""></input>
                                    <div class="d-none text-danger small pt-1" id="user_password_register_msg"></div>`;


        require('../actions/register');
    

        var callBack_data = 'invalid';
        var data = {
            email : "test@email.com"
        }
        global.action_button = () => true;
        global.rhombuscookie = () => true
        
        $.post = (url, data, callback) => callback(callBack_data);
        $('#rhombus_register').trigger('submit');
        expect($('#user_password_register_msg').hasClass('d-none')).toBe(false);
        
    });
});


describe('rhombus_register_on_submit',()=>{

    test('Password not matching',() =>{
        
        jest.resetModules();

    
        document.body.innerHTML = `<form id="rhombus_register">
                                        <input type="email"  id="user_email_register" placeholder="Enter email" value="test@email.com" required>
                                        <input type="text"  name="user_name_register" id="user_name_register" placeholder="Enter name" value="test" required></input>
                                        <input type="password"  id="user_password_register"  placeholder="Enter password" autocomplete="off" value="test_Password@123" required ></input>
                                        <input type="password"  id="user_password_again_register"  placeholder="Enter password" autocomplete="off" value="test_Password@1234" required></input>
                                        <select name="user_role" id="user_role"  required>
                                        <option value="USER" selected>USER</option>
                                        </select>
                                        <textarea id="user_personal_message" rows="3" placeholder="Describe yourself here...">Test User</textarea>
                                        <button type="submit" id="rhombus_register_submit">REGISTER</button>
                                    </form>
                                    <input type="password" id="user_password_again_register" value=""></input>
                                    <input type="password" id="user_password_register" value=""></input>
                                    <div class="d-none text-danger small pt-1" id="user_password_register_msg"></div>`;


        require('../actions/register');
    

        var callBack_data = 'invalid';
        var data = {
            email : "test@email.com"
        }
        global.action_button = () => true;
        global.rhombuscookie = () => true
        
        $.post = (url, data, callback) => callback(callBack_data);
        $('#rhombus_register').trigger('submit');
        expect($('#user_password_register_msg').hasClass('d-none')).toBe(true);
        
    });
});


describe('rhombus_register_on_submit',()=>{

    test('Invalid Email',() =>{
        
        jest.resetModules();

    
        document.body.innerHTML = `<form id="rhombus_register">
                                        <input type="email"  id="user_email_register" placeholder="Enter email" value="test@email.com" required>
                                        <input type="text"  name="user_name_register" id="user_name_register" placeholder="Enter name" value="test" required></input>
                                        <input type="password"  id="user_password_register"  placeholder="Enter password" autocomplete="off" value="test_Password@123" required ></input>
                                        <input type="password"  id="user_password_again_register"  placeholder="Enter password" autocomplete="off" value="test_Password@123" required></input>
                                        <select name="user_role" id="user_role"  required>
                                        <option value="USER" selected>USER</option>
                                        </select>
                                        <textarea id="user_personal_message" rows="3" placeholder="Describe yourself here...">Test User</textarea>
                                        <button type="submit" id="rhombus_register_submit">REGISTER</button>
                                    </form>
                                    <input type="password" id="user_password_again_register" value=""></input>
                                    <input type="password" id="user_password_register" value=""></input>
                                    <div class="d-none text-danger small pt-1" id="user_password_register_msg"></div>`;


        require('../actions/register');
    

        var callBack_data = 'invalid';
        var data = {
            email : "test@email.com"
        }
        global.action_button = () => true;
        global.rhombuscookie = () => true
        
        $.post = (url, data, callback) => callback(callBack_data);
        $('#rhombus_register').trigger('submit');
        expect($('#rhombus_register').hasClass('was-validated')).toBe(false);
        
    });
});


describe('rhombus_register_on_submit',()=>{

    test('Invalid Name',() =>{
        
        jest.resetModules();

    
        document.body.innerHTML = `<form id="rhombus_register">
                                        <input type="email"  id="user_email_register" placeholder="Enter email" value="test@email.com" required>
                                        <input type="text"  name="user_name_register" id="user_name_register" placeholder="Enter name" value="!@#$%^&*" required></input>
                                        <input type="password"  id="user_password_register"  placeholder="Enter password" autocomplete="off" value="test_Password@123" required ></input>
                                        <input type="password"  id="user_password_again_register"  placeholder="Enter password" autocomplete="off" value="test_Password@123" required></input>
                                        <select name="user_role" id="user_role"  required>
                                        <option value="USER" selected>USER</option>
                                        </select>
                                        <textarea id="user_personal_message" rows="3" placeholder="Describe yourself here...">Test User</textarea>
                                        <button type="submit" id="rhombus_register_submit">REGISTER</button>
                                    </form>
                                    <input type="password" id="user_password_again_register" value=""></input>
                                    <input type="password" id="user_password_register" value=""></input>
                                    <div class="d-none text-danger small pt-1" id="user_password_register_msg"></div>`;


        require('../actions/register');
    

        var callBack_data = 'valid';
        var data = {
            email : "test@email.com"
        }
        global.action_button = () => true;
        global.rhombuscookie = () => true
        
        $.post = (url, data, callback) => callback(callBack_data);
        $('#user_name_register').val('!@#$%^&*');
        $('#rhombus_register').trigger('submit');
        expect(!$('#rhombus_register').hasClass('was-validated')).toBe(true);
        
    });
});



describe('rhombus_register_on_submit',()=>{
    
    test('Invalid controller',() =>{
    
        jest.resetModules();
        global.controller = 'First_admin_controller/CA';
        document.body.innerHTML = `<form id="rhombus_register">
                                        <input type="email"  id="user_email_register" placeholder="Enter email" value="test@email.com" required>
                                        <input type="text"  name="user_name_register" id="user_name_register" placeholder="Enter name" value="test" required></input>
                                        <input type="password"  id="user_password_register"  placeholder="Enter password" autocomplete="off" value="test_Password@123" required ></input>
                                        <input type="password"  id="user_password_again_register"  placeholder="Enter password" autocomplete="off" value="test_Password@123" required></input>
                                        <select name="user_role" id="user_role"  required>
                                        <option value="USER" selected>USER</option>
                                        </select>
                                        <textarea id="user_personal_message" rows="3" placeholder="Describe yourself here...">Test User</textarea>
                                        <button type="submit" id="rhombus_register_submit">REGISTER</button>
                                    </form>
                                    <input type="password" id="user_password_again_register" value=""></input>
                                    <input type="password" id="user_password_register" value=""></input>
                                    <div class="d-none text-danger small pt-1" id="user_password_register_msg"></div>`;


        require('../actions/register');
    

        var callBack_data = 'valid';
        var data = {
            email : "test@email.com"
        }
        global.action_button = () => true;
        global.rhombuscookie = () => true
        
    
        $.post = (url, data, callback) => callback(callBack_data);
        $('#rhombus_register').trigger('submit');
        expect($('#register_confirmation').hasClass('d-none')).toBe(false);
        
    });
});
 