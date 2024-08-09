<?php 

namespace App\Controllers\Admins;

use App\Database\Database;
use App\Helpers\JsonResponder;
use App\Models\Payment;
use App\Models\Payments;
use Slim\Psr7\Request;
use Slim\Psr7\Response;

class AdminPaymentController
{

    private $jwtSecret;
    private $db;

    /**
     * AuthController constructor.
     * 
     * @param string $jwtSecret The JWT secret key
     */
    public function __construct($jwtSecret)
    {
        $this->jwtSecret = $_ENV['JWT_SECRET'];
        $this->db = new Database();
    }

    public function getAll(Request $request, Response $response, $args)
    {


        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Expose-Headers: ');
        header('Access-Control-Allow-Headers: *');

        // Extract query parameters
        $queryParams = $request->getQueryParams();
        
        $sortBy = $queryParams['sort_by'] ?? 'created_at';
        $limit = $queryParams['length'] ?? $queryParams['limit'] ?? 10;
        $offset = $queryParams['start'] ?? $queryParams['offset'] ?? 0;
        $searchValue = $queryParams['search'] ?? $queryParams['search']['value'] ?? null;
        $orderDirection = $queryParams['order'][0]['dir'] ?? 'desc';

        $courses = new Payments($this->db);

        // Fetch users from the model
        $users = $courses->getPayments($limit, $offset, $sortBy, $orderDirection, $searchValue);

        $totalRecords = $courses->getPaymentsCount();

        $totalAmountMade = $courses->getTotalAmount();

        return JsonResponder::generate($response, [
            'code' => 200,
            'message' => 'fetched successfully',
            'totalAmountMade' => $totalAmountMade,
            "items" => count($users),
            "totalRecords" => $totalRecords,
            "filteredRecords" => $totalRecords,
            'data' => $users
        ], 200);

    }

 public function getAllNGN(Request $request, Response $response, $args)
    {


        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Expose-Headers: ');
        header('Access-Control-Allow-Headers: *');

        // Extract query parameters
        $queryParams = $request->getQueryParams();
        
        $sortBy = $queryParams['sort_by'] ?? 'created_at';
        $limit = $queryParams['length'] ?? $queryParams['limit'] ?? 10;
        $offset = $queryParams['start'] ?? $queryParams['offset'] ?? 0;
        $searchValue = $queryParams['search'] ?? $queryParams['search']['value'] ?? null;
        $orderDirection = $queryParams['order'][0]['dir'] ?? 'desc';

        $courses = new Payments($this->db);

        // Fetch users from the model
        $users = $courses->getPaymentsWithCurrencyNGN($limit, $offset, $sortBy, $orderDirection, $searchValue);

        $totalRecords = $courses->getPaymentsCount();

        $totalAmountMade = $courses->getTotalAmountNGN();

        return JsonResponder::generate($response, [
            'code' => 200,
            'message' => 'fetched successfully',
            'totalAmountMade' => $totalAmountMade,
            "items" => count($users),
            "totalRecords" => $totalRecords,
            "filteredRecords" => $totalRecords,
            'data' => $users
        ], 200);

    }
    
     public function getAllUSD(Request $request, Response $response, $args)
    {


        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Expose-Headers: ');
        header('Access-Control-Allow-Headers: *');

        // Extract query parameters
        $queryParams = $request->getQueryParams();
        
        $sortBy = $queryParams['sort_by'] ?? 'created_at';
        $limit = $queryParams['length'] ?? $queryParams['limit'] ?? 10;
        $offset = $queryParams['start'] ?? $queryParams['offset'] ?? 0;
        $searchValue = $queryParams['search'] ?? $queryParams['search']['value'] ?? null;
        $orderDirection = $queryParams['order'][0]['dir'] ?? 'desc';

        $courses = new Payments($this->db);

        // Fetch users from the model
        $users = $courses->getPaymentsWithCurrencyNotNGN($limit, $offset, $sortBy, $orderDirection, $searchValue);

        $totalRecords = $courses->getPaymentsCount();

        $totalAmountMade = $courses->getTotalAmountNotNGN();

        return JsonResponder::generate($response, [
            'code' => 200,
            'message' => 'fetched successfully',
            'totalAmountMade' => $totalAmountMade,
            "items" => count($users),
            "totalRecords" => $totalRecords,
            "filteredRecords" => $totalRecords,
            'data' => $users
        ], 200);

    }
}
