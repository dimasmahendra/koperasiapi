<?php

return [

     'driver' => 'smtp',
    'host' => 'smtp.gmail.com',
    'port' => 587,
    'from' => array('address' => 'koperasisender@gmail.com', 'name' => 'Koperasi Modern'),
    'encryption' => 'tls',
    'username' => 'koperasisender@gmail.com',
    'password' => 'Koperasi123',
    'sendmail' => '/usr/sbin/sendmail -bs',
    'pretend' => false,

];
