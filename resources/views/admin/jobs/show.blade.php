@extends('layouts.app')
@section('title', 'รายละเอียดงาน (Job Detail)')

@section('content')
@php
    $id        = $job['_id']['$oid'] ?? ($job['_id'] ?? ($job['id'] ?? '-'));
    $message   = $job['message'] ?? '-';

    $status    = $job['status'] ?? 'unknown';
    $statusCls = match(strtolower($status)) {
        'pending' => 'bg-warning',
        'done', 'completed' => 'bg-success',
        'failed' => 'bg-danger',
        default => 'bg-secondary'
    };

    $summary   = $job['resultSummary'] ?? ($job['result']['summary'] ?? '-');

    $priority  = $job['priority'] ?? ($job['result']['urgency'] ?? ($job['urgency'] ?? null));
    $prioCls   = match(strtolower($priority ?? '')) {
        'high' => 'bg-danger',
        'medium' => 'bg-warning text-dark',
        'low' => 'bg-success',
        default => 'bg-secondary'
    };

    $category  = $job['category'] ?? ($job['result']['category'] ?? '-');
    $language  = $job['language'] ?? ($job['result']['language'] ?? '-');

    $createdAt = $job['createdAt']['$date'] ?? ($job['created_at'] ?? ($job['createdAt'] ?? '-'));
    $updatedAt = $job['updatedAt']['$date'] ?? ($job['updated_at'] ?? ($job['updatedAt'] ?? '-'));

    $tone      = $job['tone']   ?? null;
    $errorMsg  = $job['error']  ?? null;
@endphp

<div class="container">
    <h3 class="mb-4">รายละเอียดงาน (Job)</h3>

    <div class="card">
        <div class="card-body">
            <p><strong>ID:</strong> <span id="job-id">{{ $id }}</span></p>
            <p><strong>ข้อความ (Message):</strong> <span id="job-message">{{ $message }}</span></p>

            <p>
                <strong>สถานะ (Status):</strong>
                <span id="job-status" class="badge {{ $statusCls }}">{{ $status }}</span>
            </p>

            <p><strong>สรุปผล (Summary):</strong> <span id="job-summary">{{ $summary }}</span></p>

            <p>
                <strong>ความเร่งด่วน (Urgency/Priority):</strong>
                @if($priority)
                    <span id="job-priority" class="badge {{ $prioCls }}">{{ strtolower($priority) }}</span>
                @else
                    <span id="job-priority">-</span>
                @endif
            </p>

            <p><strong>หมวดหมู่ (Category):</strong> <span id="job-category">{{ $category }}</span></p>
            <p><strong>ภาษา (Language):</strong> <span id="job-language">{{ $language }}</span></p>

            @if($tone !== null)
                <p><strong>โทนข้อความ (Tone):</strong> <span id="job-tone">{{ $tone === '' ? '-' : $tone }}</span></p>
            @endif

            @if($errorMsg)
                <p class="text-danger"><strong>Error:</strong> <span id="job-error">{{ $errorMsg }}</span></p>
            @endif

            <p><strong>วันที่สร้าง:</strong> <span id="job-createdAt">{{ $createdAt }}</span></p>
            <p><strong>อัปเดตล่าสุด:</strong> <span id="job-updatedAt">{{ $updatedAt }}</span></p>
        </div>
    </div>

    <div class="mt-3">
        <a href="{{ route('admin.jobs.index') }}" class="btn btn-secondary">ย้อนกลับ</a>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function(){
  const socket = io(window.WS_URL || location.origin, { transports: ['websocket','polling'], withCredentials: true });
  const jobId = @json($id);

  socket.on('connect', () => {
    console.log('WS connected for job detail', jobId);
  });

  socket.on('jobStatusUpdate', (job) => {
    const id = job._id?.$oid || job._id || job.id;
    if (id !== jobId) return;

    const prio = job.priority ?? job.urgency ?? null;

    document.getElementById('job-message').textContent   = job.message ?? '-';
    document.getElementById('job-status').innerHTML      = badgeStatus(job.status);
    document.getElementById('job-summary').textContent   = job.resultSummary ?? '-';
    document.getElementById('job-priority').innerHTML    = badgePriority(prio);
    document.getElementById('job-category').textContent  = job.category ?? '-';
    document.getElementById('job-language').textContent  = job.language ?? '-';
    document.getElementById('job-updatedAt').textContent = job.updatedAt ?? job.updated_at ?? '-';
  });

  function badgeStatus(st) {
    const s = (st || '').toLowerCase();
    const cls = s === 'pending' ? 'bg-warning'
              : (s === 'done' || s === 'completed') ? 'bg-success'
              : s === 'failed' ? 'bg-danger'
              : 'bg-secondary';
    return `<span class="badge ${cls}">${st ?? '-'}</span>`;
  }

  function badgePriority(p) {
    const s = (p || '').toLowerCase();
    const cls = s === 'high' ? 'bg-danger'
              : s === 'medium' ? 'bg-warning text-dark'
              : s === 'low' ? 'bg-success'
              : 'bg-secondary';
    return p ? `<span class="badge ${cls}">${s}</span>` : '-';
  }
})();
</script>
@endpush
