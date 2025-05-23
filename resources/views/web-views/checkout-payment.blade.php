@extends('layouts.front-end.app')

@section('title',\App\CPU\translate('Choose Payment Method'))

@push('css_or_js')
    <style>
        .stripe-button-el {
            display: none !important;
        }

        .razorpay-payment-button {
            display: none !important;
        }
    </style>

    {{--stripe--}}
    <script src="https://polyfill.io/v3/polyfill.min.js?version=3.52.1&features=fetch"></script>
    <script src="https://js.stripe.com/v3/"></script>
    {{--stripe--}}
@endpush

@section('content')
    <!-- Page Content-->
    <div class="container pb-5 mb-2 mb-md-4 rtl"
         style="text-align: {{Session::get('direction') === "rtl" ? 'right' : 'left'}};">
        <div class="row">
            <div class="col-md-12 mb-5 pt-5">
                <div class="feature_header __feature_header">
                    <span>{{ \App\CPU\translate('payment_method')}}</span>
                </div>
            </div>
            <section class="col-lg-8">
                <div class="checkout_details">
                @include('web-views.partials._checkout-steps',['step'=>3])
                <!-- Payment methods accordion-->
                    <h2 class="h6 pb-3 mb-2 mt-5">{{\App\CPU\translate('choose_payment')}}</h2>

                    <div class="row g-3">
                        @php($config=\App\CPU\Helpers::get_business_settings('cash_on_delivery'))
                        @if(!$cod_not_show && $config['status'])
                            <div class="col-sm-6" id="cod-for-cart">
                                <div class="card cursor-pointer">
                                    <div class="card-body __h-100px">
                                        <form action="{{route('checkout-complete')}}" method="get" class="needs-validation">
                                            <input type="hidden" name="payment_method" value="cash_on_delivery">
                                            <button class="btn btn-block click-if-alone" type="submit">
                                                <img width="150" class="__mt-n-10" src="{{asset('assets/front-end/img/cod.png')}}"/>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @php($coupon_discount = session()->has('coupon_discount') ? session('coupon_discount') : 0)
                        @php($amount = \App\CPU\CartManager::cart_grand_total() - $coupon_discount)
                        @php($digital_payment=\App\CPU\Helpers::get_business_settings('digital_payment'))

                        @if ($digital_payment['status']==1)
                            @php($config=\App\CPU\Helpers::get_business_settings('wallet_status'))
                            @if($config==1)
                                <div class="col-sm-6">
                                    <div class="card cursor-pointer">
                                        <div class="card-body __h-100px">
                                            {{-- <form action="{{route('checkout-complete-wallet')}}" method="get" class="needs-validation"> --}}
                                                <button class="btn btn-block click-if-alone" type="submit"
                                                    data-toggle="modal" data-target="#wallet_submit_button">
                                                    <img width="150" class="__mt-n-10"
                                                        src="{{asset('assets/front-end/img/wallet.png')}}"/>
                                                </button>
                                            {{-- </form> --}}
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @php($config=\App\CPU\Helpers::get_business_settings('ssl_commerz_payment'))
                            @if($config['status'])
                                <div class="col-sm-6">
                                    <div class="card cursor-pointer">
                                        <div class="card-body __h-100px">
                                            <form action="{{ url('/pay-ssl') }}" method="POST" class="needs-validation">
                                                <input type="hidden" value="{{ csrf_token() }}" name="_token"/>
                                                <button class="btn btn-block click-if-alone" type="submit">
                                                    <img width="150"
                                                        src="{{asset('assets/front-end/img/sslcomz.png')}}"/>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @php($config=\App\CPU\Helpers::get_business_settings('paypal'))
                            @if($config['status'])
                                <div class="col-sm-6">
                                    <div class="card cursor-pointer">
                                        <div class="card-body __h-100px">
                                            <form class="needs-validation" method="POST" id="payment-form"
                                                action="{{route('pay-paypal')}}">
                                                {{ csrf_field() }}
                                                <button class="btn btn-block click-if-alone" type="submit">
                                                    <img width="150"
                                                        src="{{asset('assets/front-end/img/paypal.png')}}"/>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endif



                            @php($config=\App\CPU\Helpers::get_business_settings('stripe'))
                            @if($config['status'])
                                <div class="col-sm-6">
                                    <div class="card cursor-pointer">
                                        <div class="card-body __h-100px">
                                            <button class="btn btn-block click-if-alone" type="button" id="checkout-button">
                                                {{-- <i class="czi-card"></i> {{\App\CPU\translate('Credit / Debit card ( Stripe )')}} --}}
                                                <img width="150"
                                                src="{{asset('assets/front-end/img/stripe.png')}}"/>
                                            </button>
                                            <script type="text/javascript">
                                                // Create an instance of the Stripe object with your publishable API key
                                                var stripe = Stripe('{{$config['published_key']}}');
                                                var checkoutButton = document.getElementById("checkout-button");
                                                checkoutButton.addEventListener("click", function () {
                                                    fetch("{{route('pay-stripe')}}", {
                                                        method: "GET",
                                                    }).then(function (response) {
                                                        console.log(response)
                                                        return response.text();
                                                    }).then(function (session) {
                                                        /*console.log(JSON.parse(session).id)*/
                                                        return stripe.redirectToCheckout({sessionId: JSON.parse(session).id});
                                                    }).then(function (result) {
                                                        if (result.error) {
                                                            alert(result.error.message);
                                                        }
                                                    }).catch(function (error) {
                                                        console.error("{{\App\CPU\translate('Error')}}:", error);
                                                    });
                                                });
                                            </script>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @php($config=\App\CPU\Helpers::get_business_settings('razor_pay'))
                            @php($inr=\App\Model\Currency::where(['symbol'=>'₹'])->first())
                            @php($usd=\App\Model\Currency::where(['code'=>'USD'])->first())
                            @if(isset($inr) && isset($usd) && $config['status'])

                                <div class="col-sm-6">
                                    <div class="card cursor-pointer">
                                        <div class="card-body __h-100px">
                                            <form action="{!!route('payment-razor')!!}" method="POST">
                                            @csrf
                                            <!-- Note that the amount is in paise = 50 INR -->
                                                <!--amount need to be in paisa-->
                                                <script src="https://checkout.razorpay.com/v1/checkout.js"
                                                        data-key="{{ \Illuminate\Support\Facades\Config::get('razor.razor_key') }}"
                                                        data-amount="{{(round(\App\CPU\Convert::usdToinr($amount)))*100}}"
                                                        data-buttontext="Pay {{(\App\CPU\Convert::usdToinr($amount))*100}} INR"
                                                        data-name="{{\App\Model\BusinessSetting::where(['type'=>'company_name'])->first()->value}}"
                                                        data-description=""
                                                        data-image="{{Storage::url('company/'.\App\Model\BusinessSetting::where(['type'=>'company_web_logo'])->first()->value)}}"
                                                        data-prefill.name="{{auth('customer')->user()->f_name}}"
                                                        data-prefill.email="{{auth('customer')->user()->email}}"
                                                        data-theme.color="#ff7529">
                                                </script>
                                            </form>
                                            <button class="btn btn-block click-if-alone" type="button"
                                                    onclick="$('.razorpay-payment-button').click()">
                                                <img width="150"
                                                    src="{{asset('assets/front-end/img/razor.png')}}"/>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @php($config=\App\CPU\Helpers::get_business_settings('paystack'))
                            @if($config['status'])
                                <div class="col-sm-6">
                                    <div class="card cursor-pointer">
                                        <div class="card-body __h-100px">
                                            @php($config=\App\CPU\Helpers::get_business_settings('paystack'))
                                            @php($order=\App\Model\Order::find(session('order_id')))
                                            <form method="POST" action="{{ route('paystack-pay') }}" accept-charset="UTF-8"
                                                class="form-horizontal"
                                                role="form">
                                                @csrf
                                                <div class="row">
                                                    <div class="col-md-8 col-md-offset-2">
                                                        <input type="hidden" name="email"
                                                            value="{{auth('customer')->user()->email}}"> {{-- required --}}
                                                        <input type="hidden" name="orderID"
                                                            value="{{session('cart_group_id')}}">
                                                        <input type="hidden" name="amount"
                                                            value="{{\App\CPU\Convert::usdTozar($amount*100)}}"> {{-- required in kobo --}}
                                                        <input type="hidden" name="quantity" value="1">
                                                        <input type="hidden" name="currency"
                                                            value="{{\App\CPU\Helpers::currency_code()}}">
                                                        <input type="hidden" name="metadata"
                                                            value="{{ json_encode($array = ['key_name' => 'value',]) }}"> {{-- For other necessary things you want to add to your payload. it is optional though --}}
                                                        <input type="hidden" name="reference"
                                                            value="{{ Paystack::genTranxRef() }}"> {{-- required --}}
                                                        <p>
                                                            <button class="paystack-payment-button" style="display: none"
                                                                    type="submit"
                                                                    value="Pay Now!"></button>
                                                        </p>
                                                    </div>
                                                </div>
                                            </form>
                                            <button class="btn btn-block click-if-alone" type="button"
                                                    onclick="$('.paystack-payment-button').click()">
                                                <img width="100"
                                                    src="{{asset('assets/front-end/img/paystack.png')}}"/>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @php($myr=\App\Model\Currency::where(['code'=>'MYR'])->first())
                            @php($usd=\App\Model\Currency::where(['code'=>'usd'])->first())
                            @php($config=\App\CPU\Helpers::get_business_settings('senang_pay'))
                            @if(isset($myr) && isset($usd) && $config['status'])
                                <div class="col-sm-6">
                                    <div class="card cursor-pointer">
                                        <div class="card-body __h-100px">
                                            @php($config=\App\CPU\Helpers::get_business_settings('senang_pay'))
                                            @php($user=auth('customer')->user())
                                            @php($secretkey = $config['secret_key'])
                                            @php($data = new \stdClass())
                                            @php($data->merchantId = $config['merchant_id'])
                                            @php($data->detail = 'payment')
                                            @php($data->order_id = session('cart_group_id'))
                                            @php($data->amount = \App\CPU\Convert::usdTomyr($amount))
                                            @php($data->name = $user->f_name.' '.$user->l_name)
                                            @php($data->email = $user->email)
                                            @php($data->phone = $user->phone)
                                            @php($data->hashed_string = md5($secretkey . urldecode($data->detail) . urldecode($data->amount) . urldecode($data->order_id)))

                                            <form name="order" method="post"
                                                action="https://{{env('APP_MODE')=='live'?'app.senangpay.my':'sandbox.senangpay.my'}}/payment/{{$config['merchant_id']}}">
                                                <input type="hidden" name="detail" value="{{$data->detail}}">
                                                <input type="hidden" name="amount" value="{{$data->amount}}">
                                                <input type="hidden" name="order_id" value="{{$data->order_id}}">
                                                <input type="hidden" name="name" value="{{$data->name}}">
                                                <input type="hidden" name="email" value="{{$data->email}}">
                                                <input type="hidden" name="phone" value="{{$data->phone}}">
                                                <input type="hidden" name="hash" value="{{$data->hashed_string}}">
                                            </form>

                                            <button class="btn btn-block click-if-alone" type="button"
                                                    onclick="document.order.submit()">
                                                <img width="100"
                                                    src="{{asset('assets/front-end/img/senangpay.png')}}"/>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @php($config=\App\CPU\Helpers::get_business_settings('paymob_accept'))
                            @if($config['status'])
                                <div class="col-sm-6">
                                    <div class="card cursor-pointer">
                                        <div class="card-body __h-100px">
                                            <form class="needs-validation" method="POST" id="payment-form-paymob"
                                                action="{{route('paymob-credit')}}">
                                                {{ csrf_field() }}
                                                <button class="btn btn-block click-if-alone" type="submit">
                                                    <img width="150"
                                                        src="{{asset('assets/front-end/img/paymob.png')}}"/>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @php($config=\App\CPU\Helpers::get_business_settings('bkash'))
                            @if(isset($config)  && $config['status'])
                                <div class="col-sm-6">
                                    <div class="card cursor-pointer">
                                        <div class="card-body __h-100px">
                                            <button class="btn btn-block click-if-alone" id="bKash_button"
                                                    onclick="BkashPayment()">
                                                <img width="100" src="{{asset('assets/front-end/img/bkash.png')}}"/>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @php($config=\App\CPU\Helpers::get_business_settings('paytabs'))
                            @if(isset($config)  && $config['status'])
                                <div class="col-sm-6">
                                    <div class="card cursor-pointer">
                                        <div class="card-body __h-100px">
                                            <button class="btn btn-block click-if-alone __mt-n-11" onclick="location.href='{{route('paytabs-payment')}}'">
                                                <img width="150"
                                                    src="{{asset('assets/front-end/img/paytabs.png')}}"/>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            {{--@php($config=\App\CPU\Helpers::get_business_settings('fawry_pay'))
                            @if(isset($config)  && $config['status'])
                                <div class="col-sm-6">
                                    <div class="card cursor-pointer">
                                        <div class="card-body __h-100px">
                                            <button class="btn btn-block __mt-n-11" onclick="location.href='{{route('fawry')}}'">
                                                <img width="150" src="{{asset('assets/front-end/img/fawry.svg')}}"/>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endif--}}

                            @php($config=\App\CPU\Helpers::get_business_settings('mercadopago'))
                            @if(isset($config) && $config['status'])
                                <div class="col-sm-6">
                                    <div class="card cursor-pointer">
                                        <div class="card-body pt-2 __h-100px">
                                            <a class="btn btn-block click-if-alone" href="{{route('mercadopago.index')}}">
                                                <img width="150"
                                                    src="{{asset('assets/front-end/img/MercadoPago_(Horizontal).svg')}}"/>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @php($config=\App\CPU\Helpers::get_business_settings('flutterwave'))
                            @if(isset($config) && $config['status'])
                                <div class="col-sm-6">
                                    <div class="card cursor-pointer">
                                        <div class="card-body pt-2 __h-100px">
                                            <form method="POST" action="{{ route('flutterwave_pay') }}">
                                                {{ csrf_field() }}

                                                <button class="btn btn-block click-if-alone" type="submit">
                                                    <img width="200"
                                                        src="{{asset('assets/front-end/img/fluterwave.png')}}"/>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @php($config=\App\CPU\Helpers::get_business_settings('paytm'))
                            @if(isset($config) && $config['status'])
                                <div class="col-sm-6">
                                    <div class="card cursor-pointer">
                                        <div class="card-body __h-100px">
                                            <a class="btn btn-block click-if-alone" href="{{route('paytm-payment')}}">
                                                <img class="__inline-55" src="{{asset('assets/front-end/img/paytm.png')}}"/>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @php($config=\App\CPU\Helpers::get_business_settings('liqpay'))
                            @if(isset($config) && $config['status'])
                                <div class="col-sm-6">
                                    <div class="card cursor-pointer">
                                        <div class="card-body __h-100px">
                                            <a class="btn btn-block click-if-alone" href="{{route('liqpay-payment')}}">
                                                <img class="__inline-55 mt-0" src="{{asset('assets/front-end/img/liqpay4.png')}}"/>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endif

                    </div>
                    <!-- Navigation (desktop)-->
                    <div class="row justify-content-center">
                        <div class="col-md-6 text-center mt-5">
                            <a class="btn btn-secondary btn-block" href="{{route('checkout-details')}}">
                                <span class="d-none d-sm-inline">{{\App\CPU\translate('Back to Shipping')}}</span>
                                <span class="d-inline d-sm-none">{{\App\CPU\translate('Back')}}</span>
                            </a>
                        </div>
                    </div>
                </div>
            </section>
            <!-- Sidebar-->
            @include('web-views.partials._order-summary')
        </div>
    </div>

    <!-- Modal -->
  <div class="modal fade" id="wallet_submit_button" aria-labelledby="exampleModalLongTitle" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLongTitle">{{\App\CPU\translate('wallet_payment')}}</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        @php($customer_balance = auth('customer')->user()->wallet_balance)
        @php($remain_balance = $customer_balance - $amount)
        <form action="{{route('checkout-complete-wallet')}}" method="get" class="needs-validation">
            @csrf
            <div class="modal-body">
                <div class="form-row">
                    <div class="form-group col-12">
                        <label for="">{{\App\CPU\translate('your_current_balance')}}</label>
                        <input class="form-control" type="text" value="{{\App\CPU\Helpers::currency_converter($customer_balance)}}" readonly>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-12">
                        <label for="">{{\App\CPU\translate('order_amount')}}</label>
                        <input class="form-control" type="text" value="{{\App\CPU\Helpers::currency_converter($amount)}}" readonly>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-12">
                        <label for="">{{\App\CPU\translate('remaining_balance')}}</label>
                        <input class="form-control" type="text" value="{{\App\CPU\Helpers::currency_converter($remain_balance)}}" readonly>
                        @if ($remain_balance<0)
                        <label class="__color-crimson">{{\App\CPU\translate('you do not have sufficient balance for pay this order!!')}}</label>
                        @endif
                    </div>
                </div>

            </div>
            <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">{{\App\CPU\translate('close')}}</button>
            <button type="submit" class="btn btn--primary" {{$remain_balance>0? '':'disabled'}}>{{\App\CPU\translate('submit')}}</button>
            </div>
        </form>
      </div>
    </div>
  </div>
