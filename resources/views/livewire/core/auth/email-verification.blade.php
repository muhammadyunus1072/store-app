<form wire:submit='store' class="form w-100" novalidate="novalidate">
    @csrf
    <!--begin::Heading-->
    <div class="text-center mb-11">
        <!--begin::Title-->
        <h1 class="text-dark fw-bolder mb-3">Email Verification</h1>
        <!--end::Title-->
        <!--begin::Subtitle-->
        <!--end::Subtitle=-->
    </div>
    <!--end::Login options-->
    <!--begin::Separator-->
    <div class="separator separator-content my-14">
        <span class="text-gray-500 fw-semibold fs-7">Please check your email</span>
    </div>
    <!--end::Separator-->
    <!--begin::Input group=-->
    <div class="fv-row mb-8">
        <!--begin::Email-->
        <input type="text" placeholder="Email" name="email" autocomplete="off"
            class="form-control form-controller-solid" value="{{ $email }}" disabled />
        <!--end::Email-->
    </div>
    <div class="fv-row mb-8">
        <div class="captcha" wire:ignore>
            <span>{!! captcha_img() !!}</span>
            <button type="button" class="btn btn-danger" class="reload" id="reload">
                &#x21bb;
            </button>
        </div>
    </div>
    <div class="fv-row mb-8">
        <input id="captcha" type="text" class="form-control @error('captcha') is-invalid @enderror"
            placeholder="Enter Captcha" wire:model="captcha">
        @error('captcha')
            <div class="invalid-feedback">
                {{ $message }}
            </div>
        @enderror
    </div>
    <!--end::Input group=-->
    <!--begin::Submit button-->
    <div class="d-grid mb-10">
        <span id='btn-submit-info'></span>
        <button type="submit" id="btn-submit" class="btn btn-primary" wire:ignore.self>
            <!--begin::Indicator label-->
            <span class="indicator-label">Resend Email Verification</span>
            <!--end::Indicator label-->
            <!--begin::Indicator progress-->
            <span class="indicator-progress" wire:loading>Please wait...
                <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
            <!--end::Indicator progress-->
        </button>
    </div>
    <!--end::Submit button-->
</form>

@push('js')
    <script type="text/javascript">
        function getCaptcha() {
            $.ajax({
                type: 'GET',
                url: '{{ route('reload_captcha') }}',
                success: function(data) {
                    $(".captcha span").html(data.captcha);
                }
            });
        };

        $('#reload').click(function() {
            getCaptcha();
        });

        Livewire.on('reload-captcha', () => {
            getCaptcha();
        });
    </script>

    <script type="text/javascript">
        var interval_send_email;
        var time_send_email;

        $(() => {
            disable_send_email();
        });

        Livewire.on('disabled-send-email', () => {
            disable_send_email();
        });

        function disable_send_email() {
            time_send_email = {{ config('template.email_verification_delay_time') }};

            $('#btn-submit').attr('disabled', true);
            $('#btn-submit-info').text(`Kirim Ulang Email Dalam ${time_send_email} Detik`);

            interval_send_email = setInterval(() => {
                time_send_email -= 1;

                if (time_send_email == 0) {
                    clearInterval(interval_send_email);
                    $('#btn-submit-info').text(``);
                    $('#btn-submit').attr('disabled', false);
                    $('#reload').trigger('click');
                } else {
                    $('#btn-submit-info').text(`Kirim Ulang Email Dalam ${time_send_email} Detik`);
                }
            }, 1000);
        }
    </script>
@endpush
