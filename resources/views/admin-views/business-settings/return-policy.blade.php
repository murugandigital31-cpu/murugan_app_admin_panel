@extends('layouts.admin.app')

@section('title', translate('Return policy'))

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            @include('admin-views.business-settings.partial.page-setup-menu')

            <div class="row align-items-center">
                <div class="col-sm mb-2 mb-sm-0">
                    <h5 class="d-flex flex-wrap justify-content-end">
                        <label class="switch--custom-label toggle-switch toggle-switch-sm d-inline-flex">
                            <span class="mr-2 switch--custom-label-text text-primary on text-uppercase">{{ translate('on') }}</span>
                            <span class="mr-2 switch--custom-label-text off text-uppercase">{{ translate('Status') }}</span>
                            <input type="checkbox"
                                   data-route="{{ route('admin.business-settings.page-setup.return-policy.status', [$status['value'] ? 0 : 1]) }}"
                                   data-message="{{ $status['value']? translate('you want to disable this page'): translate('you want to active this page') }}"
                                   class="toggle-switch-input status-change-alert" id="stocksCheckbox"
                                {{ $status['value'] ? 'checked' : '' }}>
                            <span class="toggle-switch-label text">
                                <span class="toggle-switch-indicator"></span>
                            </span>
                        </label>
                    </h5>
                </div>
            </div>
        </div>
        <div class="row gx-2 gx-lg-3">
            <div class="col-sm-12 col-lg-12 mb-3 mb-lg-2">
                <form action="{{route('admin.business-settings.page-setup.return-policy')}}" method="post" id="return-form">
                    @csrf
                    <div class="form-group">
                        <div id="editor" class="min-h-116px">{!! $data['value'] !!}</div>
                        <textarea name="return_policy" id="hiddenArea" style="display:none;"></textarea>
                    </div>


                    <div class="btn--container justify-content-end">
                        <button type="reset" class="btn btn--reset" id="reset">{{translate('reset')}}</button>
                        <button type="submit" class="btn btn--primary">{{translate('submit')}}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@push('script_2')
    <script src="{{ asset('public/assets/admin/js/quill-editor.js') }}"></script>
    <script type="text/javascript">
        $(document).ready(function () {
            var bn_quill = new Quill('#editor', {
                theme: 'snow'
            });

            $('#return-form').on('submit', function () {
                var myEditor = document.querySelector('#editor');
                $('#hiddenArea').val(myEditor.children[0].innerHTML);
            });
        });
        $('#reset').click(function() {
            location.reload();
        });
    </script>
@endpush
