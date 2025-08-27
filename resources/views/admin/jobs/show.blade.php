@extends('layouts.app')
@section('title', 'รายละเอียดงาน (Job Detail)')

@section('content')
<div class="container">
    <h3 class="mb-4">รายละเอียดงาน (Job)</h3>

    <div class="card">
        <div class="card-body">
            <p><strong>ID:</strong> {{ $job['_id'] ?? $job['id'] ?? '-' }}</p>
            <p><strong>ข้อความ (Message):</strong> {{ $job['message'] ?? '-' }}</p>
            <p>
                <strong>สถานะ (Status):</strong>
                @php $st = $job['status'] ?? 'unknown'; @endphp
                <span class="badge
                    @if($st==='pending') bg-warning
                    @elseif($st==='done') bg-success
                    @elseif($st==='failed') bg-danger
                    @else bg-secondary @endif">
                    {{ $st }}
                </span>
            </p>

            <p><strong>สรุปผล (Summary):</strong> {{ $job['result']['summary'] ?? $job['resultSummary'] ?? '-' }}</p>

            @php
                $urgency = $job['result']['urgency'] ?? $job['urgency'] ?? null;
                $urgClass = match(strtolower($urgency ?? '')) {
                    'high'   => 'bg-danger',
                    'medium' => 'bg-warning text-dark',
                    'low'    => 'bg-success',
                    default  => 'bg-secondary'
                };
            @endphp
            <p>
                <strong>ความเร่งด่วน (Urgency):</strong>
                @if($urgency)
                    <span class="badge {{ $urgClass }}">{{ strtolower($urgency) }}</span>
                @else
                    -
                @endif
            </p>

            <p><strong>หมวดหมู่ (Category):</strong> {{ $job['result']['category'] ?? '-' }}</p>
            <p><strong>ภาษา (Language):</strong> {{ $job['result']['language'] ?? '-' }}</p>
            <p><strong>วันที่สร้าง:</strong> {{ $job['created_at'] ?? $job['createdAt'] ?? '-' }}</p>
        </div>
    </div>

    <div class="mt-3">
        <a href="{{ route('admin.jobs.index') }}" class="btn btn-secondary">ย้อนกลับ</a>
    </div>
</div>
@endsection
