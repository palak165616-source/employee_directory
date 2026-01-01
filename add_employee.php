<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors','1');
error_reporting(E_ALL);

include 'db.php';
$showSuccess = false;

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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = [];

    $Name = trim($_POST['Name'] ?? '');
    if ($Name === '' || strlen($Name) > 100) {
        $errors[] = "Name is required (max 100 chars).";
    }

    $raw_email = $_POST['Email_ID'] ?? '';
    if ($raw_email === '') {
        $space = strpos($Name, " ");
        $lastn = substr($Name, ($space+1));
        $first = substr($Name, 0, $space);
        $Email_ID = $first.".".$lastn."@kilburnazon.com";
    } else {
        $Email_ID = filter_var($raw_email, FILTER_SANITIZE_EMAIL);
        if (!$Email_ID || !filter_var($Email_ID, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Invalid email address.";
        }
    }

    $raw_phone = $_POST['Phone_Number'] ?? '';
    if ($raw_phone !== '') {
        $Phone_Number = $raw_phone;
    } else {
        $Phone_Number = null;
    }

    $raw_salary = $_POST['Salary'] ?? '';
    if ($raw_salary !== '') {
        if (!filter_var($raw_salary, FILTER_VALIDATE_FLOAT)) {
            $errors[] = "Salary must be a valid integer.";
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

    if ($position_id === 9 ) { 
        $id_emp = 15110000;
    } elseif ($position_id === 10 ) {
        $id_emp = 15210000;
    } elseif ($position_id === 13 ) {
        $id_emp = 15310000;
    } elseif ($position_id === 12 ) {
        $id_emp = 15410000;
    } elseif ($position_id === 14 ) {
        $id_emp = 15510000;
    } elseif ($position_id === 2 ) {
        $id_emp = 12110000;
    } elseif ($position_id === 4 ) {
        $id_emp = 12210000;
    } elseif ($position_id === 5 ) {
        $id_emp = 12310000;
    } elseif ($position_id === 6 ) {
        $id_emp = 14310000;
    } elseif ($position_id === 11 ) {
        $id_emp = 14210000;
    } elseif ($position_id === 7 ) {
        $id_emp = 14110000;
    } elseif ($position_id === 1 ) {
        $id_emp = 13110000;
    } elseif ($position_id === 8 ) {
        $id_emp = 13210000;
    }
    $flag = true;
    while ($flag){
        $randomnum = random_int(1000,9999);
        $final_id = $id_emp+$randomnum;
        $sql = "SELECT * FROM Employee WHERE Employee_ID = $final_id";
        $result = $conn->query($sql);
        if ($result->num_rows == 0) {
            $flag = false;
        }
    }
    

    if (count($errors) > 0) {
        foreach ($errors as $e) {
            echo "<p style=\"color:red;\">Error: " . htmlspecialchars($e) . "</p>";
        }
        exit;
    }

    $stmt = $conn->prepare("
      INSERT INTO Employee (
        Name, Phone_Number, Department_ID, Position_ID, Email_ID,
        Date_Of_Birth, Home_Address, NIN, Salary, Start_Date, Office_ID, Contract_ID, Employee_ID
      ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
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
      $final_id
    );
    $stmt->execute();

    $new_emp_id = $conn->insert_id; 
    if (!$new_emp_id) {
        die("Failed to retrieve new employee ID");
    }

    $stmt2 = $conn->prepare("
    INSERT INTO Employee_Emergency_Contact (
        ID, Contact_Name, Relation, Phone_Number
    ) VALUES (?, ?, ?, ?)
    ");
    $stmt2->bind_param(
    "isss",              // i = employee id, s = strings for other cols
    $new_emp_id,
    $ename,
    $erelation,
    $enumber
    );

    if ($stmt2->execute()) {
        $showSuccess = true;
    } else {
        echo "Database error: " . htmlspecialchars($stmt->error);
    }
    $stmt2->close();
    $stmt->close();
}
?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Add Employee</title>
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
    label.required::after {
        content: " *";
        color: red;
        font-weight: bold;
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
  <h1>Add Employee</h1><br>
    <?php if ($showSuccess): ?>
    <div id="successPopup">
        <div class="content">
        <p>Employee added successfully!</p>
        </div>
    </div>
    <script>
        const popup = document.getElementById('successPopup');
        popup.style.display = 'flex';
        // if user clicks anywhere outside the content, hide the popup
        popup.addEventListener('click', function(e) {
        if (e.target === popup) {
            popup.style.display = 'none';
        }
        });
    </script>
    <?php endif; ?>

  <form method="post" action="">

    <h2>Personal Information</h2><br>
    <label for="Name">Name:</label>
    <input type="text" id="Name" name="Name" maxlength="100" required><br><br>

    <label for="Email_ID">Email:</label>
    <input type="email" id="Email_ID" name="Email_ID"><br><br>

    <label for="Phone_Number">Phone number:</label>
    <input type="text" id="Phone_Number" name="Phone_Number"><br><br>

    <label for="Date_Of_Birth">Date of Birth:</label>
    <input type="date" id="Date_Of_Birth" name="Date_Of_Birth" required><br><br>

    <label for="Home_Address">Home Address:</label>
    <input type="text" id="Home_Address" name="Home_Address" maxlength="255" required><br><br>

    <label for="NIN">NIN:</label>
    <input type="text" id="NIN" name="NIN"><br><br>
    
    <h2>Contract Details</h2><br>
    <label for="Salary">Salary:</label>
    <input type="number" id="Salary" name="Salary" step="0.01" ><br><br>

    <label for="Start_Date">Start Date:</label>
    <input type="date" id="Start_Date" name="Start_Date" value="<?php echo date('Y-m-d'); ?>"><br><br>

    <label for="department_id">Department:</label>
    <select name="department_id" id="department_id" >
        <option value=''>N/A</option>
        <?php
        while ($row = mysqli_fetch_assoc($dept_result)) {
            $id = (int)$row['Department_ID'];
            $name = htmlspecialchars($row['Name'], ENT_QUOTES, 'UTF-8');
            echo "<option value=\"$id\">$name</option>\n";
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
            echo "<option value=\"$id\">$title</option>\n";
        }
        ?>
    </select>
    <br><br>

    <label for="office_id">Office:</label>
    <select name="office_id" id="office_id">
        <option value=''>N/A</option>
        <?php
        while ($row = mysqli_fetch_assoc($office_result)) {
            $id = (int)$row['Office_ID'];
            $name = htmlspecialchars($row['Office_Name'], ENT_QUOTES, 'UTF-8');
            echo "<option value=\"$id\">$name</option>\n";
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

    <h2>Emergency Contact Details</h2><br>   
    <label for="Emergency_Name">Name:</label>
    <input type="text" id="Emergency_Name" name="Emergency_Name"><br><br>

    <label for="Emergency_Relation">Relation:</label>
    <input type="text" id="Emergency_Relation" name="Emergency_Relation"><br><br>

    <label for="Emergency_Number">Phone Number:</label>
    <input type="text" id="Emergency_Number" name="Emergency_Number"><br><br>

    <button type="submit">Submit</button>
  </form>

</body>
</html>
