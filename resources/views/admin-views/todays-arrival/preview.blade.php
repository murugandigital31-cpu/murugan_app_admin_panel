@extends('layouts.admin.app')

@section('title', translate('Preview Today\'s Arrival Posters'))

@section('content')
    <div class="content container-fluid">
        <div class="page-header">
            <h1 class="page-header-title">
                <span class="page-header-icon">
                    <img src="{{asset('public/assets/admin/img/banner.png')}}" class="w--20" alt="{{ translate('todays arrival') }}">
                </span>
                <span>
                    {{translate('Preview Posters')}} - {{$arrival->title}}
                </span>
            </h1>
        </div>
        
        <!-- Arrival Info -->
        <div class="card mb-3">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h5>{{translate('Arrival Details')}}</h5>
                        <p><strong>{{translate('Title')}}:</strong> {{$arrival->title}}</p>
                        <p><strong>{{translate('Product')}}:</strong> {{$arrival->product->name ?? translate('N/A')}}</p>
                        <p><strong>{{translate('Branch')}}:</strong> {{$arrival->branch->name ?? translate('N/A')}}</p>
                        @if($arrival->branch)
                            <p><strong>{{translate('WhatsApp')}}:</strong> {{$arrival->branch->whatsapp_number}}</p>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <h5>{{translate('WhatsApp Message Template')}}</h5>
                        <div class="bg-light p-3 rounded">
                            {{$arrival->formatted_whatsapp_message}}
                        </div>
                        @if($arrival->branch)
                            <div class="mt-2">
                                <a href="https://wa.me/{{$arrival->branch->formatted_whatsapp_number}}?text={{urlencode($arrival->formatted_whatsapp_message)}}" 
                                   target="_blank" class="btn btn-success">
                                    <i class="tio-chat"></i> {{translate('Test WhatsApp')}}
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Poster -->
        <div class="card mb-3">
            <div class="card-header">
                <h5 class="card-title mb-0">{{translate('Main Poster')}}</h5>
            </div>
            <div class="card-body text-center">
                <img src="{{$arrival->main_poster_url}}" class="img-fluid rounded" style="max-height: 400px;" 
                     alt="{{translate('Main Poster')}}" data-toggle="modal" data-target="#imageModal" 
                     data-src="{{$arrival->main_poster_url}}" style="cursor: pointer;">
            </div>
        </div>

        <!-- Additional Posters -->
        @if($arrival->poster_images && count($arrival->poster_images) > 0)
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">{{translate('Additional Posters')}} 
                    <span class="badge badge-soft-info">{{count($arrival->poster_images)}} {{translate('images')}}</span>
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    @foreach($arrival->poster_images as $index => $image)
                        <div class="col-md-3 mb-3">
                            <div class="border rounded p-2">
                                <img src="{{asset('storage/app/public/todays-arrival/poster/' . $image)}}" 
                                     class="img-fluid rounded" style="height: 200px; width: 100%; object-fit: cover; cursor: pointer;" 
                                     alt="{{translate('Poster')}} {{$index + 1}}"
                                     data-toggle="modal" data-target="#imageModal" 
                                     data-src="{{asset('storage/app/public/todays-arrival/poster/' . $image)}}">
                                <p class="text-center mt-2 mb-0">
                                    <small>{{translate('Poster')}} {{$index + 1}}</small>
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        <!-- Mobile Preview Simulation -->
        <div class="card mt-3">
            <div class="card-header">
                <h5 class="card-title mb-0">{{translate('Mobile App Preview Simulation')}}</h5>
            </div>
            <div class="card-body">
                <div class="row justify-content-center">
                    <div class="col-md-4">
                        <div class="border rounded p-3" style="background: #f8f9fa;">
                            <h6 class="text-center mb-3">{{translate('Flutter App Preview')}}</h6>
                            
                            <!-- Simulated mobile card -->
                            <div class="card" style="border-radius: 15px; box-shadow: 0 4px 8px rgba(0,0,0,0.1);">
                                <div class="card-body p-3">
                                    <h6 class="mb-2">{{$arrival->title}}</h6>
                                    
                                    <!-- Main poster -->
                                    <img src="{{$arrival->main_poster_url}}" class="img-fluid rounded mb-2" 
                                         style="height: 150px; width: 100%; object-fit: cover;" 
                                         alt="{{translate('Main Poster')}}">
                                    
                                    <!-- Product info -->
                                    <p class="mb-2"><small><strong>{{translate('Product')}}:</strong> {{$arrival->product->name ?? translate('N/A')}}</small></p>
                                    
                                    <!-- Additional posters preview -->
                                    @if($arrival->poster_images && count($arrival->poster_images) > 0)
                                    <div class="d-flex flex-wrap mb-2">
                                        @foreach(array_slice($arrival->poster_images, 0, 3) as $image)
                                            <img src="{{asset('storage/app/public/todays-arrival/poster/' . $image)}}" 
                                                 class="rounded mr-1 mb-1" style="height: 40px; width: 40px; object-fit: cover;" 
                                                 alt="{{translate('Poster')}}">
                                        @endforeach
                                        @if(count($arrival->poster_images) > 3)
                                            <div class="d-flex align-items-center justify-content-center rounded mr-1" 
                                                 style="height: 40px; width: 40px; background: #e9ecef; font-size: 12px;">
                                                +{{count($arrival->poster_images) - 3}}
                                            </div>
                                        @endif
                                    </div>
                                    @endif
                                    
                                    <!-- Simulated WhatsApp button -->
                                    <button class="btn btn-success btn-sm btn-block" style="border-radius: 20px;">
                                        <i class="tio-chat"></i> {{translate('WhatsApp Checkout')}}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Back button -->
        <div class="text-center mt-3">
            <a href="{{route('admin.todays-arrival.add-new')}}" class="btn btn-secondary">
                <i class="tio-back-ui"></i> {{translate('Back to List')}}
            </a>
        </div>
    </div>

    <!-- Image Modal -->
    <div class="modal fade" id="imageModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{translate('Poster Preview')}}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body text-center">
                    <img id="modalImage" src="" class="img-fluid" alt="{{translate('Poster')}}">
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script_2')
    <script>
        $(document).ready(function() {
            // Handle image modal
            $('[data-toggle="modal"]').on('click', function() {
                let src = $(this).data('src');
                $('#modalImage').attr('src', src);
            });
        });
    </script>
@endpush