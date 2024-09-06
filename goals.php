<?php
session_start();
require_once 'init.php'; // Include the initialization file

// Check if the user is logged in and session ID matches
if (!isset($_SESSION['user_id']) || !isset($_SESSION['session_id']) || session_id() !== $_SESSION['session_id']) {
    // If session ID doesn't match or user is not logged in, redirect to login page
    header("Location: login.php");
    exit();
}

// Fetch the username from the session
$username = $_SESSION['username'];
$welcome_message = htmlspecialchars($username);

// Fetch the goals data from the database
$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM Goals WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$goals = $result->fetch_all(MYSQLI_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">

<head>
<?php include 'head.html'; ?>
</head>

<body>
<?php include 'body.php'; ?>
<main id="main" class="main">
    <div class="pagetitle">
      <h1>Goals</h1>
    </div><!-- End Page Title -->

    <section class="section">
      <div class="row">
        <div class="col-lg-12">
          <div class="card">
            <div class="card-body">
              
              <!-- Table with stripped rows -->
              <table class="table datatable">
                <thead>
                  <tr>
                    <th>Title</th>
                    <th>Target amount</th>
                    <th>Current Amount</th>
                    <th data-type="date" data-format="YYYY/DD/MM">Target Date</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($goals as $goal): ?>
                  <tr>
                    <td><?php echo htmlspecialchars($goal['goal_name']); ?></td>
                    <td><?php echo htmlspecialchars($goal['target_amount']); ?></td>
                    <td><?php echo htmlspecialchars($goal['current_amount']); ?></td>
                    <td><?php echo htmlspecialchars($goal['target_date']); ?></td>
                    <td>
                      <button class="edit-btn btn btn-warning" data-id="<?php echo $goal['goal_id']; ?>">Edit</button>
                      <button class="delete-btn btn btn-danger" data-id="<?php echo $goal['goal_id']; ?>">Delete</button>
                    </td>
                  </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
              <!-- End Table with stripped rows -->

              <!-- Form for Adding/Editing Goals -->
              <div id="goal-form-container" style="display:none;">
                <form id="goal-form" method="post" action="save_goal.php">
                  <input type="hidden" name="goal_id" id="goal_id">
                  <div class="form-group">
                    <label for="goal_name">Title</label>
                    <input type="text" class="form-control" id="goal_name" name="goal_name" required>
                  </div>
                  <div class="form-group">
                    <label for="target_amount">Target Amount</label>
                    <input type="number" step="0.01" class="form-control" id="target_amount" name="target_amount" required>
                  </div>
                  <div class="form-group">
                    <label for="current_amount">Current Amount</label>
                    <input type="number" step="0.01" class="form-control" id="current_amount" name="current_amount" required>
                  </div>
                  <div class="form-group">
                    <label for="target_date">Target Date</label>
                    <input type="date" class="form-control" id="target_date" name="target_date" required>
                  </div>
                  <button type="submit" class="btn btn-primary">Save</button>
                  <button type="button" class="btn btn-secondary" id="cancel-btn">Cancel</button>
                </form>
              </div>

            </div>
          </div>
        </div>
      </div>
      <!-- Add New Button -->
      <button id="add-new-btn" class="btn btn-primary mb-3">Add New</button>
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
    const addNewBtn = document.getElementById('add-new-btn');
    const goalFormContainer = document.getElementById('goal-form-container');
    const goalForm = document.getElementById('goal-form');
    const cancelBtn = document.getElementById('cancel-btn');
    const editButtons = document.querySelectorAll('.edit-btn');
    const deleteButtons = document.querySelectorAll('.delete-btn');

    addNewBtn.addEventListener('click', function() {
        goalForm.reset();
        goalForm.querySelector('#goal_id').value = '';
        goalFormContainer.style.display = 'block';
    });

    cancelBtn.addEventListener('click', function() {
        goalFormContainer.style.display = 'none';
    });

    editButtons.forEach(button => {
        button.addEventListener('click', function() {
            const goalId = this.dataset.id;
            fetch('get_goal.php?id=' + goalId)
                .then(response => response.json())
                .then(data => {
                    goalForm.querySelector('#goal_id').value = data.goal_id;
                    goalForm.querySelector('#goal_name').value = data.goal_name;
                    goalForm.querySelector('#target_amount').value = data.target_amount;
                    goalForm.querySelector('#current_amount').value = data.current_amount;
                    goalForm.querySelector('#target_date').value = data.target_date;
                    goalFormContainer.style.display = 'block';
                });
        });
    });

    deleteButtons.forEach(button => {
        button.addEventListener('click', function() {
            if (confirm('Are you sure you want to delete this goal?')) {
                const goalId = this.dataset.id;
                fetch('delete_goal.php?id=' + goalId)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            window.location.reload();
                        } else {
                            alert('Failed to delete goal.');
                        }
                    });
            }
        });
    });
});
</script>

</body>
</html>
