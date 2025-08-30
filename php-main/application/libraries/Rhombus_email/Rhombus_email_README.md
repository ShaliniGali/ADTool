

### sending an email

Below is a summary of all possible inputs
```
[receiverEmail=>'string',
subject=>'string',
receiverName=>(default)''|'string',
greeting=>(default)Hello|string
title=>(default)''|string
type=>'(default)custom|resetPassword|verifyEmail|welcome
link=>'string' - only needed if template requires a link
content=>(default - not needed if useing a predefined template)FALSE|[array containing sections as follows in any order, any number of times. they will be shown in the order they appear in this array
['type'=>'text','text'=>'string'],
['type'=>'button','link'=>'string']
['type'=>'special','text'=>'string'] //special text shows up in a centered grey box
['type'=>'image','link'=>'string','altText'=>(default)''|'string','width'=>(default)from image|'string']
['type'=>'row',row=>[array of other email sections (text button speical and image) will show up in that order in a row next to eachother, cannot put a row inside a row]]
header:(default)TRUE|FALSE|array=>{logo=>(default)link to rhombus logo|string,logoalt=>(defaut)'rhombus'|string,logoWdith=>(default)70|int,title=>(default)Rhombus Power Inc|string}
footer:(default)TRUE|FALSE|array=>(date=>(default)current date and time|string,OS=>(default)currentOS,browser=>(default)currentBrowser|string,ipAddress=>(default)IP address of sender|string)    ]
```

Examples of usage 
```php

    //
    //  Loading the library
	//
	$this->load->library('rhombus_Email');

	//testing and checking the content
	//setting the status to test before running the rhombus email the email will not be sent
	$this->rb_email->status = "test";
	$this->rb_email->rhombus_email(array(
	'receiverEmail'=>'lea@rhombuspower.com',
	'subject'=>'Rhombus Document Request',
	'receiverName'=>'lea',
	'template'=> 'custom',
	'content' => [
		['type'=>'text','text'=>"test test!."]
		]));
	echo($this->rb_email->content); //content in the email can be accessed
	$this->rb_email->status = "success"; //should be changed back afterwards or it will remain in test mode

	//email with a custom template
	$this->rb_email->rhombus_email(array(
	'receiverEmail'=>'lea@rhombuspower.com',
	'subject'=>'Rhombus Document Request',
	'receiverName'=>'lea',
	'template'=> 'custom',
	'content' => [
		['type'=>'row','row'=>[['type'=>'text','text'=>'hello'],['type'=>'button','link'=>'#','linkText'=>'Upload Documents'],['type'=>'image','width'=>'150','src'=>'https://linkshare2.flippydemos.com/uploaded_images/dogs_497551602.jpg']]],
		['type'=>'text','text'=>"You have been requested from Rhombus Power Inc. to upload documents.<br><br>User Info:<br>Name:  <br> Email:<br><br>Admin Info:<br>Name:  <br> Email:  <br><br>Admin Message:<br>]<br><br>..<br><br> Thanks, <br> IT Team <br><br>Please make sure that this document request email is sent through it@rhombuspower.com and the link should lead to one of the Rhombus UI. If you think that this is an unusual link then please contact it@rhombuspower.com."],
		['type'=>'image','src'=>'https://linkshare2.flippydemos.com/uploaded_images/dogs_497551602.jpg']
		]));
	if($this->rb_email->status != 'success'){
		echo($this->rb_email->message);
		//handel error here
		//if the status is not success the email wont send
    }
    
    //using the welcome template
    //custom header with logo image
	$this->rb_email->rhombus_email(array('receiverEmail'=>'lea@rhombuspower.com',
											'subject'=>'test',
											'template'=>'welcome',
										'header' => array('title'=>':0','logoSrc'=>'https://linkshare2.flippydemos.com/uploaded_images/dogs_497551602.jpg')
									));
	if($this->rb_email->status != 'success'){
		echo($this->rb_email->message);
		//handel error here
		//if the status is not success the email wont send
    }
    
    //using the change passsword template (requires a link)
    //custom footer
	$this->rb_email->rhombus_email(array('receiverEmail'=>'lea@rhombuspower.com',
											'subject'=>'test',
											'template'=>'resetPassword',
											'link'=>'#',
										'footer' => array('os'=>':0',
											'date'=>false,
											'browser'=>false,
											'ipAddress'=>'nearby')
									));

	if($this->rb_email->status != 'success'){
		echo($this->rb_email->message);
		//handel error here
		//if the status is not success the email wont send
	}
```