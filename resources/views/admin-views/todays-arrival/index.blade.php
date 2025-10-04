@extends('layouts.admin.app')

@section('title', translate('Today\'s Arrival Management'))

@section('content')
    <div class="content container-fluid">
        <div class="page-header">
            <h1 class="page-header-title">
                <span class="page-header-icon">
                    <img src="{{asset('public/assets/admin/img/banner.png')}}" class="w--20" alt="{{ translate('todays arrival') }}">
                </span>
                <span>
                    {{translate('Today\'s Arrival Setup')}}
                </span>
            </h1>
        </div>
        
        <div class="card mb-3">
            <div class="card-body">
                <form action="{{route('admin.todays-arrival.store')}}" method="post" enctype="multipart/form-data">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="row g-3">
                                <div class="col-12">
                                    <div class="form-group mb-3">
                                        <label class="input-label" for="title">{{translate('Title')}}</label>
                                        <input type="text" name="title" value="{{old('title')}}" class="form-control" 
                                               placeholder="{{ translate('Today\'s special arrival') }}" maxlength="255" required>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-group mb-3">
                                        <label class="input-label" for="description">{{translate('Description')}}</label>
                                        <textarea name="description" class="form-control" rows="3" 
                                                  placeholder="{{ translate('Brief description about this arrival') }}">{{old('description')}}</textarea>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-group mb-3">
                                        <label class="input-label" for="branch_ids">{{translate('Branches')}} 
                                            <span class="input-label-secondary">*</span></label>
                                        <select name="branch_ids[]" class="form-control js-select2-custom" multiple required>
                                            @foreach($branches as $branch)
                                                <option value="{{$branch['id']}}" {{in_array($branch['id'], old('branch_ids', [])) ? 'selected' : ''}}>
                                                    {{$branch['name']}} ({{$branch['whatsapp_number']}})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-group mb-3">
                                        <label class="input-label" for="product_ids">{{translate('Products')}} 
                                            <span class="input-label-secondary">*</span></label>
                                        <select name="product_ids[]" class="form-control js-select2-custom" multiple required>
                                            @foreach($products as $product)
                                                <option value="{{$product['id']}}" {{in_array($product['id'], old('product_ids', [])) ? 'selected' : ''}}>
                                                    {{$product['name']}}
                                                </option>
                                            @endforeach
                                        </select>
                                        <small class="text-muted">{{translate('Select multiple products for this arrival')}}</small>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-group mb-3">
                                        <label class="input-label" for="arrival_date">{{translate('Arrival Date')}} 
                                            <span class="input-label-secondary">*</span></label>
                                        <input type="date" name="arrival_date" value="{{old('arrival_date', date('Y-m-d'))}}" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-group mb-3">
                                        <label class="input-label" for="whatsapp_message_template">{{translate('WhatsApp Message Template')}}</label>
                                        <textarea name="whatsapp_message_template" class="form-control" rows="3" 
                                                  placeholder="{{ translate('Hi! I\'m interested in {product_name} from today\'s arrival. Is it available?') }}">{{old('whatsapp_message_template', 'Hi! I\'m interested in {product_name} from today\'s arrival. Is it available?')}}</textarea>
                                        <small class="text-muted">{{translate('Use {product_name} to include product name, {branch_name} for branch name')}}</small>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="row">
                                        <div class="col-6">
                                            <div class="form-group mb-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="whatsapp_enabled" id="whatsapp_enabled" 
                                                           {{old('whatsapp_enabled') ? 'checked' : ''}}>
                                                    <label class="form-check-label" for="whatsapp_enabled">
                                                        {{translate('Enable WhatsApp')}}
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="form-group mb-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="show_in_app" id="show_in_app" 
                                                           {{old('show_in_app', true) ? 'checked' : ''}}>
                                                    <label class="form-check-label" for="show_in_app">
                                                        {{translate('Show in App')}}
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
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
                                           accept=".jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*" hidden required>
                                    <img id="mainPosterViewer" src="{{asset('public/assets/admin/img/upload-vertical.png')}}" 
                                         alt="{{ translate('main poster image') }}"/>
                                </label>
                            </div>
                        </div>
                        
                        <!-- Additional Poster Images -->
                        <div class="col-12">
                            <h5 class="text--title">{{translate('Additional Poster Images')}} 
                                <small class="text-muted">({{translate('Optional - Max 5 images')}})</small>
                            </h5>
                            <div class="row" id="posterImagesContainer">
                                <div class="col-md-2 mb-3">
                                    <label class="upload--square">
                                        <input type="file" name="poster_images[]" class="poster-image-input" 
                                               accept=".jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*" hidden>
                                        <img class="poster-image-viewer" src="{{asset('public/assets/admin/img/upload-vertical.png')}}" 
                                             alt="{{ translate('poster image') }}"/>
                                    </label>
                                </div>
                                <div class="col-md-2 mb-3">
                                    <label class="upload--square">
                                        <input type="file" name="poster_images[]" class="poster-image-input" 
                                               accept=".jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*" hidden>
                                        <img class="poster-image-viewer" src="{{asset('public/assets/admin/img/upload-vertical.png')}}" 
                                             alt="{{ translate('poster image') }}"/>
                                    </label>
                                </div>
                                <div class="col-md-2 mb-3">
                                    <label class="upload--square">
                                        <input type="file" name="poster_images[]" class="poster-image-input" 
                                               accept=".jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*" hidden>
                                        <img class="poster-image-viewer" src="{{asset('public/assets/admin/img/upload-vertical.png')}}" 
                                             alt="{{ translate('poster image') }}"/>
                                    </label>
                                </div>
                                <div class="col-md-2 mb-3">
                                    <label class="upload--square">
                                        <input type="file" name="poster_images[]" class="poster-image-input" 
                                               accept=".jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*" hidden>
                                        <img class="poster-image-viewer" src="{{asset('public/assets/admin/img/upload-vertical.png')}}" 
                                             alt="{{ translate('poster image') }}"/>
                                    </label>
                                </div>
                                <div class="col-md-2 mb-3">
                                    <label class="upload--square">
                                        <input type="file" name="poster_images[]" class="poster-image-input" 
                                               accept=".jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*" hidden>
                                        <img class="poster-image-viewer" src="{{asset('public/assets/admin/img/upload-vertical.png')}}" 
                                             alt="{{ translate('poster image') }}"/>
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-12">
                            <div class="btn--container justify-content-end">
                                <button type="reset" class="btn btn--reset">{{translate('reset')}}</button>
                                <button type="submit" class="btn btn--primary">{{translate('submit')}}</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header border-0">
                <div class="card--header justify-content-between max--sm-grow">
                    <h5 class="card-title">{{translate('Today\'s Arrival List')}} 
                        <span class="badge badge-soft-secondary">{{ $arrivals->total() }}</span>
                    </h5>
                    <form action="{{url()->current()}}" method="GET">
                        <div class="input-group">
                            <input type="search" name="search" class="form-control"
                                   placeholder="{{translate('Search by title or product')}}" aria-label="Search"
                                   value="{{$search}}" required autocomplete="off">
                            <div class="input-group-append">
                                <button type="submit" class="input-group-text">
                                    {{translate('search')}}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="table-responsive datatable-custom">
                <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                    <thead class="thead-light">
                    <tr>
                        <th class="border-0">{{translate('#')}}</th>
                        <th class="border-0">{{translate('Main Poster')}}</th>
                        <th class="border-0">{{translate('Title')}}</th>
                        <th class="border-0">{{translate('Product')}}</th>
                        <th class="border-0">{{translate('Branch')}}</th>
                        <th class="border-0">{{translate('WhatsApp')}}</th>
                        <th class="text-center border-0">{{translate('Status')}}</th>
                        <th class="text-center border-0">{{translate('Action')}}</th>
                    </tr>
                    </thead>

                    <tbody>
                    @foreach($arrivals as $key=>$arrival)
                        <tr>
                            <td>{{$key+1}}</td>
                            <td>
                                <div>
                                    <img class="img-vertical-150"
                                         src="{{$arrival->main_poster ? asset('storage/app/public/arrivals/' . $arrival->main_poster) : asset('public/assets/admin/img/no-image.jpg')}}"
                                         alt="{{ translate('main poster') }}"
                                    >
                                </div>
                            </td>
                            <td>
                                <span class="d-block font-size-sm text-body text-trim-25">
                                    {{$arrival['title']}}
                                </span>
                                @if($arrival->poster_images && count($arrival->poster_images) > 0)
                                    <small class="badge badge-soft-info">
                                        +{{count($arrival->poster_images)}} {{translate('images')}}
                                    </small>
                                @endif
                            </td>
                            <td>
                                <span class="d-block font-size-sm text-body">
                                    @if($arrival->product_ids && count($arrival->product_ids) > 0)
                                        @foreach($arrival->products() as $product)
                                            <span class="badge badge-soft-primary">{{$product->name}}</span>
                                        @endforeach
                                    @else
                                        {{translate('No products')}}
                                    @endif
                                </span>
                            </td>
                            <td>
                                <span class="d-block font-size-sm text-body">
                                    @if($arrival->branch_id && count($arrival->branch_id) > 0)
                                        @foreach($arrival->branches() as $branch)
                                            <div>{{$branch->name}}</div>
                                            <small class="text-muted">{{$branch->whatsapp_number}}</small>
                                        @endforeach
                                    @else
                                        {{translate('No branches')}}
                                    @endif
                                </span>
                            </td>
                            <td>
                                @if($arrival->branch_id && count($arrival->branch_id) > 0)
                                    @foreach($arrival->branches() as $branch)
                                        <a href="https://wa.me/{{$branch->whatsapp_number}}?text={{urlencode($arrival->formatted_whatsapp_message)}}" 
                                           target="_blank" class="btn btn-sm btn-outline-success mb-1">
                                            <i class="tio-chat"></i> {{$branch->name}}
                                        </a>
                                    @endforeach
                                @else
                                    <span class="text-muted">{{translate('No branches')}}</span>
                                @endif
                            </td>
                            <td>
                                <label class="toggle-switch my-0">
                                    <input type="checkbox"
                                           class="toggle-switch-input status-change-alert" 
                                           id="arrivalCheckbox{{ $arrival->id }}"
                                           data-route="{{ route('admin.todays-arrival.status', [$arrival->id, $arrival->is_active ? 0 : 1]) }}"
                                           data-message="{{ $arrival->is_active ? translate('you_want_to_disable_this_arrival'): translate('you_want_to_active_this_arrival') }}"
                                        {{ $arrival->is_active ? 'checked' : '' }}>
                                    <span class="toggle-switch-label mx-auto text">
                                        <span class="toggle-switch-indicator"></span>
                                    </span>
                                </label>
                            </td>
                            <td>
                                <div class="btn--container justify-content-center">
                                    <a class="action-btn btn--info btn-outline-info" 
                                       href="{{route('admin.todays-arrival.preview', $arrival->id)}}" 
                                       title="{{translate('Preview Posters')}}">
                                        <i class="tio-visible"></i></a>
                                    <a class="action-btn"
                                       href="{{route('admin.todays-arrival.edit',[$arrival['id']])}}">
                                        <i class="tio-edit"></i></a>
                                    <a class="action-btn btn--danger btn-outline-danger form-alert" href="javascript:"
                                       data-id="arrival-{{$arrival['id']}}"
                                       data-message="{{ translate("Want to delete this arrival") }}">
                                        <i class="tio-delete-outlined"></i>
                                    </a>
                                </div>
                                <form action="{{route('admin.todays-arrival.delete',[$arrival['id']])}}"
                                      method="post" id="arrival-{{$arrival['id']}}">
                                    @csrf @method('delete')
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                <table>
                    <tfoot>
                    {!! $arrivals->links() !!}
                    </tfoot>
                </table>
            </div>
            
            @if(count($arrivals) == 0)
                <div class="text-center p-4">
                    <img class="w-120px mb-3" src="{{asset('/public/assets/admin/svg/illustrations/sorry.svg')}}" 
                         alt="{{ translate('image') }}">
                    <p class="mb-0">{{translate('No arrivals to show')}}</p>
                </div>
            @endif
        </div>
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

            $('.status-change-alert').on('change', function () {
                let route = $(this).data('route');
                let message = $(this).data('message');
                status_change_alert(route, message, event);
            });

            $('.form-alert').on('click', function () {
                let id = $(this).data('id');
                let message = $(this).data('message');
                form_alert(id, message);
            });
        });
    </script>
@endpush