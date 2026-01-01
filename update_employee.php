<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors','1');
error_reporting(E_ALL);

include 'db.php';
$showSuccess = false;
$update_success = false;

$dept_sql = "SELECT Department_ID, Name FROM Department ORDER BY Name";
$dept_result = mysqli_query($conn, $dept_sql);
if (!$dept_result) {
  die("Departments query failed: " . mysqli_error($conn));
}

$contract_sql = "SELECT Contract_ID, Contract_Type FROM Contract ORDER BY Contract_Type";
$contract_result = mysqli_query($conn, $contract_sql);
if (!$contract_result) {
  die("Contracts query failed: " . mysqli_error($conn));
}

$office_sql = "SELECT Office_ID, Office_Name FROM Office ORDER BY Office_Name";
$office_result = mysqli_query($conn, $office_sql);
if (!$office_result) {
  die("Office query failed: " . mysqli_error($conn));
}

$job_sql = "SELECT Position_ID, Position_Title FROM JobPosition ORDER BY Position_Title";
$job_result = mysqli_query($conn, $job_sql);
if (!$job_result) {
  die("JobPosition query failed: " . mysqli_error($conn));
}

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

    $Name = trim($_POST['Name'] ?? '');
    if ($Name === '' || strlen($Name) > 100) {
        $errors[] = "Name is required (max 100 chars).";
    }

    $raw_email = $_POST['Email_ID'] ?? '';
    if ($raw_email === '') {
        $Email_ID = null;
    } else {
        $Email_ID = filter_var($raw_email, FILTER_SANITIZE_EMAIL);
        if (!$Email_ID || !filter_var($Email_ID, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Invalid email address.";
        }
    }

    $raw_phone = $_POST['Phone_Number'] ?? '';
    if ($raw_phone !== '' && strlen($raw_phone)<100) {
        $Phone_Number = $raw_phone;
    } else {
        $Phone_Number = null;
    }

    $raw_salary = $_POST['Salary'] ?? '';
    if ($raw_salary !== '') {
        if (!filter_var($raw_salary, FILTER_VALIDATE_FLOAT)) {
            $errors[] = "Salary must be a valid double value.";
        } else {
            $Salary = (float)$raw_salary;
        }
    } else {
        $Salary = null;
    }

    $raw_dob = $_POST['Date_Of_Birth'] ?? '';
    $dob = null;
    if ($raw_dob !== '') {
        $d = DateTime::createFromFormat('Y-m-d', $raw_dob);
        if (!$d || $d->format('Y-m-d') !== $raw_dob) {
            $errors[] = "Invalid date of birth.";
        } else {
            $dob = $raw_dob;
        }
    } else {
        $errors[] = "Date of birth is required.";
    }

    $raw_start = $_POST['Start_Date'] ?? '';
    $Start_Date = null;
    if ($raw_start !== '') {
        $d2 = DateTime::createFromFormat('Y-m-d', $raw_start);
        if (!$d2 || $d2->format('Y-m-d') !== $raw_start) {
            $errors[] = "Invalid start date.";
        } else {
            $Start_Date = $raw_start;
        }
    }

    $raw_home_address = trim($_POST['Home_Address'] ?? '');
    if ($raw_home_address !== '' || strlen($raw_home_address) > 255 ) {
        $home_address = $raw_home_address;
    } else {
        $home_address = null;
    }

    $nin = trim($_POST['NIN'] ?? '');
    if ($nin === '') {
        $nin = null;
    }

    $dept_raw     = $_POST['department_id']  ?? '';
    $pos_raw      = $_POST['position_id']    ?? '';
    $office_raw   = $_POST['office_id']      ?? '';
    $contract_raw   = $_POST['contract_id']      ?? '';

    if (!filter_var($pos_raw, FILTER_VALIDATE_INT) || (int)$pos_raw <= 0) {
        $errors[] = "Invalid position selected.";
    } else {
        $position_id = (int)$pos_raw;
    }

    if ($position_id === 9 || $position_id === 10 || $position_id === 12 ) { 
        $department_id = 4;
    } elseif ($position_id === 7 || $position_id === 6 || $position_id === 11 ) {
        $department_id = 3;
    } elseif ($position_id === 2 || $position_id === 4 || $position_id === 5 ) {
        $department_id = 1;
    } elseif ($position_id === 1 || $position_id === 8 ) {
        $department_id = 2;
    } elseif ($dept_raw === '' ) {
        $department_id = null;    
    } else {
        $department_id = (int)$dept_raw;
    }

    if (!filter_var($contract_raw, FILTER_VALIDATE_INT) || (int)$contract_raw <= 0) {
        $errors[] = "Invalid contract selected.";
    } else {
        $contract_id = (int)$contract_raw;
    }

    if ($office_raw === '' ) {
        $office_id = null;
    } else if (filter_var($office_raw, FILTER_VALIDATE_INT)) {
        $office_id = (int)$office_raw;
    } else {
        $errors[] = "Invalid office selected.";
    }

    $ename = trim($_POST['Emergency_Name'] ?? '');
    if ($ename === '') {
        $ename = null;
    }

    $erelation = trim($_POST['Emergency_Relation'] ?? '');
    if ($erelation === '') {
        $erelation = null;
    }

    $enumber = trim($_POST['Emergency_Number'] ?? '');
    if ($enumber === '') {
        $enumber = null;
    }

    if (count($errors) > 0) {
        foreach ($errors as $e) {
            echo "<p style=\"color:red;\">Error: " . htmlspecialchars($e) . "</p>";
        }
        exit;
    }

    if (!empty($_POST['is_edit']) && $_POST['is_edit'] == '1' && !empty($_POST['ID'])) {
        $update_sql = "
        UPDATE Employee
        SET Name=?, Phone_Number=?, Department_ID=?, Position_ID=?, Email_ID=?, 
        Date_Of_Birth=?, Home_Address=?, NIN=?, Salary=?, Start_Date=?, Office_ID=?, Contract_ID=?
        WHERE ID = ?
        ";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param(
        "ssiissssdsiii",
        $Name,
        $Phone_Number,
        $department_id,
        $position_id,
        $Email_ID,
        $dob,
        $home_address,
        $nin,
        $Salary,
        $Start_Date,
        $office_id,
        $contract_id,
        $_POST['ID']
        );
        if ($stmt->execute()){
            $update_success = true;
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Update Employee</title>
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
  <h1>Find Employee Information for Updation</h1><br>
    <form method="post" action="">
        <label for="emid">Employee ID:</label>
        <input type="number" id="emid" name="emid" required><br><br>
        <button type="submit" name="fetch_id">Get Information</button>
    </form>
 
    <?php if ($showSuccess): ?>
        <form method="post" action="">
            <input type="hidden" name="is_edit" value="1">
            <input type="hidden" name="ID" value="<?php echo htmlspecialchars($employee['ID']); ?>">

            <h2>Personal Information</h2>
            <label for="Name">Name:</label>
            <input type="text" id="Name" name="Name" value="<?php echo htmlspecialchars($employee['Name']); ?>" maxlength="100" required><br><br>

            <label for="Email_ID">Email:</label>
            <input type="email" id="Email_ID" name="Email_ID" value="<?php echo htmlspecialchars($employee['Email_ID'] ?? ''); ?>"><br><br>

            <label for="Phone_Number">Phone number:</label>
            <input type="text" id="Phone_Number" name="Phone_Number" value="<?php echo htmlspecialchars($employee['Phone_Number'] ?? ''); ?>"><br><br>

            <label for="Date_Of_Birth">Date of Birth:</label>
            <input type="date" id="Date_Of_Birth" name="Date_Of_Birth" value="<?php echo htmlspecialchars($employee['Date_Of_Birth']); ?>" required><br><br>

            <label for="Home_Address">Home Address:</label>
            <input type="text" id="Home_Address" name="Home_Address" maxlength="255" value="<?php echo htmlspecialchars($employee['Home_Address'] ?? ''); ?>" required><br><br>

            <label for="NIN">NIN:</label>
            <input type="text" id="NIN" name="NIN" value="<?php echo htmlspecialchars($employee['NIN'] ?? ''); ?>"><br><br>
            
            <h2>Contract Details</h2>
            <label for="Salary">Salary:</label>
            <input type="number" id="Salary" name="Salary" value="<?php echo htmlspecialchars($employee['Salary']); ?>" step="0.01"><br><br>

            <label for="Start_Date">Start Date:</label>
            <input type="date" id="Start_Date" name="Start_Date" value="<?php echo htmlspecialchars($employee['Date_Of_Birth']); ?>"><br><br>

            <label for="department_id">Department:</label>
            <select name="department_id" id="department_id" required>
                <?php
                $empDep = isset($employee['Department_ID']) ? $employee['Department_ID'] : '';
                $selBlank = ($empDep === '' ? ' selected' : '');
                echo '<option value=""' . $selBlank . '>— Select Department —</option>' . "\n";
                while ($row = mysqli_fetch_assoc($dept_result)) {
                    $id = (int)$row['Department_ID'];
                    $name = htmlspecialchars($row['Name'], ENT_QUOTES, 'UTF-8');
                    $selected = ($empDep !== '' && (int)$empDep === $id) ? ' selected' : '';
                    echo "<option value=\"$id\"$selected>$name</option>\n";
                }
                ?>
            </select>
            <br><br>

            <label for="position_id">Position:</label>
            <select name="position_id" id="position_id" required>
                <?php
                while ($row = mysqli_fetch_assoc($job_result)) {
                    $id = (int)$row['Position_ID'];
                    $title = htmlspecialchars($row['Position_Title'], ENT_QUOTES, 'UTF-8');
                    $selected = ($id === (int)$employee['Position_ID']) ? ' selected' : '';
                    echo "<option value=\"$id\"$selected>$title</option>\n";
                }
                ?>
            </select>
            <br><br>

            <label for="office_id">Office:</label>
            <select name="office_id" id="office_id">
                <?php
                $empPos = isset($employee['Office_ID']) ? $employee['Office_ID'] : '';
                $selBlank = ($empPos === '' ? ' selected' : '');
                echo '<option value=""' . $selBlank . '>— Select position —</option>' . "\n";
                while ($row = mysqli_fetch_assoc($office_result)) {
                    $id = (int)$row['Office_ID'];
                    $name = htmlspecialchars($row['Office_Name'], ENT_QUOTES, 'UTF-8');
                    $sel = ($empPos !== '' && (int)$empPos === $id) ? ' selected' : '';
                    echo "<option value=\"$id\"$sel>$name</option>\n";
                }
                ?>
            </select>
            <br><br>

            <label for="contract_id">Contract:</label>
            <select name="contract_id" id="contract_id">
                <?php
                while ($row = mysqli_fetch_assoc($contract_result)) {
                    $id = (int)$row['Contract_ID'];
                    $name = htmlspecialchars($row['Contract_Type'], ENT_QUOTES, 'UTF-8');
                    echo "<option value=\"$id\">$name</option>\n";
                }
                ?>
            </select>
            <br><br>

            <button type="submit" name="submit_employee">Update</button>
        </form>
    <?php endif; ?>
    <?php if ($update_success): ?>
        <div id="successPopup">
            <div class="content">
            <p>Employee updated successfully!</p>
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
