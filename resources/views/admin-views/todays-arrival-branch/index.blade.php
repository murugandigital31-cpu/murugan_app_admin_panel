@extends('layouts.admin.app')

@section('title', translate('Today\'s Arrival Branch Management'))

@section('content')
    <div class="content container-fluid">
        <div class="page-header">
            <h1 class="page-header-title">
                <span class="page-header-icon">
                    <img src="{{asset('public/assets/admin/img/branch.png')}}" class="w--20" alt="{{ translate('branch') }}">
                </span>
                <span>
                    {{translate('Today\'s Arrival Branch Setup')}}
                </span>
            </h1>
        </div>
        
        <div class="card mb-3">
            <div class="card-body">
                <form action="{{route('admin.todays-arrival-branch.store')}}" method="post">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="input-label" for="name">{{translate('Branch Name')}}</label>
                                <input type="text" name="name" value="{{old('name')}}" class="form-control" 
                                       placeholder="{{ translate('Enter branch name') }}" maxlength="100" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="input-label" for="whatsapp_number">{{translate('WhatsApp Number')}}</label>
                                <input type="text" name="whatsapp_number" value="{{old('whatsapp_number')}}" 
                                       class="form-control" placeholder="{{ translate('e.g. +971501234567') }}" 
                                       maxlength="20" required>
                                <small class="text-muted">{{translate('Include country code (e.g. +971501234567)')}}</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="input-label" for="contact_person">{{translate('Contact Person')}}</label>
                                <input type="text" name="contact_person" value="{{old('contact_person')}}" 
                                       class="form-control" placeholder="{{ translate('Contact person name') }}" 
                                       maxlength="100">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="input-label" for="address">{{translate('Address')}}</label>
                                <input type="text" name="address" value="{{old('address')}}" 
                                       class="form-control" placeholder="{{ translate('Branch address') }}" 
                                       maxlength="255">
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
                    <h5 class="card-title">{{translate('Today\'s Arrival Branch List')}} 
                        <span class="badge badge-soft-secondary">{{ $branches->total() }}</span>
                    </h5>
                    <form action="{{url()->current()}}" method="GET">
                        <div class="input-group">
                            <input type="search" name="search" class="form-control"
                                   placeholder="{{translate('Search by name or WhatsApp')}}" aria-label="Search"
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
                        <th class="border-0">{{translate('Branch Name')}}</th>
                        <th class="border-0">{{translate('WhatsApp Number')}}</th>
                        <th class="border-0">{{translate('Contact Person')}}</th>
                        <th class="border-0">{{translate('Address')}}</th>
                        <th class="text-center border-0">{{translate('Status')}}</th>
                        <th class="text-center border-0">{{translate('Action')}}</th>
                    </tr>
                    </thead>

                    <tbody>
                    @foreach($branches as $key=>$branch)
                        <tr>
                            <td>{{$key+1}}</td>
                            <td>
                                <span class="d-block font-size-sm text-body">
                                    {{$branch['name']}}
                                </span>
                            </td>
                            <td>
                                <span class="d-block font-size-sm text-body">
                                    {{$branch['whatsapp_number']}}
                                </span>
                                <a href="https://wa.me/{{$branch->formatted_whatsapp_number}}" 
                                   target="_blank" class="btn btn-sm btn-outline-success">
                                    <i class="tio-chat"></i> {{translate('Test WhatsApp')}}
                                </a>
                            </td>
                            <td>
                                <span class="d-block font-size-sm text-body">
                                    {{$branch['contact_person'] ?? translate('N/A')}}
                                </span>
                            </td>
                            <td>
                                <span class="d-block font-size-sm text-body text-trim-50">
                                    {{$branch['address'] ?? translate('N/A')}}
                                </span>
                            </td>
                            <td>
                                <label class="toggle-switch my-0">
                                    <input type="checkbox"
                                           class="toggle-switch-input status-change-alert" 
                                           id="branchCheckbox{{ $branch->id }}"
                                           data-route="{{ route('admin.todays-arrival-branch.status', [$branch->id, $branch->status ? 0 : 1]) }}"
                                           data-message="{{ $branch->status? translate('you_want_to_disable_this_branch'): translate('you_want_to_active_this_branch') }}"
                                        {{ $branch->status ? 'checked' : '' }}>
                                    <span class="toggle-switch-label mx-auto text">
                                        <span class="toggle-switch-indicator"></span>
                                    </span>
                                </label>
                            </td>
                            <td>
                                <div class="btn--container justify-content-center">
                                    <a class="action-btn"
                                       href="{{route('admin.todays-arrival-branch.edit',[$branch['id']])}}">
                                        <i class="tio-edit"></i></a>
                                    <a class="action-btn btn--danger btn-outline-danger form-alert" href="javascript:"
                                       data-id="branch-{{$branch['id']}}"
                                       data-message="{{ translate("Want to delete this branch") }}">
                                        <i class="tio-delete-outlined"></i>
                                    </a>
                                </div>
                                <form action="{{route('admin.todays-arrival-branch.delete',[$branch['id']])}}"
                                      method="post" id="branch-{{$branch['id']}}">
                                    @csrf @method('delete')
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                <table>
                    <tfoot>
                    {!! $branches->links() !!}
                    </tfoot>
                </table>
            </div>
            
            @if(count($branches) == 0)
                <div class="text-center p-4">
                    <img class="w-120px mb-3" src="{{asset('/public/assets/admin/svg/illustrations/sorry.svg')}}" 
                         alt="{{ translate('image') }}">
                    <p class="mb-0">{{translate('No branches to show')}}</p>
                </div>
            @endif
        </div>
    </div>
@endsection

@push('script_2')
    <script>
        $(document).ready(function () {
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