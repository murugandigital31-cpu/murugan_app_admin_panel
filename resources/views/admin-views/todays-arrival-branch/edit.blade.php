@extends('layouts.admin.app')

@section('title', translate('Edit Today\'s Arrival Branch'))

@section('content')
    <div class="content container-fluid">
        <div class="page-header">
            <h1 class="page-header-title">
                <span class="page-header-icon">
                    <img src="{{asset('public/assets/admin/img/branch.png')}}" class="w--20" alt="{{ translate('branch') }}">
                </span>
                <span>
                    {{translate('Edit Today\'s Arrival Branch')}}
                </span>
            </h1>
        </div>
        
        <div class="card">
            <div class="card-body">
                <form action="{{route('admin.todays-arrival-branch.update', $branch->id)}}" method="post">
                    @csrf
                    @method('PUT')
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="input-label" for="name">{{translate('Branch Name')}}</label>
                                <input type="text" name="name" value="{{old('name', $branch->name)}}" 
                                       class="form-control" placeholder="{{ translate('Enter branch name') }}" 
                                       maxlength="100" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="input-label" for="whatsapp_number">{{translate('WhatsApp Number')}}</label>
                                <input type="text" name="whatsapp_number" 
                                       value="{{old('whatsapp_number', $branch->whatsapp_number)}}" 
                                       class="form-control" placeholder="{{ translate('e.g. +971501234567') }}" 
                                       maxlength="20" required>
                                <small class="text-muted">{{translate('Include country code (e.g. +971501234567)')}}</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="input-label" for="contact_person">{{translate('Contact Person')}}</label>
                                <input type="text" name="contact_person" 
                                       value="{{old('contact_person', $branch->contact_person)}}" 
                                       class="form-control" placeholder="{{ translate('Contact person name') }}" 
                                       maxlength="100">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="input-label" for="address">{{translate('Address')}}</label>
                                <input type="text" name="address" value="{{old('address', $branch->address)}}" 
                                       class="form-control" placeholder="{{ translate('Branch address') }}" 
                                       maxlength="255">
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="btn--container justify-content-end">
                                <a href="{{route('admin.todays-arrival-branch.add-new')}}" 
                                   class="btn btn--reset">{{translate('back')}}</a>
                                <button type="submit" class="btn btn--primary">{{translate('update')}}</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Test WhatsApp Section -->
        <div class="card mt-3">
            <div class="card-header">
                <h5 class="card-title mb-0">{{translate('Test WhatsApp Integration')}}</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p>{{translate('Current WhatsApp Number')}}: <strong>{{$branch->whatsapp_number}}</strong></p>
                        <p>{{translate('Formatted Number')}}: <strong>{{$branch->formatted_whatsapp_number}}</strong></p>
                    </div>
                    <div class="col-md-6">
                        <a href="https://wa.me/{{$branch->formatted_whatsapp_number}}?text={{urlencode('Test message from Today\'s Arrival system')}}" 
                           target="_blank" class="btn btn-success">
                            <i class="tio-chat"></i> {{translate('Test WhatsApp')}}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection