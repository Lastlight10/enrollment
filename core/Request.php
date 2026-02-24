<?php
namespace App\Core;

class Request {
  protected $data;

  public function __construct() {
    // Collect all input data
    $this->data = array_merge($_GET, $_POST);
  }

  public function all() {
    return $this->data;
  }
  public function get($key, $default = null)
  {
    $data = $this->all();
    return $data[$key] ?? $default;
  }

  public function input($key, $default = null) {
    return $this->data[$key] ?? $default;
  }

  /**
   * Simple Validation Logic
   */
  public function validate(array $rules) {
    $validated = [];
    $errors = [];

    foreach ($rules as $field => $rule) {
      // Handle nested array validation like 'fees.*.amount'
      if (strpos($field, '.*.') !== false) {
        $this->validateArrayField($field, $rule, $validated, $errors);
        continue;
      }

      $value = $this->input($field);

      // Basic 'required' check
      if (strpos($rule, 'required') !== false && empty($value)) {
        $errors[] = "$field is required.";
      }

      $validated[$field] = $value;
    }

    if (!empty($errors)) {
      $_SESSION['error'] = implode(' ', $errors);
      // Redirect back to previous page
      header('Location: ' . $_SERVER['HTTP_REFERER']);
      exit();
    }

    return $validated;
  }

  private function validateArrayField($field, $rule, &$validated, &$errors) {
    // Logic for checking fees.*.type etc.
    // For now, we will simply extract the data for your manual process
    $parts = explode('.*.', $field);
    $parentKey = $parts[0]; // e.g., 'fees'
    $childKey = $parts[1];  // e.g., 'amount'

    if (isset($this->data[$parentKey])) {
      $validated[$parentKey] = $this->data[$parentKey];
    }
  }
}
?>