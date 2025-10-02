<--------------------- Connecting to XAMPP ----------------------->

1. This folder should be in /c/xampp/htdocs/
ex. PS C:\xampp\htdocs\DB2>

2. Open XAMPP Control Panel and start the Apache server.

3. Create a database named 'agrifresh' in xampp or what name u put in the db.php.

4. Cd to database folder and run the seed.php like this:
PS C:\xampp\htdocs\DB2> cd database
PS C:\xampp\htdocs\DB2\database> php seed.php

[NOTE] :
- scripts/data are just for testing the database if its working so if u want to test your website already just remove the references of the scripts/data in the seed.php


------------------------------ SET UP OTP -------------------------------------------

add .env file then put gmail and app password. use this format 

EMAIL_USER="pogi123@gmail.com"
EMAIL_PASS="jasd kyvs hscws jshr" (App password, not email password)   

SMTP_HOST="smtp.gmail.com"
SMTP_PORT="587"
EMAIL_FROM="AgriFresh! <youremail@gmail.com>"

then check also the otp.php inside backend/routes/otp.php
check the path 

$python = 'C:\\Users\\admin\\AppData\\Local\\Programs\\Python\\Python313\\python.exe';

// Path to your Python script
$pythonScript = realpath(__DIR__ . '/../../../scripts/send-otp.py');

if that path is $python = 'C: etc. is not correct, adjust it based on your installed python.
