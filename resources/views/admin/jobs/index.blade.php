@extends('layouts.app')
@section('title', 'จัดการงาน (Jobs)')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>จัดการงาน (Jobs)</h3>

        {{-- ฟอร์มสร้างงานใหม่ → POST /admin/jobs (ไปยิง API /jobs/ingest ใน Controller) --}}
        <form action="{{ route('admin.jobs.store') }}" method="POST" class="d-flex gap-2" style="min-width: 380px;">
            @csrf
            <input type="text" name="message" class="form-control" placeholder="พิมพ์ข้อความเพื่อสร้างงาน…" required>
            <button class="btn btn-primary">
                <i class="fas fa-plus"></i> สร้างงาน
            </button>
        </form>
    </div>

    {{-- Flash messages --}}
    @if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
    @if(session('error'))   <div class="alert alert-danger">{{ session('error') }}</div>   @endif

    <div class="card">
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>ข้อความ </th>
                        <th>สถานะ </th>
                        <th>สรุปผล</th>
                        <th>วันที่สร้าง</th>
                        <th class="text-end">การจัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        // ป้องกันกรณี API คืน { data: [...] }
                        $rows = $jobs['data'] ?? ($jobs ?? []);
                    @endphp

                    @if(!empty($rows))
                        @foreach($rows as $index => $job)
                            @php
                                $id      = $job['_id'] ?? $job['id'] ?? '';
                                $st      = $job['status'] ?? 'unknown';
                                $created = $job['created_at'] ?? $job['createdAt'] ?? '-';

                                // อ่านค่า urgency ได้หลายรูปแบบ
                                $urgency  = $job['result']['urgency'] ?? $job['urgency'] ?? null;
                                $urgClass = match(strtolower($urgency ?? '')) {
                                    'high'   => 'bg-danger',
                                    'medium' => 'bg-warning text-dark',
                                    'low'    => 'bg-success',
                                    default  => 'bg-secondary'
                                };
                            @endphp
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $job['message'] ?? '-' }}</td>
                                <td>
                                    <span class="badge
                                        @if($st==='pending') bg-warning
                                        @elseif($st==='done') bg-success
                                        @elseif($st==='failed') bg-danger
                                        @else bg-secondary @endif">
                                        {{ $st }}
                                    </span>
                                </td>
                                <td>
                                    @if($urgency)
                                        <span class="badge {{ $urgClass }}">{{ strtolower($urgency) }}</span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>{{ $created }}</td>
                                <td class="text-end">
                                    <a href="{{ route('admin.jobs.show', $id) }}" class="btn btn-sm btn-info">ดู</a>

                                    @if($st !== 'done')
                                        <form action="{{ route('admin.jobs.retry', $id) }}" method="POST" style="display:inline-block;">
                                            @csrf
                                            @method('PATCH')
                                            <button class="btn btn-sm btn-warning" type="submit">แก้ไข</button>
                                        </form>
                                    @endif

                                    <form action="{{ route('admin.jobs.destroy', $id) }}" method="POST" style="display:inline-block;"
                                          onsubmit="return confirm('ยืนยันลบงานนี้หรือไม่?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-danger" type="submit">ลบ</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    @else
                        {{-- ยังไม่มีข้อมูล → ตัวอย่าง UI --}}
                        <tr>
                            <td>1</td>
                            <td>ทดสอบระบบ</td>
                            <td><span class="badge bg-danger">failed</span></td>
                            <td><span class="badge bg-danger">high</span></td>
                            <td>2025-08-26 10:00</td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-info" disabled>ดู</button>
                                <button class="btn btn-sm btn-warning" disabled>แก้ไข</button>
                                <button class="btn btn-sm btn-danger" disabled>ลบ</button>
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
