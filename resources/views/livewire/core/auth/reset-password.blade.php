<form wire:submit='store' class="form w-100" novalidate="novalidate">
    @csrf
    <!--begin::Heading-->
    <div class="text-center mb-11">
        <!--begin::Title-->
        <h1 class="text-dark fw-bolder mb-3">Reset Password</h1>
        <!--end::Title-->
        <!--begin::Subtitle-->
        <!--end::Subtitle=-->
    </div>
    <!--end::Login options-->
    <!--begin::Separator-->
    <div class="separator separator-content my-14">
        <span class="text-gray-500 fw-semibold fs-7">Please Enter Your Email and New Password.</span>
    </div>
    <!--end::Separator-->
    <!--begin::Input group=-->
    <div class="fv-row mb-8">
        <!--begin::Email-->
        <input type="text" placeholder="Email" class="form-control form-controller-solid" value="{{ $email }}"
            disabled />
        <!--end::Email-->
    </div>
    <!--begin::Input group=-->
    <div class="fv-row mb-8">
        <!--begin::Password-->
        <input type="password" placeholder="Password" wire:model="password" autocomplete="off"
            class="form-control bg-transparent" />
        <!--end::Password-->
    </div>
    <!--end::Input group=-->
    <!--begin::Input group=-->
    <div class="fv-row mb-8">
        <!--begin::Repeat Password-->
        <input type="password" placeholder="Repeat Password" wire:model="retypePassword" autocomplete="off"
            class="form-control bg-transparent" />
        <!--end::Repeat Password-->
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
        <input id="captcha" type="text" class="form-control" placeholder="Enter Captcha"
            wire:model="captcha">
    </div>
    <!--begin::Submit button-->
    <div class="d-grid mb-10">
        <button type="submit" id="kt_sign_in_submit" class="btn btn-primary">
            <!--begin::Indicator label-->
            <span class="indicator-label">Reset Password</span>
            <!--end::Indicator label-->
            <!--begin::Indicator progress-->
            <span class="indicator-progress" wire:loading>
                Please wait...
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
@endpush
