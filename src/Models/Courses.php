<?php

namespace App\Models;

use App\Database\Database;
use PDOException;
use PDO;
use Monolog\Logger;

class Courses extends Database
{
    protected $table = 'courses';
    protected $fillable = [
        'title',
        'slug',
        'price',
        'usd',
        'author',
        'description',
        'video',
        'cat_id',
        'type_id',
        'course_videos',
        'quiz_videos',
        'live_session_videos',
        'image',
        'course_id',
        'created_at',
        'last_updated_at',
    ];
    
    protected $db;

    public function __construct(Database $db)
    {
        parent::__construct(); // Initialize the database connection
        $this->db = $db;
    }

    public function getCoursesCount()
    {
        $stmt = $this->db->query("SELECT COUNT(*) FROM {$this->table}");
        return $stmt->fetchColumn();
    }

    public function getCourses($limit = 10, $offset = 0, $sortBy = 'title', $orderDirection = 'asc', $search = null)
    {
        // Define valid and forbidden fields
        $forbiddenFields = ['password', 'user_id', 'last_login'];
        $validColumns = array_diff([
            'title',
            'slug',
            'price',
            'usd',
            'author',
            'description',
            // 'video',
            'cat_id',
            'type_id',
            // 'course_videos',
            // 'quiz_videos',
            // 'live_session_videos',
            'image',
            'course_id',
            // 'created_at',
            'last_updated_at',
        ]);

        // Map column index to column name
        $columnIndexToName = [
            0 => 'sn',
            1 => 'surname',
            2 => 'username',
            3 => 'role'
            // Add more mappings as needed for additional columns
        ];

        // Ensure sortBy is a valid column
        if (!in_array($sortBy, $validColumns)) {
            $sortBy = 'title';
        }

        // Start building the query
        $sql = "SELECT " . implode(", ", $validColumns) . " FROM {$this->table}";

        // Apply search condition if provided
        if ($search !== null) {
            $searchConditions = [];
            foreach ($validColumns as $column) {
                $searchConditions[] = "$column LIKE :search";
            }
            $sql .= " WHERE " . implode(" OR ", $searchConditions);
        }

        // Apply sorting, limit, and offset
        $sql .= " ORDER BY $sortBy $orderDirection";
        $sql .= " LIMIT :limit OFFSET :offset";

        // Prepare and execute the query
        $stmt = $this->db->prepare($sql);

        // Bind parameters
        if ($search !== null) {
            $stmt->bindValue(':search', '%' . $search . '%', PDO::PARAM_STR);
        }
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);

        $stmt->execute();

        // Fetch and return results
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    // Create or update user based on data array
    public function createCourse(array $data)
    {
        $columns = implode(', ', array_keys($data));
        $values = ':' . implode(', :', array_keys($data));

        $query = "INSERT INTO {$this->table} ($columns) VALUES ($values)";

        try {
            $stmt = $this->db->prepare($query);

            foreach ($data as $key => $value) {
                $stmt->bindValue(":$key", $value);
            }

            $stmt->execute();
            return true;
        } catch (PDOException $e) {
            // var_dump($e->getMessage());
            return false;
        }
    }

    // Update user based on email and data array
    public function updateCourse(array $data, $cat_id) {
        $query = "UPDATE {$this->table} SET ";

        foreach ($data as $key => $value) {
            $query .= "$key = :$key, ";
        }

        $query = rtrim($query, ', ');
        $query .= " WHERE cat_id = :cat_id";

        try {
            $stmt = $this->db->prepare($query);

            foreach ($data as $key => $value) {
                $stmt->bindValue(":$key", $value);
            }
            $stmt->bindValue(":cat_id", $cat_id);

            $stmt->execute();
            return true;
        } catch (PDOException $e) {
            // var_dump($e->getMessage());
            return false;
        }
    }

    // Delete a user by ID
    public function deleteCourse($cat_id)
    {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE cat_id = :cat_id");
        $stmt->execute(['cat_id' => $cat_id]);
        return $stmt->rowCount() > 0;
    }

    public function fetchCourseDetails( $identifier) {
        
        $query = "SELECT * FROM {$this->table} WHERE title = :title OR slug = :slug OR  course_id = :course_id ";

        try {
            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':title', $identifier);
            $stmt->bindValue(':course_id', $identifier);
            $stmt->bindValue(':slug', $identifier);
            $stmt->execute();
            $category = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$category) {
                return false;
            }

            return $category;

        } catch (PDOException $e) {
            return false;
        }

    }

    public function fetchCourseById($courseId) {
        
        $query = "SELECT * FROM {$this->table} WHERE course_id = :course_id";

        try {
            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':course_id', $courseId);
            $stmt->bindValue(':name', $courseId);
            $stmt->execute();
            $category = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$category) {
                return false;
            }

            return $category;

        } catch (PDOException $e) {
            return false;
        }

    }

    public function fetchCourseByTitle($courseTitle) {
        
        $query = "SELECT * FROM {$this->table} WHERE title = :course_title";

        try {
            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':course_title', $courseTitle);
            $stmt->execute();
            $category = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$category) {
                return false;
            }

            return $category;

        } catch (PDOException $e) {
            return false;
        }

    }


    // Additional methods can be added here as needed
       public function getSingleCourse($slug)
{
    $forbiddenFields = ['password', 'user_id', 'last_login'];
    $validColumns = array_diff([
        'title',
        'slug',
        'price',
        'usd',
        'author',
        'description',
        "course_videos",
        "quiz_videos",
        "live_session_videos",
        'course_id',
        'cat_id',
        'type_id',
        'image',
        'last_updated_at',
    ], $forbiddenFields);

    $sql = "SELECT " . implode(", ", $validColumns) . " FROM {$this->table} WHERE slug = :slug LIMIT 1";
    
    $stmt = $this->db->prepare($sql);
    $stmt->bindValue(':slug', $slug, PDO::PARAM_STR);

    $stmt->execute();

  
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

    // Additional methods can be added here as needed
    public function updateCourses(array $data, $robot_id)
   
{
    
     $query = "UPDATE {$this->table} SET "; 

    foreach ($data as $key => $value) {
        $query .= "$key = :$key, ";
    }

    $query = rtrim($query, ', ');
    $query .= " WHERE slug = :robot_id";
    
    try {
        $stmt = $this->db->prepare($query);

        foreach ($data as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }
        $stmt->bindValue(":robot_id", $robot_id);

        $stmt->execute();
        return true;
    } catch (PDOException $e) {
        return false;
    }
}
}
