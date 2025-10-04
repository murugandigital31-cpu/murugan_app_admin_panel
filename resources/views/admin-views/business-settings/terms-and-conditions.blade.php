@extends('layouts.admin.app')

@section('title', translate('Terms and Conditions'))

@push('css_or_js')

@endpush

@section('content')
    <div class="content container-fluid">
        <div class="page-header">
            @include('admin-views.business-settings.partial.page-setup-menu')
        </div>
        <div class="row gx-2 gx-lg-3">
            <div class="col-sm-12 col-lg-12 mb-3 mb-lg-2">
                <form action="{{route('admin.business-settings.page-setup.terms-and-conditions')}}" method="post" id="tnc-form">
                    @csrf
                    <div class="form-group">
                        <div id="editor" class="min-h-116px">{!! $termsAndConditions->value !!}</div>
                        <textarea name="tnc" id="hiddenArea" style="display:none;"></textarea>
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

            $('#tnc-form').on('submit', function () {
                var myEditor = document.querySelector('#editor');
                $('#hiddenArea').val(myEditor.children[0].innerHTML);
            });
        });

        $('#reset').click(function() {
            location.reload();
        });
    </script>
@endpush
