@extends('layouts.app')
@section('title', 'จัดการงาน (Jobs)')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>จัดการงาน (Jobs)</h3>

        {{-- ฟอร์มสร้างงานใหม่ --}}
        <form action="{{ route('admin.jobs.store') }}" method="POST" class="d-flex gap-2" style="min-width: 400px;">
            @csrf
            <input type="text" name="message" class="form-control" placeholder="พิมพ์ข้อความเพื่อสร้างงาน…" required>
            <button class="btn btn-primary" style="white-space:nowrap; padding-left:1rem; padding-right:1rem;">
              <i class="fas fa-plus" style="margin-right:.5rem;"></i>สร้างงาน
            </button>
        </form>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>ชื่อ</th>
                        <th>ข้อความ</th>
                        <th>สถานะ</th>
                        <th>ความเร่งด่วน</th>
                        <th>วันที่สร้าง</th>
                        <th class="text-end">การจัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $rows = $jobs['data'] ?? ($jobs ?? []);
                    @endphp

                    @if(!empty($rows))
                        @foreach($rows as $index => $job)
                            @php
                                // id รองรับทั้ง Mongo {_id:{ $oid }} และ id ปกติ
                                $id = $job['_id']['$oid'] ?? $job['_id'] ?? $job['id'] ?? '';

                                // status
                                $st = strtolower($job['status'] ?? 'unknown');
                                $statusBadge = match($st) {
                                    'pending' => 'bg-warning',
                                    'done', 'completed' => 'bg-success',
                                    'failed' => 'bg-danger',
                                    default => 'bg-secondary',
                                };

                                // priority / urgency
                                $priority = $job['priority'] ?? ($job['result']['urgency'] ?? $job['urgency'] ?? null);
                                $prio = strtolower($priority ?? '');
                                $priorityBadge = match($prio) {
                                    'high' => 'bg-danger',
                                    'medium' => 'bg-warning text-dark',
                                    'low' => 'bg-success',
                                    default => 'bg-secondary',
                                };

                                // createdAt
                                $created =
                                    $job['createdAt']['$date']
                                    ?? $job['created_at']
                                    ?? $job['createdAt']
                                    ?? '-';
                            @endphp

                            <tr data-row-id="{{ $id }}">
                                <td>{{ $index + 1 }}</td>
                                <td data-col="name">{{ $job['name'] ?? '-' }}</td>
                                <td data-col="message">{{ $job['message'] ?? '-' }}</td>
                                <td data-col="status"><span class="badge {{ $statusBadge }}">{{ $st }}</span></td>
                                <td data-col="priority">
                                    @if($priority)
                                        <span class="badge {{ $priorityBadge }}">{{ $prio }}</span>
                                    @else - @endif
                                </td>
                                <td data-col="updatedAt">{{ $created }}</td>
                                <td class="text-end">
                                    <a href="{{ route('admin.jobs.show', $id) }}" class="btn btn-sm btn-info">ดู</a>
                                    <form action="{{ route('admin.jobs.destroy', $id) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('ยืนยันลบงานนี้หรือไม่?');">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-danger" type="submit">ลบ</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    @else
                        {{-- ตัวอย่าง UI --}}
                        <tr data-row-id="sample">
                            <td>1</td>
                            <td data-col="name">ตัวอย่างชื่อ</td>
                            <td data-col="message">ทดสอบระบบ</td>
                            <td data-col="status"><span class="badge bg-success">completed</span></td>
                            <td data-col="priority"><span class="badge bg-success">low</span></td>
                            <td data-col="updatedAt">2025-08-28T02:19:22.021Z</td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-info" disabled>ดู</button>
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

