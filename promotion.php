<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors','1');
error_reporting(E_ALL);

include 'db.php';
$showSuccess = false;
$showUpdate = false;

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

elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_employee'])) {

    $errors = [];
    $raw_salary = $_POST['Current_Salary'] ?? '';
    if ($raw_salary !== '') {
        if (!filter_var($raw_salary, FILTER_VALIDATE_FLOAT)) {
            $errors[] = "Invalid Format.";
        } else {
            $Salary = (float)$raw_salary;
        }
    } else {
        $Salary = null;
    }

    $payraise = $_POST['Payraise'] ?? '';
    if ($payraise !== '') {
        if (!filter_var($payraise, FILTER_VALIDATE_FLOAT)) {
            $errors[] = "Invalid Format.";
        } else {
            $Pay = $payraise;
        }
    } else {
        $Pay = null;
    }

    $new_Salary = $Salary + (($Salary*$Pay)/100);

    if (!empty($_POST['is_edit']) && $_POST['is_edit'] == '1' && !empty($_POST['ID'])) {
        $ins = $conn->prepare("
            INSERT INTO Promotions
            (Old_Salary,
            New_Salary,
            Payraise_Percent,
            Date_Promoted
            ) VALUES (?, ?, ?, NOW())
        ");

        $ins->bind_param(
            "ddd",
            $Salary,
            $new_Salary,
            $Pay
        );

        $ins->execute();
        $ins->close();

        $new_pr_id = $conn->insert_id;

        $update_sql = "
        UPDATE Employee
        SET Salary=?, Promotion_ID=?
        WHERE ID = ?
        ";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param(
        "dii",
        $new_Salary,
        $new_pr_id,
        $_POST['ID']
        );
        if ($stmt->execute()) {
            $showUpdate = true;
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Promotion</title>
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
  <h1>Find Employee Information for Promotion</h1><br>
    <form method="post" action="">
        <label for="emid">Employee ID:</label>
        <input type="number" id="emid" name="emid" required><br><br>
        <button type="submit" name="fetch_id">Get Information</button>
    </form>
 
    <?php if ($showSuccess): ?>
        <form method="post" action="">
            <input type="hidden" name="is_edit" value="1">
            <input type="hidden" name="ID" value="<?php echo htmlspecialchars($employee['ID']); ?>">

            <h2>Salary Details</h2><br>
            <label for="Name">Name:</label>
            <input type="text" id="Name" name="Name" value="<?php echo htmlspecialchars($employee['Name']); ?>" maxlength="100" required><br><br>

            <label for="Current_Salary">Current Salary:</label>
            <input type="number" id="Current_Salary" name="Current_Salary" value="<?php echo htmlspecialchars($employee['Salary']); ?>" step="0.01"><br><br>

            <label for="Payraise">Payraise (%):</label>
            <input type="number" id="Payraise" name="Payraise" step="0.01" ><br><br><br>

            <button type="submit" name="submit_employee">Apply Promotion</button>
        </form>
    <?php endif; ?>

    <?php if ($showUpdate): ?>
        <div id="successPopup">
            <div class="content">
            <p>Salary updated successfully!</p>
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
