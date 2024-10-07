@extends('app.layouts.auth')

@section('body-class', 'layout-default layout-login-image')

@section('content')
    <!--begin::Body-->
    <div class="d-flex flex-column-fluid flex-lg-row-auto justify-content-center justify-content-lg-end p-12">
        <!--begin::Wrapper-->
        <div class="bg-body d-flex flex-column flex-center rounded-4 w-md-600px p-10">
            <!--begin::Content-->
            <div class="d-flex flex-center flex-column align-items-stretch h-lg-100 w-md-400px">
                <!--begin::Wrapper-->
                <div class="d-flex flex-center flex-column flex-column-fluid pb-15 pb-lg-20">
                    <livewire:core.auth.login />

                    @if (config('template.forgot_password_route'))
                        <div class="d-flex flex-stack flex-wrap gap-3 fs-base fw-semibold mb-8">
                            <a href="{{ route(config('template.forgot_password_route')) }}" class="link-primary">Forgot Password ?</a>
                        </div>
                    @endif

                    @if (config('template.registration_route'))
                        <div class="text-gray-500 text-center fw-semibold fs-6">
                            Not a Member yet?
                            <a href="{{ route(config('template.registration_route')) }}" class="link-primary">Sign up</a>
                        </div>
                    @endif
                </div>
                <!--end::Wrapper-->
            </div>
            <!--end::Content-->
        </div>
        <!--end::Wrapper-->
    </div>
    <!--end::Body-->
@endsection
