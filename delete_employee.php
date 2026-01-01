<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors','1');
error_reporting(E_ALL);

include 'db.php';
$showSuccess = false;
$DeleteSuccess = false;

if (isset($_POST['fetch_id'])) {
    $errors = [];

    $emid = $_POST['emid'] ?? '';
    if ($emid === '' ) {
        $errors[] = "ID is required";
    } elseif (!filter_var($emid, FILTER_VALIDATE_INT)) {
        $errors[] = "ID format not valid. Should be INT";
    }

    $stmt = $conn->prepare("SELECT * FROM Employee WHERE Employee_ID = ?");
    $stmt->bind_param("i", $emid);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $employee = $row;
        $showSuccess = true;
    } else {
        $errors[] = "No employee found with that ID.";
    }
    $stmt->close();
}

elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_employee'])) {
  $errors = [];

  $Reason = trim($_POST['Reason'] ?? '');
  if ($Reason === '' || strlen($Reason) > 255) {
      $errors[] = "Reason is required (max 255 chars).";
  }

  $Terminator_ID = trim($_POST['Terminator_ID'] ?? '');
  if ($Terminator_ID === '' || (!filter_var($Terminator_ID, FILTER_VALIDATE_INT))) {
      $errors[] = "Admin ID is required (INT).";
  }

  if (!empty($_POST['is_edit']) && $_POST['is_edit'] == '1' && !empty($_POST['ID'])) {

    $emp_id = intval($_POST['ID']); 
    $stmt1 = $conn->prepare("SELECT * FROM Employee WHERE ID = ?");
    $stmt1->bind_param("i", $emp_id);
    $stmt1->execute();
    $result = $stmt1->get_result();
    $employee = $result->fetch_assoc();
    
    $ins = $conn->prepare("
        INSERT INTO TerminationLog
          (Employee_ID,
          Name,
          Phone_Number,
          Department_ID,
          Position_ID,
          Email_ID,
          Date_Of_Birth,
          Home_Address,
          Contract_ID,
          Leave_ID,
          Office_ID,
          NIN,
          Salary,
          Start_Date,
          Terminated_At,
          Termination_Reason,
          Terminator_ID,
          Promotion_ID
          ) VALUES (?, ?, ?, ?,
          ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?,?)
      ");

      $ins->bind_param(
        "issiisssiiisissii",
        $employee['Employee_ID'],
        $employee['Name'],
        $employee['Phone_Number'],
        $employee['Department_ID'],
        $employee['Position_ID'],
        $employee['Email_ID'],
        $employee['Date_Of_Birth'],
        $employee['Home_Address'],
        $employee['Contract_ID'],
        $employee['Leave_ID'],
        $employee['Office_ID'],
        $employee['NIN'],
        $employee['Salary'],
        $employee['Start_Date'],
        $Reason,
        $Terminator_ID,
        $employee['Promotion_ID']
      );

      $ins->execute();
      $ins->close();

      $delcontact = $conn->prepare("DELETE FROM Employee_Emergency_Contact WHERE ID = ?");
      $delcontact->bind_param("i", $employee['ID']);
      $delcontact->execute();
      $delcontact->close();

      $del = $conn->prepare("DELETE FROM Employee WHERE ID = ?");
      $del->bind_param("i", $employee['ID']);
      if ($del->execute()) {
          $DeleteSuccess = true;
      }
      $del->close();
  }
}
?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Delete Employee</title>
    <style>
    .table-container {
      width: 100%;
      overflow-x: auto;
    }
    table {
      width: 100%;
      border-collapse: collapse;
    }
    th, td {
      border: 1px solid #aaa;
      padding: 8px;
      text-align: left;
      word-wrap: break-word;  
    }
    form {
      margin-bottom: 20px;
    }
    #successPopup {
      display: none;
      position: fixed;
      top:0; left:0; right:0; bottom:0;
      background: rgba(0,0,0,0.5);
      justify-content: center;
      align-items: center;
    }
    #successPopup .content {
      background: white;
      padding: 20px;
      border-radius: 6px;
      max-width: 300px;
      text-align: center;
    }
    .btn-home {
      display: inline-block;
      padding: 8px 16px;
      background-color: #A8D3F0;
      color: white;
      text-decoration: none;
      border-radius: 4px;
    }
    .btn-home:hover {
      background-color: #2592DA;
    }
 
  </style>
</head>
<body style="
      background-color: #E9F4FB;
      margin: 0;
      padding: 20px;
      font-family: Arial, sans‑serif;
    ">
  <a href="home.php" class="btn-home">Home</a>
  <div style="
      max-width: 800px;
      margin: 40px auto;
      padding: 20px;
      background: #A8D3F0;
      border-radius: 12px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
      font-family: Arial, sans-serif;
    ">
  <h1>Find Employee Information for Deletion</h1><br>
    <form method="post" action="">
        <label for="emid">Employee ID:</label>
        <input type="number" id="emid" name="emid" required><br><br>
        <button type="submit" name="fetch_id">Get Information</button>
    </form>
 
    <?php if ($showSuccess): ?>
        <form method="post" action="">
            <input type="hidden" name="is_edit" value="1">
            <input type="hidden" name="ID" value="<?php echo htmlspecialchars($employee['ID']); ?>">

            <h2>Termination Information</h2><br>
            <label for="Name">Name:</label>
            <input type="text" id="Name" name="Name" value="<?php echo htmlspecialchars($employee['Name']); ?>" maxlength="100" required><br><br>

            <label for="Reason">Reason for termination:</label>
            <input type="text" id="Reason" name="Reason" maxlength="255" required><br><br>

            <label for="Terminator_ID">Admin ID:</label>
            <input type="number" id="Terminator_ID" name="Terminator_ID" required><br><br>          

            <button type="submit" name="delete_employee">Delete Employee</button>
        </form>
    <?php endif; ?> 

    <?php if ($DeleteSuccess): ?>
      <div id="successPopup">
        <div class="content">
        <p>Employee terminated successfully!</p>
        </div>
      </div>
      <script>
          const popup = document.getElementById('successPopup');
          popup.style.display = 'flex';
          popup.addEventListener('click', function(e) {
          if (e.target === popup) {
              popup.style.display = 'none';
          }
          });
      </script> 
    <?php endif; ?> 

</body>
</html>
