<?php 


namespace App\Controllers;

use App\Database\Database;
use App\Helpers\JsonResponder;
use App\Models\Admin;
use App\Models\Cart;
use App\Models\User;
use App\Models\Courses;
use App\Models\Robots;
use Slim\Psr7\Request;
use Slim\Psr7\Response;

class CartController
{
    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    // Create or update a cart

    // public function upsert(Request $request, Response $response, $args)
    // {

    //     header('Access-Control-Allow-Origin: *');
    //     header('Access-Control-Allow-Credentials: true');
    //     header('Access-Control-Expose-Headers: ');
    //     header('Access-Control-Allow-Headers: *');

    //     $parsedBody = $request->getParsedBody();

    //     $email = $parsedBody['email'] ?? null;
    //     $products = $parsedBody['products'] ?? null;

    //     if (!$email || !$products) {
    //         return JsonResponder::generate($response, [
    //             'code' => 400,
    //             'message' => 'email and products are required',
    //             'data' => null
    //         ], 400);
    //     }

    //     if (!is_array($products)) {
    //         return JsonResponder::generate($response, [
    //             'code' => 401,
    //             'message' => 'invalid products',
    //             'data' => null
    //         ], 401);
    //     }

    //     $userModel = new User($this->db);
    //     $user = $userModel->fetchUserInfo($email);

    //     if (!$user) {
    //         return JsonResponder::generate($response, [
    //             'code' => 404,
    //             'message' => 'user not found',
    //             'data' => null
    //         ], 404);
    //     }

    //     $user_id = $user['user_id'];

    //     $cartModel = new Cart($this->db);
    //     $existingCart = $cartModel->fetchCartByUserId($user_id);

    //     // Validate products
    //     list($validProducts, $invalidProducts) = $this->validateProducts($products);

    //     if ($existingCart) {
    //         $existingProducts = json_decode($existingCart['products'], true) ?? [];



    //         // Merge products: Update quantities if products already exist in the cart
    //         foreach ($validProducts as $validProduct) {



    //             $productExists = false;

    //             // Check if the slug key exists in validProduct
    //             if (!isset($validProduct['slug'])) {
    //                 continue; // Skip this iteration if slug is missing
    //             }

    //             // Check if existing products array is empty
    //             if (empty($existingProducts)) {
    //                 $existingProducts[] = $validProduct;
    //             }
    //             else
    //             {

                                
    //                 foreach ($existingProducts as &$existingProduct) {
    //                     $slug = $existingProduct['slug'] ?? null;
    //                     // Check if the slug key exists in existingProduct
    //                     if (!isset($slug)) {
    //                         // echo '<pre>';
    //                         // echo "Error: 'slug' key does not exist in existingProduct\n";
    //                         // var_dump($existingProduct);
    //                         // echo '</pre>';
    //                         continue; // Skip this iteration if slug is missing
    //                     }

    //                     // Compare slugs
    //                     if ($existingProduct['slug'] === $validProduct['slug']) {
    //                         // Update quantities if the product exists in the cart
    //                         $existingProduct['quantities'] += $validProduct['quantities'];
    //                         $productExists = true;
    //                         // echo '<pre>';
    //                         // echo "Product found and quantities updated\n";
    //                         // var_dump($existingProduct);
    //                         // echo '</pre>';
    //                         break;
    //                     }
    //                 }

    //             }

    //             // If the product does not exist in the cart, add it
    //             if (!$productExists) {
    //                 $existingProducts[] = $validProduct;
    //             }
    //         }


    //         $updatedCart = $cartModel->updateCart([
    //             'user_id' => $user_id,
    //             'products' => json_encode($existingProducts)
    //         ], $user['user_id']);

    //         if (!$updatedCart) {
    //             return JsonResponder::generate($response, [
    //                 'code' => 500,
    //                 'message' => 'failed to update cart',
    //                 'data' => null
    //             ], 500);
    //         }

    //         // $validProducts = $existingProducts;
    //         $count = count($validProducts);

    //     } else {

    //         $count = count($validProducts);

    //         $createdCart = $cartModel->createCart([
    //             'user_id' => $user_id,
    //             'products' => json_encode($validProducts)
    //         ]);

    //         if (!$createdCart) {
    //             return JsonResponder::generate($response, [
    //                 'code' => 500,
    //                 'message' => 'failed to create cart',
    //                 'data' => null
    //             ], 500);
    //         }
    //     }

        