@endsection

@push('script')

  @php($mode = App\CPU\Helpers::get_business_settings('bkash')['environment']??'sandbox')
    @if($mode=='live')
        <script id="myScript"
                src="https://scripts.pay.bka.sh/versions/1.2.0-beta/checkout/bKash-checkout.js"></script>
    @else
        <script id="myScript"
                src="https://scripts.sandbox.bka.sh/versions/1.2.0-beta/checkout/bKash-checkout-sandbox.js"></script>
    @endif

    <script>
        setTimeout(function () {
            $('.stripe-button-el').hide();
            $('.razorpay-payment-button').hide();
        }, 10)
    </script>

    <script type="text/javascript">
        function BkashPayment() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                }
            });
            $('#loading').show();
            // get token
            $.ajax({
                url: "{{ route('bkash-get-token') }}",
                type: 'POST',
                contentType: 'application/json',
                success: function (data) {
                    $('#loading').hide();
                    $('pay-with-bkash-button').trigger('click');
                    if (data.hasOwnProperty('msg')) {
                        showErrorMessage(data) // unknown error
                    }
                },
                error: function (err) {
                    $('#loading').hide();
                    showErrorMessage(err);
                }
            });
        }

        let paymentID = '';
        bKash.init({
            paymentMode: 'checkout',
            paymentRequest: {},
            createRequest: function (request) {
                setTimeout(function () {
                    createPayment(request);
                }, 2000)
            },
            executeRequestOnAuthorization: function (request) {
                $.ajax({
                    url: '{{ route('bkash-execute-payment') }}',
                    type: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify({
                        "paymentID": paymentID
                    }),
                    success: function (data) {
                        if (data) {
                            if (data.paymentID != null) {
                                BkashSuccess(data);
                            } else {
                                showErrorMessage(data);
                                bKash.execute().onError();
                            }
                        } else {
                            $.get('{{ route('bkash-query-payment') }}', {
                                payment_info: {
                                    payment_id: paymentID
                                }
                            }, function (data) {
                                if (data.transactionStatus === 'Completed') {
                                    BkashSuccess(data);
                                } else {
                                    createPayment(request);
                                }
                            });
                        }
                    },
                    error: function (err) {
                        bKash.execute().onError();
                    }
                });
            },
            onClose: function () {
                // for error handle after close bKash Popup
            }
        });

        function createPayment(request) {
            // because of createRequest function finds amount from this request
            request['amount'] = "{{round(\App\CPU\Convert::usdTobdt($amount),2)}}"; // max two decimal points allowed
            $.ajax({
                url: '{{ route('bkash-create-payment') }}',
                data: JSON.stringify(request),
                type: 'POST',
                contentType: 'application/json',
                success: function (data) {
                    $('#loading').hide();
                    if (data && data.paymentID != null) {
                        paymentID = data.paymentID;
                        bKash.create().onSuccess(data);
                    } else {
                        bKash.create().onError();
                    }
                },
                error: function (err) {
                    $('#loading').hide();
                    showErrorMessage(err.responseJSON);
                    bKash.create().onError();
                }
            });
        }

        function BkashSuccess(data) {
            $.post('{{ route('bkash-success') }}', {
                payment_info: data
            }, function (res) {
                @if(session()->has('payment_mode') && session('payment_mode') == 'app')
                    location.href = '{{ route('payment-success')}}';
                @else
                    location.href = '{{route('order-placed')}}';
                @endif
            });
        }

        function showErrorMessage(response) {
            let message = 'Unknown Error';
            if (response.hasOwnProperty('errorMessage')) {
                let errorCode = parseInt(response.errorCode);
                let bkashErrorCode = [2001, 2002, 2003, 2004, 2005, 2006, 2007, 2008, 2009, 2010, 2011, 2012, 2013, 2014,
                    2015, 2016, 2017, 2018, 2019, 2020, 2021, 2022, 2023, 2024, 2025, 2026, 2027, 2028, 2029, 2030,
                    2031, 2032, 2033, 2034, 2035, 2036, 2037, 2038, 2039, 2040, 2041, 2042, 2043, 2044, 2045, 2046,
                    2047, 2048, 2049, 2050, 2051, 2052, 2053, 2054, 2055, 2056, 2057, 2058, 2059, 2060, 2061, 2062,
                    2063, 2064, 2065, 2066, 2067, 2068, 2069, 503,
                ];
                if (bkashErrorCode.includes(errorCode)) {
                    message = response.errorMessage
                }
            }
            Swal.fire("Payment Failed!", message, "error");
        }

        function click_if_alone() {
            let total = $('.checkout_details .click-if-alone').length;
            if (Number.parseInt(total) < 2) {
                $('.click-if-alone').click()
                $('.checkout_details').html('<h1>{{\App\CPU\translate('Redirecting_to_the_payment')}}......</h1>');
            }
        }
        click_if_alone();

    </script>
@endpush
