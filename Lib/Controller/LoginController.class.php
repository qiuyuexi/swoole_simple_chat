<?php

class LoginController{

    private  $base;

    public function __construct ()
    {
        $this->base = new BaseController();

    }

    public  function  login(){

        //sleep(5);
        writeLog(1);
        echo 'success';
    }



}