    //     return JsonResponder::generate($response, [
    //         'code' => 200,
    //         'message' => 'cart processed successfully',
    //         'data' => [
    //             'total' => count($products),
    //             'total_valid' => $count,
    //             'total_invalid' => count($invalidProducts),
    //             'valid_products' => $validProducts,
    //             'invalid_products' => $invalidProducts
    //         ]
    //     ], 200);
    // }

    public function upsert(Request $request, Response $response, $args)
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Expose-Headers: ');
        header('Access-Control-Allow-Headers: *');

        $parsedBody = $request->getParsedBody();
        
       
        

        $email = $parsedBody['email'] ?? null;
        $products = $parsedBody['products'] ?? null;
        $country = $parsedBody['country'] ?? null;
        
        // echo  $country; exit;

        if (!$email || !$products) {
            return JsonResponder::generate($response, [
                'code' => 400,
                'message' => 'email and products are required',
                'data' => null
            ], 400);
        }

        if (!is_array($products)) {
            return JsonResponder::generate($response, [
                'code' => 401,
                'message' => 'invalid products',
                'data' => null
            ], 401);
        }

        $userModel = new User($this->db);
        $user = $userModel->fetchUserInfo($email);

        if (!$user) {
            return JsonResponder::generate($response, [
                'code' => 404,
                'message' => 'user not found',
                'data' => null
            ], 404);
        }

        $user_id = $user['user_id'];

        $cartModel = new Cart($this->db);
        $existingCart = $cartModel->fetchCartByUserId($user_id);

        // Validate products
        list($validProducts, $invalidProducts) = $this->validateProducts($products , $country);
        
        // var_dump($validProducts); exit;

        if ($existingCart) {
            $existingProducts = json_decode($existingCart['products'], true) ?? [];

            // Merge products: Update quantities if products already exist in the cart
            foreach ($validProducts as $validProduct) {
                $productExists = false;

                // Check if the slug key exists in validProduct
                if (!isset($validProduct['slug'])) {
                    continue; // Skip this iteration if slug is missing
                }

                foreach ($existingProducts as &$existingProduct) {
                    $slug = $existingProduct['slug'] ?? null;
                    // Check if the slug key exists in existingProduct
                    if (!isset($slug)) {
                        continue; // Skip this iteration if slug is missing
                    }

                    // Compare slugs
                    if ($existingProduct['slug'] === $validProduct['slug']) {
                        // Update quantities if the product exists in the cart
                        $existingProduct['quantities'] += $validProduct['quantities'];
                        $productExists = true;
                        break;
                    }
                    else
                    {
                        $existingProduct['quantities'] = 1;
                    }
                }

                // If the product does not exist in the cart, add it
                if (!$productExists) {
                    $existingProducts[] = $validProduct;
                }
            }

            foreach ($existingProducts as $key => $validProduct) {
                
                $validProduct['quantities'] = 1;
                $existingProducts[$key] = $validProduct;

            }

            $updatedCart = $cartModel->updateCart([
                'products' => json_encode($existingProducts)
            ], $user['user_id']);

            if (!$updatedCart) {
                return JsonResponder::generate($response, [
                    'code' => 500,
                    'message' => 'failed to update cart',
                    'data' => null
                ], 500);
            }


            $validProducts = $existingProducts;
            $count = count($existingProducts);

        } else {
            $count = count($validProducts);

            foreach ($validProducts as $key => $validProduct) {
                
                $validProduct['quantities'] = 1;
                $validProducts[$key] = $validProduct;

            }

            $createdCart = $cartModel->createCart([
                'user_id' => $user_id,
                'products' => json_encode($validProducts)
            ]);

            if (!$createdCart) {
                return JsonResponder::generate($response, [
                    'code' => 500,
                    'message' => 'failed to create cart',
                    'data' => null
                ], 500);
            }
        }

