<?php
namespace Controllers;

use App\Repositories\UserAccounts\UserRepository;
use App\Repositories\StaffRepositories\PaymentRepository;
use App\Core\Controller;
use App\Core\Request;
use Models\Payment as PaymentModel;
use App\Core\Logger;
use Exception;
use Dompdf\Dompdf;
use Dompdf\Options;

class PaymentController extends Controller {
// In the payments() method
public function payments() {
    $payments = PaymentModel::with(['enrollment.user', 'enrollment.period', 'verifier'])
                ->orderBy('created_at', 'desc')
                ->get();

    // Fetch all periods for the filter dropdown
    $periods = \Models\AcademicPeriod::orderBy('acad_year', 'desc')->get();

    return $this->staffView(
        'staff/payments', 
        [
            'payments' => $payments,
            'periods' => $periods, // Pass periods here
            'title' => 'Manage Payments'
        ]
    );
}

    // Remove $id from the arguments
public function downloadReceipt(Request $request) {
      try {
        // 1. Get the IDs string from the URL (sent by your JS visibleIds.join(','))
        $ids_raw = $_GET['ids'] ?? '';

        if (empty($ids_raw)) {
          $_SESSION['error'] = "Cannot generate report: No records were selected or visible.";
          return $this->redirect("/staff/payments");
        }

        // 2. Convert string "1,2,3" into array [1, 2, 3]
        $ids = explode(',', $ids_raw);

        // 3. Fetch ONLY the payments that were visible on the table
        // We still use 'with' to prevent N+1 queries during PDF generation
        $payments = PaymentModel::with(['enrollment.user', 'enrollment.period', 'verifier'])
                    ->whereIn('id', $ids)
                    ->orderBy('created_at', 'desc')
                    ->get();

        if ($payments->isEmpty()) {
          $_SESSION['error'] = "No records found in the database for the selected items.";
          return $this->redirect("/staff/payments");
        }

        $projectRoot = realpath(__DIR__ . '/../');

        // 1. Define a writable storage path
        // If you don't have a 'storage' folder, create one in your project root via FTP
        $storagePath = $projectRoot . '/storage/temp';
        if (!is_dir($storagePath)) {
            mkdir($storagePath, 0755, true);
        }

        // 2. Configure Options
        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('chroot', $projectRoot);

        // THE FIX: Explicitly set directories to prevent the "Path cannot be empty" error
        $options->set('tempDir', $storagePath);
        $options->set('fontDir', $storagePath);
        $options->set('fontCache', $storagePath);

        $dompdf = new Dompdf($options);

        ob_start();
        // The view remains the same, it just loops through the $payments collection
        include __DIR__ . '/../views/staff/payment_report.php'; 
        $html = ob_get_clean();

        $dompdf->loadHtml($html);
        $dompdf->setPaper('letter', 'portrait');
        $dompdf->render();
        
        // Use a generic name or timestamp
        $filename = "Payment_Report_" . date('Y-m-d_His') . ".pdf";
        $dompdf->stream($filename, ["Attachment" => false]);
        exit;

      } catch (Exception $ex) {
        $_SESSION['error'] = "Error generating PDF: " . $ex->getMessage();
        return $this->redirect("/staff/payments");
      }
    }
}