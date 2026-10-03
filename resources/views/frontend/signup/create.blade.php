<!doctype html>
<html lang="en" class="no-js" dir="ltr">

<head>
   <meta charset="utf-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
   <title>Remote Management Signup</title>

   <link rel="icon" href="{{ asset('public/favicon_bugle.png') }}" type="image/x-icon">

   <!-- CSS -->
   <link rel="stylesheet" href="https://designmykitchen.cloud/public/backend/ebazar.style.min.css">
   <link rel="stylesheet" href="https://designmykitchen.cloud/public/backend/custom.css">

   <style>

body {
      background: 
         linear-gradient(rgba(255,255,255,0.7), rgba(255,255,255,0.7)),
         url('{{ asset("public/Solar-substation1.jpg") }}');
      background-size: cover;
      background-position: center;
      background-repeat: no-repeat;
   }

      .signup-card {
         max-width: 37rem;
         width: 100%;
         margin: 0 auto;
         /* ensures horizontal center */
         border-radius: 20px;
         padding: 40px 45px;
         background: #fff;
         box-shadow: 0px 4px 25px rgba(0, 0, 0, 0.15);
      }

      .signup-card .form-control,
      .signup-card .form-select {
         width: 100%;
         /* ensures all inputs equal width */
      }

      .form-label {
         font-weight: 600;
      }

      .form-control-sm,
      .form-select-sm {
         padding: 9px 12px;
         border-radius: 8px;
      }

      h1 {
         font-size: 32px;
         font-weight: 700;
      }

      .section-title {
         font-size: 18px;
         margin-bottom: 12px;
         font-weight: 600;
      }

      .error {
         font-size: 13px;
      }

      .top-links ul {
         display: flex;
         list-style: none;
         padding: 0;
      }

      .top-links li {
         margin-left: 20px;
      }

      .top-links a {
         color: #000;
         font-weight: bold;
      }
   </style>
</head>

<body style="background-color: #FBFAF6;">

   <div id="ebazar-layout">

      <div class="main p-2 p-xl-5">

         <!-- Top Right Links -->
         <div class="row mb-3">
            <div class="col-lg-10"></div>
            <div class="col-lg-2 top-links"style="margin-left: 89rem;">
               <ul>
                  <li><a href="{{ url('contact-us') }}">Contact</a></li>
                  <li><a href="{{ url('about-us') }}">About Us</a></li>
               </ul>
            </div>
         </div>

         <div class="body d-flex justify-content-center">
            <a href="{{ url('/') }}" class="mb-4 text-center w-100">
               <img src="{{ asset('public/Bugle_logo.png') }}" style="width: 280px;margin-left: 686px;">
            </a>
         </div>

         <div class="d-flex justify-content-center align-items-center" style="min-height: 100vh; width: 100%;">
            <div class="signup-card">

               <form class="row g-3" action="{{ url('signup/store') }}" method="POST" id="CustomerForm"
                  enctype="multipart/form-data">
                  @csrf

                  <div class="col-12 text-center mb-4">
                     <h1>Create Your Account</h1>
                  </div>


                  <div class="col-12">
                     <p class="section-title"><u>Basic Information</u><span class="text-danger">*</span></p>
                  </div>

                  <div class="col-md-6" style="margin-block: 16px;">
                     <label class="form-label">Username</label>
                     <input type="text" name="username" class="form-control form-control-sm"
                        placeholder="Enter username">
                     <span class="text-danger error username"></span>
                  </div>

                  <div class="col-md-6" style="margin-block: 16px;">
                     <label class="form-label">First Name</label>
                     <input type="text" name="first_name" class="form-control form-control-sm"
                        placeholder="Enter first name">
                     <span class="text-danger error first_name"></span>
                  </div>

                  <div class="col-md-6" style="margin-block: 16px;">
                     <label class="form-label">Last Name</label>
                     <input type="text" name="last_name" class="form-control form-control-sm"
                        placeholder="Enter last name">
                     <span class="text-danger error last_name"></span>
                  </div>

                 <div class="col-md-6" style="margin-block: 16px;">
                     <label class="form-label">Email Address</label>
                     <input type="email" name="email" class="form-control form-control-sm"
                        placeholder="Enter email address">
                     <span class="text-danger error email"></span>
                  </div>

                 <div class="col-md-6" style="margin-block: 16px;">
                     <label class="form-label">Mobile No</label>
                     <input type="tel" maxlength="10" name="phone" class="form-control form-control-sm"
                        placeholder="Enter mobile no">
                     <span class="text-danger error phone"></span>
                  </div>

                 <div class="col-md-6" style="margin-block: 16px;">
                     <label class="form-label">Password</label>
                     <input type="password" name="password" class="form-control form-control-sm"
                        placeholder="8+ characters required">
                     <span class="text-danger error password"></span>
                  </div>

                 <div class="col-md-6" style="margin-block: 16px;">
                     <label class="form-label">Confirm Password</label>
                     <input type="password" name="confirm_password" class="form-control form-control-sm"
                        placeholder="Confirm password">
                     <span class="text-danger error confirm_password"></span>
                  </div>

                 <div class="col-md-6" style="margin-block: 16px;">
                     <label class="form-label">Select Admin Role</label>
                     <select class="form-select form-select-sm" name="role_id">
                        <option value="">Please Select</option>
                        @foreach($roles as $val)
                           <option value="{{ $val->id }}">{{ $val->name }}</option>
                        @endforeach
                     </select>
                     <span class="text-danger error role_id"></span>
                  </div>

                  <!-- Checkbox -->
                  <div class="col-12 mt-2">
                     <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="confirmation" id="confirmation">
                        <label class="form-check-label" for="confirmation">
                           I accept the <a href="{{ url('terms-condition') }}" target="_blank">Terms & Conditions</a>
                        </label>
                     </div>
                     <span class="text-danger" id="confirmation_error"></span>
                  </div>

               
                        <div class="col-12 d-flex justify-content-center mt-4 btn btn-lg btn-light text-uppercase"style="margin-left: 211px;margin-top: 14px;">
                           <button type="submit" class="btn btn-lg btn-light text-uppercase">
                              <b>SIGN UP</b>
                           </button>
                        </div>




                  <div class="col-12 text-center mt-3">
                     <p style="margin-left: 8rem;">Already have an account? <a href="{{ url('/') }}">Sign in here</a></p>
                  </div>

               </form>

            </div>
         </div>

      </div>
   </div>

   <script src="https://akuntha.in/public/backend/dist/assets/bundles/libscripts.bundle.js"></script>
   <script src="{{ asset('public/validation/CustomerFormValidation.js') }}"></script>

</body>

</html>