        return JsonResponder::generate($response, [
            'code' => 200,
            'message' => 'cart processed successfully',
            'data' => [
                'total' => count($products),
                'total_valid' => $count,
                'total_invalid' => count($invalidProducts),
                'valid_products' => $validProducts,
                'invalid_products' => $invalidProducts
            ]
        ], 200);
    }




    // Fetch a cart by user email
   public function fetchByEmail(Request $request, Response $response, $args)
{
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Expose-Headers: ');
    header('Access-Control-Allow-Headers: *');

    // Extract the email parameter from the query parameters
    $email = $request->getQueryParams()['email'] ?? null;

    // Check if the email is provided
    if (!$email) {
        return JsonResponder::generate($response, [
            'code' => 400,
            'message' => 'email is required',
            'data' => null
        ], 400);
    }

    // Fetch user information by email
    $userModel = new User($this->db);
    $user = $userModel->fetchUserInfo($email);

    // Check if the user exists
    if (!$user) {
        return JsonResponder::generate($response, [
            'code' => 404,
            'message' => 'user not found',
            'data' => null
        ], 404);
    }

    // Get the user ID from the fetched user information
    $user_id = $user['user_id'];

    // Fetch the cart for the user ID
    $cartModel = new Cart($this->db);
    $cart = $cartModel->fetchCartByUserId($user_id);

    // Check if the cart exists
    if (!$cart) {
        return JsonResponder::generate($response, [
            'code' => 404,
            'message' => 'cart not found',
            'data' => null
        ], 404);
    }

    unset($cart['id']);
    unset($cart['user_id']);


    $products = json_decode($cart['products'], true);

  
    $totalItems = 0;
    $totalAmount = 0.0;


    foreach ($products as $key => $product) {
        $baseUrl = $_ENV['APPLICATION_BACKEND_URL'];

        if ($product["type"] === 'robot') {
            $robotsModel = new Robots($this->db);
            $id = $robotsModel->fetchRobotByTitle($product['title'])['robot_id'] ?? null;
            $image = $robotsModel->fetchRobotByTitle($product['title'])['image'] ?? null;
            $fullUrl = $baseUrl . '/uploads/robots/' . $id;
        } else if ($product["type"] === 'course') {
            $course = new Courses($this->db);
            $id = $course->fetchCourseByTitle($product['title'])['course_id'] ?? null;
            $image = $course->fetchCourseByTitle($product['title'])['image'] ?? null;
            $fullUrl = $baseUrl . '/uploads/courses/' . $id;
        }

        $product['image'] = $fullUrl . $image;
        $product['price'] = floatval($product['price']);
        $totalItems += $product['quantities'];
        $totalAmount += $product['quantities'] * $product['price'];

        $products[$key] = $product;
    }

  
    $formattedTotalAmount = number_format($totalAmount, 2, '.', '');


    $cart['total_items'] = $totalItems;
    $cart['total_amount'] = floatval($formattedTotalAmount);
    $cart['products'] = $products; // Ensure products are included in the response


    $cart = array_merge(['total_items' => $totalItems, 'total_amount' => $formattedTotalAmount], $cart);

  
    return JsonResponder::generate($response, [
        'code' => 200,
        'message' => 'cart fetched successfully',
        'data' => $cart
    ], 200);
}

    
    // Delete a cart by cart ID
    public function delete(Request $request, Response $response, $args)
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Expose-Headers: ');
        header('Access-Control-Allow-Headers: *');

         // Extract the email parameter from the query parameters
        // $email = $request->getQueryParams()['email'] ?? null;
        $email = $request->getParsedBody()['email'] ?? null;
    
        // Check if the email is provided
        if (!$email) {
            return JsonResponder::generate($response, [
                'code' => 400,
                'message' => 'email is required',
                'data' => null
            ], 400);
        }
    
        // Fetch user information by email
        $userModel = new User($this->db);
        $user = $userModel->fetchUserInfo($email);
    
        // Check if the user exists
        if (!$user) {
            return JsonResponder::generate($response, [
                'code' => 404,
                'message' => 'user not found',
                'data' => null
            ], 404);
        }
    
        // Get the user ID from the fetched user information
        $user_id = $user['user_id'];
    
        // Fetch the cart for the user ID
        $cartModel = new Cart($this->db);
        $cart = $cartModel->fetchCartByUserId($user_id);
        
        
    
        // Check if the cart exists
        if (!$cart) {
            return JsonResponder::generate($response, [
                'code' => 404,
                'message' => 'cart not found',
                'data' => null
            ], 404);
        }

        $hasUpdatedCart = $cartModel->updateCart([
            'products' => json_encode([])
        ], $user['user_id']);

        if ( ! $hasUpdatedCart )
        {
            return JsonResponder::generate($response, [

                'code' => 500,
                'message' => 'could not delete cart'

            ], 500);
        }
    
        return JsonResponder::generate($response, [
            'code' => 200,
            'message' => 'Cart deleted successfully',
            'data' => null
        ], 200);
    }

    public function removeItem(Request $request, Response $response, $args)
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Expose-Headers: ');
        header('Access-Control-Allow-Headers: *');

        $parsedBody = $request->getParsedBody();
    
        $email = $parsedBody['email'] ?? null;
        $products = $parsedBody['products'] ?? null;
         $country = $parsedBody['country'] ?? null;
       
    
        if (!$email || !$products) {
            return JsonResponder::generate($response, [
                'code' => 400,
                'message' => 'email and products are required',
                'data' => null
            ], 400);
        }
    
        if (!is_array($products)) {
            return JsonResponder::generate($response, [
                'code' => 401,
                'message' => 'invalid products',
                'data' => null
            ], 401);
        }
    
        $userModel = new User($this->db);
        $user = $userModel->fetchUserInfo($email);
    
        if (!$user) {
            return JsonResponder::generate($response, [
                'code' => 404,
                'message' => 'user not found',
                'data' => null
            ], 404);
        }
    
        $user_id = $user['user_id'];
    
        $cartModel = new Cart($this->db);
        $existingCart = $cartModel->fetchCartByUserId($user_id);
    
        // Validate products
        list($validProducts, $invalidProducts) = $this->validateProducts($products , $country);
    
        if ($existingCart) {
            $existingProducts = json_decode($existingCart['products'], true) ?? [];
    
            // Remove products: Filter out products to be removed
            foreach ($validProducts as $validProduct) {
                $slugToRemove = $validProduct['slug'] ?? null;
                if ($slugToRemove) {
                    foreach ($existingProducts as $key => $existingProduct) {
                        if ($existingProduct['slug'] === $slugToRemove) {
                            unset($existingProducts[$key]);
                            break;
                        }
                    }
                }
            }
    
            // Reindex the array
            $existingProducts = array_values($existingProducts);
    
            // Update the cart with remaining products
            $updatedCart = $cartModel->updateCart([
                'user_id' => $user_id,
                'products' => json_encode($existingProducts)
            ], $user['user_id']);
    
            if (!$updatedCart) {
                return JsonResponder::generate($response, [
                    'code' => 500,
                    'message' => 'failed to update cart',
                    'data' => null
                ], 500);
            }
    
            $count = count($existingProducts);
    
        } else {
            return JsonResponder::generate($response, [
                'code' => 404,
                'message' => 'cart not found',
                'data' => null
            ], 404);
        }
    
        return JsonResponder::generate($response, [
            'code' => 200,
            'message' => 'cart updated successfully',
            'data' => [
                'total' => count($products),
                'total_valid' => $count,
                'total_invalid' => count($invalidProducts),
                'remaining_products' => $existingProducts,
                'invalid_products' => $invalidProducts
            ]
        ], 200);
    }
    

    private function validateProducts(array $products , $country )
    {
        $validProducts = [];
        $invalidProducts = [];
    
        foreach ($products as $product) {
            $slug = $product['slug'] ?? null;
            // var_dump($slug);
            $productDetails = $this->fetchProductDetails($slug , $country );
            
            if ($productDetails) {
                $validProducts[] = array_merge($product, $productDetails);
            } else {
                $invalidProducts[] = $product;
            }
        }
    
        return [$validProducts, $invalidProducts];
    }
    
    // Method to fetch product details based on slug or ID
   private function fetchProductDetails($productSlugOrId, $country)
{
    $coursesModel = new Courses($this->db);
    $robotsModel = new Robots($this->db);
    $adminModel = new Admin($this->db);

    // Check if the product exists in Courses
    $courseDetails = $coursesModel->fetchCourseDetails($productSlugOrId);
    
    if ($courseDetails) {
        $authorDetails = $adminModel->fetchAdminInfo($courseDetails['author']);
        if ($authorDetails) {
            $price = ($country == 'Nigeria') 
                ? floatval(number_format((float)$courseDetails['price'], 2, '.', ''))
                : floatval(number_format((float)$courseDetails['usd'], 2, '.', ''));

            return [
                'type' => 'course',
                'title' => $courseDetails['title'],
                'author' => $courseDetails['author'],
                'price' => $price,
                // Add other attributes as needed
            ];
        }
    }

    // Check if the product exists in Robots
    $robotDetails = $robotsModel->fetchRobotDetails($productSlugOrId);
    // var_dump($robotDetails); exit;

    if ($robotDetails) {
        $authorDetails = $adminModel->fetchAdminInfo($robotDetails['author']);
        if ($authorDetails) {
            $price = ($country == 'Nigeria') 
                ? floatval(number_format((float)$robotDetails['price'], 2, '.', ''))
                : floatval(number_format((float)$robotDetails['usd'], 2, '.', ''));

            return [
                'type' => 'robot',
                'title' => $robotDetails['title'],
                'author' => $robotDetails['author'],
                'price' => $price,
                // Add other attributes as needed
            ];
        }
    }

    // If product not found in either model, return null
    return null;
}

    

}
