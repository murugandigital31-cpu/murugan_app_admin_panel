@extends('layouts.admin.app')

@section('title', translate('Unit Management'))

@section('content')
<div class="content container-fluid">
    <!-- Page Header -->
    <div class="page-header">
        <h1 class="page-header-title">
            <span class="page-header-icon">
                <i class="tio-category"></i>
            </span>
            <span>
                {{translate('Product Unit Management')}}
            </span>
        </h1>
    </div>
    <!-- End Page Header -->

    <div class="row g-2">
        <div class="col-sm-12 col-lg-12 mb-3 mb-lg-2">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">
                        <span class="card-header-icon">
                            <i class="tio-add-circle"></i>
                        </span>
                        <span>
                            {{translate('Add New Unit')}}
                        </span>
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{route('admin.business-settings.unit.store')}}" method="post">
                        @csrf
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="input-label" for="unit_name">{{translate('Unit Name')}}</label>
                                    <input type="text" name="unit_name" class="form-control" id="unit_name" 
                                           placeholder="{{translate('e.g., Kilogram')}}" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="input-label" for="unit_short_name">{{translate('Short Name')}}</label>
                                    <input type="text" name="unit_short_name" class="form-control" id="unit_short_name" 
                                           placeholder="{{translate('e.g., kg')}}" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="input-label" for="unit_type">{{translate('Unit Type')}}</label>
                                    <select name="unit_type" class="form-control" id="unit_type" required>
                                        <option value="weight">{{translate('Weight')}}</option>
                                        <option value="volume">{{translate('Volume')}}</option>
                                        <option value="length">{{translate('Length')}}</option>
                                        <option value="piece">{{translate('Piece/Count')}}</option>
                                        <option value="other">{{translate('Other')}}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label class="input-label d-block">&nbsp;</label>
                                    <button type="submit" class="btn btn-primary btn-block">
                                        <i class="tio-add"></i> {{translate('Add Unit')}}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-sm-12 col-lg-12 mb-3 mb-lg-2">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">
                        <span class="card-header-icon">
                            <i class="tio-list"></i>
                        </span>
                        <span>
                            {{translate('Unit List')}}
                        </span>
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                            <thead class="thead-light">
                                <tr>
                                    <th>{{translate('SL')}}</th>
                                    <th>{{translate('Unit Name')}}</th>
                                    <th>{{translate('Short Name')}}</th>
                                    <th>{{translate('Type')}}</th>
                                    <th>{{translate('Status')}}</th>
                                    <th class="text-center">{{translate('Action')}}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($units as $key => $unit)
                                <tr id="unit-row-{{$unit->id}}">
                                    <td>{{$key+1}}</td>
                                    <td>
                                        <span class="d-block font-size-sm text-body">
                                            {{$unit->unit_name ?? translate('N/A')}}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-soft-info">
                                            {{$unit->unit_short_name ?? translate('N/A')}}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-soft-secondary">
                                            {{ucfirst($unit->unit_type ?? 'other')}}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-soft-{{$unit->is_active ? 'success' : 'danger'}}" id="status-badge-{{$unit->id}}">
                                            <i class="tio-{{$unit->is_active ? 'checkmark' : 'clear'}}-circle"></i>
                                            {{$unit->is_active ? translate('Active') : translate('Inactive')}}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        @if($unit->is_default)
                                            <span class="badge badge-soft-primary">
                                                <i class="tio-star"></i> {{translate('Default')}}
                                            </span>
                                        @else
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-sm btn-white"
                                                        onclick="editUnit({{$unit->id}})"
                                                        title="{{translate('Edit')}}">
                                                    <i class="tio-edit"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-white"
                                                        onclick="toggleStatus({{$unit->id}})"
                                                        title="{{translate('Toggle Status')}}">
                                                    <i class="tio-toggle"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-white text-danger"
                                                        onclick="deleteUnit({{$unit->id}})"
                                                        title="{{translate('Delete')}}">
                                                    <i class="tio-delete"></i>
                                                </button>
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center">
                                        <div class="py-5">
                                            <i class="tio-info-outined" style="font-size: 3rem; color: #ccc;"></i>
                                            <p class="mt-3">{{translate('No units found')}}</p>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Info Card -->
    <div class="row mt-3">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="tio-info"></i> {{translate('About Unit Management')}}
                    </h5>
                    <p class="card-text">
                        {{translate('Unit management allows you to define custom units for your products. You can add units like kg, liter, piece, box, etc.')}}
                    </p>
                    <ul>
                        <li>{{translate('Default units (kg, gm, ltr, ml, pc) cannot be deleted or modified')}}</li>
                        <li>{{translate('You can add custom units based on your business needs')}}</li>
                        <li>{{translate('Units can be activated/deactivated without deleting them')}}</li>
                        <li>{{translate('Only active units will appear in product add/edit forms')}}</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Unit Modal -->
