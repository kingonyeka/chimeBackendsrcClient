<?php 

namespace App\Controllers\Admins;

use App\Database\Database;
use App\Helpers\JsonResponder;
use App\Helpers\RandomStringGenerator;
use App\Models\Categories;
use App\Models\CategoriesType;
use Slim\Psr7\Request;
use Slim\Psr7\Response;

class AdminCategoryController
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

    public function create(Request $request, Response $response, $args) 
    {

        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Expose-Headers: ');
        header('Access-Control-Allow-Headers: *');

        $parsedBody = $request->getParsedBody();

        $name = $parsedBody['name'] ?? null;
        $description = $parsedBody['description'] ?? '';
        

        if ( ! $name )
        {
            return JsonResponder::generate($response, [
                'code' => 401,
                'message' => 'category name is required'
            ], 401);
        }

        $categoryModel = new Categories($this->db);
        $categoryInfo = $categoryModel->fetchCategoryInfo($name);

        if ( $categoryInfo !== false )
        {
            return JsonResponder::generate($response, [
                'code' => 401,
                'message' => 'category already exist'
            ], 401);
        }

        $categoryId = RandomStringGenerator::generate(20);
        $createdAt = date("Y-m-d H:i:s");
        $lastUpdatedAt = date("Y-m-d H:i:s");

        $dbData = [
            'title' => $name,
            'description' => $description,
            'cat_id' => $categoryId,
            'created_at' => $createdAt,
            'last_updated_at' => $lastUpdatedAt
        ];

        $addNewCategoryToDB = $categoryModel->createCategory($dbData);

        if ( ! $addNewCategoryToDB )
        {
            return JsonResponder::generate($response, [
                'code' => 401,
                'message' => 'failed to create new category',
                'data' => null
            ], 401);
        }

        return JsonResponder::generate($response, [
            'code' => 201,
            'message' => 'category created successfully',
            'data' => null
        ], 201);


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
    
        $categoriesModel = new Categories($this->db);
    
        // Fetch categories from the model
        $categories = $categoriesModel->getCategories($limit, $offset, $sortBy, $orderDirection, $searchValue);
    
        $categoryTypeModel = new CategoriesType($this->db);
    
        // Initialize the response data structure
        $responseData = [];
        $totalCategories = count($categories);
        $totalTypes = 0;
    
        foreach ($categories as $category) {
            $title = $category['title'] ?? '';
    
            // Fetch types for each category by title
            $types = $categoryTypeModel->fetchCategoriesTypesByCatId($category['cat_id']);
            $responseData[$title] = $types;
    
            $totalTypes += count($types);
        }
    
        $totalRecords = $categoriesModel->getCategoriesCount();
    
        return JsonResponder::generate($response, [
            'code' => 200,
            'message' => 'Fetched successfully',
            'totalCategories' => $totalCategories,
            'totalTypes' => $totalTypes,
            'totalRecords' => $totalRecords,
            'data' => $responseData
        ], 200);
    }
    


}