<form wire:submit='store' id="register-form" method="post" class="form w-100" novalidate="novalidate">
    @csrf
    <!--begin::Heading-->
    <div class="text-center mb-11">
        <!--begin::Title-->
        <h1 class="text-dark fw-bolder mb-3">Sign Up</h1>
        <!--end::Title-->
        <!--begin::Subtitle-->
        <!--end::Subtitle=-->
    </div>
    <!--end::Login options-->
    <!--begin::Separator-->
    <div class="separator separator-content my-14">
        <span class="w-125px text-gray-500 fw-semibold fs-7">With username / email</span>
    </div>
    <!--end::Separator-->
    <!--begin::Input group=-->
    <div class="fv-row mb-8">
        <!--begin::Email-->
        <input type="text" placeholder="Name" wire:model="name" autocomplete="off"
            class="form-control bg-transparent @error('name') is-invalid @enderror" />

        @error('name')
            <div class="invalid-feedback">
                {{ $message }}
            </div>
        @enderror
        <!--end::Email-->
    </div>
    <!--begin::Input group=-->
    <div class="fv-row mb-8">
        <!--begin::Username-->
        <input type="text" placeholder="Username" wire:model="username" autocomplete="off"
            class="form-control bg-transparent @error('username') is-invalid @enderror" />

        @error('username')
            <div class="invalid-feedback">
                {{ $message }}
            </div>
        @enderror
        <!--end::Username-->
    </div>
    <!--end::Input group=-->
    <!--begin::Input group=-->
    <div class="fv-row mb-8">
        <!--begin::Email-->
        <input type="text" placeholder="Email" wire:model="email" autocomplete="off"
            class="form-control bg-transparent @error('email') is-invalid @enderror" />

        @error('email')
            <div class="invalid-feedback">
                {{ $message }}
            </div>
        @enderror
        <!--end::Email-->
    </div>
    <!--end::Input group=-->
    <!--begin::Input group=-->
    <div class="fv-row mb-8">
        <!--begin::Password-->
        <input type="password" placeholder="Password" wire:model="password" autocomplete="off"
            class="form-control bg-transparent @error('password') is-invalid @enderror" />

        @error('password')
            <div class="invalid-feedback">
                {{ $message }}
            </div>
        @enderror
        <!--end::Password-->
    </div>
    <!--end::Input group=-->
    <!--begin::Input group=-->
    <div class="fv-row mb-8">
        <!--begin::Repeat Password-->
        <input placeholder="Repeat Password" wire:model="retypePassword" type="password" autocomplete="off"
            class="form-control bg-transparent @error('retypePassword') is-invalid @enderror" />

        @error('retypePassword')
            <div class="invalid-feedback">
                {{ $message }}
            </div>
        @enderror
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
        <button type="submit" id="kt_sign_in_submit" class="btn btn-primary">
            <!--begin::Indicator label-->
            <span class="indicator-label">Sign Up</span>
            <!--end::Indicator label-->
            <!--begin::Indicator progress-->
            <span class="indicator-progress" wire:loading>Please wait...
                <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
            <!--end::Indicator progress-->
        </button>
    </div>
    <!--end::Submit button-->
    <!--begin::Sign up-->
    <div class="text-gray-500 text-center fw-semibold fs-6" wire:ignore>
        Already have an Account?
        <a href="{{ route('login') }}" class="link-primary">Sign in</a>
    </div>
    <!--end::Sign up-->
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
