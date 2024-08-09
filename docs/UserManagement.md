# User (UserController)

## GET /user/details
- **Description:** Retrieves user details.
- **JWT Required:** Yes
- **Query Parameters:**
  - `email`: The user's email address.
- **Response:**
  - Success (200):
    ```json
      
    {
        "first_name": "Shifu-Nfor",
        "last_name": "Nyuiring-yoh Rhagninyui",
        "middle_name": "Miracle",
        "email": "nforshifu.234@gmail.com",
        "address": "123 Street, City",
        "user_id": "FMQE12cXrc7IYWwHmAQr",
        "status": "active",
        "courses_purchased": 1,
        "robots_purchased": 2,
        "joined_telegram": 0,
        "last_logged_in": "2024-06-16 13:15:25",
        "verified_email": 1,
        "courses": [
            {
                "id": 2,
                "title": "Test Course",
                "slug": "test-course",
                "price": 12345,
                "author": "pcAUzmbsQcSEDKUKI2Cf",
                "description": "Test description",
                "video": "",
                "cat_id": "VWhKgpYfdaFz6r2NoJzu",
                "type_id": "vwrher87698mt9097r5enbsvacr35tvs4d6bf75",
                "image": "img_667071192ee442.08136390.png",
                "course_videos": [
                    "http://localhost:8000/uploads/courses/MjG5UbJ5JxYoASGwUhMr/course/chapter_01.mp4",
                    "http://localhost:8000/uploads/courses/MjG5UbJ5JxYoASGwUhMr/course/chapter_02.mp4",
                    "http://localhost:8000/uploads/courses/MjG5UbJ5JxYoASGwUhMr/course/chapter_03.mp4",
                    "http://localhost:8000/uploads/courses/MjG5UbJ5JxYoASGwUhMr/course/chapter_04.mp4",
                    "http://localhost:8000/uploads/courses/MjG5UbJ5JxYoASGwUhMr/course/chapter_05.mp4",
                    "http://localhost:8000/uploads/courses/MjG5UbJ5JxYoASGwUhMr/course/chapter_06.mp4"
                ],
                "quiz_videos": [
                    "http://localhost:8000/uploads/courses/MjG5UbJ5JxYoASGwUhMr/quiz/quiz_chapter_01.mp4",
                    "http://localhost:8000/uploads/courses/MjG5UbJ5JxYoASGwUhMr/quiz/quiz_chapter_02.mp4",
                    "http://localhost:8000/uploads/courses/MjG5UbJ5JxYoASGwUhMr/quiz/quiz_chapter_03.mp4",
                    "http://localhost:8000/uploads/courses/MjG5UbJ5JxYoASGwUhMr/quiz/quiz_chapter_04.mp4",
                    "http://localhost:8000/uploads/courses/MjG5UbJ5JxYoASGwUhMr/quiz/quiz_chapter_05.mp4",
                    "http://localhost:8000/uploads/courses/MjG5UbJ5JxYoASGwUhMr/quiz/quiz_chapter_06.mp4"
                ],
                "live_session_videos": [
                    "http://localhost:8000/uploads/courses/MjG5UbJ5JxYoASGwUhMr/live_session/live_session_01.mp4"
                ],
                "course_id": "MjG5UbJ5JxYoASGwUhMr",
                "created_at": "2024-06-17 18:23:37",
                "last_updated_at": "2024-06-17 18:23:37"
            }
        ],
        "robots": [
            {
                "id": 1,
                "title": "Mine on Toron Network for 20 days",
                "slug": "mine-on-toron-network-for-20-days",
                "price": 12345,
                "author": "pcAUzmbsQcSEDKUKI2Cf",
                "description": "Robot Description",
                "zip": "http://localhost:8000/uploads/robots/NuYdXRyHXzZEsTwgvKmk/robots/zip_66703d01c4e716.15645965.zip",
                "cat_id": "qZG7eeSngNIHXzqMjGPn",
                "type_id": "dtunjd8nofiymtngiponiftr5se",
                "image": "http://localhost:8000/uploads/robots/NuYdXRyHXzZEsTwgvKmk/img/img_66703d01c4a213.72247695.png",
                "robot_id": "NuYdXRyHXzZEsTwgvKmk",
                "created_at": "2024-06-17 14:41:21",
                "last_updated_at": "2024-06-17 14:41:21"
            },
            {
                "id": 2,
                "title": "Test Robot",
                "slug": "test-robot",
                "price": 12345,
                "author": "pcAUzmbsQcSEDKUKI2Cf",
                "description": "Test description",
                "zip": "http://localhost:8000/uploads/robots/h4me3irQuP7cP61oVezg/robots/zip_6670710f8cec62.29050979.zip",
                "cat_id": "qZG7eeSngNIHXzqMjGPn",
                "type_id": "dtunjd8nofiymtngiponiftr5se",
                "image": "http://localhost:8000/uploads/robots/h4me3irQuP7cP61oVezg/img/img_6670710f8c95d1.82367037.jpg",
                "robot_id": "h4me3irQuP7cP61oVezg",
                "created_at": "2024-06-17 18:23:27",
                "last_updated_at": "2024-06-17 18:23:27"
            }
        ]
    }

    ```


    ```
      ?email=user@example.com
    ```
    
  - Error (400/401/404):
    ```json
    {
      "code": 400/401/404,
      "message": "Error message",
      "data": null
    }
    ```

## POST /user/details/update
- **Description:** Updates user details.
- **JWT Required:** Yes
- **Request Body:**
  ```json
  {
    "first_name": "John",
    "last_name": "Doe",
    "middle_name": "M",
    "address": "456 Avenue, Town",
    "email": "user@example.com",
    "old_password": "old_password",
    "new_password": "new_password",
    "confirm_new_password": "new_password"
  }
  ```
- **Response:**
  - Success (200):
    ```json
    {
      "code": 200,
      "message": "User updated successfully",
      "data": null
    }
    ```
  - Error (400/401/404):
    ```json
    {
      "code": 400/401/404,
      "message": "Error message",
      "data": null
    }
    ```

---

### NFORSHIFU234 Dev👨🏾‍💻🖤. &copy; 2024

---