<?php
namespace Controllers;

use App\Core\Controller;
use App\Repositories\StaffRepositories\CurriculumRepository;
use App\Repositories\StaffRepositories\SubjectRepository;

class CurriculumController extends Controller {
  protected $curriculumRepo;
  protected $subjectRepo;

  public function __construct() {
    $this->curriculumRepo = new CurriculumRepository();
    $this->subjectRepo = new SubjectRepository();
  }

  // In CurriculumController.php

  public function index() {
    // Fetch courses that ALREADY have curriculum items
    $curriculums = $this->curriculumRepo->getActiveCurriculums();
    
    // Fetch courses that DON'T have a curriculum yet (for the "Add" dropdown)
    $availableCourses = $this->curriculumRepo->getAvailableCourses();

    $this->staffView('staff/curriculum', [
      'curriculums' => $curriculums,
      'availableCourses' => $availableCourses
    ]);
  }
  public function manage($request, $courseId) {
  // Use $courseId (the string '1'), NOT $request
  $course = $this->curriculumRepo->getByCourseId($courseId);
  $allSubjects = $this->subjectRepo->all();

  $this->staffView('staff/curriculum/manage', [
    'course'      => $course,
    'allSubjects' => $allSubjects
  ]);
}


  public function store() {
  $courseId = $this->input('course_id');
  $subjectId = $this->input('subject_id');
  
  $data = [
    'course_id'  => $courseId,
    'subject_id' => $subjectId,
    'year_level' => $this->input('year_level'),
    'semester'   => $this->input('semester')
  ];

  $result = $this->curriculumRepo->add($data);

  if (!$result) {
    $_SESSION['error'] = "Subject is already part of the curriculum.";
    return $this->redirect("/staff/curriculum/manage/{$courseId}");
  }

  // Fetch the subject object to get the code
  $subject = $this->subjectRepo->findById($subjectId); 
  
  // Use the code in the success message
  $_SESSION['success'] = "Subject added: " . ($subject ? $subject->subject_code : "Successfully");

  $this->redirect("/staff/curriculum/manage/{$courseId}");
}

  /**
   * Handle updating subject placement
   */
  public function update() {
    $courseId = $this->input('course_id');
    $subjectId = $this->input('subject_id');
    
    $data = [
        'year_level' => $this->input('year_level'),
        'semester'   => $this->input('semester')
    ];

    $this->curriculumRepo->update($courseId, $subjectId, $data);
    
    $_SESSION['success'] = "Placement updated successfully.";
    $this->redirect("/staff/curriculum/manage/{$courseId}");
  }

  public function destroy() {
      $courseId = $this->input('course_id');
      $subjectId = $this->input('subject_id');

      $this->curriculumRepo->delete($courseId, $subjectId);
      
      $_SESSION['success'] = "Subject removed from curriculum.";
      $this->redirect("/staff/curriculum/manage/{$courseId}");
  }
  /**
 * Initialize a curriculum by redirecting to the manage page
 */
  public function setup() {
    $courseId = $this->input('course_id');
    // We simply redirect to the manage page where they can start adding subjects
    $this->redirect("/staff/curriculum/manage/{$courseId}");
  }

  /**
   * Wipe all subjects from a specific course curriculum
   */
  public function wipe() {
    $courseId = $this->input('course_id');
    $this->curriculumRepo->deleteAllByCourse($courseId);
    $this->redirect("/staff/curriculum");
  }
}