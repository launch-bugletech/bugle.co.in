<!doctype html>
<html class="no-js" lang="en" dir="ltr">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=Edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Contact Us - Bugle</title>
    <link rel="stylesheet" href="https://designmykitchen.cloud/public/backend/ebazar.style.min.css">
    <link rel="stylesheet" href="https://designmykitchen.cloud/public/backend/custom.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
          <link rel="icon" href="{{asset('public/favicon_bugle.png')}}" type="image/x-icon">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            background-color: #f5f5f5;
            color: #333;
        }

        .page-header {
            background: white;
            padding: 20px 50px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
         }
         
         .page-header .logo img {
            height: 60px;
            width: auto;
         }
         
        .logo img {
            height: 87px;
        }

        .nav-menu {
            display: flex;
            list-style: none;
            gap: 30px;
            margin: 0;
            padding: 0;
         }
         
         .nav-menu li a {
            text-decoration: none;
            color: #333;
            font-weight: 600;
            font-size: 1rem;
            transition: color 0.3s ease;
            padding: 8px 15px;
            border-radius: 5px;
         }
         
         .nav-menu li a:hover {
            color: #f61d33ff;
         }
            
         .nav-menu li a.active {
            color: #f61d33ff;
            background: rgba(246, 29, 51, 0.1);
         }
         
        .contact-hero {
            text-align: center;
            padding: 60px 20px;
            background: linear-gradient(#f61d33ff, #f61d33ff 0%, #f61d33ff 100%);
            color: white;
        }

        .contact-hero h1 {
            font-size: 48px;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .contact-container {
            max-width: 1200px;
            margin: -40px auto 60px;
            padding: 0 20px;
        }

        .map-section {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            margin-bottom: 40px;
        }

        .map-section iframe {
            width: 100%;
            height: 400px;
            border: none;
        }

        .content-grid {
            display: grid;
            grid-template-columns: 1fr 1.5fr;
            gap: 40px;
            margin-top: 40px;
        }

        .contact-info-section {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }

        .contact-info-section h3 {
            font-size: 24px;
            margin-bottom: 30px;
            color: #f61d33ff;
            border-bottom: 3px solid #f61d33ff;
            padding-bottom: 10px;
        }

        .info-item {
            display: flex;
            align-items: flex-start;
            margin-bottom: 25px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 10px;
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .info-item:hover {
            transform: translateX(5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .info-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(#f61d33ff, #f61d33ff 0%, #f61d33ff 100%);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 20px;
            flex-shrink: 0;
        }

        .info-icon i {
            font-size: 20px;
        }

        .info-content h4 {
            font-size: 16px;
            color: #dcaf61d33ff53e;
            margin-bottom: 5px;
        }

        .info-content p {
            color: #666;
            line-height: 1.6;
        }

        .form-section {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }

        .form-section h3 {
            font-size: 24px;
            margin-bottom: 30px;
            color: #f61d33ff;
            border-bottom: 3px solid #f61d33ff;
            padding-bottom: 10px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #f61d33ff;
        }

        .form-group textarea {
            resize: vertical;
            min-height: 120px;
        }

        .submit-btn {
            background: linear-gradient(#f61d33ff, #f61d33ff 0%, #f61d33ff 100%);
            color: white;
            padding: 15px 40px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(220,165,62);
        }1

        .flash-message {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #28a745;
            color: white;
            padding: 15px 25px;
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            z-index: 9999;
            animation: slideIn 0.5s ease;
        }

        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @media (max-width: 968px) {
            .content-grid {
                grid-template-columns: 1fr;
            }

            .form-row {
                grid-template-columns: 1fr;
            }

            .contact-hero h1 {
                font-size: 32px;
            }
        }

        .error {
            color: #dc3545;
            font-size: 12px;
            margin-top: 5px;
            display: block;
        }
    </style>
</head>
<body>
  <header class="page-header">
         <div class="logo">
            <a href="/">
               <img src="public/Bugle_logo.png" alt="Bugle Logo">
            </a>
         </div>
         <nav>
            <ul class="nav-menu">
              <li><a href="/admin/home">Home</a></li>
               <li><a href="/about-us" >About Us</a></li>
               <li><a href="/contact-us"class="active">Contact</a></li>
            </ul>
         </nav>
      </header>

    <section class="contact-hero">
        <h1>CONTACT US</h1>
    </section>

    <div class="contact-container">
        <!-- Map Section -->
       <div class="map-section">
    <iframe
        src="https://www.google.com/maps?q=Bugle+Technologies+Private+Limited,+Plot+520,+Sector+1,+Gandhinagar,+Gujarat+382007&output=embed"
        width="100%"
        height="450"
        style="border:0;"
        allowfullscreen
        loading="lazy"
        referrerpolicy="no-referrer-when-downgrade">
    </iframe>
</div>


        <!-- Content Grid -->
        <div class="content-grid">
            <!-- Contact Information -->
            <div class="contact-info-section">
                <h3>Get In Touch</h3>
                
                <div class="info-item">
                    <div class="info-icon">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <div class="info-content">
                        <h4>Address</h4>
                        <p>Plot 520, Opp Lord Ayappa Temple,<br>Sector 1, Gandhinagar 382007</p>
                    </div>
                </div>

                <div class="info-item">
                    <div class="info-icon">
                        <i class="fas fa-phone"></i>
                    </div>
                    <div class="info-content">
                        <h4>Phone</h4>
                        <p>+91 94264 02423</p>
                    </div>
                </div>

                <div class="info-item">
                    <div class="info-icon">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div class="info-content">
                        <h4>Email</h4>
                        <p>sales@bugle.in</p>
                    </div>
                </div>
            </div>

            <!-- Contact Form -->
            <div class="form-section">
                <h3>Contact Us Now</h3>
                <form action="/contact/store" method="POST" id="contactForm">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Your Name</label>
                            <input type="text" name="contact_name" placeholder="Enter your name" required>
                            <span class="error contact_name"></span>
                        </div>
                        <div class="form-group">
                            <label>Your Email</label>
                            <input type="email" name="contact_email" placeholder="Enter your email" required>
                            <span class="error contact_email"></span>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Contact Number</label>
                            <input type="tel" name="contact_no" placeholder="Enter your contact number" required>
                            <span class="error contact_no"></span>
                        </div>
                        <div class="form-group">
                            <label>Subject</label>
                            <input type="text" name="subject" placeholder="Enter subject" required>
                            <span class="error subject"></span>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Your Message</label>
                        <textarea name="message" placeholder="Enter your message" required></textarea>
                        <span class="error message"></span>
                    </div>

                    <button type="submit" class="submit-btn">Send Message</button>
                </form>
            </div>
        </div>
    </div>

    <script src="https://designmykitchen.cloud/public/backend/dist/assets/bundles/libscripts.bundle.js"></script>
</body>
</html>