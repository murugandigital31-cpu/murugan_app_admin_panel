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
                                        <label class="input-label" for="arrival_branch_id">{{translate('Branch')}} 
                                            <span class="input-label-secondary">*</span></label>
                                        
                                        <select name="arrival_branch_id" class="form-control" required>
                                            <option value="">{{translate('Select Branch')}}</option>
                                            @foreach($branches as $branch)
                                                <option value="{{$branch['id']}}" {{old('arrival_branch_id', $arrival->arrival_branch_id) == $branch['id'] ? 'selected' : ''}}>
                                                    {{$branch['name']}} @if($branch['whatsapp_number'])({{$branch['whatsapp_number']}})@endif
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-group mb-3">
                                        <label class="input-label" for="category_filter">{{translate('Filter by Category')}}
                                            <span class="text-muted">({{translate('Optional')}})</span></label>
                                        <select id="categoryFilter" class="form-control">
                                            <option value="">{{translate('All Categories')}}</option>
                                            @foreach($categories as $category)
                                                <option value="{{$category['id']}}">{{$category['name']}}</option>
                                            @endforeach
                                        </select>
                                        <small class="text-muted">{{translate('Filter products by category for easier selection')}}</small>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-group mb-3">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <label class="input-label mb-0" for="product_ids">{{translate('Products')}}
                                                <span class="input-label-secondary">*</span>
                                                <small class="text-muted">({{translate('Currently selected')}}: <span id="selectedCount">0</span>)</small>
                                            </label>
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-sm btn-success" onclick="showBulkAddModal()">
                                                    <i class="tio-add"></i> {{translate('Bulk Add')}}
                                                </button>
                                                <button type="button" class="btn btn-sm btn-danger" onclick="showBulkRemoveModal()">
                                                    <i class="tio-delete"></i> {{translate('Bulk Remove')}}
                                                </button>
                                            </div>
                                        </div>
                                        <select name="product_ids[]" id="productSelect" class="form-control js-select2-custom" multiple required>
                                            @foreach($products as $product)
                                                @php
                                                    $selectedProducts = is_array($arrival->product_ids) ? $arrival->product_ids :
                                                                       (is_string($arrival->product_ids) ? json_decode($arrival->product_ids, true) : []);
                                                    $isSelected = is_array($selectedProducts) && in_array($product['id'], $selectedProducts);

                                                    $categoryIds = '';
                                                    if (isset($product['category_ids'])) {
                                                        if (is_array($product['category_ids'])) {
                                                            $categoryIds = implode(',', array_column($product['category_ids'], 'id'));
                                                        } elseif (is_string($product['category_ids'])) {
                                                            $decoded = json_decode($product['category_ids'], true);
                                                            if (is_array($decoded)) {
                                                                $categoryIds = implode(',', array_column($decoded, 'id'));
                                                            }
                                                        }
                                                    }
                                                @endphp
                                                <option value="{{$product['id']}}"
                                                        data-category="{{$categoryIds}}"
                                                        {{$isSelected ? 'selected' : ''}}>
                                                    {{$product['name']}}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-group mb-3">
                                        <label class="input-label" for="arrival_date">{{translate('Arrival Date')}} 
                                            <span class="input-label-secondary">*</span></label>
                                        <input type="date" name="arrival_date" value="{{old('arrival_date', $arrival->arrival_date ? $arrival->arrival_date->format('Y-m-d') : '')}}" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-group mb-3">
                                        <label class="input-label" for="description">{{translate('Description')}}</label>
                                        <textarea name="description" class="form-control" rows="3" 
                                                  placeholder="{{ translate('Brief description about this arrival') }}">{{old('description', $arrival->description)}}</textarea>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-group mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="show_in_app" id="show_in_app" 
                                                   {{old('show_in_app', $arrival->show_in_app) ? 'checked' : ''}}>
                                            <label class="form-check-label" for="show_in_app">
                                                {{translate('Show in App')}}
                                            </label>
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
                                           accept=".jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*" hidden>
                                    <img id="mainPosterViewer" 
                                         src="{{$arrival->main_poster_url ?: asset('public/assets/admin/img/upload-vertical.png')}}" 
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
                                        <img src="{{asset('uploads/arrivals/' . $image)}}" 
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

