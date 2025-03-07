
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Trust Services</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">

  <style>
    html {
      height: 100%;
      margin: 0;
    }
    body {
      height: 100%;
      margin: 0;
      background: linear-gradient(to bottom, #0081fa, #000000); /* Blue gradient background */
      display: flex;
      justify-content: center; /* Center horizontally */
      align-items: center;     /* Center vertically */
    }
    .module {
      padding: 20px;
      border-radius: 10px; /* Rounded corners */
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2); /* Subtle shadow for depth */
      background-color: white; /* White background for the form */
    }
    .container {
      display: flex;
      justify-content: center; /* Center horizontally */
      align-items: center;     /* Center vertically */
    }
    .form-container {
      max-width: 500px;  /* Set a max width for the form */
      width: 100%;       /* Make it responsive */
      padding: 20px;     /* Padding around the form */
      border-radius: 10px;  /* Rounded corners for the form container */
      text-align: center;   /* Center align text */
    }
    .btn {
      background-color: #007bff; /* Bootstrap primary blue color */
      color: white;              /* Button text color */
      border: none;              /* Remove border */
      width: 100%;               /* Full width */
      padding: 10px;             /* Uniform padding for buttons */
      border-radius: 5px;        /* Rounded corners for buttons */
      font-size: 16px;           /* Font size for buttons */
      cursor: pointer;           /* Pointer cursor for buttons */
      transition: background-color 0.3s; /* Smooth transition for hover */
    }
    .btn:hover {
      background-color: #0056b3; /* Darker blue on hover */
    }
    /* Centering styles for links within form-group */
    .form-group a {
      display: block;   
      margin-top: 10px; 
    }
    /* Reduce vertical space specifically for the registration form (#model-signup) */
    #model-signup.form-container {
      padding: 10px !important;  /* Less padding around the signup form */
    }
    #model-signup .form-group {
      margin-bottom: 10px;       /* Reduce spacing between fields */
    }
  </style>
</head>
<body>