<div class="modal fade" id="editUnitModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{translate('Edit Unit')}}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{route('admin.business-settings.unit.update')}}" method="post">
                @csrf
                <input type="hidden" name="unit_id" id="edit_unit_id">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="edit_unit_name">{{translate('Unit Name')}}</label>
                        <input type="text" name="unit_name" class="form-control" id="edit_unit_name" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_unit_short_name">{{translate('Short Name')}}</label>
                        <input type="text" name="unit_short_name" class="form-control" id="edit_unit_short_name" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_unit_type">{{translate('Unit Type')}}</label>
                        <select name="unit_type" class="form-control" id="edit_unit_type" required>
                            <option value="weight">{{translate('Weight')}}</option>
                            <option value="volume">{{translate('Volume')}}</option>
                            <option value="length">{{translate('Length')}}</option>
                            <option value="piece">{{translate('Piece/Count')}}</option>
                            <option value="other">{{translate('Other')}}</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{translate('Close')}}</button>
                    <button type="submit" class="btn btn-primary">{{translate('Save Changes')}}</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('script_2')
<script>
    function editUnit(unitId) {
        // Fetch unit data via AJAX
        $.ajax({
            url: '{{route('admin.business-settings.unit.edit', ['id' => ':id'])}}'.replace(':id', unitId),
            type: 'GET',
            success: function(response) {
                if (response.success) {
                    $('#edit_unit_id').val(response.data.id);
                    $('#edit_unit_name').val(response.data.unit_name);
                    $('#edit_unit_short_name').val(response.data.unit_short_name);
                    $('#edit_unit_type').val(response.data.unit_type);
                    $('#editUnitModal').modal('show');
                } else {
                    toastr.error(response.message);
                }
            },
            error: function(xhr) {
                toastr.error('{{translate("Failed to load unit data")}}');
            }
        });
    }

    function toggleStatus(unitId) {
        $.ajax({
            url: '{{route('admin.business-settings.unit.toggle-status', ['id' => ':id'])}}'.replace(':id', unitId),
            type: 'GET',
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message);

                    // Update the status badge
                    const badge = $('#status-badge-' + unitId);
                    if (response.is_active) {
                        badge.removeClass('badge-soft-danger').addClass('badge-soft-success');
                        badge.html('<i class="tio-checkmark-circle"></i> {{translate("Active")}}');
                    } else {
                        badge.removeClass('badge-soft-success').addClass('badge-soft-danger');
                        badge.html('<i class="tio-clear-circle"></i> {{translate("Inactive")}}');
                    }
                } else {
                    toastr.error(response.message);
                }
            },
            error: function(xhr) {
                toastr.error('{{translate("Failed to update status")}}');
            }
        });
    }

    function deleteUnit(unitId) {
        Swal.fire({
            title: '{{translate("Are you sure?")}}',
            text: "{{translate('You will not be able to recover this unit!')}}",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: '{{translate("Yes, delete it!")}}',
            cancelButtonText: '{{translate("Cancel")}}'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{route('admin.business-settings.unit.delete', ['id' => ':id'])}}'.replace(':id', unitId),
                    type: 'DELETE',
                    data: {
                        _token: '{{csrf_token()}}'
                    },
                    success: function(response) {
                        if (response.success) {
                            toastr.success(response.message);
                            $('#unit-row-' + unitId).fadeOut(300, function() {
                                $(this).remove();
                            });
                        } else {
                            toastr.error(response.message);
                        }
                    },
                    error: function(xhr) {
                        toastr.error('{{translate("Failed to delete unit")}}');
                    }
                });
            }
        });
    }
</script>
@endpush

