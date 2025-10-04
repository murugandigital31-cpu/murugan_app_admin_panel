@extends('layouts.admin.app')

@section('title', translate('Edit Today\'s Arrival'))

@section('content')
    <div class="content container-fluid">
        <div class="page-header">
            <h1 class="page-header-title">
                <span class="page-header-icon">
                    <img src="{{asset('public/assets/admin/img/banner.png')}}" class="w--20" alt="{{ translate('todays arrival') }}">
                </span>
                <span>
                    {{translate('Edit Today\'s Arrival')}}
                </span>
            </h1>
        </div>
        
        <div class="card">
            <div class="card-body">
                <form action="{{route('admin.todays-arrival.update', $arrival->id)}}" method="post" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="row g-3">
                                <div class="col-12">
                                    <div class="form-group mb-3">
                                        <label class="input-label" for="title">{{translate('Title')}}</label>
                                        <input type="text" name="title" value="{{old('title', $arrival->title)}}" 
                                               class="form-control" placeholder="{{ translate('Today\'s special arrival') }}" 
                                               maxlength="255" required>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-group mb-3">
                                        <label class="input-label" for="branch_id">{{translate('Branch')}} 
                                            <span class="input-label-secondary">*</span></label>
                                        <select name="branch_id" class="form-control js-select2-custom" required>
                                            <option value="">{{translate('Select Branch')}}</option>
                                            @foreach($branches as $branch)
                                                <option value="{{$branch['id']}}" 
                                                    {{(old('branch_id', $arrival->branch_id) == $branch['id']) ? 'selected' : ''}}>
                                                    {{$branch['name']}} ({{$branch['whatsapp_number']}})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-group mb-3">
                                        <label class="input-label" for="product_id">{{translate('Product')}} 
                                            <span class="input-label-secondary">*</span></label>
                                        <select name="product_id" class="form-control js-select2-custom" required>
                                            <option value="">{{translate('Select Product')}}</option>
                                            @foreach($products as $product)
                                                <option value="{{$product['id']}}" 
                                                    {{(old('product_id', $arrival->product_id) == $product['id']) ? 'selected' : ''}}>
                                                    {{$product['name']}}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-group mb-3">
                                        <label class="input-label" for="whatsapp_message_template">{{translate('WhatsApp Message Template')}}</label>
                                        <textarea name="whatsapp_message_template" class="form-control" rows="3" 
                                                  placeholder="{{ translate('Hi! I\'m interested in {product_name} from today\'s arrival. Is it available?') }}">{{old('whatsapp_message_template', $arrival->whatsapp_message_template ?? 'Hi! I\'m interested in {product_name} from today\'s arrival. Is it available?')}}</textarea>
                                        <small class="text-muted">{{translate('Use {product_name} to include product name, {branch_name} for branch name')}}</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex flex-column justify-content-center h-100">
                                <h5 class="text-center mb-3 text--title text-capitalize">
                                    {{translate('Main Poster Image')}}
                                    <small class="text-danger">* ( {{translate('ratio')}} 16:9 )</small>
                                </h5>
                                <label class="upload--vertical">
                                    <input type="file" name="main_poster" id="mainPoster" class="" 
                                           accept=".jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*" hidden>
                                    <img id="mainPosterViewer" src="{{$arrival->main_poster_url}}" 
                                         alt="{{ translate('main poster image') }}"/>
                                </label>
                            </div>
                        </div>
                        
                        <!-- Current Additional Poster Images -->
                        @if($arrival->poster_images && count($arrival->poster_images) > 0)
                        <div class="col-12">
                            <h5 class="text--title">{{translate('Current Additional Images')}}</h5>
                            <div class="row">
                                @foreach($arrival->poster_images as $index => $image)
                                <div class="col-md-2 mb-3" id="currentImage{{$index}}">
                                    <div class="position-relative">
                                        <img src="{{asset('storage/app/public/todays-arrival/poster/' . $image)}}" 
                                             class="img-fluid rounded" style="height: 100px; width: 100%; object-fit: cover;" 
                                             alt="{{translate('poster image')}}">
                                        <button type="button" class="btn btn-sm btn-danger position-absolute" 
                                                style="top: 5px; right: 5px;" 
                                                onclick="removeCurrentImage({{$index}}, '{{$image}}')">
                                            <i class="tio-delete-outlined"></i>
                                        </button>
                                    </div>
                                    <input type="hidden" name="existing_images[]" value="{{$image}}" id="existing{{$index}}">
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif
                        
                        <!-- New Additional Poster Images -->
                        <div class="col-12">
                            <h5 class="text--title">{{translate('Add New Poster Images')}} 
                                <small class="text-muted">({{translate('Optional - Max 5 total images')}})</small>
                            </h5>
                            <div class="row" id="posterImagesContainer">
                                <div class="col-md-2 mb-3">
                                    <label class="upload--square">
                                        <input type="file" name="poster_images[]" class="poster-image-input" 
                                               accept=".jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*" hidden>
                                        <img class="poster-image-viewer" src="{{asset('public/assets/admin/img/upload-square.png')}}" 
                                             alt="{{ translate('poster image') }}"/>
                                    </label>
                                </div>
                                <div class="col-md-2 mb-3">
                                    <label class="upload--square">
                                        <input type="file" name="poster_images[]" class="poster-image-input" 
                                               accept=".jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*" hidden>
                                        <img class="poster-image-viewer" src="{{asset('public/assets/admin/img/upload-square.png')}}" 
                                             alt="{{ translate('poster image') }}"/>
                                    </label>
                                </div>
                                <div class="col-md-2 mb-3">
                                    <label class="upload--square">
                                        <input type="file" name="poster_images[]" class="poster-image-input" 
                                               accept=".jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*" hidden>
                                        <img class="poster-image-viewer" src="{{asset('public/assets/admin/img/upload-square.png')}}" 
                                             alt="{{ translate('poster image') }}"/>
                                    </label>
                                </div>
                                <div class="col-md-2 mb-3">
                                    <label class="upload--square">
                                        <input type="file" name="poster_images[]" class="poster-image-input" 
                                               accept=".jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*" hidden>
                                        <img class="poster-image-viewer" src="{{asset('public/assets/admin/img/upload-square.png')}}" 
                                             alt="{{ translate('poster image') }}"/>
                                    </label>
                                </div>
                                <div class="col-md-2 mb-3">
                                    <label class="upload--square">
                                        <input type="file" name="poster_images[]" class="poster-image-input" 
                                               accept=".jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*" hidden>
                                        <img class="poster-image-viewer" src="{{asset('public/assets/admin/img/upload-square.png')}}" 
                                             alt="{{ translate('poster image') }}"/>
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-12">
                            <div class="btn--container justify-content-end">
                                <a href="{{route('admin.todays-arrival.add-new')}}" 
                                   class="btn btn--reset">{{translate('back')}}</a>
                                <button type="submit" class="btn btn--primary">{{translate('update')}}</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Test WhatsApp Section -->
        @if($arrival->branch)
        <div class="card mt-3">
            <div class="card-header">
                <h5 class="card-title mb-0">{{translate('Test WhatsApp Integration')}}</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <p><strong>{{translate('Branch')}}:</strong> {{$arrival->branch->name}}</p>
                        <p><strong>{{translate('WhatsApp Number')}}:</strong> {{$arrival->branch->whatsapp_number}}</p>
                        <p><strong>{{translate('Message Template')}}:</strong></p>
                        <div class="bg-light p-3 rounded">
                            {{$arrival->formatted_whatsapp_message}}
                        </div>
                    </div>
                    <div class="col-md-4">
                        <a href="https://wa.me/{{$arrival->branch->formatted_whatsapp_number}}?text={{urlencode($arrival->formatted_whatsapp_message)}}" 
                           target="_blank" class="btn btn-success btn-lg">
                            <i class="tio-chat"></i> {{translate('Test WhatsApp')}}
                        </a>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
@endsection

@push('script_2')
    <script>
        $(document).ready(function () {
            // Main poster image preview
            $('#mainPoster').change(function() {
                readURL(this, '#mainPosterViewer');
            });

            // Additional poster images preview
            $('.poster-image-input').change(function() {
                let viewer = $(this).siblings('.poster-image-viewer');
                readURL(this, viewer);
            });

            function readURL(input, target) {
                if (input.files && input.files[0]) {
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        $(target).attr('src', e.target.result);
                    }
                    reader.readAsDataURL(input.files[0]);
                }
            }
        });

        function removeCurrentImage(index, filename) {
            if (confirm('{{translate("Are you sure you want to remove this image?")}}')) {
                $('#currentImage' + index).remove();
                $('#existing' + index).remove();
            }
        }
    </script>
@endpush