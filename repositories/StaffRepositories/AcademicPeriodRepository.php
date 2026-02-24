<?php
namespace App\Repositories\StaffRepositories;

use App\Core\Repository;
use Models\AcademicPeriod;

class AcademicPeriodRepository extends Repository {
  public function all() {
    return AcademicPeriod::orderBy('acad_year', 'DESC')->get();
  }
  public function getActivePeriods() {
    return AcademicPeriod::where('is_active', 1)->get();
  }

  public function create(array $data) {
    // If this new period is set to active, deactivate all others first
    if (isset($data['is_active']) && $data['is_active'] == 1) {
      AcademicPeriod::where('is_active', 1)->update(['is_active' => 0]);
    }
    return AcademicPeriod::create($data);
  }

  public function update($id, array $data) {
  $period = AcademicPeriod::findOrFail($id);
  
  // Explicitly set to 0 if the checkbox was unchecked (missing from $data)
  $data['is_active'] = isset($data['is_active']) ? 1 : 0;

  // If we are setting THIS one to active (1), deactivate all others
  if ($data['is_active'] == 1) {
    AcademicPeriod::where('id', '!=', $id)->update(['is_active' => 0]);
  }

  $period->fill($data);
  
  if (!$period->isDirty()) {
    return 'no_changes';
  }

  return $period->save();
}

  public function delete($id) {
    return AcademicPeriod::destroy($id);
  }
  
}