<?php
require_once 'init.php';
session_start();

// Check if the user is logged in and session ID matches
if (!isset($_SESSION['user_id']) || !isset($_SESSION['session_id']) || session_id() !== $_SESSION['session_id']) {
    // If session ID doesn't match or user is not logged in, redirect to login page
    header("Location: login.php");
    exit();
}

// Fetch the username from the session
$username = $_SESSION['username'];
$welcome_message = htmlspecialchars($username);

// Display the welcome message
echo $welcome_message;

require_once 'check_permission.php';

// Check if the user has permission to manage users
if (!check_permission($conn, 'add_templates')) {
    header("Location: access_denied.html");
    exit();
}


?>

<!DOCTYPE html>
<html lang="en">

<head>
  <?php include 'head.html'; ?>
</head>

<body>
  <?php include 'admin-body.php'; ?>
  <main id="main" class="main">
    <div class="pagetitle">
      <h1>Form</h1>
    </div><!-- End Page Title -->

    <section class="section">
      <div class="row">
        <div class="col-lg-6" style="width: 100%;">

          <div class="card">
            <div class="card-body">
              <h5 class="card-title">Notification Template</h5>

              <!-- General Form Elements -->
              <form id="notificationForm" style="width: 100%;">
                <div class="row mb-3">
                    <label for="templateTitle" class="col-sm-2 col-form-label">Template Title</label>
                    <div class="col-sm-10">
                        <input type="text" id="templateTitle" class="form-control">
                    </div>
                </div>
                <div class="row mb-3">
                    <label for="templateContent" class="col-sm-2 col-form-label">Template</label>
                    <div class="col-sm-10">
                        <textarea id="templateContent" class="form-control" style="height: 100px"></textarea>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-sm-10 d-flex justify-content-center">
                        <button type="button" id="previousBtn" class="btn btn-primary mx-1">Previous</button>
                        <button type="button" id="updateBtn" class="btn btn-primary mx-1">Update</button>
                        <button type="button" id="dropBtn" class="btn btn-primary mx-1">Drop</button>
                        <button type="button" id="saveBtn" class="btn btn-primary mx-1">Save</button>
                        <button type="button" id="commitBtn" class="btn btn-primary mx-1">Commit</button>
                        <button type="button" id="nextBtn" class="btn btn-primary mx-1">Next</button>
                    </div>
                </div>
            </form>
              
              <!-- Page Number Display -->
              <div id="pageNumber" class="text-center mt-3">Page: 1</div>

            </div>
          </div>

        </div>
      </div>
    </section>
  </main><!-- End #main -->

  <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

  <!-- Vendor JS Files -->
  <script src="assets/vendor/apexcharts/apexcharts.min.js"></script>
  <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="assets/vendor/chart.js/chart.umd.js"></script>
  <script src="assets/vendor/echarts/echarts.min.js"></script>
  <script src="assets/vendor/quill/quill.js"></script>
  <script src="assets/vendor/simple-datatables/simple-datatables.js"></script>
  <script src="assets/vendor/tinymce/tinymce.min.js"></script>
  <script src="assets/vendor/php-email-form/validate.js"></script>

  <!-- Template Main JS File -->
  <script src="assets/js/main.js"></script>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
        let formDataArray = JSON.parse(localStorage.getItem('formDataArray')) || [];
        let currentIndex = -1;

        const templateTitle = document.getElementById('templateTitle');
        const templateContent = document.getElementById('templateContent');
        const previousBtn = document.getElementById('previousBtn');
        const nextBtn = document.getElementById('nextBtn');
        const saveBtn = document.getElementById('saveBtn');
        const updateBtn = document.getElementById('updateBtn');
        const dropBtn = document.getElementById('dropBtn');
        const commitBtn = document.getElementById('commitBtn');
        const pageNumber = document.getElementById('pageNumber');

        function updateFormDisplay(index) {
            if (index >= 0 && index < formDataArray.length) {
                templateTitle.value = formDataArray[index].title;
                templateContent.value = formDataArray[index].content;
            } else {
                templateTitle.value = '';
                templateContent.value = '';
            }
            updatePageNumber();
        }

        function updatePageNumber() {
            pageNumber.textContent = `Page: ${currentIndex + 1} of ${formDataArray.length}`;
        }

        function saveFormData() {
            localStorage.setItem('formDataArray', JSON.stringify(formDataArray));
        }

        function saveToBuffer(entry) {
            fetch('saveToBuffer.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(entry)
            })
            .then(response => response.json())
            .then(data => {
                console.log('Saved to server buffer:', data);
            })
            .catch(error => {
                console.error('Error saving to server buffer:', error);
            });
        }

        function updateBuffer(updatedEntry, originalTitle) {
            fetch('updateBuffer.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ ...updatedEntry, originalTitle })
            })
            .then(response => response.json())
            .then(data => {
                console.log('Updated in server buffer:', data);
            })
            .catch(error => {
                console.error('Error updating server buffer:', error);
            });
        }

        function deleteFromBuffer(title) {
            fetch('deleteFromBuffer.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ title })
            })
            .then(response => response.json())
            .then(data => {
                console.log('Deleted from server buffer:', data);
            })
            .catch(error => {
                console.error('Error deleting from server buffer:', error);
            });
        }

        saveBtn.addEventListener('click', function() {
            const newEntry = {
                title: templateTitle.value,
                content: templateContent.value
            };

            // Client-side buffering: Save to localStorage
            formDataArray.push(newEntry);
            currentIndex = formDataArray.length - 1;
            updateFormDisplay(currentIndex);
            saveFormData();

            // Server-side buffering: Save to buffer
            saveToBuffer(newEntry);
        });

        commitBtn.addEventListener('click', function() {
            const newEntry = {
                title: templateTitle.value,
                content: templateContent.value
            };

            fetch('saveToDatabase.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(newEntry)
            })
            .then(response => response.json())
            .then(data => {
                console.log('Saved to database:', data);
                if (data.message === 'Data successfully saved to database.') {
                    formDataArray.push(newEntry);
                    currentIndex = formDataArray.length - 1;
                    updateFormDisplay(currentIndex);
                    saveFormData();

                    // Save to buffer as well
                    saveToBuffer(newEntry);
                } else {
                    console.error('Database error:', data.message);
                }
            })
            .catch(error => {
                console.error('Error saving to database:', error);
            });
        });

        updateBtn.addEventListener('click', function() {
            if (currentIndex >= 0) {
                const updatedEntry = {
                    title: templateTitle.value,
                    content: templateContent.value
                };

                fetch('updateDatabase.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ ...updatedEntry, originalTitle: formDataArray[currentIndex].title })
                })
                .then(response => response.json())
                .then(data => {
                    console.log('Updated in database:', data);
                    if (data.message === 'Data successfully updated in database.') {
                        const originalTitle = formDataArray[currentIndex].title;
                        formDataArray[currentIndex] = updatedEntry;
                        updateFormDisplay(currentIndex);
                        saveFormData();

                        // Update in buffer as well
                        updateBuffer(updatedEntry, originalTitle);
                    } else {
                        console.error('Database error:', data.message);
                    }
                })
                .catch(error => {
                    console.error('Error updating in database:', error);
                });
            }
        });

        dropBtn.addEventListener('click', function() {
            if (currentIndex >= 0) {
                const titleToDelete = formDataArray[currentIndex].title;

                fetch('deleteFromDatabase.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ title: titleToDelete })
                })
                .then(response => response.json())
                .then(data => {
                    console.log('Deleted from database:', data);
                    if (data.message === 'Data successfully deleted from database.') {
                        formDataArray.splice(currentIndex, 1);
                        currentIndex = Math.min(currentIndex, formDataArray.length - 1);
                        updateFormDisplay(currentIndex);
                        saveFormData();

                        // Delete from buffer as well
                        deleteFromBuffer(titleToDelete);
                    } else {
                        console.error('Database error:', data.message);
                    }
                })
                .catch(error => {
                    console.error('Error deleting from database:', error);
                });
            }
        });

        previousBtn.addEventListener('click', function() {
            if (currentIndex > 0) {
                currentIndex--;
                updateFormDisplay(currentIndex);
            }
        });

        nextBtn.addEventListener('click', function() {
            if (currentIndex < formDataArray.length - 1) {
                currentIndex++;
                updateFormDisplay(currentIndex);
            } else {
                currentIndex++;
                updateFormDisplay(currentIndex);
            }
        });

        updateFormDisplay(currentIndex);
    });
  </script>

</body>

</html>
