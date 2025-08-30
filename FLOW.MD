<----------------------------------------- TABLE RELATIONSHIP AND FLOW ------------------------------------------->


[USER_MANAGEMENT]
ADMIN TABLE
├── admin_id (PK)
├── first_name, last_name, email, password
└── → ADMIN_LOGS (admin_id FK)
    └── → PRODUCT (created_by FK) // when admin created or update that product (created by)

CUSTOMER TABLE  
├── customer_id (PK)
├── first_name, last_name, email, password
├── → CUSTOMER_ADDRESS (customer_id FK)
├── → CART (customer_id FK)
└── → ORDERS (customer_id FK)


[PRODUCT_CATALOG_MANAGEMENT]
CATEGORY TABLE
├── category_id (PK)
├── category_name
└── → PRODUCT (category_id FK)
    ├── → CART_ITEM (product_id FK)
    └── → ORDER_DETAIL (product_id FK)

[SHOPPING_CART_FLOW]
CUSTOMER → CART → CART_ITEM → PRODUCT
   ↓         ↓        ↓         ↓
customer_id  cart_id  product_id  name, price


[ORDER_PROCESSING_FLOW]
CUSTOMER → CUSTOMER_ADDRESS → ORDERS → ORDER_DETAIL → PRODUCT
   ↓            ↓              ↓         ↓            ↓
customer_id   address_id    order_id  product_id   name, price


<----------------------------------------- DATABASE TABLE SUMMARY ------------------------------------------->

[Admin] - admin of the agrifresh
[Admin_logs] - tracks the actions of the admin
[Category] - category of the products
[Product] - base product of agrifresh
[Customer] - buyers
[Order] - acts as the base info of the ordered products and the delivery address
[Order_detail] - contains the products ordered


// Added Tables and reasoning

[Cart] :
--> shopping cart of the customer 
--> since there's some cases that customer might logged out before checking out so we need to have a cart in database
so we can store there orderitems so when the customer logs in again we can retrieve their cart.

[Cart_item] => [OPTIONAL]:
--> items in the cart
--> optional but useful since we are converting the products that are added to cart by the customer into getting the
necessary info from the product table so its much easier and also flexible since if ever you guys decided to add some individual coupons or discount to each product we can just add discount/ coupon column in this cart_item;

[Customer_address] => [OPTIONAL] :
--> addresses of the customer
--> the old shipping address TEXT used in order table is already good and simple but if we want to save the addresses of the customer so we don't ask the customer everytime they are going to order we can save it in this table and just reference it in the order table.


