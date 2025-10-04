@extends('layouts.admin.app')

@section('title', translate('verify_offline_payments'))
@push('css_or_js')
    <style>
        table{
            width: 100%
        }
    </style>
@endpush
@section('content')
    <div class="content container-fluid">
        <div class="page-header">
            <h1 class="mb-0 page-header-title">
                <span class="page-header-icon">
                    <img src="{{asset('public/assets/admin/img/all_orders.png')}}" class="w--20" alt="">
                </span>
                <span class="">
                    {{translate('verify_offline_payments')}}
                    <span class="badge badge-pill badge-soft-secondary ml-2">{{ $orders->total() }}</span>
                </span>
            </h1>
            <ul class="nav nav-tabs border-0 my-2">
                <li class="nav-item">
                    <a class="nav-link {{Request::is('admin/verify-offline-payment/pending')?'active':''}}"  href="{{route('admin.verify-offline-payment', ['pending'])}}">{{ translate('Pending Orders') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{Request::is('admin/verify-offline-payment/denied')?'active':''}}"  href="{{route('admin.verify-offline-payment', ['denied'])}}">{{ translate('Denied Orders') }}</a>
                </li>
            </ul>
        </div>
        <div class="card">
            <div class="card-body p-20px">
                <div class="order-top">
                    <div class="card--header">
                        <form action="{{url()->current()}}" method="GET">
                            <div class="input-group">
                                <input id="datatableSearch_" type="search" name="search"
                                       class="form-control"
                                       placeholder="{{translate('Search by order ID')}}" aria-label="Search"
                                       value="{{$search}}" required autocomplete="off">
                                <div class="input-group-append">
                                    <button type="submit" class="input-group-text">
                                        {{translate('Search')}}
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="table-responsive datatable-custom">
                    <table class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                        <thead class="thead-light">
                        <tr>
                            <th class="">
                                {{translate('#')}}
                            </th>
                            <th class="table-column-pl-0">{{translate('order ID')}}</th>
                            <th>{{translate('Delivery')}} {{translate('date')}}</th>
                            <th>{{translate('customer')}}</th>
                            <th>{{translate('total amount')}}</th>
                            <th>{{translate('Payment_Method')}}</th>
                            <th>{{translate('Verification_Status')}}</th>
                            <th>
                                <div class="text-center">
                                    {{translate('action')}}
                                </div>
                            </th>
                        </tr>
                        </thead>

                        <tbody id="set-rows">
                        @foreach($orders as $key=>$order)

                            <tr class="status-{{$order['order_status']}} class-all">
                                <td class="">
                                    {{$orders->firstItem()+$key}}
                                </td>
                                <td class="table-column-pl-0">
                                    <a href="{{route('admin.orders.details',['id'=>$order['id']])}}">{{$order['id']}}</a>
                                </td>
                                <td>
                                    <div>
                                        {{date('d M Y',strtotime($order['delivery_date']))}}
                                        <span>{{$order->time_slot?date(config('time_format'), strtotime($order->time_slot['start_time'])).' - ' .date(config('time_format'), strtotime($order->time_slot['end_time'])) :'No Time Slot'}}</span>
                                    </div>
                                </td>
                                <td>
                                    @if($order->is_guest == 0)
                                        @if(isset($order->customer))
                                            <div>
                                                <a class="text-body text-capitalize font-medium"
                                                   href="{{route('admin.customer.view',[$order['user_id']])}}">{{$order->customer['f_name'].' '.$order->customer['l_name']}}</a>
                                            </div>
                                            <div class="text-sm">
                                                <a href="Tel:{{$order->customer['phone']}}">{{$order->customer['phone']}}</a>
                                            </div>
                                        @elseif($order->user_id != null && !isset($order->customer))
                                            <label
                                                class="text-danger">{{translate('Customer_not_available')}}
                                            </label>
                                        @else
                                            <label
                                                class="text-success">{{translate('Walking Customer')}}
                                            </label>
                                        @endif
                                    @else
                                        <label
                                            class="text-success">{{translate('Guest Customer')}}
                                        </label>
                                    @endif
                                </td>
                                <td>
                                    <div class="mw-90">
                                        <div>
                                                <?php
                                                $vatStatus = $order->details[0] ? $order->details[0]->vat_status : '';
                                                if($vatStatus == 'included'){
                                                    $orderAmount = $order['order_amount'] - $order['total_tax_amount'];
                                                }else{
                                                    $orderAmount = $order['order_amount'];
                                                }
                                                ?>
                                            {{ Helpers::set_symbol($orderAmount) }}
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <?php
                                        $paymentInfo = json_decode($order->offline_payment?->payment_info, true);
                                    ?>
                                    {{ $paymentInfo['payment_name'] ?? null }}
                                </td>
                                <td class="text-capitalize">
                                    @if($order->offline_payment?->status == 0)
                                        <span class="badge badge-soft-info">
                                            {{translate('pending')}}
                                        </span>
                                    @elseif($order->offline_payment?->status == 2)
                                        <span class="badge badge-soft-danger">
                                            {{translate('denied')}}
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <div class="justify-content-center gap-2 d-flex">
                                        <button class="btn px-3 btn--primary offline_details" type="button" id="offline_details"
                                                data-id="{{ $order['id'] }}" data-target="" data-toggle="modal">
                                            {{ translate('Verify_Payment') }}
                                        </button>
                                        @if($order->offline_payment?->status != 2)
                                            <button class="btn badge-danger deny-payment-btn" data-order-id="{{ $order->id }}" data-status="2">{{ translate('deny') }}</button>
                                        @endif
                                        <a class="btn badge-info flex-grow-1" href="{{route('admin.orders.verify-offline-payment', ['order_id' => $order->id, 'status' => 1])}}">{{ translate('approve') }}</a>
                                    </div>
                                </td>
                            </tr>

                        @endforeach
                        </tbody>
                    </table>
                </div>
                @if(count($orders)==0)
                    <div class="text-center p-4">
                        <img class="w-120px mb-3" src="{{asset('public/assets/admin')}}/svg/illustrations/sorry.svg" alt="Image Description">
                        <p class="mb-0">{{ translate('No_data_to_show')}}</p>
                    </div>
                @endif
            </div>

            <div class="card-footer border-0">
                <div class="d-flex justify-content-center justify-content-sm-end">
                    {!! $orders->links() !!}
                </div>
            </div>
        </div>
    </div>


    <div class="modal fade" id="quick-view" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered coupon-details modal-lg" role="document">
            <div class="modal-content" id="quick-view-modal">
            </div>
        </div>
    </div>

    <div class="modal fade" id="denyPaymentModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body pt-5 p-md-5">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
                    <div class="d-flex justify-content-center mb-4">
                        <img width="75" height="75" src="{{asset('public/assets/admin/svg/components/info.svg')}}" class="rounded-circle" alt="">
                    </div>

                    <h3 class="text-start mb-1 fw-medium text-center">{{translate('Are you sure you want to deny?')}}</h3>
                    <p class="text-start fs-12 fw-medium text-muted text-center">{{translate('Please insert the deny note for this payment request')}}</p>
                    <form method="GET" id="denyPaymentForm">
                        <div>
                            <label for="add-your-note" class="d-block mb-2 fz-12px title-clr d-flex align-items-end gap-2">{{translate('Deny Note')}} <span class="text-danger fz-12px">*</span></label>
                            <textarea class="form-control h-69px" placeholder="{{translate('Type here your note')}}" name="denied_note" id="add-your-note" maxlength="255" required></textarea>
                            <div class="d-flex justify-content-center mt-3 gap-3">
                                <button type="button" class="btn btn--reset min-w-120px px-2" data-dismiss="modal" aria-label="Close">{{translate('Cancel')}}</button>
                                <button type="submit" class="btn btn-primary min-w-120px">{{translate('Submit')}}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script_2')

    <script>
        "use strict";

        $('.offline_details').on('click', function() {
            var orderId = $(this).data('id');
            get_offline_payment(orderId);
        });

        function get_offline_payment(id){

            $.ajax({
                type: 'GET',
                url: '{{route('admin.offline-modal-view')}}',
                data: {
                    id: id
                },
                beforeSend: function () {
                    $('#loading').show();
                },
                success: function (data) {
                    $('#loading').hide();
                    $('#quick-view').modal('show');
                    $('#quick-view-modal').empty().html(data.view);
                }
            });
        }

        $(document).on('click', '.deny-payment-btn', function() {
            var orderId = $(this).data('order-id');
            var status = $(this).data('status');
            var url = '{{url('/')}}/admin/orders/verify-offline-payment/'+ orderId + '/' + status;

            $('#denyPaymentForm').attr('action', url);
            $('#denyPaymentModal').modal('show');
        });

    </script>

@endpush
