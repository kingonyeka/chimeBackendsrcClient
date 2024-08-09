## Base URL

```javascript
const BASE_URL = "http://localhost:8000" // remeber to change it in the JS file
```

## Authentication

All endpoints require a Bearer token for authorization. Include the following header in your requests:

```
Authorization: Bearer <your_token>
```

## Endpoints

### 1. File Upload Endpoint

#### URL

```
POST /upload
```

#### Description

Uploads file chunks to the server. This endpoint supports chunked file uploads to handle large files.

#### Request Headers

```json
{
    "Authorization": "Bearer <your_token>"
}
```

#### Request Body (Form Data)

| Field          | Type   | Description                                    |
|----------------|--------|------------------------------------------------|
| file           | File   | The file chunk being uploaded                  |
| chunkNumber    | Number | The chunk number of the current file piece     |
| totalChunks    | Number | The total number of chunks for the file        |
| filename       | String | The original name of the file                  |
| course_title   | String | (Optional) The title of the course or robot    |
| upload_type    | String | (Optional) The type of the upload (`course`, `quiz`, `live_session`, `robots`) |

#### Example Request

```javascript
const formData = new FormData();
formData.append('file', chunk);
formData.append('chunkNumber', chunkNumber);
formData.append('totalChunks', totalChunks);
formData.append('filename', fileName);
formData.append('course_title', courseTitle);
formData.append('upload_type', uploadType);

const response = await fetch(`${BASE_URL}/upload`, {
    method: 'POST',
    headers: {
        'Authorization': `Bearer ${token}`
    },
    body: formData
});
```

#### Response

##### Success (200)

```json
{
    "fileName": "uploaded_filename.ext",
    "message": "File assembled successfully."
}
```

##### Chunk Upload Success (200)

```json
{
    "message": "Chunk <chunkNumber> of <totalChunks> uploaded successfully."
}
```

##### Failure (400/500)

```json
{
    "message": "Error message detailing what went wrong."
}
```

---

### 2. Create Course Endpoint

#### URL

```
POST /admin/course/create
```

#### Description

Creates a new course with the provided details and uploaded files.

#### Request Headers

```json
{
    "Authorization": "Bearer <your_token>"
}
```

#### Request Body (Form Data)

| Field                | Type   | Description                                |
|----------------------|--------|--------------------------------------------|
| title                | String | The title of the course                    |
| price                | Number | The price of the course                    |
| description          | String | The description of the course              |
| author               | String | The author ID                              |
| category             | String | The category ID                            |
| type                 | String | The type of category                       |
| image_name           | String | The name of the uploaded image file        |
| course_video_names[] | Array  | Array of course video file names           |
| quiz_video_names[]   | Array  | Array of quiz video file names             |
| live_session_video_names[] | Array  | Array of live session video file names |

#### Example Request

```javascript
const formData = new FormData();
formData.append('title', 'Course Title');
formData.append('price', 99.99);
formData.append('description', 'Course Description');
formData.append('author', 'author_id');
formData.append('category', 'category_id');
formData.append('type', 'category_type');
formData.append('image_name', 'image.jpg');
formData.append('course_video_names[]', 'chapter_01.mp4');
formData.append('quiz_video_names[]', 'quiz_chapter_01.mp4');
formData.append('live_session_video_names[]', 'live_session_01.mp4');

const response = await fetch(`${BASE_URL}/admin/course/create`, {
    method: 'POST',
    headers: {
        'Authorization': `Bearer ${token}`
    },
    body: formData
});
```

#### Response

##### Success (201)

```json
{
    "code": 201,
    "message": "course created successfully",
    "data": null
}
```

##### Failure (401)

```json
{
    "code": 401,
    "message": "error message detailing what went wrong",
    "data": null
}
```

---

### 3. Create Robot Endpoint

#### URL

```
POST /admin/robots/create
```

#### Description

Creates a new robot with the provided details and uploaded files.

#### Request Headers

```json
{
    "Authorization": "Bearer <your_token>"
}
```

#### Request Body (Form Data)

| Field          | Type   | Description                                |
|----------------|--------|--------------------------------------------|
| title          | String | The title of the robot                     |
| price          | Number | The price of the robot                     |
| description    | String | The description of the robot               |
| author         | String | The author ID                              |
| category       | String | The category ID                            |
| type           | String | The type of category                       |
| image_name     | String | The name of the uploaded image file        |
| zip_file_name  | String | The name of the uploaded zip file          |

#### Example Request

```javascript
const formData = new FormData();
formData.append('title', 'Robot Title');
formData.append('price', 99.99);
formData.append('description', 'Robot Description');
formData.append('author', 'author_id');
formData.append('category', 'category_id');
formData.append('type', 'category_type');
formData.append('image_name', 'image.jpg');
formData.append('zip_file_name', 'robot.zip');

const response = await fetch(`${BASE_URL}/admin/robots/create`, {
    method: 'POST',
    headers: {
        'Authorization': `Bearer ${token}`
    },
    body: formData
});
```

#### Response

##### Success (201)

```json
{
    "code": 201,
    "message": "robot created successfully",
    "data": null
}
```

##### Failure (401)

```json
{
    "code": 401,
    "message": "error message detailing what went wrong",
    "data": null
}
```

---

## Handling File Uploads

In the frontend, files are uploaded in chunks to handle large file sizes. The `uploadFile` function handles this process by sending each chunk sequentially to the `/upload` endpoint.

### Example `uploadFile` Function

```javascript
async function uploadFile(file, courseTitle = null, uploadType = null) {
    const chunkSize = 1024 * 1024; // 1MB
    const totalChunks = Math.ceil(file.size / chunkSize);
    const fileName = file.name;

    for (let chunkNumber = 0; chunkNumber < totalChunks; chunkNumber++) {
        const start = chunkNumber * chunkSize;
        const end = Math.min(file.size, start + chunkSize);
        const chunk = file.slice(start, end);

        const formData = new FormData();
        formData.append('file', chunk);
        formData.append('chunkNumber', chunkNumber);
        formData.append('totalChunks', totalChunks);
        formData.append('filename', fileName);

        if (courseTitle) {
            formData.append('course_title', courseTitle);
        }

        if (uploadType) {
            formData.append('upload_type', uploadType);
        }

        const response = await fetch(`${BASE_URL}/upload`, {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${token}`
            },
            body: formData
        });

        const result = await response.json();

        if (response.status !== 200) {
            alert(result.message);
            throw new Error(result.message);
        }

        if (chunkNumber === totalChunks - 1) {
            return result;
        }
    }
}
```

### Handling Form Submission

The form submission process involves uploading files and then sending the form data to create the course or robot.

```javascript
form.addEventListener('submit', async (event) => {
    event.preventDefault();

    const formData = new FormData(form);
    const imageFile = document.getElementById('image').files[0];
    const courseTitle = form.querySelector("#title").value;

    if (imageFile) {
        const imageUploadResult = await uploadFile(imageFile, courseTitle);
        formData.append('image_name', imageUploadResult.fileName);
    }

    const assets = document.getElementById('asset')?.files;
    if (assets && assets.length > 0) {
        await handleFileUpload(assets, 'course');
    }

    fetch(`${BASE_URL}/admin/course/create`, {
        method: 'POST',
        headers: {
            'Authorization': `Bearer ${token}`
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.code === 201) {
            alert(data.message);
            form.reset();
        } else {
            alert(data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert(error);
    });
});

async function handleFileUpload(files, type) {
    for (let file of files) {
        await uploadFile(file, courseTitle

, type);
    }
}
```



---

##### NFORSHIFU234 Dev👨🏾‍💻🖤. &copy; 2024

---