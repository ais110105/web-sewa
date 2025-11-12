<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>@yield('title', 'Web Sewa')</title>
    <meta content="width=device-width, initial-scale=1.0, shrink-to-fit=no" name="viewport" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" href="{{ asset('template/assets/img/kaiadmin/favicon.ico') }}" type="image/x-icon" />

    <!-- Fonts and icons -->
    <script src="{{ asset('template/assets/js/plugin/webfont/webfont.min.js') }}"></script>
    <script>
        WebFont.load({
            google: { families: ["Public Sans:300,400,500,600,700"] },
            custom: {
                families: ["Font Awesome 5 Solid", "Font Awesome 5 Regular", "Font Awesome 5 Brands", "simple-line-icons"],
                urls: ["{{ asset('template/assets/css/fonts.min.css') }}"],
            },
            active: function () {
                sessionStorage.fonts = true;
            },
        });
    </script>

    <!-- CSS Files -->
    <link rel="stylesheet" href="{{ asset('template/assets/css/bootstrap.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('template/assets/css/plugins.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('template/assets/css/kaiadmin.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}" />

    @stack('styles')
</head>
<body>
    <div class="wrapper">
        <!-- Sidebar -->
        @include('layouts.partials.sidebar')
        <!-- End Sidebar -->

        <div class="main-panel">
            <!-- Header -->
            @include('layouts.partials.header')
            <!-- End Header -->

            <!-- Main Content -->
            <div class="container">
                <div class="page-inner">
                    @yield('content')
                </div>
            </div>
            <!-- End Main Content -->

            <!-- Footer -->
            @include('layouts.partials.footer')
            <!-- End Footer -->
        </div>
    </div>

    <!-- Core JS Files -->
    <script src="{{ asset('template/assets/js/core/jquery-3.7.1.min.js') }}"></script>
    <script src="{{ asset('template/assets/js/core/popper.min.js') }}"></script>
    <script src="{{ asset('template/assets/js/core/bootstrap.min.js') }}"></script>

    <!-- jQuery Scrollbar -->
    <script src="{{ asset('template/assets/js/plugin/jquery-scrollbar/jquery.scrollbar.min.js') }}"></script>

    <!-- Kaiadmin JS -->
    <script src="{{ asset('template/assets/js/kaiadmin.min.js') }}"></script>

    <!-- Toast Notification Plugin -->
    <script src="{{ asset('template/assets/js/plugin/bootstrap-notify/bootstrap-notify.min.js') }}"></script>

    <script>
        // Toast notification helper
        function showToast(message, type = 'info') {
            const icons = {
                success: 'fa fa-check',
                error: 'fa fa-times',
                warning: 'fa fa-exclamation-triangle',
                info: 'fa fa-info-circle'
            };

            const colors = {
                success: 'success',
                error: 'danger',
                warning: 'warning',
                info: 'info'
            };

            $.notify({
                icon: icons[type] || icons.info,
                message: message
            }, {
                type: colors[type] || colors.info,
                placement: {
                    from: 'top',
                    align: 'right'
                },
                time: 1000,
                delay: 3000
            });
        }

        // Confirm dialog helper with Bootstrap Modal
        function confirmAction(message, callback, options = {}) {
            const title = options.title || 'Confirmation';
            const confirmText = options.confirmText || 'Yes, proceed!';
            const cancelText = options.cancelText || 'Cancel';
            const confirmClass = options.confirmClass || 'btn-danger';

            // Create modal HTML
            const modalId = 'confirmModal_' + Date.now();
            const modalHtml = `
                <div class="modal fade" id="${modalId}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">${title}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <p>${message}</p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">${cancelText}</button>
                                <button type="button" class="btn ${confirmClass}" id="${modalId}_confirm">${confirmText}</button>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            // Append modal to body
            $('body').append(modalHtml);

            // Show modal
            const modal = new bootstrap.Modal(document.getElementById(modalId));
            modal.show();

            // Handle confirm button click
            $(`#${modalId}_confirm`).on('click', function() {
                modal.hide();
                callback();
                // Remove modal from DOM after animation
                setTimeout(() => {
                    $(`#${modalId}`).remove();
                    $('.modal-backdrop').remove();
                }, 300);
            });

            // Clean up on modal hide
            $(`#${modalId}`).on('hidden.bs.modal', function() {
                $(`#${modalId}`).remove();
                $('.modal-backdrop').remove();
            });
        }

        @if(session('success'))
            showToast('{{ session('success') }}', 'success');
        @endif

        @if(session('error'))
            showToast('{{ session('error') }}', 'error');
        @endif

        @if(session('warning'))
            showToast('{{ session('warning') }}', 'warning');
        @endif

        @if(session('info'))
            showToast('{{ session('info') }}', 'info');
        @endif
    </script>

    @stack('scripts')
</body>
</html>
