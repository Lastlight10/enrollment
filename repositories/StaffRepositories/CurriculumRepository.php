<?php
namespace App\Repositories\StaffRepositories;

use App\Core\Repository;
use Models\Curriculum;
use Models\Course;

class CurriculumRepository extends Repository{
  /**
   * Get Course and its subjects linked via the curriculums table
   */
  public function getByCourseId($courseId) {
    return Course::with(['curriculumSubjects' => function($query) {
      $query->orderBy('curriculums.year_level', 'asc')
            ->orderBy('curriculums.semester', 'asc');
    }])->findOrFail((int)$courseId);
  }
  // In CurriculumRepository.php

  public function getActiveCurriculums() {
    // Returns courses that have at least one subject in the curriculums table
    return Course::has('curriculumSubjects')->with('curriculumSubjects')->get();
  }

  public function getAvailableCourses() {
    // Returns courses that are NOT yet in the curriculums table
    return Course::doesntHave('curriculumSubjects')->get();
  }

  public function add($data) {
    $exists = Curriculum::where('course_id', $data['course_id'])
                        ->where('subject_id', $data['subject_id'])
                        ->exists();

    if ($exists) {
        return false; // Stop here, don't insert
    }

    return Curriculum::create($data);
  }

  public function update($courseId, $subjectId, $data) {
    return Curriculum::where('course_id', $courseId)
      ->where('subject_id', $subjectId)
      ->update($data);
  }

  public function delete($courseId, $subjectId) {
    return Curriculum::where('course_id', $courseId)
      ->where('subject_id', $subjectId)
      ->delete();
  }
  /**
 * Delete all subject associations for a specific course
 */
  public function deleteAllByCourse($courseId) {
    return Curriculum::where('course_id', $courseId)->delete();
  }
}