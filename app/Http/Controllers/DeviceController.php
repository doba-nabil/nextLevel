<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\Order;
use \App\Models\Branch;
use App\Models\Section;
use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class DeviceController extends Controller
{

//
    public function __construct(Request $request)
    {
        app()->setLocale($request->header('Lang'));
        $this->lang = $request->header('Lang');
        $this->firebase_token = $request->header('FirebaseToken');
        $this->token = $request->header('Auth');


        $this->branch_id = 0;

        if ($this->token != "") {
            $branch = Branch::where('token', $this->token)->first();
            if ($branch) {
                if ($this->firebase_token != '') {
                    $branch->firebase = $this->firebase_token;
                }
                $branch->lang = $this->lang;
                $branch->save();

                $this->branch_id = $branch['id'];
            }
        }
    }

    public function login()
    {
        $request = request()->all();
        $validator = Validator::make($request, [
            "username" => "required",
            "password" => "required"
        ]);

        if ($validator->fails()) {
            return Response()->json(['status' => 0, 'message' => trans('auth.failed')], 401);
        } else {
            $branch = Branch::where('username', $request['username'])->where('password', $request['password'])->where('active', 1)->first();
            if ($branch) {
                $branch->token = Hash::make($branch['username']);
                $branch->save();

                $info['id'] = $branch['id'];
                $info['token'] = $branch['token'];
            } else {
                return Response()->json(['status' => 0, 'message' => trans('auth.failed')], 401);
            }
        }
        $response['status'] = 1;
        $response['message'] = trans('auth.success');
        $response['data'] = $info;
        return Response()->json($response);
    }

    public function orders()
    {
        if ($this->branch_id == 0) {
            return Response()->json(['status' => 0, 'message' => trans('auth.failed')], 401);
        }

        $branch = Branch::find($this->branch_id);
        if ($branch && $branch['active'] == 1) {

            $orders = [];
            $all_orders = Order::whereIn('status', ['processing'])->where('branch_id',$branch['id'])->orderBy('id', 'desc')->get();
            $currency = \App\Models\Currency::getCurrentCurrencySign() ?? 'SR';

            foreach ($all_orders as $one_order) {
                $order['id'] = $one_order['id'];
                if ($one_order->user_id && $one_order->user) {
                    $order['customer'] = $one_order->user->name;
                    $order['phone'] = $one_order->user->phone;
                }else{
                    $order['customer'] = $one_order->guest_name;
                    $order['phone'] = $one_order->guest_phone;
                }
                $order['type'] = $one_order->order_type === 'delivery' ? __('admin.delivery') : __('admin.pickup');
                $order['date'] = $one_order['scheduled_date'] ?? date('Y-m-d',strtotime($one_order['created_at']));

                $order['total'] = number_format($one_order->total + $one_order->delivery_cost - $one_order->discount_amount, 3) . ' ' . $currency;

                array_push($orders, $order);
            }

            $response['status'] = 1;
            $response['message'] = trans('admin.orders');
            $response['data'] = $orders;
            return Response()->json($response);

        } else {
            return Response()->json(['status' => 0, 'message' => trans('api.wrong_login')], 401);
        }
    }

    public function order($id)
    {
        if ($this->branch_id == 0) {
            return Response()->json(['status' => 0, 'message' => trans('auth.failed')], 401);
        }

        $branch = Branch::find($this->branch_id);
        if ($branch && $branch['active'] == 1) {
            $order['products'] = [];
            $currency = \App\Models\Currency::getCurrentCurrencySign() ?? 'SR';

            $one_order = Order::find($id);
            foreach ($one_order['items'] as $item) {
                $product['name'] = $item->product->name;
                $product['addons'] = [];

                foreach ($item->addons as $addon) {
                    if ($addon->addon) {
                        array_push($product['addons'], $addon->addon->name);
                    }
                }
                foreach ($item->children as $addon) {
                    if ($addon->product) {
                        array_push($product['addons'], $addon->product->name);
                    }
                }

                $product['quantity'] = $item['quantity'];
                $product['price'] = number_format($item->total, 3) .' '. \App\Models\Currency::getCurrentCurrencySign();
                $product['notes'] = $item->notes;

                array_push($order['products'], $product);
            }

            $order['id'] = $one_order['id'];
            $order['type'] = $one_order->order_type === 'delivery' ? __('admin.delivery') : __('admin.pickup');

            if ($one_order->user_id && $one_order->user) {
                $order['customer'] = $one_order->user->name;
                $order['phone'] = $one_order->user->phone;
            }else{
                $order['customer'] = $one_order->guest_name;
                $order['phone'] = $one_order->guest_phone;
            }
            $order['date'] = $one_order['scheduled_date'] ?? date('Y-m-d h:i A',strtotime($one_order['created_at']));
            $order['payment'] = ucfirst($one_order->payment_method ?? 'Cash');
            $order['branch'] = $one_order->branch ? e($one_order->branch->name) : '-';
                
            $order['address'] = '';
            $order['qr_code'] = $one_order->armada_qr;
            
            $order['sub_total'] = number_format($one_order->total - $one_order->delivery_cost, 3) . ' ' . $currency;

            if ($one_order->delivery_cost > 0) {
                $order['delivery'] = number_format($one_order->delivery_cost, 3) . ' ' . $currency;
            }else{
                $order['delivery'] = number_format(0, 3) . ' ' . $currency;
            }

            if ($one_order->discount_amount > 0) {
                $order['discount'] = number_format($one_order->discount_amount, 3) . ' ' . $currency;
            }else{
                $order['discount'] = number_format(0, 3) . ' ' . $currency;
            }
            
            $order['total'] = number_format($one_order->total - $one_order->discount_amount, 3) . ' ' . $currency;
            $order['notes'] = $one_order['order_notes'];

            $response['status'] = 1;
            $response['message'] = '#'.$one_order->order_number;
            $response['data'] = $order;
            return Response()->json($response);

        } else {
            return Response()->json(['status' => 0, 'message' => trans('api.wrong_login')], 401);
        }
    }

    

    public function products()
    {
        if ($this->branch_id == 0) {
            return Response()->json(['status' => 0, 'message' => trans('auth.failed')], 401);
        }

        $branch = Branch::find($this->branch_id);
        if ($branch && $branch['active'] == 1) {

            $categories = [];
            $all_categories = \App\Models\Category::where('active', 1)->orderBy('order', 'asc')->get();
            $currency = \App\Models\Currency::getCurrentCurrencySign() ?? 'SR';

            foreach ($all_categories as $one_category) {
                $category['id'] = $one_category->id;
                $category['name'] = $one_category->name;
                $category['products'] = [];

                $all_products = \App\Models\Product::where('category_id', $category['id'])->whereExists(function ($query) use ($branch) {
                            $query->select(DB::raw(1))->where('product_branches.branch_id', $branch['id'])
                                ->from('product_branches')
                                ->whereColumn('products.id', 'product_branches.product_id');
                        })->get();
                        
                foreach ($all_products as $one_product) {
                    $product_branch = \App\Models\ProductBranch::where('product_id',$one_product->id)->where('branch_id',$branch['id'])->first();

                    $product['id'] = $one_product->id;
                    $product['name'] = $one_product->name;
                    $product['desc'] = $one_product->description;
                    $product['active'] = ($product_branch->status == 'available') ? 'yes' : 'no' ;
                    $product['image'] = $one_product->getFirstMediaUrl('products');
                    $product['offer'] = 'no';
                    $product['price'] = '';
                    $product['offer_price'] = '';
                    
                    if($one_product['prices'][0] && $one_product['prices'][0]['discount_type'] == null){
                        $product['price'] = $one_product['prices'][0]['price'] . ' ' . $currency;
                    }elseif($one_product['prices'][0] && $one_product['prices'][0]['discount_type'] == 'fixed'){
                        $product['offer'] = 'yes';
                        $product['price'] = $one_product['prices'][0]['price'] . ' ' . $currency;
                        $product['offer_price'] = $one_product['prices'][0]['discount_price'] . ' ' . $currency;
                    }elseif($one_product['prices'][0] && $one_product['prices'][0]['discount_type'] == null){
                        $product['offer'] = 'yes';
                        $product['price'] = $one_product['prices'][0]['price'] . ' ' . $currency;
                        $product['offer_price'] = $one_product['prices'][0]['price'] - ($one_product['prices'][0]['price'] * $one_product['prices'][0]['discount_percentage'] / 100) . ' ' . $currency;
                    }
                    
                    array_push($category['products'], $product);
                }
                if (count($category['products']) > 0) {
                    array_push($categories, $category);
                }
            }

            $response['status'] = 1;
            $response['message'] = trans('admin.menu');
            $response['data'] = $categories;
            return Response()->json($response);
        } else {
            return Response()->json(['status' => 0, 'message' => trans('api.wrong_login')], 401);
        }
    }

    public function product($id)
    {
        if ($this->branch_id == 0) {
            return Response()->json(['status' => 0, 'message' => trans('auth.failed')], 401);
        }

        $branch = Branch::find($this->branch_id);
        if ($branch && $branch['active'] == 1) {
            $request = request()->all();
            $validator = Validator::make($request, [
                "active" => "required"
            ]);

            if ($validator->fails()) {
                return Response()->json(['status' => 0, 'message' => trans('validation.required')], 401);
            } else {
                $product = \App\Models\Product::find($id);
                $product_branch = \App\Models\ProductBranch::where('product_id',$id)->where('branch_id',$branch['id'])->first();

                if($product_branch['status'] == 'available'){
                    $product_branch->status = 'unavailable';
                }else{
                    $product_branch->status = 'available';
                }
                $product_branch->save();

                $response['status'] = 1;
                $response['message'] = trans('admin.update_success');
                $response['data'] = (object)[];
                return Response()->json($response);
            }
        } else {
            return Response()->json(['status' => 0, 'message' => trans('admin.not_allow')], 401);
        }
    }

    public function delete_account()
    {
        if ($this->branch_id == 0) {
            return Response()->json(['status' => 0, 'message' => trans('auth.failed')], 401);
        }

        $branch = Branch::find($this->branch_id);
        $branch->active = 0;
        $branch->save();

        $response['status'] = 1;
        $response['message'] = trans('admin.delete_success');
        $response['data'] = (object)[];
        return Response()->json($response);
    }

}
