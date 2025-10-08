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
                                        <label class="input-label" for="arrival_branch_id">{{translate('Branch')}} 
                                            <span class="input-label-secondary">*</span></label>
                                        <select name="arrival_branch_id" class="form-control" required>
                                            <option value="">{{translate('Select Branch')}}</option>
                                            @foreach($branches as $branch)
                                                <option value="{{$branch['id']}}" {{old('arrival_branch_id') == $branch['id'] ? 'selected' : ''}}>
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
                                                        {{in_array($product['id'], old('product_ids', [])) ? 'selected' : ''}}>
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
                        <th class="border-0">{{translate('SL')}}</th>
                        <th class="border-0">{{translate('Main Poster')}}</th>
                        <th class="border-0">{{translate('Title')}}</th>
                        <th class="border-0">{{translate('Product')}}</th>
                        <th class="border-0">{{translate('Branch')}}</th>
                        <th class="border-0">{{translate('Date')}}</th>
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
                                         src="{{$arrival->main_poster_url ?: asset('public/assets/admin/img/no-image.jpg')}}"
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
                                    @if($arrival->arrivalBranch)
                                        <div>{{$arrival->arrivalBranch->name}}</div>
                                        <small class="text-muted">{{$arrival->arrivalBranch->whatsapp_number}}</small>
                                    @else
                                        {{translate('No branch')}}
                                    @endif
                                </span>
                            </td>
                            <td>
                                <span class="d-block font-size-sm text-body">
                                    @if($arrival->arrival_date)
                                        <div class="font-weight-medium">
                                            {{ \Carbon\Carbon::parse($arrival->arrival_date)->format('d M Y') }}
                                        </div>
                                        <small class="text-muted">
                                            {{ \Carbon\Carbon::parse($arrival->arrival_date)->diffForHumans() }}
                                        </small>
                                    @else
                                        <span class="text-muted">{{translate('No date')}}</span>
                                    @endif
                                </span>
                            </td>
                            <td>
                                @if($arrival->arrivalBranch)
                                    <a href="https://wa.me/{{$arrival->arrivalBranch->whatsapp_number}}?text={{urlencode($arrival->formatted_whatsapp_message)}}"
                                       target="_blank" class="btn btn-sm btn-outline-success mb-1">
                                        <i class="tio-chat"></i> {{$arrival->arrivalBranch->name}}
                                    </a>
                                @else
                                    <span class="text-muted">{{translate('No branch')}}</span>
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
                        <select id="bulkAddSelect" class="form-control js-select2-custom" multiple>
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
                    <button type="button" class="btn btn-success" onclick="bulkAddProducts()">
                        <i class="tio-add"></i> {{translate('Add Products')}}
                    </button>
                </div>
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
                <div class="modal-body">
                    <div class="form-group">
                        <label>{{translate('Select Products to Remove')}}</label>
                        <select id="bulkRemoveSelect" class="form-control js-select2-custom" multiple>
                            <!-- Will be populated dynamically with currently selected products -->
                        </select>
                        <small class="text-muted">{{translate('Select multiple products to remove at once')}}</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{translate('Cancel')}}</button>
                    <button type="button" class="btn btn-danger" onclick="bulkRemoveProducts()">
                        <i class="tio-delete"></i> {{translate('Remove Products')}}
                    </button>
                </div>
            </div>
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

            // Destroy Select2 first
            $productSelect.select2('destroy');

            // Get all options
            const $options = $productSelect.find('option');

            if (!categoryId) {
                // Show all products
                $options.prop('disabled', false).show();
            } else {
                // Hide/show products based on category
                $options.each(function() {
                    const $option = $(this);
                    const productCategories = $option.data('category');

                    // Check if product belongs to selected category
                    if (productCategories && productCategories.toString().includes(categoryId)) {
                        $option.prop('disabled', false).show();
                    } else {
                        $option.prop('disabled', true).hide();
                    }
                });
            }

            // Reinitialize Select2
            $productSelect.select2({
                placeholder: 'Select multiple products for this arrival',
                allowClear: true
            });
        }

        function filterBulkAddProductsByCategory(categoryId) {
            const $bulkAddSelect = $('#bulkAddSelect');

            // Destroy Select2 first
            $bulkAddSelect.select2('destroy');

            // Get all options
            const $options = $bulkAddSelect.find('option');

            if (!categoryId) {
                // Show all products
                $options.prop('disabled', false).show();
            } else {
                // Hide/show products based on category
                $options.each(function() {
                    const $option = $(this);
                    const productCategories = $option.data('category');

                    // Check if product belongs to selected category
                    if (productCategories && productCategories.toString().includes(categoryId)) {
                        $option.prop('disabled', false).show();
                    } else {
                        $option.prop('disabled', true).hide();
                    }
                });
            }

            // Reinitialize Select2
            $bulkAddSelect.select2({
                placeholder: 'Select multiple products to add at once',
                allowClear: true
            });
        }

        function showBulkAddModal() {
            // Initialize Select2 for bulk add modal
            $('#bulkAddSelect').select2({
                dropdownParent: $('#bulkAddModal'),
                placeholder: '{{translate("Select products to add")}}',
                allowClear: true
            });
            $('#bulkAddModal').modal('show');
        }

        function showBulkRemoveModal() {
            // Get currently selected products
            const selectedProducts = $('#productSelect').val() || [];

            if (selectedProducts.length === 0) {
                toastr.warning('{{translate("No products selected to remove")}}');
                return;
            }

            // Populate bulk remove select with currently selected products
            const $bulkRemoveSelect = $('#bulkRemoveSelect');
            $bulkRemoveSelect.empty();

            selectedProducts.forEach(function(productId) {
                const productOption = $('#productSelect option[value="' + productId + '"]');
                const productName = productOption.text();
                $bulkRemoveSelect.append(new Option(productName, productId, false, false));
            });

            // Initialize Select2 for bulk remove modal
            $bulkRemoveSelect.select2({
                dropdownParent: $('#bulkRemoveModal'),
                placeholder: '{{translate("Select products to remove")}}',
                allowClear: true
            });

            $('#bulkRemoveModal').modal('show');
        }

        function bulkAddProducts() {
            const productsToAdd = $('#bulkAddSelect').val() || [];

            if (productsToAdd.length === 0) {
                toastr.warning('{{translate("Please select at least one product to add")}}');
                return;
            }

            // Get currently selected products
            let currentlySelected = $('#productSelect').val() || [];

            // Add new products (avoid duplicates)
            productsToAdd.forEach(function(productId) {
                if (!currentlySelected.includes(productId)) {
                    currentlySelected.push(productId);
                }
            });

            // Update the main select
            $('#productSelect').val(currentlySelected).trigger('change');

            // Close modal and show success message
            $('#bulkAddModal').modal('hide');
            toastr.success('{{translate("Products added successfully")}}');

            // Clear bulk add selection
            $('#bulkAddSelect').val(null).trigger('change');
        }

        function bulkRemoveProducts() {
            const productsToRemove = $('#bulkRemoveSelect').val() || [];

            if (productsToRemove.length === 0) {
                toastr.warning('{{translate("Please select at least one product to remove")}}');
                return;
            }

            // Get currently selected products
            let currentlySelected = $('#productSelect').val() || [];

            // Remove selected products
            currentlySelected = currentlySelected.filter(function(productId) {
                return !productsToRemove.includes(productId);
            });

            // Update the main select
            $('#productSelect').val(currentlySelected).trigger('change');

            // Close modal and show success message
            $('#bulkRemoveModal').modal('hide');
            toastr.success('{{translate("Products removed successfully")}}');

            // Clear bulk remove selection
            $('#bulkRemoveSelect').val(null).trigger('change');
        }
    </script>
@endpush