<?php

namespace App\Repositories\Cart;

use App\Models\Cart;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DatabaseRepository implements CartRepository{
    protected $name = 'cart';
    protected $items = [];
    public function all()
    {
        if(! $this->items){
            $this->items = Cart::where('cookie_id' , $this->getCookieId())->orWhere('user_id' , Auth::id())->get();
        }
        return $this->items;
        }

    public function add($item , $qty= 1)
    {
        // $cart = Cart::where([
        //     'cookie_id'=>$this->getCookieId(),
        //     'product_id' => ($item instanceof Product)? $item->id : $item,
        // ])->first();

        // if($cart){
        //     $cart->update([
        //     'user_id' => Auth::id(),
        //     'quantity' => DB::raw('quantity +' .$qty) ,
        //     ]);
        // }
        // else {
        //     Cart::create([
        //         'cookie_id'=>$this->getCookieId(),
        //         'product_id' => ($item instanceof Product)? $item->id : $item,
        //         'user_id' => Auth::id(),
        //         'quantity' => DB::raw('quantity +' .$qty) ,
        //     ]);
        // }
        return Cart::updateOrCreate([
            'cookie_id'=>$this->getCookieId(),
            'product_id' => ($item instanceof Product)? $item->id : $item,
        ],[
            'user_id' => Auth::id(),
            'quantity' => DB::raw('quantity +' .$qty) ,
        ]);
    }

    public function clear()
    {
        return Cart::where('cookie_id' , $this->getCookieId())->orWhere('user_id' , Auth::id())->delete();
    }

    protected function getCookieId()
    {
        $id = Cookie::get('cart_cookie_id');
        if(!$id){
            $id = Str::uuid();
            Cookie::queue('cart_cookie_id' , $id , 60*24*30);
        }
        return $id;
    }

    public function total(){
        $items = $this->all();
        return $items->sum(function ($item){
            return $item->quantity * $item->product->price;
        });
    }
}
