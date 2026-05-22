<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8" />

        <title>Veron Indonesia | Login</title>

        <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes" />
        <meta name="color-scheme" content="light dark" />
        <meta name="theme-color" content="#007bff" media="(prefers-color-scheme: light)" />
        <meta name="theme-color" content="#1a1a1a" media="(prefers-color-scheme: dark)" />
        <meta name="title" content="Veron Indonesia | Login" />
        <meta name="author" content="Veron" />
        <meta name="description" content="Security Veron Indonesia" />
        <meta name="supported-color-schemes" content="light dark" />

        <link rel="icon" type="image/x-icon" href="{{ asset('images/icon.png') }}">
        <link rel="preload" href="{{ asset('css/adminlte.css') }}" as="style" />

        <link
            rel="stylesheet"
            href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css"
            integrity="sha256-tXJfXfp6Ewt1ilPzLDtQnJV4hclT9XuaZUKyUvmyr+Q="
            crossorigin="anonymous"
            media="print"
            onload="this.media='all'"
        />

        <link
            rel="stylesheet"
            href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/styles/overlayscrollbars.min.css"
            crossorigin="anonymous"
        />

        <link
            rel="stylesheet"
            href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css"
            crossorigin="anonymous"
        />

        <link rel="stylesheet" href="{{ asset('css/adminlte.css') }}" />
    </head>

    <body class="login-page bg-body-secondary">
        <div class="login-box">
            <div class="card card-outline card-primary">
                <div class="card-header text-center">
                    <h1 class="mb-0">Veron Indonesia</h1>
                </div>

                <div class="card-body login-card-body">
                    <p class="login-box-msg">Sign in to start your session</p>

                    <form action="{{ route('web.login') }}" method="POST">
                        @csrf

                        <div class="input-group mb-3">
                            <div class="form-floating">
                                <input
                                    type="email"
                                    id="loginEmail"
                                    name="email"
                                    class="form-control"
                                    placeholder=""
                                >

                                <label for="loginEmail">Email</label>
                            </div>

                            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                        </div>

                        <div class="input-group mb-3">
                            <div class="form-floating">
                                <input
                                    type="password"
                                    id="loginPassword"
                                    name="password"
                                    class="form-control"
                                    placeholder=""
                                >

                                <label for="loginPassword">Password</label>
                            </div>

                            <span class="input-group-text cursor-pointer" onclick="togglePassword()">
                                <i class="bi bi-lock-fill" id="passwordLock"></i>
                            </span>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                Sign In
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

        <script
            src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/browser/overlayscrollbars.browser.es6.min.js"
            crossorigin="anonymous"
        ></script>

        <script
            src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
            crossorigin="anonymous"
        ></script>

        <script
            src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.min.js"
            crossorigin="anonymous"
        ></script>

        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script src="{{ asset('js/adminlte.js') }}"></script>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                initializeSidebarScrollbar();
            });

            function initializeSidebarScrollbar() {
                const sidebarWrapper = document.querySelector('.sidebar-wrapper');
                const isMobile = window.innerWidth <= 992;

                if (
                    sidebarWrapper &&
                    OverlayScrollbarsGlobal?.OverlayScrollbars &&
                    !isMobile
                ) {
                    OverlayScrollbarsGlobal.OverlayScrollbars(sidebarWrapper, {
                        scrollbars: {
                            theme: 'os-theme-light',
                            autoHide: 'leave',
                            clickScroll: true,
                        },
                    });
                }
            }

            function togglePassword() {
                const passwordInput = document.getElementById('loginPassword');
                const passwordIcon = document.getElementById('passwordLock');

                const isPassword = passwordInput.type === 'password';

                passwordInput.type = isPassword ? 'text' : 'password';

                passwordIcon.classList.toggle('bi-lock-fill');
                passwordIcon.classList.toggle('bi-unlock-fill');
            }
        </script>

        @if(session('success'))
            <script>
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: @json(session('success')),
                });
            </script>
        @endif

        @if(session('error'))
            <script>
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: @json(session('error')),
                });
            </script>
        @endif

        @if($errors->any())
            <script>
                Swal.fire({
                    icon: 'error',
                    title: 'Validation Error',
                    html: `{!! implode('<br>', $errors->all()) !!}`,
                });
            </script>
        @endif

    </body>
</html>
