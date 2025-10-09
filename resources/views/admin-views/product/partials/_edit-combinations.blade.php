@if(count($combinations) > 0)
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
                    $variantType = $combination['type'];
                    // Sanitize variant type for input name: replace spaces, dots, and special chars with underscore
                    $sanitizedType = str_replace([' ', '.', '-'], '_', $variantType);
                @endphp
                <tr>
                    <td>
                        <label for="" class="control-label">{{ $variantType }}</label>
                    </td>
                    <td>
                        <input type="number" name="price_{{ $sanitizedType }}"
                               value="{{$combination['price'] ?? 0}}" min="0"
                               step="any"
                               class="form-control" required>
                    </td>
                    <td>
                        <input type="number" name="stock_{{ $sanitizedType }}"
                               value="{{ $combination['stock'] ?? 0 }}"
                               min="0" max="1000000" onkeyup="update_qty()"
                               class="form-control variant-stock" required>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endif
