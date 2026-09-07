<?php

namespace App\Http\Controllers;

use App\Models\AcademicSession;
use App\Models\ClassYear;
use App\Models\Student;
use Illuminate\Http\Request;

class ParentStudentController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'dob' => ['nullable', 'date', 'before:today'],
            'school' => ['nullable', 'string', 'max:255'],
            'gender' => ['nullable', 'string', 'max:20'],
            'class_year_id' => ['required', 'exists:class_years,id'],
        ]);

        $classYear = ClassYear::query()
            ->where('is_active', true)
            ->findOrFail($validated['class_year_id']);

        $student = Student::query()->create([
            'parent_id' => $request->user()->id,
            'name' => $validated['name'],
            'dob' => $validated['dob'] ?? null,
            'school' => $validated['school'] ?? null,
            'gender' => $validated['gender'] ?? null,
        ]);

        $student->enrollments()->create([
            'class_year_id' => $classYear->id,
            'academic_session_id' => AcademicSession::query()
                ->where('is_active', true)
                ->latest('starts_at')
                ->value('id'),
        ]);

        return redirect()
            ->route('parent.dashboard')
            ->with('success', $student->name.' has been added.');
    }
}
