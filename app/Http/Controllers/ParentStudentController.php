<?php

namespace App\Http\Controllers;

use App\Models\AcademicSession;
use App\Models\AuditLog;
use App\Models\ClassYear;
use App\Models\Student;
use Illuminate\Http\Request;

class ParentStudentController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'preferred_name' => ['nullable', 'string', 'max:255'],
            'dob' => ['nullable', 'date', 'before:today'],
            'school' => ['nullable', 'string', 'max:255'],
            'gender' => ['nullable', 'string', 'max:20'],
            'address_line1' => ['nullable', 'string', 'max:255'],
            'address_line2' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:120'],
            'postcode' => ['nullable', 'string', 'max:30'],
            'country' => ['nullable', 'string', 'max:120'],
            'emergency_contact_name' => ['nullable', 'string', 'max:255'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:30'],
            'learning_needs' => ['nullable', 'string', 'max:2000'],
            'medical_notes' => ['nullable', 'string', 'max:2000'],
            'class_year_id' => ['required', 'exists:class_years,id'],
        ]);

        $classYear = ClassYear::query()
            ->where('is_active', true)
            ->findOrFail($validated['class_year_id']);

        $student = Student::query()->create([
            'parent_id' => $request->user()->id,
            'name' => $validated['name'],
            'preferred_name' => $validated['preferred_name'] ?? null,
            'dob' => $validated['dob'] ?? null,
            'school' => $validated['school'] ?? null,
            'gender' => $validated['gender'] ?? null,
            'address_line1' => $validated['address_line1'] ?? null,
            'address_line2' => $validated['address_line2'] ?? null,
            'city' => $validated['city'] ?? null,
            'postcode' => $validated['postcode'] ?? null,
            'country' => $validated['country'] ?? null,
            'emergency_contact_name' => $validated['emergency_contact_name'] ?? null,
            'emergency_contact_phone' => $validated['emergency_contact_phone'] ?? null,
            'learning_needs' => $validated['learning_needs'] ?? null,
            'medical_notes' => $validated['medical_notes'] ?? null,
        ]);

        $student->enrollments()->create([
            'class_year_id' => $classYear->id,
            'academic_session_id' => AcademicSession::query()
                ->where('is_active', true)
                ->latest('starts_at')
                ->value('id'),
        ]);

        AuditLog::record('student.created', $student, [
            'new' => [
                'student_code' => $student->student_code,
                'name' => $student->name,
                'class_year' => $classYear->name,
            ],
        ], $request->user());

        return redirect()
            ->route('parent.dashboard')
            ->with('success', $student->name.' has been added.');
    }
}
