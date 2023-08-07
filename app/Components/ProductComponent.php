<?php

namespace App\Components;

use App\Models\ProductDescription;
use Cviebrock\EloquentSluggable\Services\SlugService;
use Illuminate\Support\Facades\DB;

class ProductComponent
{
    public function index(){
        return 'тест';
    }

    public function NewGoodsSlaider(){//получение последних 20 товаров

        $mass_prod_id=[];
        $products_id=DB::connection('mysql2')->table('sd_product')->select('product_id')->where('quantity','>',0)->where('price','>',0)->latest('product_id')->limit(20)->get();
        $products_id->each(function ($item) use(&$mass_prod_id){
            $mass_prod_id[]=$item->product_id;
        });

       $products= $this->ProductInit($mass_prod_id);

        return $products;

    }
    public function ExclusiveSlaider($value_search){//получение последних 20 товаров

        $mass_prod_id=[];
        $query=DB::connection('mysql2')->table('sd_product')->select('sd_product.product_id')
            ->where('sd_product.quantity','>',0)
            ->where('sd_product.price','>',0);

        if (is_array($value_search)){
            foreach ($value_search as  $key=> $val){
                if($key == 0){
                    $query->Where('sd_product_description.tag', 'LIKE', '%'.$val.'%');
                }else{
                    $query->orWhere('sd_product_description.tag', 'LIKE', '%'.$val.'%');
                }
            }
        }else{
            $query->where('sd_product_description.tag','LIKE','%'.$value_search.'%');
        }

        $query->join('sd_product_description','sd_product.product_id','=','sd_product_description.product_id')
            ->latest('sd_product.product_id')
            ->limit(20);

        $products_id=$query->get();

        $products_id->each(function ($item) use(&$mass_prod_id){
            $mass_prod_id[]=$item->product_id;
        });

        $products= $this->ProductInit($mass_prod_id);

        return $products;

    }


    public function ProductInit($mass_prod_id){//получение информации по товару

        $imageComponent= new ImageComponent();
        $products_mass=[];

        $query=DB::connection('mysql2')->table('sd_product')->whereIn('sd_product.product_id',$mass_prod_id)
            ->select('sd_product.product_id', 'sd_product.price', 'sd_product.model', 'sd_product.mpn', 'sd_product.quantity', 'sd_product.manufacturer_id', 'sd_manufacturer.image AS manufacturer_image', 'sd_manufacturer.name AS manufacturer_name' , 'sd_manufacturer.strana AS manufacturer_region', 'sd_product.image', 'sd_product_description.name', 'sd_product_description.description', 'sd_product_description.seo_title', 'sd_product_description.seo_h1', 'sd_product_description.tag', 'sd_product_description.slug','sd_product_to_category.category_id' )
            ->where('sd_product.status','=',1)
            ->where('sd_product_to_category.main_category','=',1)
            ->where('sd_product_to_category.category_id','!=',568)//убрал подарки
            ->join('sd_product_description','sd_product.product_id','=','sd_product_description.product_id')
            ->join('sd_product_to_category','sd_product.product_id','=','sd_product_to_category.product_id')
            ->leftJoin('sd_manufacturer','sd_product.manufacturer_id','=','sd_manufacturer.manufacturer_id');
        $products= $query->get();

        $products_discount=DB::connection('mysql2')->table('sd_product_discount')->whereIn('sd_product_discount.product_id',$mass_prod_id)//получил цены в зависимости от группы пользователя
            ->select('sd_product_discount.product_id', 'sd_product_discount.price', 'sd_product_discount.customer_group_id')
            ->get();

        $products_attr=DB::connection('mysql2')->table('sd_product_attribute')->whereIn('sd_product_attribute.product_id',$mass_prod_id)//получил атрибуты товара
        ->select('sd_product_attribute.product_id', 'sd_product_attribute.attribute_id', 'sd_product_attribute.text')
            ->get();

        $products->each(function ($item) use (&$products_mass, &$products_discount, &$products_attr,&$imageComponent){
            $data=(array)$item;
            $customer_group_id=0;
            $products_mass[$data['product_id']]=$data;
            if($products_mass[$data['product_id']]['mpn'] == 1){
                //mpn спец предложение в случае mpn = 1 делаем запрос на получение цены
                $product_special=DB::connection('mysql2')->table('sd_product_special')->where('sd_product_special.product_id',$products_mass[$data['product_id']]['product_id'])//получил спец цену на товар
                    ->select('sd_product_special.price', 'sd_product_special.customer_group_id', 'sd_product_special.priority')
                    ->get();
                $products_mass[$data['product_id']]['special_price']=$product_special->all();
            }
            $filtered_discount = $products_discount->where('product_id', $data['product_id']);
            if(!empty($filtered_discount)){
                $products_mass[$data['product_id']]['product_discount']=$filtered_discount->all();
                //если известен customer_group_id то заменяем price на нужный

                if(isset($customer_group_id)){
                    $price_customer_group=$filtered_discount->where('customer_group_id',$customer_group_id)->first();
                    $price_customer_group=(array)$price_customer_group;
                    $products_mass[$data['product_id']]['price']=$price_customer_group['price'];
                }

            }
            $filtered_attr = $products_attr->where('product_id', $data['product_id']);
            if(!empty($filtered_attr)){
                $products_mass[$data['product_id']]['product_attr']=$filtered_attr->all();
            }

            if(!empty($products_mass[$data['product_id']]['image'])){
                $image_name=substr($products_mass[$data['product_id']]['image'],  strrpos($products_mass[$data['product_id']]['image'], '/' ));
                $imageComponent->checkImg($products_mass[$data['product_id']]['image'],$image_name,'product');//проверяю есть ли на сервере эта картинка, если нет то создаю
                $products_mass[$data['product_id']]['image']='/image/product'.$image_name;
            }
            if(!empty($products_mass[$data['product_id']]['manufacturer_image'])){
                $image_name=substr($products_mass[$data['product_id']]['manufacturer_image'],  strrpos($products_mass[$data['product_id']]['manufacturer_image'], '/' ));
                $imageComponent->checkImg($products_mass[$data['product_id']]['manufacturer_image'],$image_name,'brand');//проверяю есть ли на сервере эта картинка, если нет то создаю
                $products_mass[$data['product_id']]['manufacturer_image']='/image/brand'.$image_name;
            }


            if(empty($products_mass[$data['product_id']]['slug'])){//чпу
                $product_description=ProductDescription::findOrFail($products_mass[$data['product_id']]['product_id']);
                $slug = SlugService::createSlug(ProductDescription::class, 'slug', $product_description->name);//чпу slug
                $product_description->slug=$slug;
                $product_description->save();
                $products_mass[$data['product_id']]['slug']=$slug;
            }

        });
        return($products_mass);
    }


}