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



