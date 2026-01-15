<?php
namespace App\Repositories\StaffRepositories;

use App\Core\Repository;
use Models\Subject;

class SubjectRepository extends Repository {
  public function all() {
    // Eager load the course relationship to avoid N+1 query issues
    return Subject::with('course')->orderBy('subject_code', 'ASC')->get();
  }

  public function create(array $data) {
    return Subject::create($data);
  }

  public function update($id, array $data) {
    $subject = Subject::findOrFail($id);
    $subject->fill($data);
    
    if (!$subject->isDirty()) {
      return 'no_changes';
    }

    return $subject->save();
  }

  public function delete($id) {
    return Subject::destroy($id);
  }
}