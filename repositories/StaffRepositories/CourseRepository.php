<?php

namespace App\Repositories\StaffRepositories;

use App\Core\Repository;
use App\Core\Logger;
use Models\Course;

class CourseRepository extends Repository
{
    public function allNoGe() {
        return Course::where('id', '!=', '2')// 2 is id for course GE
            ->orderBy('course_name')
            ->get();
    }

    public function all() {
        return Course::all()->sortBy('course_name')->values()->all();
    }
    public function thisCourseOnly($id) {
        if ($id == 2) {
            return []; // Return empty array instead of null
        }

        $course = Course::find($id);

        // If a course is found, wrap it in an array; otherwise, return an empty array
        return $course ? [$course] : [];
    }

    public function create(array $data) {
        return Course::create($data);
    }

    public function update($id, array $data) {
        $course = Course::findOrFail($id);
        $course->fill($data);

        // Check if any data actually changed
        if (!$course->isDirty()) {
            return 'no_changes';
        }

        return $course->save();
    }

    public function delete($id) {
        $course = Course::findOrFail($id);
        return $course->delete();
    }
}

?>