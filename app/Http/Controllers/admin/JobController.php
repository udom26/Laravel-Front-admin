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

    public function index()
{
    $res = Http::acceptJson()->get("{$this->base}/crud");
    $rawJobs = $res->successful() ? ($res->json() ?? []) : [];

    $jobs = array_map(function ($job) {
        return [
            'id'            => $job['_id']['$oid'] ?? ($job['_id'] ?? $job['id'] ?? null),
            'message'       => $job['message'] ?? '',
            'status'        => $job['status'] ?? '',
            'resultSummary' => $job['resultSummary'] ?? '',
            'category'      => $job['category'] ?? '',
            'priority'      => $job['priority'] ?? '',
            'language'      => $job['language'] ?? '',
            'created_at'    => $job['createdAt']['$date'] ?? ($job['created_at'] ?? $job['createdAt'] ?? ''),
        ];
    }, $rawJobs);

    return view('admin.jobs.index', compact('jobs'));
}



  public function show(string $id)
{
    $res = Http::acceptJson()->get("{$this->base}/crud/{$id}");
    abort_unless($res->successful(), 404);

    $raw = $res->json() ?? [];

    // Normalize โครง Mongo -> คีย์เรียบๆ ใช้ง่ายใน Blade
    $job = [
        'id'            => $raw['_id']['$oid'] ?? ($raw['_id'] ?? ($raw['id'] ?? '-')),
        'message'       => $raw['message'] ?? '-',
        'status'        => $raw['status'] ?? 'unknown',
        'resultSummary' => $raw['resultSummary'] ?? ($raw['result']['summary'] ?? '-'),
        'category'      => $raw['category'] ?? ($raw['result']['category'] ?? '-'),
        'priority'      => $raw['priority'] ?? ($raw['result']['urgency'] ?? ($raw['urgency'] ?? null)),
        'language'      => $raw['language'] ?? ($raw['result']['language'] ?? '-'),
        'tone'          => $raw['tone'] ?? null,
        'error'         => $raw['error'] ?? null,
        'created_at'    => $raw['createdAt']['$date'] ?? ($raw['created_at'] ?? ($raw['createdAt'] ?? '-')),
        'updated_at'    => $raw['updatedAt']['$date'] ?? ($raw['updated_at'] ?? ($raw['updatedAt'] ?? '-')),
    ];

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
