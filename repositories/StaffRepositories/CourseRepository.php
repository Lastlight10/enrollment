<?php

namespace App\Repositories\StaffRepositories;

use App\Core\Repository;
use App\Core\Logger;
use Models\Course;

class CourseRepository extends Repository
{
    public function all() {
        return Course::all();
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