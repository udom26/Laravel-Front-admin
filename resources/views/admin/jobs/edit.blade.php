@extends('layouts.app')
@section('title', 'แก้ไขงาน')

@section('content')
<div class="container">
    <h3>แก้ไขงาน</h3>

    <form action="{{ route('admin.jobs.update', $job['_id'] ?? $job['id']) }}" method="POST">
        @csrf
        @method('PATCH')

        <div class="mb-3">
            <label for="message" class="form-label">ข้อความ</label>
            <input type="text" id="message" name="message" class="form-control"
                   value="{{ $job['message'] ?? '' }}" required>
        </div>

        <div class="mb-3">
            <label for="status" class="form-label">สถานะ</label>
            <select id="status" name="status" class="form-select">
                @php $st = $job['status'] ?? 'pending'; @endphp
                <option value="pending" {{ $st==='pending' ? 'selected' : '' }}>Pending</option>
                <option value="done" {{ $st==='done' ? 'selected' : '' }}>Done</option>
                <option value="failed" {{ $st==='failed' ? 'selected' : '' }}>Failed</option>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">บันทึกการแก้ไข</button>
        <a href="{{ route('admin.jobs.index') }}" class="btn btn-secondary">ยกเลิก</a>
    </form>
</div>
@endsection