<section class="module">
  <div class="container">
    <div class="row">
      <!-- LOGIN FORM -->
      <div class="col-sm-12 form-container" id="logindiv">
        <div style="display: flex; justify-content: center; margin-bottom: 20px;">
          <img src="https://i.ibb.co/xtt7PMZG/android-icon-36x36.png" 
               alt="android-icon-36x36" 
               style="width: 200px; height: 200px;">
        </div>

        <h4 class="font-alt">
          <a href="menu.php" style="text-decoration: none; color: inherit;">Trust Services</a>
        </h4>
        <hr class="divider-w mb-10">

        <form class="form" id="login-form" action="login.php" method="POST">
          <div class="form-group">
            <!-- For login, we're using the email address as the unique identifier -->
            <input class="form-control" id="login-email" type="text" name="email" placeholder="Email" required />
          </div>
          <div class="form-group">
            <input class="form-control" id="login-password" type="password" name="password" placeholder="Password" required />
          </div>
          <div class="form-group">
            <button class="btn btn-round btn-b" type="submit">Login</button>
          </div>
          <div class="form-group">
            <a href="#">Forgot Password?</a>
          </div>
        </form>
        <p>Don't have an account? <a href="#" id="showSignup">Register</a></p>
      </div>
      
      <!-- SIGNUP FORM -->
      <div class="col-sm-12 form-container" id="model-signup" style="display: none;">
        <div style="display: flex; justify-content: center; margin-bottom: 20px;">
          <img src="https://i.ibb.co/xtt7PMZG/android-icon-36x36.png" 
               alt="android-icon-36x36" 
               style="width: 200px; height: 200px;">
        </div>

        <h4 class="font-alt">Trust Services</h4>
        <hr class="divider-w mb-10">

        <form class="form" id="signup-form" action="register.php" method="POST">
          <!-- User Type Selection -->
          <div class="form-group">
            <label for="user_type"><strong>I am a:</strong></label><br>
            <input type="radio" id="resident" name="user_type" value="resident" checked>
            <label for="resident"><strong>Resident</strong></label>
            &nbsp;&nbsp;
            <input type="radio" id="serviceProvider" name="user_type" value="service_provider">
            <label for="serviceProvider"><strong>Service Provider</strong></label>
          </div>

          <!-- Common Fields: Name, Age, Contact Number, Email, Password -->
          <div class="form-group">
            <input class="form-control" id="name" type="text" name="name" placeholder="Name" required />
          </div>
          <div class="form-group">
            <input class="form-control" id="age" type="number" name="age" placeholder="Age" required />
          </div>
          <div class="form-group">
            <input class="form-control" id="phone_no" type="text" name="phone_no" placeholder="Contact Number" required />
          </div>
          <div class="form-group">
            <input class="form-control" id="email" type="email" name="email" placeholder="Email" required />
          </div>
          <div class="form-group">
            <input class="form-control" id="signup-password" type="password" name="password" placeholder="Password" required />
          </div>

          <!-- Resident-Specific Fields -->
          <div id="residentFields">
            <div class="form-group">
              <input class="form-control" id="address" type="text" name="address" placeholder="Address" />
            </div>
            <div class="form-group">
              <input class="form-control" id="occupation" type="text" name="occupation" placeholder="Occupation" />
            </div>
          </div>

          <!-- Service Provider-Specific Fields -->
          <div id="serviceProviderFields" style="display: none;">
            <div class="form-group">
              <input class="form-control" id="company_name" type="text" name="company_name" placeholder="Company Name" />
            </div>
            <div class="form-group">
              <select class="form-control" id="service_provided" name="service_provided">
                <option value="">Select Service Provided</option>
                <option value="plumbing">Plumbing</option>
                <option value="electrical">Electrical</option>
                <option value="cleaning">Cleaning</option>
                <option value="gardening">Gardening</option>
              </select>
            </div>
          </div>

          <div class="form-group">
            <button class="btn btn-block btn-round btn-b" type="submit">Register</button>
          </div>
        </form>
        <p>Already have an account? <a href="#" id="showLogin">Login</a></p>
      </div>
    </div>
  </div>
</section>

<!-- Toggle Logic for switching forms -->
<script>
  // Toggle between login and signup forms
  document.getElementById('showSignup').addEventListener('click', function(event) {
      event.preventDefault();
      document.getElementById('logindiv').style.display = 'none';
      document.getElementById('model-signup').style.display = 'block';
  });
  document.getElementById('showLogin').addEventListener('click', function(event) {
      event.preventDefault();
      document.getElementById('model-signup').style.display = 'none';
      document.getElementById('logindiv').style.display = 'block';
  });
  // Toggle resident/service provider fields based on radio selection
  document.addEventListener('DOMContentLoaded', function() {
      const residentRadio = document.getElementById('resident');
      const serviceProviderRadio = document.getElementById('serviceProvider');
      const residentFields = document.getElementById('residentFields');
      const serviceProviderFields = document.getElementById('serviceProviderFields');
      const addressField = document.getElementById('address');
      const occupationField = document.getElementById('occupation');
      const companyNameField = document.getElementById('company_name');
      const serviceProvidedField = document.getElementById('service_provided');

      function toggleFields() {
          if (residentRadio.checked) {
              residentFields.style.display = 'block';
              serviceProviderFields.style.display = 'none';
              // Make resident fields required
              addressField.required = true;
              occupationField.required = true;
              // Make service provider fields not required
              companyNameField.required = false;
              serviceProvidedField.required = false;
          } else {
              residentFields.style.display = 'none';
              serviceProviderFields.style.display = 'block';
              // Make service provider fields required
              addressField.required = false;
              occupationField.required = false;
              companyNameField.required = true;
              serviceProvidedField.required = true;
          }
      }
      residentRadio.addEventListener('change', toggleFields);
      serviceProviderRadio.addEventListener('change', toggleFields);
      // Initialize fields on page load
      toggleFields();
  });
</script>

<!-- Bootstrap & jQuery -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>
