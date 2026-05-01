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
  public function payments() {
        // Fetch all payments with enrollment and user relationships
        $payments = PaymentModel::with(['enrollment.user', 'verifier'])->orderBy('created_at', 'desc')->get();
        return $this->staffView(
            'staff/payments', 
            ['payments' => $payments,
            'title' => 'Manage Payments'],
            );
    }

    // Remove $id from the arguments
    public function downloadReceipt(Request $request) {
      try {
        
        // Get filter values from the URL
        $search = $_GET['search'] ?? '';
        $type = $_GET['type'] ?? '';
        $status = $_GET['status'] ?? '';
        $from = $_GET['from'] ?? '';
        $to = $_GET['to'] ?? '';

        $query = PaymentModel::with(['enrollment.user', 'verifier']);
        $payments = $query->orderBy('created_at', 'desc')->get();

        // CHECK IF EMPTY
        if ($payments->isEmpty()) {
          $_SESSION['error'] = "Cannot generate report: No records found for the selected filters.";
          return $this->redirect("/staff/payments");
        }

        // Apply filters to the SQL query if they exist
        if (!empty($search)) {
          $query->whereHas('enrollment.user', function($q) use ($search) {
            $q->where('first_name', 'LIKE', "%$search%")
            ->orWhere('last_name', 'LIKE', "%$search%")
            ->orWhere('id_number', 'LIKE', "%$search%");
          });
        }
        if (!empty($type)) {
          $query->where('payment_type', $type);
        }

        if (!empty($status)) {
            $query->where('status', $status);
        }
        if (!empty($from)) {
        $query->whereDate('created_at', '>=', $from);
        }
        if (!empty($to)) {
            $query->whereDate('created_at', '<=', $to);
        }
        $payments = $query->orderBy('created_at', 'desc')->get();
          $projectRoot = realpath(__DIR__ . '/../');

        // Standard Dompdf setup...
        $options = new \Dompdf\Options();
        $options->set('isRemoteEnabled', true);
        $dompdf = new \Dompdf\Dompdf($options);
        $options->set('chroot', $projectRoot);

        ob_start();
        include __DIR__ . '/../views/staff/payment_report.php'; 
        $html = ob_get_clean();

        $dompdf->loadHtml($html);
        $dompdf->setPaper('letter', 'portrait');
        $dompdf->render();
        $dompdf->stream("Filtered_Payment_Report.pdf", ["Attachment" => false]);
        exit;
      } catch (Exception $ex) {
        $_SESSION['error'] = $ex->getMessage();
        return $this->redirect("/staff/payments");
      }
    }
}