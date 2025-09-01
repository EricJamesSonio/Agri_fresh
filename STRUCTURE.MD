code/ 
---> backend/                   # Backend logics to manipulate and control the data from database to frontend
---> ----> api/                 # Api entry point connection of frontend & Backend
---> ----> ----> index.php      # main api entry point (calls routes)
---> ----> ----> .htaccess      # security for the backend

---> ----> Controller/      # functions / logic
---> ----> Model/           # model of data table
---> ----> Routes/          # entrance

---> database/          # Database creation & seedings
---> ----> Model/
---> ----> Scripts/
---> ----> db.php          # database creation to xampp
---> ----> function.php    # main function used by the models and scripts
---> ----> seed.php        # seeder of data to database

---> frontend/          # designs and structure

---> ----> admin/           # admin panel
---> ----> ----> index.html
---> ----> ----> sections/
---> ----> ----> ----> orders.html
---> ----> ----> ----> products.html
---> ----> ----> ----> sales.html
---> ----> ----> css/
---> ----> ----> ---->  index.css
---> ----> ----> js/
---> ----> ----> ---->  index.js


---> ----> html/
---> ----> ---->  index.php # home pagedefault
---> ----> css/

---> ----> js/
---> ----> --> config.js    # based helper for connecting backend to js

---> ----> images/

