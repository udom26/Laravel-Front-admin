<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Admin')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- CSRF สำหรับฟอร์ม/axios ฯลฯ --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Bootstrap CSS --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    {{-- ช่องเผื่อใส่สไตล์เฉพาะหน้า --}}
    @stack('styles')
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="{{ route('admin.dashboard') }}">Admin Panel</a>

            {{-- ปุ่มพับ/ขยายเมนูบนมือถือ --}}
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#topnav" aria-controls="topnav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            {{-- เมนูด้านขวา --}}
            <div class="collapse navbar-collapse" id="topnav">
                <ul class="navbar-nav ms-auto">
                    @if(session('is_admin'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.jobs.*') ? 'active' : '' }}" href="{{ route('admin.jobs.index') }}">
                                Jobs
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('logout') }}">Logout</a>
                        </li>
                    @else
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('login') ? 'active' : '' }}" href="{{ route('login') }}">Login</a>
                        </li>
                    @endif
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">

        {{-- Flash messages กลางหน้า --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @yield('content')
    </div>

    {{-- Bootstrap JS (ทำให้ navbar toggle ได้) --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" defer></script>

    {{-- Socket.IO client --}}
    <script src="https://cdn.socket.io/4.7.5/socket.io.min.js" defer></script>

    {{-- Expose WS_URL ให้หน้าเพจใช้: auto-fix โปรโตคอล/สแลช/พอร์ต --}}
    <script>
      (function () {
        // ดึงค่าจาก config; ถ้าไม่ตั้ง WS_URL จะ fallback ไป API_BASE_URL
        var raw = "{{ rtrim(config('api.ws_url') ?: config('api.base_url'), '/') }}";

        // ถ้ายังไม่มีให้ปล่อยว่าง
        if (!raw) {
          window.WS_URL = '';
          return;
        }

        // แปลงโปรโตคอล: http -> ws, https -> wss (กันพิมพ์ผิดแบบ ws://https://)
        try {
          var u = new URL(raw);
          if (u.protocol === 'http:')  u.protocol = 'ws:';
          if (u.protocol === 'https:') u.protocol = 'wss:';
          // เก็บกลับ โดยไม่ให้มีสแลชท้าย
          window.WS_URL = (u.origin + u.pathname).replace(/\/+$/, '');
        } catch (e) {
          // ถ้าเป็นสตริงธรรมดา (ไม่ใช่ URL สมบูรณ์) ก็ใช้ตามเดิมแต่ตัดสแลชท้าย
          window.WS_URL = String(raw).replace(/\/+$/, '');
        }

        // helper ไว้ประกอบ namespace ให้ถูกต้อง
        window.wsNs = function (ns) {
          ns = String(ns || '').trim();
          if (!ns) return window.WS_URL;
          return window.WS_URL + (ns.startsWith('/') ? ns : '/' + ns);
        };
      })();
    </script>

    {{-- ช่องให้หน้าลูกใส่สคริปต์เพิ่มเติม --}}
    @stack('scripts')
</body>
</html>
