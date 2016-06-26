# Eloquent Shopping Cart
A simple Laravel Based Shopping Cart. Uses persistent Eloquent Databases instead of Sessions.  

### Note
If you have a couple minutes, I would request you to read the [motivation](#motivation) behind why I chose to make this package in the first place, and why this is different from the other Laravel based packages available. But of course, you may choose to dive right in!

Another thing, I know that the test cases are in shambles. I aim to fix that soon.

# Installation
You can install the package through [Composer](https://getcomposer.org).

Run the Composer require command from the Terminal:

    composer require gauravojha/shoppingcart-eloquent
	
Next up, add the service provider of the package and alias the package. Open the `config/app.php` file and:
* add the following line to the `providers` array

	> Gojha\Shoppingcart\ShoppingCartProvider::class,

* add the following line to the `aliases` array

	>  'Cart' => Gojha\Shoppingcart\CartFacade::class,
	
Since this package uses the Eloquent databases and an associated Model, you also need to run the following command from the root of your application:

	> php artisan vendor:publish --provider="Gojha\Shoppingcart\ShoppingCartProvider"

And of course, it goes without saying, run the migrations after this to create the database using `php artisan migrate`

With the above steps done and dusted, you are ready to start using this shopping cart.

# Usage

All the usage scenarios below are explained with a single Controller at the end in the [Examples](#examples) section

The methods below use the model `App\ShoppingCartItem`, which is automatically provided for you as part of the `vendor:publish` command.

The shopping cart gives you the following methods to use:

**Cart::addToCart($productid, $price, $quantity)**

Adds the desired number or `$quantity` of the passed product (identified by its unique `$productid`) to the shopping cart. The `$price` in this implementation is the  price *of a single item* and not the total cost of all similar items in the cart.
```php
// get a product to add to the cart
// this will fetch from the database a product, which has 'id' equal to $id
$product = Product::find($id);
// now add 4 such products to the cart
Cart::addToCart($product->id, $product->price, 4);
```
If the same product for the user already exists in the cart, the quantity would be updated, instead of creating a new record.

**Cart::content()**

Will return all the contents of the shopping cart to the user.
Once you have the content, you can retrieve the 'product_id' from it, and further retrieve the products based on the id from the database and perform operations on it.
```php
// obtain all the products in the cart 
$contents = Cart::content();

// obtain the no.of products in the cart
$count = Cart::content()->count();
```

**Cart::removeFromCart($productid)**

Will remove the product from the cart whose `id` is equal to the passed `$productid`
```php
// if a product exists in the cart which has a $product->id = 4, then it will be removed from the cart in its entirety
Cart::removeFromCart(4);
```

**Cart::emptyCart()**

Destroys all the contents of the shopping cart.
```php
// remove all the products in the shopping cart
Cart::emptyCart();
```

**Cart::updateItemInCart($productid, $quantity)**

Updates the `$quantity` of the product present in the shopping cart whose product->id equals `$productid`.
```php
// change the quantity of products. 
// Assuming the cart has a product which has a product with id '4', and you want to change its quantity to 1.
Cart::updateItemInCart(4, 1);
```

**Cart::totalPrice()**

Every application may have a different process to calculate the total cost of the items in the cart. In this implementation, inside `ShoppingCartImpl` file, I have declared two private variables `$taxRate` and `$deliveryCharges` set to some defaults. The method `totalPrice` calculates the total price the customer has to pay during checkout, by first adding the total cost of all items in the cart, then adding the tax and delivery charges over it. The code is pretty straightforward and simple, so it shouldnt prove hard to modify it and use it as per your requirement.
```php
$totalPrice = Cart::totalPrice();
```

That is about it. Since the records are inserted using the authenticated user id, there is no chance of a conflict or other users able to view your records. Moreover, you can add products, go for that week long vacation, and since the data is persisted, find the products right where you left them.


# Examples
As already mentioned, this package (if you use as it is), imposes some basic constraints/assumptions over you.

For this section:
* assume a simple table 'products' with the following structure
	```php
	<?php

	use Illuminate\Database\Schema\Blueprint;
	use Illuminate\Database\Migrations\Migration;

	class CreateProductsTable extends Migration
	{
    		/**
     		* Run the migrations.
     		*
     		* @return void
     		*/
    		public function up()
    		{
				Schema::create('products', function (Blueprint $table) {
            		$table->increments('id');
            		$table->string('product_name')->unique();
            		$table->decimal('price_per_item')->default(0.00);
            		$table->timestamps();
        		});
    		}

    		/**
     		* Reverse the migrations.
     		*
     		* @return void
     		*/
    		public function down()
    		{
        		Schema::drop('products');
    		}
	}
	```
* assume a controller with the following skeleton
	```php
	<?php
	namespace App\Http\Controllers;

	use Illuminate\Http\Request;

	use App\Http\Requests;
	use App\Product;
	use Cart;
	use Auth;

	class CartController extends Controller
	{
		/**
     		 * Create a new controller instance.
     		 *
     		 * @return void
     		 */
    		public function __construct(Request $request)
    		{
        		$this->middleware('auth');      
    		}
		...
		// Remaining controller code here
		...
	}
	```

Now if you have a `route` which is used to add a product to the shopping cart then, an example would be
```php
	public function addToCart(Request $request, Product $product)
	{
		// the request must supply the product to be added and other relevant data
		Cart::addToCart(Auth::user()->id, $product->id, $product->price_per_item, $request->input('quantity')); 
	}
```
I hope this one example should be enough to get you started and exploring

# Upcoming features 
I understand that this package is very basic, and may just fulfill a user's requirement. Moreover, it is not very robust or dynamic (for example there will surely be a scenario that a user has added some product to the cart, and the application owner in the meantime increases or decreases the product's price). 
Therefore, I aim to take up the following tasks ahead:
* update the prices of the products **still in the cart** if they change in the product's databases.
* bring in Redis support
* fix test cases

# Motivation
Before you proceed any further, please note that this is unlike the other shopping carts available. Having said that, I do not mean to take anything away from those implementations. The others are very robust, with a host of options. 

However, having tried most of these, personally, I have never been completely satisfied. In my own opinion, some drawbacks were:
* Usage of laravel sessions. Though very powerful, for me it meant: 
  * when the session ends, the items in the cart were gone. Why would I as a customer, add some products in the cart, decide to pay later, and come back after a few hours, only to find my shopping cart empty!!!
  * some shopping carts had a defect, whereby the cart wasnt user-safe. If I logged in with user A, added some items and then logged out. Then using the same browser, I logged in as user B. I could still see the items present in the cart which A added. 
* Most of the shopping carts required the developer to pass in a unique 'row identifier', or use instances of the cart. 
And some more which I don't remember for the time being ...

Hence I decided to design my own cart, which doesnt use Sessions. Rather uses a persistent database to handle the cart. The rows are identified using the 'identifier' of the logged in user and the 'identifier' of the product being added. Goodbye sessions!

There are a few assumptions this cart makes. So give it a shot only if you understand clearly the limitations this imposes on you as a developer. You can use this package if:
  - [x] you will *only* be allowing registered and **logged in** users to buy products, since this package uses the ```Auth``` Service Provider to insert the user identifier. 
  - [x] you have a table for products, where each product has a unique **id** and can be identified by it.
In the code, (see the migration provided), I have used the **users** table and my own **products** table. Please feel free to use your tables as you seem fit. Also, this is my own usage scenario. Feel free to change the code as per your preference and use.

I hope you will find this package useful and easy to use. 
