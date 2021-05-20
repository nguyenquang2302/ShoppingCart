<?php

namespace Ndq\ShoppingCart;

use App\ShoppingCartItem;
use Auth;

class ShoppingCartImpl
{
	/**
	 * The tax rate
	 */
	private $taxRate = 0;

	/**
	 * Delivery charges (if applicable)
	 */
	private $deliveryCharges = 0;

	/**
	 * Test Method
	 */
	public function helloWorld()
	{
		return "YooHoo! Hello World!";
	}

	/**
	 * Function to add items to a cart
	 */
	public function addToCart($productid, $price, $quantity)
	{
		// check if the product already exists in the cart.
		// if it does, add the quantities together
		$productAlreadyExists = 0;
		$olderProduct = ShoppingCartItem::where('product_id', '=', $productid)->where('user_id', '=', Auth::user()->id)->first();
		if($olderProduct != null) {
			$productAlreadyExists = $olderProduct->qty;
			$olderProduct->qty += $quantity;
			$olderProduct->save(); 
		} else {
			$item = new ShoppingCartItem;
			$item->user_id = Auth::user()->id;
			$item->product_id = $productid;
			$item->price = $price;
			$item->qty = $quantity;
			$item->save();
		}
	}

	/**
	 * Function to get all items in the cart
	 */
	public function content()
	{
		return ShoppingCartItem::where('user_id', '=', Auth::user()->id)->get();
	}

	/**
	 * Function to delete an item from a cart
	 */
	public function removeFromCart($productid)
	{
		ShoppingCartItem::where('product_id', '=', $productid)->where('user_id', '=', Auth::user()->id)->delete();
	}

	/**
	 * Function to remove all items from the cart
	 */
	public function emptyCart()
	{
		ShoppingCartItem::where('user_id', '=', Auth::user()->id)->delete();	
	}

	/**
	 * Function to update an item in the cart
	 */
	public function updateItemInCart($productid, $quantity)
	{
		$olderProduct = ShoppingCartItem::where('product_id', '=', $productid)->where('user_id', '=', Auth::user()->id)->first();
		if($olderProduct != null) {
			$olderProduct->qty = $quantity;
			$olderProduct->save(); 
		}
	}

	/**
	 * Function to calculate the total price of products in the cart
	 */
	public function totalPrice()
	{
		$total = 0;
		$products = $this->content();
		foreach ($products as $product) {
			$total += ($product->qty * $product->price);
		}
		$total = ($total + ($total * ($this->taxRate/100)));
		$total += $this->deliveryCharges;
		return $total;
	}
	
	public function getQtyInCartForProduct($productid)
	{
		$product = ShoppingCartItem::where('product_id', '=', $productid)->where('user_id', '=', Auth::user()->id)->first();
		return $product->qty;
	}
	
	public function getPriceOfProductInCart($productid)
	{
		$product = ShoppingCartItem::where('product_id', '=', $productid)->where('user_id', '=', Auth::user()->id)->first();
		return $product->price;
	}
}
