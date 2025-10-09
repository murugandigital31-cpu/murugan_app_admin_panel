@if(count($combinations[0]) > 0)
    <div class="table-responsive">
        <table class="table table-bordered">
            <thead>
            <tr>
                <td class="text-center">
                    <label for="" class="control-label">{{ translate('Variant') }}</label>
                </td>
                <td class="text-center">
                    <label for="" class="control-label">{{ translate('Variant Price') }}</label>
                </td>
                <td class="text-center">
                    <label for="" class="control-label">{{ translate('Variant Stock') }}</label>
                </td>
            </tr>
            </thead>
            <tbody>

            @foreach ($combinations as $key => $combination)
                @php
                    $str = '';
                    foreach ($combination as $key => $item){
                        if($key > 0 ){
                            $str .= '-'.str_replace(' ', '', $item);
                        }
                        else{
                            $str .= str_replace(' ', '', $item);
                        }
                    }
                    // Sanitize variant type for input name: replace spaces, dots, and dashes with underscore
                    $sanitizedStr = str_replace([' ', '.', '-'], '_', $str);
                @endphp
                @if(strlen($str) > 0)
                    <tr>
                        <td>
                            <label for="" class="control-label">{{ $str }}</label>
                         </td>
                        <td>
                            <input type="number" name="price_{{ $sanitizedStr }}" value="{{ $price }}" min="0" step="any"
                                   class="form-control" required>
                        </td>
                        <td>
                            <input type="number" name="stock_{{ $sanitizedStr }}" value="0" min="0" max="1000000"
                                   class="form-control variant-stock" onkeyup="update_qty()" required>
                        </td>
                    </tr>
                @endif
            @endforeach
            </tbody>
        </table>
    </div>
@endif
