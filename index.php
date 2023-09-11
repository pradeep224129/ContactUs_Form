<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "showroom";
$admin_email = "pradeep143184@gmail.com";

   // Create a connection to the database
$conn = new mysqli($servername, $username, $password, $dbname);

  // Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


    // Function to get user's IP address
function getUserIP()
{
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    }
    elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    }

    else {
        return $_SERVER['REMOTE_ADDR'];
    }
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $ip_address = getUserIP();

    $sql = "SELECT COUNT(*) AS count FROM contact_form WHERE ip_address = ? AND timestamp >= DATE_SUB(NOW(), INTERVAL 24 HOUR)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $ip_address);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if ($row["count"] > 0) {
        echo "You have already submitted a form within the last 24 hours.";
    } else {

        $name = $_POST["name"];
        $phone = $_POST["phone"];
        $email = $_POST["email"];
        $subject = $_POST["subject"];
        $message = $_POST["message"];
        $ip_address = $_SERVER['REMOTE_ADDR'];
        $errors = array();                                                                   

        // Validate fullname 
        if (empty($name)) {
            $errors['name'] = "Name is required.";
        } elseif (strlen($name) < 4) {
            $errors['name'] = "Name is too small";
        }

        // Validate phone 
        if (empty($phone)) {
            $errors['phone'] = "Phone is required.";
        } elseif (!is_numeric($phone)) {
            $errors['phone'] = "Phone number should only contain digits Not alphabetic & Special characters";
        } elseif (strlen($phone) > 10) {
            $errors['phone'] = "Phone number shouldn't be greater than 10 digits";
        } elseif (strlen($phone) < 10) {
            $errors['phone'] = "Phone number shouldn't be less than 10 digits";
        }

        // Validate email
        if (empty($email)) {
            $errors['email'] = "Email is required.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = ' Inavlid email format';
        } elseif (strlen($email) > 40) {
            $errors['email'] = "Email  shouldn't be greater than 40 characters";
        }

        // Validate subject
        if (empty($subject)) {
            $errors['subject'] = "Subject is required.";
        } elseif (strlen($subject) > 200) {
            $errors['subject'] = "Subject  shouldn't exceed  200 characters";
        }
        // Validate message 
        if (empty($message)) {
            $errors['message'] = "Message is required.";
        } elseif (strlen($message) > 1000) {
            $errors['message'] = "Message  shouldn't exceed  1000 characters";
        }
        if (empty($errors)) {
           
            $sql = "INSERT INTO contact_form (name, phone, email, subject, message,ip_address) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssss", $name, $phone, $email, $subject, $message, $ip_address);

            if ($stmt->execute()) {

                $subject = "New Form Submission";
                $message = "A new form submission has been received from IP address: $ip_address";
                $headers = "From: pard2356890@gmail.com";
                
                if (mail($admin_email, $subject, $message, $headers)) {
                    echo "Form submitted successfully, and a confirmation email has been sent to the admin.";
                    header('Location: index.php?success=true');
                } else {
                    echo "Form submitted successfully, but the confirmation email could not be sent.";
                }
            } else {
                echo "Error: " . $stmt->error;
            }

            $stmt->close();
            $conn->close();
        }
    }
}
?>

<!DOCTYPE html> 
<html lang="en"> 
<head>
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <title>Contact Form</title>
    <style>
   body {
            font-family: Arial, Helvetica, sans-serif;
            background-color: lightgreen;
            margin: 0;
            padding: 30px;
        }
        
        .container {
            max-width: 500px;
            margin: 0 auto;
            padding: 30px;
            background-color: grey;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
           }
        
        label {
            font-weight: bold;
        }
        input,

        textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        button[type="submit"] {
            background-color: #007bff;
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
        }
     
        button[type="submit"]:hover {
            background-color: #0056b3;
        }
      
        button[type="reset"] {
            background-color: #d02f47;
            color: #fff; 
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
        }

        button[type="reset"]:hover {
            background-color: #ff0000;
        }

        span {
            color: red;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Contact Us</h2>

        <?php
        if (isset($_GET['success']) && $_GET['success'] == true) {
            echo '<p style="color:green" > Form Submitted Successfully! And Confirmation Email Send </p>';
        }
        ?>

        <form action="index.php" method="POST">
            <div>
                <label>Full Name:</label>
                <input type="text" value="<?php echo isset($name) ? $name : ''; ?>" name="name">
                <span><?php echo isset($errors["name"]) ? $errors["name"] : ''; ?></span>
            </div>

            <div>
                <label>Phone Number:</label>
                <input type="text" value="<?php echo isset($phone) ? $phone : ''; ?>" name="phone">
                <span><?php echo isset($errors["phone"]) ? $errors["phone"] : ''; ?></span>
            </div>
            
            <div>
                <label>Email:</label>
                <input type="text" value="<?php echo isset($email) ? $email : ''; ?>" name="email">
                <span><?php echo isset($errors["email"]) ? $errors["email"] : ''; ?></span>
            </div>

            <div>
                <label>Subject:</label>
                <input type="text" value="<?php echo isset($subject) ? $subject : ''; ?>" name="subject">
                <span><?php echo isset($errors["subject"]) ? $errors["subject"] : ''; ?></span>
            </div>          

            <div>
                <label>Message:</label>
                <textarea type="text" name="message"><?php echo isset($message) ? $message : ''; ?></textarea>
                <span><?php echo isset($errors["message"]) ? $errors["message"] : ''; ?></span>
            </div>

            <button type="submit" class="submitbtn" value="Submit" name="submit">Submit</button>
            <button type="reset" class="resetbtn" value="Reset" name="reset">Reset</button> 
        </form>
    </div>
</body>

</html>