<!-- Bulk Add Products Modal -->
<div class="modal fade" id="bulkAddModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{translate('Bulk Add Products')}}</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form action="{{route('admin.todays-arrival.bulk-add', $arrival->id)}}" method="post">
                @csrf
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label>{{translate('Filter by Category')}}</label>
                        <select id="bulkAddCategoryFilter" class="form-control">
                            <option value="">{{translate('All Categories')}}</option>
                            @foreach($categories as $category)
                                <option value="{{$category['id']}}">{{$category['name']}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>{{translate('Select Products to Add')}}</label>
                        <select name="product_ids[]" id="bulkAddSelect" class="form-control js-select2-custom" multiple required>
                            @foreach($products as $product)
                                @php
                                    $categoryIds = '';
                                    if (isset($product['category_ids'])) {
                                        if (is_array($product['category_ids'])) {
                                            $categoryIds = implode(',', array_column($product['category_ids'], 'id'));
                                        } elseif (is_string($product['category_ids'])) {
                                            $decoded = json_decode($product['category_ids'], true);
                                            if (is_array($decoded)) {
                                                $categoryIds = implode(',', array_column($decoded, 'id'));
                                            }
                                        }
                                    }
                                @endphp
                                <option value="{{$product['id']}}" data-category="{{$categoryIds}}">
                                    {{$product['name']}}
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">{{translate('Select multiple products to add at once')}}</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{translate('Cancel')}}</button>
                    <button type="submit" class="btn btn-success">
                        <i class="tio-add"></i> {{translate('Add Products')}}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bulk Remove Products Modal -->
<div class="modal fade" id="bulkRemoveModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{translate('Bulk Remove Products')}}</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form action="{{route('admin.todays-arrival.bulk-remove', $arrival->id)}}" method="post">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label>{{translate('Select Products to Remove')}}</label>
                        <select name="product_ids[]" id="removeProductSelect" class="form-control js-select2-custom" multiple required>
                            @foreach($products as $product)
                                @php
                                    $selectedProducts = is_array($arrival->product_ids) ? $arrival->product_ids :
                                                       (is_string($arrival->product_ids) ? json_decode($arrival->product_ids, true) : []);
                                    $isSelected = is_array($selectedProducts) && in_array($product['id'], $selectedProducts);
                                @endphp
                                @if($isSelected)
                                    <option value="{{$product['id']}}">{{$product['name']}}</option>
                                @endif
                            @endforeach
                        </select>
                        <small class="text-muted">{{translate('Select multiple products to remove at once')}}</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{translate('Cancel')}}</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="tio-delete"></i> {{translate('Remove Products')}}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

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

            // Update selected count
            updateSelectedCount();
            $('#productSelect').on('change', function() {
                updateSelectedCount();
            });

            // Category filter functionality
            $('#categoryFilter').on('change', function() {
                filterProductsByCategory($(this).val());
            });

            // Bulk add modal category filter
            $('#bulkAddCategoryFilter').on('change', function() {
                filterBulkAddProductsByCategory($(this).val());
            });
        });

        function updateSelectedCount() {
            const count = $('#productSelect').val() ? $('#productSelect').val().length : 0;
            $('#selectedCount').text(count);
        }

        function filterProductsByCategory(categoryId) {
            const $productSelect = $('#productSelect');
            const $options = $productSelect.find('option');

            if (!categoryId) {
                // Show all products
                $options.show();
            } else {
                // Hide/show products based on category
                $options.each(function() {
                    const $option = $(this);
                    const productCategories = $option.data('category');

                    // Check if product belongs to selected category
                    if (productCategories && productCategories.toString().includes(categoryId)) {
                        $option.show();
                    } else {
                        $option.hide();
                    }
                });
            }

            // Refresh Select2 to show filtered options
            $productSelect.trigger('change.select2');
        }

        function filterBulkAddProductsByCategory(categoryId) {
            const $bulkAddSelect = $('#bulkAddSelect');
            const $options = $bulkAddSelect.find('option');

            if (!categoryId) {
                // Show all products
                $options.show();
            } else {
                // Hide/show products based on category
                $options.each(function() {
                    const $option = $(this);
                    const productCategories = $option.data('category');

                    // Check if product belongs to selected category
                    if (productCategories && productCategories.toString().includes(categoryId)) {
                        $option.show();
                    } else {
                        $option.hide();
                    }
                });
            }

            // Refresh Select2 to show filtered options
            $bulkAddSelect.trigger('change.select2');
        }

        function removeCurrentImage(index, filename) {
            if (confirm('{{translate("Are you sure you want to remove this image?")}}')) {
                $('#currentImage' + index).remove();
                $('#existing' + index).remove();
            }
        }

        function showBulkAddModal() {
            $('#bulkAddModal').modal('show');
        }

        function showBulkRemoveModal() {
            $('#bulkRemoveModal').modal('show');
        }
    </script>
@endpush