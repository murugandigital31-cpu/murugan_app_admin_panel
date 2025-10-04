<style>
    strike{
        font-size: 12px!important;
    }
    .input-group--style-2{
        width: 160px;
    }
    .btn-number{
        padding: 10px
    }
    .add-to-btn{
        width:37%; height: 45px
    }
</style>


<button class="close fz-24px call-when-done" type="button" data-dismiss="modal" aria-label="Close">
    <span aria-hidden="true">&times;</span>
</button>
<div class="modal-body position-relative">
    <div class="modal--media">
        <div class="modal--media-avatar">
            @if (!empty(json_decode($product['image'],true)))
                <img class="img-responsive border" src="{{$product->identityImageFullPath[0]}}"
                 data-zoom="{{$product->identityImageFullPath[0]}}"
                 alt="{{translate('Product image')}}">
            @else
                 <img src="{{asset('public/assets/admin/img/160x160/2.png')}}" >
             @endif
            <div class="cz-image-zoom-pane"></div>
        </div>

        <div class="details">
            <span class="product-name"><a href="#" class="h3 mb-2 product-title">{{ Str::limit($product->name, 100) }}</a></span>
            <div class="mb-3 text-dark">
                @if($discount > 0)
                    <strike>
                        {{ Helpers::set_symbol($product['price']) }}
                    </strike>
                @endif
                    <span class="h3 font-weight-normal text-accent ml-1">
                    {{ Helpers::set_symbol(($product['price']- $discount)) }}
                </span>
            </div>

            <div class="mb-3 text-dark">
                <span>{{ translate('Current Stock') }} : </span>
                <strong id="current-stock-count">{{ $product->total_stock }}</strong>
            </div>
        </div>
    </div>
    <div class="row pt-4">
        <div class="col-12">
            <?php
            $cart = false;
            if (session()->has('cart')) {
                foreach (session()->get('cart') as $key => $cartItem) {
                    if (is_array($cartItem) && $cartItem['id'] == $product['id']) {
                        $cart = $cartItem;
                    }
                }
            }

            ?>
            <h2>{{translate('description')}}</h2>
            <div class="overflow-y-auto max-h-300px">
                <div class="d-block text-break text-dark __descripiton-txt __not-first-hidden">
                    <span>
                        {!! $product->description !!}
                    </span>
                    <span class="show-more text--title text-right">
                        <span>
                            {{translate('see more')}}
                        </span>
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal-footer border-0 shadow-lg d-block">
    <div id="add-to-cart-form" class="mb-2">
        @csrf

        @foreach (json_decode($product->choice_options) as $key => $choice)
            <div class="h3 p-0 pt-2 text-break">{{ $choice->title }}</div>

            <div class="d-flex justify-content-left flex-wrap">
                @foreach ($choice->options as $key => $option)
                    <input class="btn-check variation-choice-input" type="radio"
                           id="{{ $choice->name }}-{{ $option }}"
                           name="{{ $choice->name }}" value="{{ $option }}"
                           @if($key == 0) checked @endif autocomplete="off">
                    <label class="btn btn-sm check-label mx-1 choice-input"
                           for="{{ $choice->name }}-{{ $option }}">{{ $option }}</label>
                @endforeach
            </div>
        @endforeach


        <input type="hidden" name="id" value="{{ $product->id }}">
        <div class="d-flex justify-content-between align-items-center flex-wrap text-dark mb-4" id="chosen_price_div">
            <div class="product-description-label">{{translate('Total Price')}}:</div>
            <div class="product-price">
                <strong id="chosen_price"></strong>
            </div>
        </div>
        <div class="d-flex flex-wrap align-items-center">
            <button class="btn btn-primary add-to-btn h-auto" id="add_to_cart_btn"
                    type="button">
                <i class="tio-shopping-cart"></i>
                {{translate('add_to_cart')}}
            </button>
            <div class="d-flex flex-grow-1 justify-content-center">
                <div class="product-quantity d-flex align-items-center">
                    <div class="align-items-center d-flex g-3">
                        <span class="input-group-btn">
                            <button class="btn btn--reset btn-number p-2 rounded-circle text-dark" type="button" id="minus_btn"
                                    data-type="minus" data-field="quantity"
                                    disabled="disabled">
                                    <i class="tio-remove  font-weight-bold"></i>
                            </button>
                        </span>
                        <input type="hidden" id="check_max_qty" value="{{ $product['total_stock'] }}">
                        <input type="text" name="quantity" id="quantity"
                               class="form-control input-number text-center cart-qty-field w-65px"
                               placeholder="1" value="1" min="1" max="100">
                        <span class="input-group-btn">
                            <button class="btn btn--reset btn-number p-2 rounded-circle text-dark" type="button" data-type="plus" id="plus_btn"
                                    data-field="quantity">
                                    <i class="tio-add  font-weight-bold"></i>
                            </button>
                        </span>
                    </div>
                </div>
                <div class="warning_popup_wrapper">
                    <div class="warning_popup rounded-lg p-3 d-none">
                        <button type="button" class="close fz-24px close_warning_popup" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                        <div class="d-flex g-2 align-items-center">
                            <img src="{{asset('public/assets/admin/img/warning.png')}}" alt="">
                            <div>
                                <h3>{{translate('warning')}}</h3>
                                <p class="stock-validation-message">
                                    {{ translate('There isn’t enough quantity on stock.') }}
                                    {{ translate('Only') }} <strong class="product-stock-count"></strong> {{ translate('items are available.') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


    </div>

</div>

<script type="text/javascript">
    "use strict";

    cartQuantityInitialize();
    getVariantPrice();

    $('#add-to-cart-form input').on('change', function () {
        getVariantPrice();
    });

    $('#add_to_cart_btn').on('click', function() {
        addToCart();
    });

    $('.show-more span').on('click', function(){
        $('.__descripiton-txt').toggleClass('__not-first-hidden')
        if($(this).hasClass('active')) {
            $('.show-more span').text('{{translate('See More')}}')
            $(this).removeClass('active')
        }else {
            $('.show-more span').text('{{translate('See Less')}}')
            $(this).addClass('active')
        }
    })

    $('.variation-choice-input').on('change', function (){
        $('.cart-qty-field').val('1');
    });

</script>
