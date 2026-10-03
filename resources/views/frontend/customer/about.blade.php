<!doctype html>
<html class="no-js" lang="en" dir="ltr">
   <head>
      <meta charset="utf-8">
      <meta http-equiv="X-UA-Compatible" content="IE=Edge">
      <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
      <title>Bugle - About Us</title>
      <link rel="icon" href="favicon_bugle.png" type="image/x-icon">
      <link rel="stylesheet" href="https://designmykitchen.cloud/public/backend/ebazar.style.min.css">
      <link rel="stylesheet" href="https://designmykitchen.cloud/public/backend/custom.css">
      <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
      <style type="text/css">
         /* Header Styles */
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
         
         .sgmn_cls {
            display: flex;
            list-style: none;
            padding: 0;
            gap: 20px;
         }
         
         .hero-section {
            background: linear-gradient( #f61d33ff 0%, #f61d33ff 100%);
            color: white;
            padding: 60px 20px;
            border-radius: 15px;
            margin-bottom: 30px;
            text-align: center;
         }
         
         .hero-section h1 {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 15px;
         }
         
         .hero-section p {
            font-size: 1.3rem;
            opacity: 0.95;
         }
         
         .stats-section {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin: 40px 0;
         }
         
         .stat-card {
            background: white;
            padding: 30px;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
         }
         
         .stat-card:hover {
            transform: translateY(-5px);
         }
         
         .stat-card i {
            font-size: 3rem;
            color: #f61d33ff;
            margin-bottom: 15px;
         }
         
         .stat-card h3 {
            font-size: 2.5rem;
            font-weight: 700;
            color: #333;
            margin: 10px 0;
         }
         
         .stat-card p {
            color: #666;
            font-size: 1rem;
         }
         
         .service-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
            margin: 30px 0;
         }
         
         .service-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
         }
         
         .service-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
         }
         
         .service-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
         }
         
         .service-content {
            padding: 25px;
         }
         
         .service-icon {
            font-size: 2.5rem;
            color: #f61d33ff;
            margin-bottom: 15px;
         }
         
         .service-card h4 {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 15px;
            color: #333;
         }
         
         .service-card p {
            color: #666;
            line-height: 1.6;
         }
         
         .section-title {
            font-size: 2.5rem;
            font-weight: 700;
            text-align: center;
            margin: 50px 0 30px;
            color: #333;
            position: relative;
         }
         
         .section-title::after {
            content: '';
            display: block;
            width: 80px;
            height: 4px;
            background: linear-gradient(#f61d33ff, #f61d33ff 0%, #f61d33ff 100%);
            margin: 15px auto 0;
            border-radius: 2px;
         }
         
         .vision-mission {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin: 40px 0;
         }
         
         .vm-card {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
         }
         
         .vm-card h3 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 20px;
            color: #333;
         }
         
         .vm-card i {
            font-size: 3rem;
            color: #f61d33ff;
            margin-bottom: 20px;
         }
         
         .feature-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin: 30px 0;
         }
         
         .feature-item {
            background: white;
            padding: 25px;
            border-radius: 10px;
            border-left: 4px solid #f61d33ff;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
         }
         
         .feature-item h5 {
            font-weight: 600;
            color: #333;
            margin-bottom: 10px;
            font-size: 1.2rem;
         }
         
         .feature-item p {
            color: #666;
            line-height: 1.6;
            margin: 0;
         }
         
         .contact-section {
            background: linear-gradient(135deg, #f61d33ff 0%, #f61d33ff 100%);
            color: white;
            padding: 50px;
            border-radius: 15px;
            margin-top: 50px;
            text-align: center;
         }
         
         .contact-section h3 {
            font-size: 2rem;
            margin-bottom: 30px;
         }
         
         .contact-info {
            display: flex;
            justify-content: center;
            gap: 40px;
            flex-wrap: wrap;
         }
         
         .contact-item {
            display: flex;
            align-items: center;
            gap: 10px;
         }
         
         .services-section {
            padding: 60px 50px;
            background: #f8f9fa;
         }
         
         .contact-item i {
            font-size: 1.5rem;
         }
      </style>
   </head>
   <body style="background-color: #f8f9fa;">
      <!-- Header -->
      <header class="page-header">
         <div class="logo">
            <a href="/">
               <img src="public/Bugle_logo.png" alt="Bugle Logo">
            </a>
         </div>
         <nav>
            <ul class="nav-menu">
               <li><a href="/admin/home">Home</a></li>
               <li><a href="/about-us" class="active">About Us</a></li>
               <li><a href="/contact-us">Contact</a></li>
            </ul>
         </nav>
      </header>

      <div id="ebazar-layout" class="theme-blue">
         <!-- main body area -->
         <div class="main p-2 py-3 p-xl-5">
            <!-- Body: Body -->
            <div class="body d-flex p-0 p-xl-5">
               <div class="container-xxl">
                  <!-- Hero Section -->
                  <div class="hero-section">
                     <h1>About Bugle Technologies Private Limited</h1>
                     <p>Building brands and digital experiences that make an impact.</p>
                  </div>
                  
                  <!-- Stats Section -->
                  <div class="stats-section">
                     <div class="stat-card">
                        <i class="fas fa-users"></i>
                        <h3>500+</h3>
                        <p>Happy Clients</p>
                     </div>
                     <div class="stat-card">
                        <i class="fas fa-check-circle"></i>
                        <h3>150+</h3>
                        <p>Project Completed</p>
                     </div>
                     <div class="stat-card">
                        <i class="fas fa-calendar-alt"></i>
                        <h3>2013</h3>
                        <p>Years of Experience</p>
                     </div>
                  </div>
                  
                  <!-- Who We Are -->
                  <div class="card" style="border: none; box-shadow: 0 4px 15px rgba(0,0,0,0.1); border-radius: 12px; margin: 40px 0;">
                     <div class="card-body" style="padding: 40px;">
                        <h2 class="section-title">Who We Are</h2>
                        <p style="font-size: 1.3rem; font-weight: bold; text-align: center; margin-bottom: 10px;">
                           From Idea to Execution
                        </p>
                        <p style="font-size: 1.1rem; line-height: 1.8; color: #555; text-align: center; max-width: 900px; margin: 0 auto;">
                           Bugle Technologies Private Limited started with a passion for media and design, creating engaging digital experiences that connected people and ideas. As we grew, we ventured into technology, developing mobile apps and websites that combined functionality with thoughtful design.
                           <br><br>
                           Our journey continued as we expanded into HR and recruitment services, helping businesses build strong teams, and pioneered digital marketing solutions to drive growth and visibility. Each new step reflected our commitment to helping clients succeed across every dimension of their business.
                           <br><br>
                           Today, Bugle Technologies also embraces AI-driven solutions and strategic consulting, empowering startups and enterprises to navigate the digital landscape with confidence. Every project we take on is guided by clarity, purpose, and measurable impact.
                        </p>
                     </div>
                  </div>
                  
                  <!-- Services Section -->
                  <div class="services-section">
                     <h2 class="section-title">Our Services</h2>
                     
                     <div class="service-grid">
                        <div class="service-card">
                           <img src="https://images.unsplash.com/photo-1561070791-2526d30994b5?w=600" alt="UI/UX Design" class="service-image">
                           <div class="service-content">
                              <i class="fas fa-pencil-ruler service-icon"></i>
                              <h4>UI/UX Design</h4>
                              <p>Creating intuitive and visually stunning user interfaces that enhance user experience and drive engagement across all digital platforms.</p>
                           </div>
                        </div>
                        
                        <div class="service-card">
                           <img src="https://images.unsplash.com/photo-1512941937669-90a1b58e7e9c?w=600" alt="Mobile App Design" class="service-image">
                           <div class="service-content">
                              <i class="fas fa-mobile-alt service-icon"></i>
                              <h4>Mobile App Design</h4>
                              <p>Designing beautiful and functional mobile applications that provide seamless experiences on iOS and Android platforms.</p>
                           </div>
                        </div>
                        
                        <div class="service-card">
                           <img src="https://images.unsplash.com/photo-1561070791-36c11767b26a?w=600" alt="Brand Identity" class="service-image">
                           <div class="service-content">
                              <i class="fas fa-palette service-icon"></i>
                              <h4>Brand Identity & Visual Systems</h4>
                              <p>Developing comprehensive brand identities and visual systems that create lasting impressions and build strong brand recognition.</p>
                           </div>
                        </div>
                        
                        <div class="service-card">
                           <img src="https://images.unsplash.com/photo-1498050108023-c5249f4df085?w=600" alt="Web Development" class="service-image">
                           <div class="service-content">
                              <i class="fas fa-code service-icon"></i>
                              <h4>Web Development</h4>
                              <p>Building responsive, high-performance websites with modern technologies that deliver exceptional user experiences and business results.</p>
                           </div>
                        </div>
                        
                        <div class="service-card">
                           <img src="https://images.unsplash.com/photo-1555949963-aa79dcee981c?w=600" alt="API Development" class="service-image">
                           <div class="service-content">
                              <i class="fas fa-plug service-icon"></i>
                              <h4>API Development & Integrations</h4>
                              <p>Creating robust APIs and seamless integrations that connect systems, enhance functionality, and streamline business processes.</p>
                           </div>
                        </div>
                     </div>
                  </div>
                  
                  <!-- Vision & Mission -->
                  <h2 class="section-title">Vision & Mission</h2>
                  <div class="vision-mission">
                     <div class="vm-card">
                        <i class="fas fa-eye"></i>
                        <h3>Our Vision</h3>
                        <p style="font-size: 1.1rem; line-height: 1.8; color: #555;">
                           We aim to be the preferred global partner for intelligent IT, marketing, and consulting solutions. Our vision is to shape a future where technology and strategy work together to fuel business success.
                        </p>
                     </div>
                     
                     <div class="vm-card">
                        <i class="fas fa-bullseye"></i>
                        <h3>Our Mission</h3>
                        <p style="font-size: 1.1rem; line-height: 1.8; color: #555;">
                           We empower businesses through innovation, technology, and creativity. Our goal is to build solutions that drive growth, inspire trust, and create lasting impact for our clients and their customers.
                        </p>
                     </div>
                  </div>
                  
                  <!-- Our Philosophy -->
                  <div style="background: #f5f0e8; padding: 60px 50px; margin: 40px 0;">
                     <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 60px; align-items: start;">
                        <div>
                           <h3 style="font-size: 1.3rem; color: #333; margin-bottom: 20px; font-weight: 600;">■ OUR PHILOSOPHY</h3>
                           <p style="color: #666; line-height: 1.8; font-size: 1rem;">
                              At Bugle Technologies Private Limited, our name represents more than just an identity. It stands for the principles that guide our work and define our partnership with every client. Each letter in <strong>BUGLE</strong> captures our belief in combining creativity, technology, and strategy to build meaningful digital growth.
                           </p>
                        </div>
                        <div>
                           <h2 style="font-size: 2.5rem; color: #666; margin-bottom: 40px; font-weight: normal;">
                              Passion that drives every project,<br>ensuring excellence and quality
                           </h2>
                        </div>
                     </div>
                  </div>

                  <!-- BUGLE Philosophy Cards -->
                  <div style="padding: 0 50px; margin-bottom: 60px;">
                     <div style="display: grid; gap: 20px;">
                        <!-- B - Bold Ideas -->
                        <div style="background: linear-gradient(135deg, #e91e63 0%, #f06292 100%); color: white; padding: 40px; border-radius: 12px;">
                           <h3 style="font-size: 2rem; margin-bottom: 15px; font-weight: 600;">B – Bold Ideas</h3>
                           <p style="font-size: 1.1rem; line-height: 1.8; opacity: 0.95;">
                              We approach every project with clarity and confidence. Our goal is to create strategies that are innovative, practical, and built around business outcomes. Bold ideas help our clients stand out in a competitive digital world.
                           </p>
                        </div>

                        <!-- U - Unified Solutions -->
                        <div style="background: linear-gradient(135deg, #9c27b0 0%, #ba68c8 100%); color: white; padding: 40px; border-radius: 12px;">
                           <h3 style="font-size: 2rem; margin-bottom: 15px; font-weight: 600;">U – Unified Solutions</h3>
                           <p style="font-size: 1.1rem; line-height: 1.8; opacity: 0.95;">
                              We bring design, technology, and marketing together to form complete digital ecosystems. Every service we provide is connected, ensuring a consistent experience that supports our clients' goals from start to finish.
                           </p>
                        </div>

                        <!-- G - Growth Driven Outcomes -->
                        <div style="background: linear-gradient(135deg, #ff5722 0%, #ff8a65 100%); color: white; padding: 40px; border-radius: 12px;">
                           <h3 style="font-size: 2rem; margin-bottom: 15px; font-weight: 600;">G – Growth Driven Outcomes</h3>
                           <p style="font-size: 1.1rem; line-height: 1.8; opacity: 0.95;">
                              Growth is the foundation of our work. Whether it's brand awareness, online performance, or business expansion, we focus on results that make a real difference and help our clients move forward.
                           </p>
                        </div>

                        <!-- L - Leadership Through Innovation -->
                        <div style="background: linear-gradient(135deg, #3f51b5 0%, #7986cb 100%); color: white; padding: 40px; border-radius: 12px;">
                           <h3 style="font-size: 2rem; margin-bottom: 15px; font-weight: 600;">L – Leadership Through Innovation</h3>
                           <p style="font-size: 1.1rem; line-height: 1.8; opacity: 0.95;">
                              We stay ahead by exploring new technologies and ideas. Our team believes in continuous learning and improvement, ensuring that every solution reflects current trends and delivers long-term value.
                           </p>
                        </div>

                        <!-- E - Excellence in Execution -->
                        <div style="background: linear-gradient(135deg, #795548 0%, #a1887f 100%); color: white; padding: 40px; border-radius: 12px;">
                           <h3 style="font-size: 2rem; margin-bottom: 15px; font-weight: 600;">E – Excellence in Execution</h3>
                           <p style="font-size: 1.1rem; line-height: 1.8; opacity: 0.95;">
                              We believe that great ideas matter only when executed with precision. Every design, campaign, and line of code is crafted with care and attention to detail. Quality, reliability, and client satisfaction define our work.
                           </p>
                        </div>
                     </div>
                  </div>
                  
                  <!-- Contact Section -->
                  <div class="contact-section">
                     <h3>Get In Touch With Us</h3>
                     <div class="contact-info">
                        <div class="contact-item">
                           <i class="fas fa-map-marker-alt"></i>
                           <span>Plot No. 520, Opp. Ayyappa Temple, Sector-1, Gandhinagar, Gujarat 382007</span>
                        </div>
                        <div class="contact-item">
                           <i class="fas fa-envelope"></i>
                           <span>info@bugletechnologies.com</span>
                        </div>
                        <div class="contact-item">
                           <i class="fas fa-phone"></i>
                           <span>+91 94264 02423</span>
                        </div>
                     </div>
                  </div>
                  
               </div>
            </div>
         </div>
      </div>
      
      <script src="https://designmykitchen.cloud/public/backend/dist/assets/bundles/libscripts.bundle.js"></script>
   </body>
</html>