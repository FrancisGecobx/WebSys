<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('staff_admin')->updateOrInsert(
            ['email' => 'kikogeb@gmail.com'],
            [
                'name'          => 'Francis Robert C. Gecobe',
                'password_hash' => Hash::make('admin123'),
                'role'          => 'Prefect of Discipline',
                'department'    => 'Office of Student Affairs',
                'is_active'     => 1,
                'created_at'    => now(),
                'updated_at'    => now(),
            ]
        );

        DB::table('students')->updateOrInsert(
            ['student_number' => '23-0537'],
            [
                'name'             => 'Francis Robert C. Gecobe',
                'email'            => 'francis.gecobe@aldersgate.edu.ph',
                'course'           => 'BSIT',
                'year_level'       => '3rd Year',
                'guardian_contact' => '09171234567',
                'password_hash'    => Hash::make('student123'),
                'created_at'       => now(),
                'updated_at'       => now(),
            ]
        );

        DB::table('courses')->updateOrInsert(
            ['course_code' => 'BSIT'],
            ['course_name' => 'Bachelor of Science in Information Technology', 'created_at' => now(), 'updated_at' => now()]
        );
        DB::table('courses')->updateOrInsert(
            ['course_code' => 'BSA'],
            ['course_name' => 'Bachelor of Science in Accountancy', 'created_at' => now(), 'updated_at' => now()]
        );

        $bsitId = DB::table('courses')->where('course_code', 'BSIT')->value('course_id');

        $subjects = [
            ['IT 311', 'Web Systems and Technologies', 'MWF', '09:00 AM - 10:00 AM'],
            ['IT 312', 'Information Assurance and Security 1', 'MWF', '10:30 AM - 11:30 AM'],
            ['IT 313', 'Networking 2 (Routing & Switching)', 'TTH', '01:00 PM - 02:30 PM'],
        ];

        foreach ($subjects as [$code, $name, $days, $time]) {
            DB::table('subjects')->updateOrInsert(
                ['subject_code' => $code],
                [
                    'subject_name'  => $name,
                    'course_id'     => $bsitId,
                    'schedule_days' => $days,
                    'schedule_time' => $time,
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]
            );
        }

        $violations = [
            ['Dress Code / Improper Uniform', 'Minor', 'Failure to wear prescribed school uniform or visible school ID inside campus.'],
            ['Classroom Disruption', 'Minor', 'Repeated disruption of ongoing classes or laboratory sessions.'],
            ['Academic Dishonesty / Cheating', 'Major', 'Cheating, plagiarism, or unauthorized collaboration during exams or coursework.'],
            ['Vandalism / Property Damage', 'Severe', 'Intentional defacement or destruction of Aldersgate College property or equipment.'],
            ['Sample Test Violation (Safe to Delete)', 'Minor', 'Unlinked test category seeded specifically to verify violation category deletion.'],
        ];

        foreach ($violations as [$name, $severity, $desc]) {
            DB::table('violation_types')->updateOrInsert(
                ['violation_name' => $name],
                [
                    'severity_level' => $severity,
                    'description'    => $desc,
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ]
            );
        }
    }
}