@push('scripts')
  <script>window.WS_URL = @json(config('services.ws_url') ?? 'http://localhost:3000');</script>
  <script src="http://localhost:3000/socket.io/socket.io.js"></script>

  <script>
  (function () {
    if (typeof io === 'undefined') { console.error('Socket.IO client not loaded'); return; }

    const socket = io(window.WS_URL || 'http://localhost:3000', {
      transports: ['websocket','polling'],
      withCredentials: true,
      path: '/socket.io',
    });

    socket.on('connect', () => console.log('WS connected', socket.id));

    socket.on('jobStatusUpdate', (job) => {
      const id = job._id?.$oid || job._id || job.id;
      if (!id) return;

      const tbody = document.querySelector('table tbody');

      // 1) ลบแถวตัวอย่างถ้ามี
      const sample = tbody.querySelector('tr[data-row-id="sample"]');
      if (sample) sample.remove();

      let row = tbody.querySelector(`[data-row-id="${id}"]`);

      // map ฟิลด์ให้ใช้ง่าย
      const name    = job.name ?? '-';
      const prio    = (job.priority ?? job.urgency ?? '').toString().toLowerCase();
      const created = job.createdAt?.$date || job.createdAt || job.created_at || '-';
      const updated = job.updatedAt || job.updated_at || created;

      if (!row) {
        // 2) งานใหม่ → สร้างแถวแล้วต่อท้ายล่างสุด
        row = document.createElement('tr');
        row.setAttribute('data-row-id', id);
        row.innerHTML = renderRowCells({
          index: (tbody.querySelectorAll('tr').length + 1),
          id,
          name,
          message: job.message ?? '-',
          status: job.status ?? 'queued',
          priority: prio || null,
          createdAt: created,
          updatedAt: updated,
        });
        tbody.appendChild(row);
      } else {
        // 3) งานเดิม → อัปเดตค่า ไม่ย้ายแถว
        setCell(row, '[data-col="name"]',     name);
        setCell(row, '[data-col="message"]',  job.message ?? '-');
        setCellHTML(row, '[data-col="status"]',   badgeStatus(job.status));
        setCellHTML(row, '[data-col="priority"]', badgePriority(prio || null));
        setCell(row, '[data-col="updatedAt"]', updated);
      }
    });

    socket.on('jobDeleted', (jobId) => {
      const id = jobId?._id?.$oid || jobId?._id || jobId?.id || jobId;
      const row = document.querySelector(`[data-row-id="${id}"]`);
      if (row) row.remove();
      renumberRows();
    });

    // ---------- helpers ----------
    function setCell(tr, sel, text) {
      const el = tr.querySelector(sel);
      if (el) el.textContent = text ?? '-';
    }
    function setCellHTML(tr, sel, html) {
      const el = tr.querySelector(sel);
      if (el) el.innerHTML = html ?? '-';
    }

    function renderRowCells({ index, id, name, message, status, priority, createdAt, updatedAt }) {
      return `
        <td>${index}</td>
        <td data-col="name">${escapeHtml(name)}</td> 
        <td data-col="message">${escapeHtml(message)}</td>
        <td data-col="status">${badgeStatus(status)}</td>
        <td data-col="priority">${badgePriority(priority)}</td>
        <td data-col="updatedAt">${escapeHtml(updatedAt || createdAt || '-')}</td>
        <td class="text-end">
          <a href="/admin/jobs/${id}" class="btn btn-sm btn-info">ดู</a>
          <form action="/admin/jobs/${id}" method="POST" style="display:inline-block;" onsubmit="return confirm('ยืนยันลบงานนี้หรือไม่?');">
            <input type="hidden" name="_token" value="${getCsrf()}">
            <input type="hidden" name="_method" value="DELETE">
            <button class="btn btn-sm btn-danger" type="submit">ลบ</button>
          </form>
        </td>
      `;
    }

    function renumberRows() {
      document.querySelectorAll('tbody tr').forEach((tr, i) => {
        const first = tr.querySelector('td:first-child');
        if (first) first.textContent = String(i + 1);
      });
    }

    function badgeStatus(st) {
      const s = (st || '').toLowerCase();
      const cls = s==='pending' ? 'bg-warning'
               : (s==='done' || s==='completed') ? 'bg-success'
               : s==='failed' ? 'bg-danger'
               : 'bg-secondary';
      return `<span class="badge ${cls}">${s || '-'}</span>`;
    }

    function badgePriority(p) {
      if (!p) return '-';
      const s = String(p).toLowerCase();
      const cls = s==='high' ? 'bg-danger'
               : s==='medium' ? 'bg-warning text-dark'
               : s==='low' ? 'bg-success'
               : 'bg-secondary';
      return `<span class="badge ${cls}">${s}</span>`;
    }

    function getCsrf() {
      const m = document.querySelector('meta[name="csrf-token"]');
      return m ? m.getAttribute('content') : '';
    }

    function escapeHtml(str) {
      return String(str ?? '').replace(/[&<>"']/g, s => ({
        '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'
      }[s]));
    }
  })();
  </script>
@endpush


