<!doctype html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

    <title>Veron Indonesia | {{ $title }}</title>

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes" />
    <meta name="color-scheme" content="light dark" />
    <meta name="theme-color" content="#007bff" media="(prefers-color-scheme: light)" />
    <meta name="theme-color" content="#1a1a1a" media="(prefers-color-scheme: dark)" />

    <meta name="title" content="Veron Indonesia | {{ $title }}" />
    <meta name="author" content="ColorlibHQ" />
    <meta name="description" content="Veron Indonesia" />

    <meta name="supported-color-schemes" content="light dark" />

    <link rel="preload" href="{{ asset('css/adminlte.css') }}" as="style" />
    <link rel="icon" type="image/x-icon" href="{{ asset('images/icon.png') }}">
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css"
        integrity="sha256-tXJfXfp6Ewt1ilPzLDtQnJV4hclT9XuaZUKyUvmyr+Q="
        crossorigin="anonymous"
        media="print"
        onload="this.media = 'all'"
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

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
        rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB"
        crossorigin="anonymous"
    >

    <link
        href="https://cdn.jsdelivr.net/npm/tom-select/dist/css/tom-select.bootstrap5.min.css"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css"
    >

    <link
        href="https://unpkg.com/gijgo@1.9.14/css/gijgo.min.css"
        rel="stylesheet"
        type="text/css"
    />

    <link rel="stylesheet" href="{{ asset('css/adminlte.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}" />
</head>

<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
    <div class="app-wrapper">

        <nav class="app-header navbar navbar-expand bg-body">
            <div class="container-fluid">

                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a
                            class="nav-link"
                            data-lte-toggle="sidebar"
                            href="#"
                            role="button"
                        >
                            <i class="bi bi-list"></i>
                        </a>
                    </li>
                </ul>

                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown">

                        <a
                            class="nav-link dropdown-toggle text-decoration-none"
                            href="#"
                            role="button"
                            data-bs-toggle="dropdown"
                            aria-expanded="false"
                        >
                              {{ strtoupper(session('user_security')->username) }}
                        </a>

                        <ul class="dropdown-menu dropdown-menu-end dropdown-menu-dark">
                            <li>
                                <form action="{{ route('web.logout') }}" method="POST">
                                    @csrf

                                    <button class="dropdown-item" type="submit">
                                        <i class="bi bi-power"></i> Logout
                                    </button>
                                </form>
                            </li>
                        </ul>

                    </li>
                </ul>

            </div>
        </nav>

        <x-sidebar />
