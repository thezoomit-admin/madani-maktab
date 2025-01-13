<?php  
class MessageService{
public $phone;
public $message;

public function __construct($phone, $message){
    $this->phone = $phone;
    $this->message = $message;
}
}