<?php
namespace App\Repositories\StaffRepositories;

use App\Core\Repository;
use App\Core\Logger;
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
    return Course::has('curriculumSubjects')
      ->with(['curriculumSubjects' => function ($query) {
        $query->reorder()
          ->orderBy('subjects.subject_code', 'asc')
          ->orderByRaw("FIELD(year_level, '1st Year', '2nd Year', '3rd Year', '4th Year')")
          ->orderByRaw("FIELD(semester, '1st Semester', '2nd Semester', 'Summer')")
          ;

        // Log the SQL for the related subjects query
        Logger::log($query->toSql());
        // Log the values (bindings) used in the FIELD() and WHERE clauses
        Logger::log(json_encode($query->getBindings()));
      }])
      ->get();
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