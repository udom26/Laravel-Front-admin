<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class JobController extends Controller
{
    private string $base;

    public function __construct()
    {
        $this->base = rtrim(config('api.base_url'), '/');
    }

    /** GET /crud */
    public function index()
    {
        $res  = Http::acceptJson()->get("{$this->base}/crud");
        $jobs = $res->successful() ? ($res->json() ?? []) : [];

        return view('admin.jobs.index', compact('jobs'));
    }

    /** GET /crud/:id */
    public function show(string $id)
    {
        $res = Http::acceptJson()->get("{$this->base}/crud/{$id}");
        abort_unless($res->successful(), 404);

        $job = $res->json();
        return view('admin.jobs.show', compact('job'));
    }

    /** GET (form) /admin/jobs/{id}/edit */
    public function edit(string $id)
    {
        $res = Http::acceptJson()->get("{$this->base}/crud/{$id}");
        abort_unless($res->successful(), 404);

        $job = $res->json();
        return view('admin.jobs.edit', compact('job'));
    }

    /** POST /jobs/ingest (สร้างงานใหม่) */
    public function store(Request $request)
    {
        $data = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
        ]);

        $res = Http::acceptJson()->post("{$this->base}/jobs/ingest", $data);

        if (!$res->successful()) {
            return back()->with('error', $res->json('message') ?? 'สร้างงานไม่สำเร็จ');
        }

        return redirect()->route('admin.jobs.index')->with('success', 'สร้างงานสำเร็จ');
    }

    /** PATCH /crud/:id (อัปเดต message/status/resultSummary) */
    public function update(Request $request, string $id)
    {
        $payload = $request->only(['message', 'status', 'resultSummary']);

        $res = Http::acceptJson()->patch("{$this->base}/crud/{$id}", $payload);

        if (!$res->successful()) {
            return back()->with('error', 'อัปเดตไม่สำเร็จ');
        }

        return redirect()->route('admin.jobs.show', $id)->with('success', 'อัปเดตแล้ว');
    }

    /** PATCH helper → สั่ง Retry (ตั้งสถานะเป็น pending) */
    public function retry(string $id)
    {
        $res = Http::acceptJson()->patch("{$this->base}/crud/{$id}", ['status' => 'pending']);

        return redirect()
            ->route('admin.jobs.index')
            ->with($res->successful() ? 'success' : 'error', $res->successful() ? 'สั่ง Retry แล้ว' : 'Retry ไม่สำเร็จ');
    }

    /** DELETE /crud/:id */
    public function destroy(string $id)
    {
        $res = Http::acceptJson()->delete("{$this->base}/crud/{$id}");

        if (!$res->successful()) {
            return back()->with('error', 'ลบไม่สำเร็จ');
        }

        return redirect()->route('admin.jobs.index')->with('success', 'ลบสำเร็จ');
    }
}
