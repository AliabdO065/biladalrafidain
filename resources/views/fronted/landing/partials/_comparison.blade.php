<section class="lk-section lk-section-alt" id="lk-comparison">
    <div class="lk-container">
        <div class="lk-section-head">
            <span class="lk-eyebrow">{{ $settings->comparison_eyebrow ?? __('Why Choose Us') }}</span>
            <h2 class="lk-h2">{{ $settings->comparison_heading ?? __('A Trusted Logistics Partner vs. Unreliable Brokers') }}</h2>
            <p>{{ $settings->comparison_subheading ?? __('Not all shipping brokers are reliable. Here is how to tell the difference.') }}</p>
        </div>

        <div class="lk-compare-wrap">
            <table class="lk-compare">
                <thead>
                    <tr>
                        <th>{{ __('Criterion') }}</th>
                        <th>{{ $settings->company_name ?? 'بلاد الرافدين' }}</th>
                        <th>{{ __('Unverified Shipping Brokers') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($comparisons as $row)
                        <tr>
                            <td>{{ $row->criterion }}</td>
                            <td class="{{ $row->us_is_positive ? 'yes' : 'no' }}">
                                <i class="fa-solid {{ $row->us_is_positive ? 'fa-circle-check' : 'fa-circle-xmark' }}"></i>{{ $row->us_value }}
                            </td>
                            <td class="{{ $row->them_is_positive ? 'yes' : 'no' }}">
                                <i class="fa-solid {{ $row->them_is_positive ? 'fa-circle-check' : 'fa-circle-xmark' }}"></i>{{ $row->them_value }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</section>
