<main class="app-main">
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0">Dashboard</h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="app-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-3 col-6">
                    <div class="small-box text-bg-primary">
                        <div class="inner">
                            <h3>{{ $count['user'] ?? 0 }}</h3>
                            <p>User</p>
                        </div>

                        <i class="bi bi-people-fill small-box-icon"></i>

                        <a
                            href="{{ route('web.user.index') }}"
                            class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-50-hover"
                        >
                            More info <i class="bi bi-link-45deg"></i>
                        </a>
                    </div>
                </div>

                <div class="col-lg-3 col-6">
                    <div class="small-box text-bg-success">
                        <div class="inner">
                            <h3>{{ $count['application'] ?? 0 }}</h3>
                            <p>Application</p>
                        </div>

                        <i class="bi bi-window-stack small-box-icon"></i>

                        <a
                            href="{{ route('web.application.index') }}"
                            class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-50-hover"
                        >
                            More info <i class="bi bi-link-45deg"></i>
                        </a>
                    </div>
                </div>

                <div class="col-lg-3 col-6">
                    <div class="small-box text-bg-warning">
                        <div class="inner text-white">
                            <h3>{{ $count['role'] ?? 0 }}</h3>
                            <p>Role</p>
                        </div>

                        <i class="bi bi-shield-lock-fill small-box-icon"></i>

                        <a
                            href="{{ route('web.role.index') }}"
                            class="small-box-footer link-dark link-underline-opacity-0 link-underline-opacity-50-hover text-white"
                        >
                            More info <i class="bi bi-link-45deg"></i>
                        </a>
                    </div>
                </div>

                <div class="col-lg-3 col-6">
                    <div class="small-box text-bg-danger">
                        <div class="inner">
                            <h3>{{ $count['permission'] ?? 0 }}</h3>
                            <p>Permission</p>
                        </div>

                        <i class="bi bi-key-fill small-box-icon"></i>

                        <a
                            href="{{ route('web.permission.index') }}"
                            class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-50-hover"
                        >
                            More info <i class="bi bi-link-45deg"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
