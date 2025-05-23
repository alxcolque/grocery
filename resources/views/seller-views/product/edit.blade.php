@extends('layouts.back-end.app-seller')

@push('css_or_js')
    <link href="{{asset('public/assets/back-end/css/tags-input.min.css')}}" rel="stylesheet">
    <link href="{{ asset('public/assets/select2/css/select2.min.css')}}" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content')


    <div class="content container-fluid">
        <!-- Page Title -->
        <div class="d-flex flex-wrap gap-2 align-items-center mb-3">
            <h2 class="h1 mb-0 d-flex align-items-center gap-2">
                <img width="20" src="{{asset('/public/assets/back-end/img/products.png')}}" alt="">
                {{\App\CPU\translate('Product')}} {{\App\CPU\translate('Edit')}}
            </h2>
        </div>
        <!-- End Page Title -->

        <!-- Content Row -->
        <div class="row">
            <div class="col-md-12">
                <form class="product-form" action="{{route('seller.product.update',$product->id)}}" method="post"
                      enctype="multipart/form-data"
                      id="product_form">
                    @csrf
                    <div class="card">
                        <div class="px-4 pt-3">
                            @php($language=\App\Model\BusinessSetting::where('type','pnc_language')->first())
                            @php($language = $language->value ?? null)
                            @php($default_lang = 'en')

                            @php($default_lang = json_decode($language)[0])
                            <ul class="nav nav-tabs w-fit-content mb-4">
                                @foreach(json_decode($language) as $lang)
                                    <li class="nav-item text-capitalize">
                                        <a class="nav-link lang_link {{$lang == $default_lang? 'active':''}}" href="#"
                                           id="{{$lang}}-link">{{\App\CPU\Helpers::get_language_name($lang).'('.strtoupper($lang).')'}}</a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>

                        <div class="card-body">
                            @foreach(json_decode($language) as $lang)
                                <?php
                                if (count($product['translations'])) {
                                    $translate = [];
                                    foreach ($product['translations'] as $t) {
                                        if ($t->locale == $lang && $t->key == "name") {
                                            $translate[$lang]['name'] = $t->value;
                                        }
                                        if ($t->locale == $lang && $t->key == "description") {
                                            $translate[$lang]['description'] = $t->value;
                                        }
                                    }
                                }
                                ?>
                                <div class="{{$lang != 'en'? 'd-none':''}} lang_form" id="{{$lang}}-form">
                                    <div class="form-group">
                                        <label class="title-color" for="{{$lang}}_name">{{ \App\CPU\translate('Name')}}
                                            ({{strtoupper($lang)}})</label>
                                        <input type="text" {{$lang == 'en'? 'required':''}} name="name[]"
                                               id="{{$lang}}_name"
                                               value="{{$translate[$lang]['name']??$product['name']}}"
                                               class="form-control" placeholder="New Product" required>
                                    </div>
                                    <input type="hidden" name="lang[]" value="{{$lang}}">
                                    <div class="form-group pt-4">
                                        <label class="title-color">{{ \App\CPU\translate('description')}}
                                            ({{strtoupper($lang)}})</label>
                                        <textarea name="description[]" class="textarea  editor-textarea"
                                                  required>{!! $translate[$lang]['description']??$product['details'] !!}</textarea>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="card mt-2 rest-part">
                        <div class="card-header">
                            <h4 class="mb-0">{{ \App\CPU\translate('General_Info')}}</h4>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label for="name" class="title-color">{{ \App\CPU\translate('product_type') }}</label>
                                        <select name="product_type" id="product_type" class="form-control" required>
                                            <option value="physical" {{ $product->product_type=='physical' ? 'selected' : ''}}>{{ \App\CPU\translate('physical') }}</option>
                                            @if($digital_product_setting)
                                                <option value="digital" {{ $product->product_type=='digital' ? 'selected' : ''}}>{{ \App\CPU\translate('digital') }}</option>
                                            @endif
                                        </select>
                                    </div>
                                    <div class="col-md-4 mb-3" id="digital_product_type_show">
                                        <label for="digital_product_type" class="title-color">{{ \App\CPU\translate("digital_product_type") }}</label>
                                        <select name="digital_product_type" id="digital_product_type" class="form-control" required>
                                            <option value="{{ old('digital_product_type') }}" {{ !$product->digital_product_type ? 'selected' : ''}} disabled>---Select---</option>
                                            <option value="ready_after_sell" {{ $product->digital_product_type=='ready_after_sell' ? 'selected' : ''}}>{{ \App\CPU\translate("Ready After Sell") }}</option>
                                            <option value="ready_product" {{ $product->digital_product_type=='ready_product' ? 'selected' : ''}}>{{ \App\CPU\translate("Ready Product") }}</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4 mb-3" id="digital_file_ready_show">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <label for="digital_file_ready" class="title-color">{{ \App\CPU\translate("ready_product_upload") }}</label>
                                                <input type="file" name="digital_file_ready" id="digital_file_ready" class="form-control">
                                            </div>
                                            <div class="col-md-6">
                                                <p class="h-100 mt-5">
                                                    <a href="{{Storage::url("/product/digital-product/$product->digital_file_ready")}}" target="_blank">{{ $product->digital_file_ready }}</a>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label for="name" class="title-color">{{ \App\CPU\translate('Category')}}</label>
                                        <select
                                            class="js-example-basic-multiple js-states js-example-responsive form-control"
                                            name="category_id"
                                            id="category_id"
                                            onchange="getRequest('{{url('/')}}/seller/product/get-categories?parent_id='+this.value,'sub-category-select','select')">
                                            <option value="0" selected disabled>---{{ \App\CPU\translate('Select')}}---</option>
                                            @foreach($categories as $category)
                                                <option
                                                    value="{{$category['id']}}" {{ $category->id==$product_category[0]->id ? 'selected' : ''}} >{{$category['name']}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="name" class="title-color">{{ \App\CPU\translate('Sub_category')}}</label>
                                        <select
                                            class="js-example-basic-multiple js-states js-example-responsive form-control"
                                            name="sub_category_id" id="sub-category-select"
                                            data-id="{{count($product_category)>=2?$product_category[1]->id:''}}"
                                            onchange="getRequest('{{url('/')}}/seller/product/get-categories?parent_id='+this.value,'sub-sub-category-select','select')">
                                        </select>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="name" class="title-color">{{ \App\CPU\translate('Sub_sub_category')}}</label>

                                        <select
                                            class="js-example-basic-multiple js-states js-example-responsive form-control"
                                            data-id="{{count($product_category)>=3?$product_category[2]->id:''}}"
                                            name="sub_sub_category_id" id="sub-sub-category-select">

                                        </select>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="code" class="title-color">{{ \App\CPU\translate('product_code_sku') }}
                                            <span class="text-danger">*</span>
                                            <a class="style-one-pro" onclick="document.getElementById('generate_number').value = getRndInteger()">
                                                {{ \App\CPU\translate('generate') }}
                                                {{ \App\CPU\translate('code') }}
                                            </a>
                                        </label>
                                        <input type="number" minlength="5" id="generate_number" name="code"
                                            class="form-control" value="{{ $product->code ? $product->code : '' }}"
                                            placeholder="{{ \App\CPU\translate('code') }}" required>
                                    </div>
                                    @if($brand_setting)
                                    <div class="col-md-4 mb-3">
                                        <label for="name" class="title-color">{{\App\CPU\translate('Brand')}}</label>
                                        <select
                                            class="js-example-basic-multiple js-states js-example-responsive form-control"
                                            name="brand_id">
                                            <option value="{{null}}" selected disabled>---{{ \App\CPU\translate('Select')}}---</option>
                                            @foreach($br as $b)
                                                <option
                                                    value="{{$b['id']}}" {{ $b->id==$product->brand_id ? 'selected' : ''}} >{{$b['name']}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @endif

                                    <div class="col-md-4 mb-3 physical_product_show">
                                        <label for="name" class="title-color">{{\App\CPU\translate('Unit')}}</label>
                                        <select
                                            class="js-example-basic-multiple js-states js-example-responsive form-control"
                                            name="unit">
                                            @foreach(\App\CPU\Helpers::units() as $x)
                                                <option
                                                    value={{$x}} {{ $product->unit==$x ? 'selected' : ''}}>{{$x}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>


                    <div class="card mt-2 rest-part physical_product_show">
                        <div class="card-header">
                            <h4 class="mb-0">{{\App\CPU\translate('Variations')}}</h4>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 form-group">
                                    <div class="d-flex gap-10 mb-2 align-items-center">
                                        <label for="colors" class="mb-0">
                                            {{\App\CPU\translate('Colors')}} :
                                        </label>
                                        <label class="switcher">
                                            <input type="checkbox" class="switcher_input"
                                                    name="colors_active" {{count($product['colors'])>0?'checked':''}}>
                                            <span class="switcher_control"></span>
                                        </label>
                                    </div>

                                    <select class="js-example-basic-multiple js-states js-example-responsive form-control color-var-select"
                                        name="colors[]" multiple="multiple"
                                        id="colors-selector" {{count($product['colors'])>0?'':'disabled'}}>
                                        @foreach (\App\Model\Color::orderBy('name', 'asc')->get() as $key => $color)
                                            <option
                                                value={{ $color->code }} {{in_array($color->code,$product['colors'])?'selected':''}}>
                                                {{$color['name']}}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6 form-group">
                                    <label for="attributes" class="title-color">
                                        {{\App\CPU\translate('Attributes')}} :
                                    </label>
                                    <select class="form-control js-select2-custom"
                                        name="choice_attributes[]" id="choice_attributes" multiple="multiple">
                                        @foreach (\App\Model\Attribute::orderBy('name', 'asc')->get() as $key => $a)
                                            @if($product['attributes']!='null')
                                                <option
                                                    value="{{ $a['id']}}" {{in_array($a->id,json_decode($product['attributes'],true))?'selected':''}}>
                                                    {{$a['name']}}
                                                </option>
                                            @else
                                                <option value="{{ $a['id']}}">{{$a['name']}}</option>
                                            @endif
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-12 mt-2 mb-2">
                                    <div class="customer_choice_options" id="customer_choice_options">
                                        @include('seller-views.product.partials._choices',['choice_no'=>json_decode($product['attributes']),'choice_options'=>json_decode($product['choice_options'],true)])
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card mt-2 rest-part">
                        <div class="card-header">
                            <h4 class="mb-0">{{\App\CPU\translate('Product price & stock')}}</h4>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="title-color">{{\App\CPU\translate('Unit_price')}}</label>
                                        <input type="number" min="0" step="0.01"
                                               placeholder="{{\App\CPU\translate('Unit price') }}"
                                               name="unit_price" class="form-control"
                                               value={{\App\CPU\BackEndHelper::usd_to_currency($product->unit_price)}} required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label
                                            class="title-color">{{\App\CPU\translate('Purchase_price')}}</label>
                                        <input type="number" min="0" step="0.01"
                                               placeholder="{{\App\CPU\translate('Purchase price') }}"
                                               name="purchase_price" class="form-control"
                                               value={{ \App\CPU\BackEndHelper::usd_to_currency($product->purchase_price) }} required>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="title-color">{{\App\CPU\translate('Tax')}}</label>
                                        <label class="badge badge-soft-info">{{\App\CPU\translate('Percent')}} ( % )</label>
                                        <input type="number" min="0" value={{ $product->tax }} step="0.01"
                                               placeholder="{{\App\CPU\translate('Tax') }}" name="tax"
                                               class="form-control" required>
                                        <input name="tax_type" value="percent" class="d--none">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="title-color">{{\App\CPU\translate('Discount')}}</label>
                                        <input type="number" min="0"
                                               value={{ $product->discount_type=='flat'?\App\CPU\BackEndHelper::usd_to_currency($product->discount): $product->discount}} step="0.01"
                                               placeholder="{{\App\CPU\translate('Discount') }}" name="discount"
                                               class="form-control" required>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="" class="title-color">{{\App\CPU\translate('Discount_Type')}}</label>
                                        <select
                                            class="form-control js-select2-custom"
                                            name="discount_type">
                                            <option value="percent" {{$product['discount_type']=='percent'?'selected':''}}>{{\App\CPU\translate('Percent')}}</option>
                                            <option value="flat" {{$product['discount_type']=='flat'?'selected':''}}>{{\App\CPU\translate('Flat')}}</option>

                                        </select>
                                    </div>
                                </div>
                                <div class="sku_combination" id="sku_combination">
                                    @include('seller-views.product.partials._edit_sku_combinations',['combinations'=>json_decode($product['variation'],true)])
                                </div>
                                <div class="row">
                                    <div class="col-md-3 mb-3 physical_product_show" id="quantity">
                                        <label class="title-color">{{\App\CPU\translate('total')}} {{\App\CPU\translate('Quantity')}} </label>
                                        <input type="number" min="0" value={{ $product->current_stock }} step="1"
                                               placeholder="{{\App\CPU\translate('Quantity') }}"
                                               name="current_stock" class="form-control" required>
                                    </div>
                                    <div class="col-md-3 mb-3" id="minimum_order_qty">
                                        <label class="title-color">{{\App\CPU\translate('minimum_order_quantity')}}</label>
                                        <input type="number" min="1" value={{ $product->minimum_order_qty }} step="1"
                                               placeholder="{{\App\CPU\translate('minimum_order_quantity') }}"
                                               name="minimum_order_qty" class="form-control" required>
                                    </div>
                                    <div class="col-md-3 mb-3 physical_product_show" id="shipping_cost">
                                        <label class="title-color">{{\App\CPU\translate('shipping_cost')}} </label>
                                        <input type="number" min="0" value="{{\App\CPU\BackEndHelper::usd_to_currency($product->shipping_cost)}}" step="1"
                                               placeholder="{{\App\CPU\translate('shipping_cost')}}"
                                               name="shipping_cost" class="form-control" required>
                                    </div>
                                    <div class="col-md-3 mb-3 physical_product_show" id="shipping_cost_multy">
                                        <div>
                                            <label class="title-color">{{\App\CPU\translate('shipping_cost_multiply_with_quantity')}} </label>
                                        </div>
                                        <div>
                                            <label class="switcher">
                                                <input type="checkbox" class="switcher_input" name="multiplyQTY"
                                                       id="" {{$product->multiply_qty == 1?'checked':''}}>
                                                <span class="switcher_control"></span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card mt-2 mb-2 rest-part">
                        <div class="card-header">
                            <h4 class="mb-0">{{\App\CPU\translate('seo_section')}}</h4>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-12 mb-4">
                                    <label class="title-color">{{\App\CPU\translate('Meta_Title')}}</label>
                                    <input type="text" name="meta_title" value="{{$product['meta_title']}}" placeholder="" class="form-control">
                                </div>

                                <div class="col-md-8 mb-4">
                                    <label class="title-color">{{\App\CPU\translate('Meta_Description')}}</label>
                                    <textarea rows="10" type="text" name="meta_description" class="form-control">
                                        {{$product['meta_description']}}
                                    </textarea>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group mb-0">
                                        <label class="title-color">{{\App\CPU\translate('Meta_Image')}}</label>
                                    </div>
                                    <div class="border-dashed">
                                        <div class="row" id="meta_img">
                                            <div class="col-6">
                                                <div class="card">
                                                    <div class="card-body">
                                                        <img class="w-100" height="auto"
                                                             onerror="this.src='{{asset('public/assets/front-end/img/image-place-holder.png')}}'"
                                                             src="{{Storage::url("/product/meta")}}/{{$product['meta_image']}}"
                                                             alt="Meta image">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card mt-2 rest-part">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-12 mb-4">
                                    <div class="mb-2 d-flex flex-wrap gap-1">
                                        <label class="title-color mb-0">{{\App\CPU\translate('Youtube video link')}}</label>
                                        <small class="badge-soft-info"> ( {{\App\CPU\translate('optional, please provide embed link not direct link.')}} )</small>
                                    </div>
                                    <input type="text" value="{{$product['video_url']}}" name="video_link" placeholder="EX : https://www.youtube.com/embed/5R06LRdUCSE" class="form-control" required>
                                </div>

                                <div class="col-md-8">
                                    <div class="mb-2 d-flex flex-wrap gap-1 align-items-baseline">
                                        <label class="mb-0">{{\App\CPU\translate('Upload product images')}}</label>
                                        <small class="text-info">* ( {{\App\CPU\translate('ratio')}} 1:1 )</small>
                                    </div>
                                    <div class="p-2 border border-dashed max-w-400">
                                        <div class="row" id="coba">
                                            @foreach (json_decode($product->images) as $key => $photo)
                                                <div class="col-sm-6 mb-3 mb-sm-0">
                                                    <div class="card">
                                                        <div class="card-body">
                                                            <img class="w-100" height="auto"
                                                                 onerror="this.src='{{asset('public/assets/front-end/img/image-place-holder.png')}}'"
                                                                 src="{{Storage::url("/product/$photo")}}"
                                                                 alt="Product image">
                                                            <a href="{{route('seller.product.remove-image',['id'=>$product['id'],'name'=>$photo])}}"
                                                               class="btn btn-danger btn-block">{{\App\CPU\translate('Remove')}}</a>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 mt-3 mt-md-0">
                                    <div class="mb-2 d-flex flex-wrap gap-1 align-items-baseline">
                                        <label for="name" class="title-color mb-0">{{\App\CPU\translate('Upload thumbnail')}}</label>
                                        <small class="text-info">* ( {{\App\CPU\translate('ratio')}} 1:1 )</small>
                                    </div>
                                    <div class="row" id="meta_img">
                                        <div class="col-6">
                                            <div class="card">
                                                <div class="card-body">
                                                    <img class="w-100" height="auto"
                                                         onerror="this.src='{{asset('public/assets/front-end/img/image-place-holder.png')}}'"
                                                         src="{{Storage::url("/product/thumbnail")}}/{{$product['thumbnail']}}"
                                                         alt="Product image">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="row" id="thumbnail"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 d-flex justify-content-end mt-3">
                                    @if($product['request_status'] == 2)
                                        <button type="button" onclick="check()" class="btn btn--primary px-4">{{ \App\CPU\translate('resubmit') }}</button>
                                    @else
                                        <button type="button" onclick="check()" class="btn btn--primary px-4">{{ \App\CPU\translate('update') }}</button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script src="{{asset('public/assets/back-end')}}/js/tags-input.min.js"></script>
    <script src="{{ asset('public/assets/select2/js/select2.min.js')}}"></script>
    <script src="{{asset('public/assets/back-end/js/spartan-multi-image-picker.js')}}"></script>
    <script>
        var imageCount = {{10-count(json_decode($product->images))}};
        var thumbnail = '{{\App\CPU\ProductManager::product_image_path('thumbnail').'/'.$product->thumbnail??asset('public/assets/back-end/img/400x400/img2.jpg')}}';
        $(function () {
            if (imageCount > 0) {
                $("#coba").spartanMultiImagePicker({
                    fieldName: 'images[]',
                    maxCount: imageCount,
                    rowHeight: 'auto',
                    groupClassName: 'col-6',
                    maxFileSize: '',
                    placeholderImage: {
                        image: '{{asset('public/assets/back-end/img/400x400/img2.jpg')}}',
                        width: '100%',
                    },
                    dropFileLabel: "Drop Here",
                    onAddRow: function (index, file) {

                    },
                    onRenderedPreview: function (index) {

                    },
                    onRemoveRow: function (index) {

                    },
                    onExtensionErr: function (index, file) {
                        toastr.error('{{\App\CPU\translate('Please only input png or jpg type file')}}', {
                            CloseButton: true,
                            ProgressBar: true
                        });
                    },
                    onSizeErr: function (index, file) {
                        toastr.error('{{\App\CPU\translate('File size too big')}}', {
                            CloseButton: true,
                            ProgressBar: true
                        });
                    }
                });
            }
            $("#thumbnail").spartanMultiImagePicker({
                fieldName: 'image',
                maxCount: 1,
                rowHeight: 'auto',
                groupClassName: 'col-10',
                maxFileSize: '',
                placeholderImage: {
                    image: '{{asset('public/assets/back-end/img/400x400/img2.jpg')}}',
                    width: '100%',
                },
                dropFileLabel: "Drop Here",
                onAddRow: function (index, file) {

                },
                onRenderedPreview: function (index) {

                },
                onRemoveRow: function (index) {

                },
                onExtensionErr: function (index, file) {
                    toastr.error('{{\App\CPU\translate('Please only input png or jpg type file')}}', {
                        CloseButton: true,
                        ProgressBar: true
                    });
                },
                onSizeErr: function (index, file) {
                    toastr.error('{{\App\CPU\translate('File size too big')}}', {
                        CloseButton: true,
                        ProgressBar: true
                    });
                }
            });

            $("#meta_img").spartanMultiImagePicker({
                fieldName: 'meta_image',
                maxCount: 1,
                rowHeight: 'auto',
                groupClassName: 'col-6',
                maxFileSize: '',
                placeholderImage: {
                    image: '{{asset('public/assets/back-end/img/400x400/img2.jpg')}}',
                    width: '100%',
                },
                dropFileLabel: "Drop Here",
                onAddRow: function (index, file) {

                },
                onRenderedPreview: function (index) {

                },
                onRemoveRow: function (index) {

                },
                onExtensionErr: function (index, file) {
                    toastr.error('{{\App\CPU\translate('Please only input png or jpg type file')}}', {
                        CloseButton: true,
                        ProgressBar: true
                    });
                },
                onSizeErr: function (index, file) {
                    toastr.error('{{\App\CPU\translate('File size too big')}}', {
                        CloseButton: true,
                        ProgressBar: true
                    });
                }
            });
        });

        function readURL(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();

                reader.onload = function (e) {
                    $('#viewer').attr('src', e.target.result);
                }

                reader.readAsDataURL(input.files[0]);
            }
        }

        $("#customFileUpload").change(function () {
            readURL(this);
        });

        $(".js-example-theme-single").select2({
            theme: "classic"
        });

        $(".js-example-responsive").select2({
            width: 'resolve'
        });
    </script>

    <script>
        function getRequest(route, id, type) {
            $.get({
                url: route,
                dataType: 'json',
                success: function (data) {
                    if (type == 'select') {
                        $('#' + id).empty().append(data.select_tag);
                    }
                },
            });
        }

        $('input[name="colors_active"]').on('change', function () {
            if (!$('input[name="colors_active"]').is(':checked')) {
                $('#colors-selector').prop('disabled', true);
            } else {
                $('#colors-selector').prop('disabled', false);
            }
        });

        $('#choice_attributes').on('change', function () {
            $('#customer_choice_options').html(null);
            $.each($("#choice_attributes option:selected"), function () {
                //console.log($(this).val());
                add_more_customer_choice_option($(this).val(), $(this).text());
            });
        });

        function add_more_customer_choice_option(i, name) {
            let n = name.split(' ').join('');
            $('#customer_choice_options').append('<div class="row"><div class="col-md-3"><input type="hidden" name="choice_no[]" value="' + i + '"><input type="text" class="form-control" name="choice[]" value="' + n + '" placeholder="{{\App\CPU\translate('Choice Title') }}" readonly></div><div class="col-lg-9"><input type="text" class="form-control" name="choice_options_' + i + '[]" placeholder="{{\App\CPU\translate('Enter choice values') }}" data-role="tagsinput" onchange="update_sku()"></div></div>');
            $("input[data-role=tagsinput], select[multiple][data-role=tagsinput]").tagsinput();
        }

        setInterval(function () {
            $('.call-update-sku').on('change', function () {
                update_sku();
            });
        }, 2000)

        $('#colors-selector').on('change', function () {
            update_sku();
        });

        $('input[name="unit_price"]').on('keyup', function () {
            update_sku();
        });

        function update_sku() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $.ajax({
                type: "POST",
                url: '{{route('seller.product.sku-combination')}}',
                data: $('#product_form').serialize(),
                success: function (data) {
                    $('#sku_combination').html(data.view);
                    if (data.length > 1) {
                        $('#quantity').hide();
                    } else {
                        $('#quantity').show();
                    }
                }
            });
        }

        $(document).ready(function () {
            let category = $("#category_id").val();
            let sub_category = $("#sub-category-select").attr("data-id");
            let sub_sub_category = $("#sub-sub-category-select").attr("data-id");
            getRequest('{{url('/')}}/seller/product/get-categories?parent_id=' + category + '&sub_category=' + sub_category, 'sub-category-select', 'select');
            getRequest('{{url('/')}}/seller/product/get-categories?parent_id=' + sub_category + '&sub_category=' + sub_sub_category, 'sub-sub-category-select', 'select');
            // color select select2
            $('.color-var-select').select2({
                templateResult: colorCodeSelect,
                templateSelection: colorCodeSelect,
                escapeMarkup: function (m) {
                    return m;
                }
            });

            function colorCodeSelect(state) {
                var colorCode = $(state.element).val();
                if (!colorCode) return state.text;
                return "<span class='color-preview' style='background-color:" + colorCode + ";'></span>" + state.text;
            }
        });
    </script>

    {{--ck editor--}}
    <script src="{{asset('/')}}vendor/ckeditor/ckeditor/ckeditor.js"></script>
    <script src="{{asset('/')}}vendor/ckeditor/ckeditor/adapters/jquery.js"></script>
    <script>
        $('.textarea').ckeditor({
            contentsLangDirection : '{{Session::get('direction')}}',
        });
    </script>
    {{--ck editor--}}

    <script>
        function check(){
            for (instance in CKEDITOR.instances) {
                CKEDITOR.instances[instance].updateElement();
            }
            var formData = new FormData(document.getElementById('product_form'));
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.post({
                url: '{{route('seller.product.update',$product->id)}}',
                data: formData,
                contentType: false,
                processData: false,
                success: function (data) {
                    if (data.errors) {
                        for (var i = 0; i < data.errors.length; i++) {
                            toastr.error(data.errors[i].message, {
                                CloseButton: true,
                                ProgressBar: true
                            });
                        }
                    } else {
                        toastr.success('{{\App\CPU\translate('product updated successfully!')}}', {
                            CloseButton: true,
                            ProgressBar: true
                        });
                        $('#product_form').submit();
                    }
                }
            });
        };
    </script>

    <script>
        update_qty();

        function update_qty() {
            var total_qty = 0;
            var qty_elements = $('input[name^="qty_"]');
            for (var i = 0; i < qty_elements.length; i++) {
                total_qty += parseInt(qty_elements.eq(i).val());
            }
            if (qty_elements.length > 0) {

                $('input[name="current_stock"]').attr("readonly", true);
                $('input[name="current_stock"]').val(total_qty);
            } else {
                $('input[name="current_stock"]').attr("readonly", false);
            }
        }

        $('input[name^="qty_"]').on('keyup', function () {
            var total_qty = 0;
            var qty_elements = $('input[name^="qty_"]');
            for (var i = 0; i < qty_elements.length; i++) {
                total_qty += parseInt(qty_elements.eq(i).val());
            }
            $('input[name="current_stock"]').val(total_qty);
        });
    </script>

    <script>
        $(".lang_link").click(function (e) {
            e.preventDefault();
            $(".lang_link").removeClass('active');
            $(".lang_form").addClass('d-none');
            $(this).addClass('active');

            let form_id = this.id;
            let lang = form_id.split("-")[0];
            console.log(lang);
            $("#" + lang + "-form").removeClass('d-none');
            if (lang == '{{$default_lang}}') {
                $(".rest-part").removeClass('d-none');
            } else {
                $(".rest-part").addClass('d-none');
            }
        });

        $(document).ready(function(){
            product_type();
            digital_product_type();

            $('#product_type').change(function(){
                product_type();
            });

            $('#digital_product_type').change(function(){
                digital_product_type();
            });
        });

        function product_type(){
            let product_type = $('#product_type').val();

            if(product_type === 'physical'){
                $('#digital_product_type_show').hide();
                $('#digital_file_ready_show').hide();
                $('.physical_product_show').show();
                $("#digital_product_type").val($("#digital_product_type option:first").val());
                $("#digital_file_ready").val('');
            }else if(product_type === 'digital'){
                $('#digital_product_type_show').show();
                $('.physical_product_show').hide();

            }
        }

        function digital_product_type(){
            let digital_product_type = $('#digital_product_type').val();
            if (digital_product_type === 'ready_product') {
                $('#digital_file_ready_show').show();
            } else if (digital_product_type === 'ready_after_sell') {
                $('#digital_file_ready_show').hide();
                $("#digital_file_ready").val('');
            }
        }
    </script>
@endpush
