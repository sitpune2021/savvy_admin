<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\Rule;
use App\Models\LabReport;

class LabReportController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $labReports = LabReport::main()->get();
        return view('pages.labReports.index', compact('labReports'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $show = false;
        $parentId = $request->parent_id;

        $labReport = null;

        // ✅ If creating new version → load latest data
        if ($parentId) {
            $mainReport = LabReport::findOrFail($parentId);
            $labReport = $mainReport->latestVersion();
            if ($labReport) {
                $labReport = $labReport->replicate(); // 🔥 BEST way
            }
        }

        return view('pages.labReports.add-edit', compact('show', 'parentId', 'labReport'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // ✅ Validation
        $validator = Validator::make($request->all(), [
            'report_name' => 'required|string|max:255',
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'report_date' => 'nullable|date',
            'expiry_date' => 'nullable|date|after_or_equal:report_date',
            'parent_id' => 'nullable|exists:lab_reports,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {

            // 📂 Upload File
            $filePath = null;

            if ($request->hasFile('file')) {
                $file = $request->file('file');

                $fileName = Str::random(10) . '_' . time() . '.' . $file->getClientOriginalExtension();

                $file->storeAs('public/lab_reports', $fileName);

                $filePath = 'lab_reports/' . $fileName;
            }

            // 🔥 Version Logic
            $parentId = $request->parent_id;

            // If editing, ignore version logic
            if ($request->id) {
                $version = LabReport::find($request->id)->version_no;
            } else {
                if ($parentId) {
                    $maxVersion = LabReport::where(function ($q) use ($parentId) {
                        $q->where('id', $parentId)
                        ->orWhere('parent_id', $parentId);
                    })->max('version_no');

                    $version = ($maxVersion ?? 0) + 1;
                } else {
                    $version = 1;
                }
            }

            // 🧾 Create Record
            $create = LabReport::create([
                'report_name' => $request->report_name,
                'file_path' => $filePath,
                'version_no' => $version,
                'parent_id' => $parentId,
                'report_date' => $request->report_date,
                'expiry_date' => $request->expiry_date,
            ]);

            return response()->json([
                'message' => 'Lab Report saved successfully.',
                'data' => $create
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'error' => 'Failed to save report: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $show = true;
        $mainReport = LabReport::findOrFail($id);

        // 🔥 always get latest version
        $labReport = $mainReport->latestVersion();
        return view('pages.labReports.add-edit', compact('show', 'labReport'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $mainReport = LabReport::findOrFail($id);

        // ✅ Always latest version
        $labReport = $mainReport->latestVersion();

        $show = false;

        return view('pages.labReports.add-edit', compact('labReport', 'show'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
