<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <!-- Font Awesome & Boxicon & Remixicon CDN link for icons -->
   <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css" rel="stylesheet">
   <link href="https://cdn.jsdelivr.net/npm/remixicon@4.0.0/fonts/remixicon.css" rel="stylesheet">
   <link href='https://unpkg.com/boxicons@2.1.1/css/boxicons.min.css' rel='stylesheet'>
   <link rel="shortcut icon" href="Resources/logo/leetmakers.ico" type="image/x-icon">
   <link rel="stylesheet" href="/assets/css/home.css">
   <title>LEET MAKERS</title>
</head>

<body>
   <header>
      <a class="logo">
         <img src="/assets/res/logo/leetmakers.jpg" style="border-radius: 50px;">&nbsp;&nbsp;<span>LEET MAKERS</span>
      </a>
      <ul class="navbar">
         <li><a href="#HOME" class="active">HOME</a></li>
         <li><a href="#ABOUT">About Us</a></li>
         <li><a href="#MISSION">Our Mission</a></li>
         <li><a href="#CONTACT">Contact</a></li>
         <li class="auth-separator"></li>
         <li><a href="/auth/authenticate?action=login" class="auth-link user"><i class="ri-login-circle-line"></i>Sign
               In</a>
         </li>
         <li><a href="/auth/authenticate?action=register" class="auth-link user"><i
                  class="ri-user-add-line"></i>Register</a>
         </li>
      </ul>
      <div class="secondary-navbar">
         <a href="/auth/authenticate?action=login" class="user"><i class="ri-login-circle-line"></i>Sign In</a>
         <a href="/auth/authenticate?action=register" class="user"><i class="ri-user-add-line"></i>Register</a>
         <div class="bx bx-menu" id="menu-icon">
         </div>
      </div>
   </header>
   <section id="HOME">
      <div class="home-container">
         <div class="home-content">
            <p class="welcome-text">WELCOME TO</p>
            <h1 class="home-title">
               <span class="dark-text">LEET</span> <span class="blue-text">MAKERS</span>
            </h1>
            <p class="home-description">
               <span class="highlight">Haven</span> for those passionate about <span class="highlight">Robotics</span>,
               <span class="highlight">Electronics</span>, <span class="highlight">Technology</span> projects, and the
               limitless bounds of <span class="highlight">Creativity</span>. It's an <span
                  class="highlight">Exhilarating
                  Journey</span> into <span class="highlight">Unexplored Realms</span> where our projects come to to
               life.
            </p>
            <a href="#ABOUT" class="learn-more-btn">
               <i class="ri-information-line"></i> Learn More
            </a>
         </div>
         <div class="home-image">
            <img src="/assets/res/illustrations/workstation.png" alt="Club WorkStation">
         </div>
      </div>
      <div class="wave-divider">
         <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320" preserveAspectRatio="none">
            <path fill="#ffffff" fill-opacity="1"
               d="M0,96L48,112C96,128,192,160,288,160C384,160,480,128,576,122.7C672,117,768,139,864,149.3C960,160,1056,160,1152,138.7C1248,117,1344,75,1392,53.3L1440,32L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z">
            </path>
         </svg>
      </div>
   </section>
   <section id="ABOUT">
      <div class="about-container">
         <div class="section-header">
            <h2>About <span>Us</span></h2>
         </div>
         <div class="about-content">
            <div class="about-text">
               <p>
                  Step into the <span>Vibrant World</span> of Technology and Robotics, where student-driven creativity
                  and technology converge within the walls of <span>1337</span> the <span>Moroccan IT School</span> in
                  <span>Benguerir</span>.
               </p>
               <p>
                  At <span>LEET MAKERS</span>, we're not your average club; we're a living, breathing community managed
                  by students in our school.
                  Whether you're a <span>Curious NewComer</span> or a <span>seasoned enthusiast</span>, <span>LEET
                     MAKERS</span> welcomes you to join a community that thrives on the fusion of technology and
                  creative expression.
               </p>
               <p>
                  What sets us apart is the spirit of <span>self-discovery</span> ingrained in our <span>School's
                     Ethos</span>. Where there are no teachers here; Our school encourages students to learn from each
                  other and discover information on their own.
                  <span>LEET MAKERS</span> embodies this philosophy, offering a space where <span>Collaborative</span>
                  learning and hands-on exploration drive our endeavors.
               </p>
            </div>
         </div>

         <!-- Stats Infinite Scroll -->
         <div class="stats-scroll-container">
            <div class="stats-scroll">
               <div class="stats-track">
                  <div class="stat-item">
                     <i class="ri-team-fill"></i>
                     <div class="stat-content">
                        <h3>42+</h3>
                        <p>Active Members</p>
                     </div>
                  </div>
                  <div class="stat-item">
                     <i class="ri-calendar-event-fill"></i>
                     <div class="stat-content">
                        <h3>13+</h3>
                        <p>Organized Events</p>
                     </div>
                  </div>
                  <div class="stat-item">
                     <i class="ri-flask-fill"></i>
                     <div class="stat-content">
                        <h3>37+</h3>
                        <p>Realized Projects</p>
                     </div>
                  </div>
                  <div class="stat-item">
                     <i class="ri-trophy-fill"></i>
                     <div class="stat-content">
                        <h3>3+</h3>
                        <p>Prizes Won</p>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>
      <div class="wave-divider">
         <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320" preserveAspectRatio="none">
            <path fill="#f8f8f8" fill-opacity="1"
               d="M0,224L48,213.3C96,203,192,181,288,181.3C384,181,480,203,576,208C672,213,768,203,864,181.3C960,160,1056,128,1152,128C1248,128,1344,160,1392,176L1440,192L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z">
            </path>
         </svg>
      </div>
   </section>
   <section id="MISSION">
      <div class="mission-container">
         <div class="section-header">
            <h2>Our <span>Mission</span></h2>
         </div>
         <div class="mission-grid">
            <div class="mission-card">
               <div class="card-header">
                  <div class="card-icon">
                     <i class="ri-search-line"></i>
                  </div>
                  <h3>Exploration and Creativity</h3>
               </div>
               <p>
                  Members are encouraged to <span>Explore</span> their <span>Interests</span>, push the boundaries of
                  technology, and cultivate a <span>Mindset</span> of continuous learning and creativity.
               </p>
            </div>
            <div class="mission-card">
               <div class="card-header">
                  <div class="card-icon">
                     <i class="ri-group-line"></i>
                  </div>
                  <h3>Community and Collaboration</h3>
               </div>
               <p>
                  In the absence of formal teachers, <span>LEET MAKERS</span> thrives on collaborative learning. Our
                  club is a space where students <span>Share knowledge, skills</span>, and work <span>Together</span> on
                  exciting projects.
               </p>
            </div>
            <div class="mission-card">
               <div class="card-header">
                  <div class="card-icon">
                     <i class="ri-global-line"></i>
                  </div>
                  <h3>Real-world Impact</h3>
               </div>
               <p>
                  Our mission <span>Extends</span> beyond the classroom. We challenge members to apply their knowledge
                  to <span>Real-World</span> scenarios, preparing them for <span>Future Career challenges</span>.
                  <span>LEET MAKERS</span> is about making a tangible <span>Impact</span> in the world through the
                  application of <span>Technology</span> and <span>Innovation</span>.
               </p>
            </div>
            <div class="mission-card">
               <div class="card-header">
                  <div class="card-icon">
                     <i class="ri-medal-line"></i>
                  </div>
                  <h3>Personal Growth and Leadership</h3>
               </div>
               <p>
                  <span>LEET MAKERS</span> is not just a club; it's a journey of <span>Personal Growth</span>. As part
                  of our mission, we are committed to providing opportunities for <span>Leadership Development</span>.
                  Members can expect mentorship, guidance, and the chance to take on roles that cultivate leadership
                  skills, preparing them for <span>Success</span> beyond the club.
               </p>
            </div>
         </div>
      </div>
      <div class="wave-divider">
         <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320" preserveAspectRatio="none">
            <path fill="#ffffff" fill-opacity="1"
               d="M0,160L48,144C96,128,192,96,288,101.3C384,107,480,149,576,165.3C672,181,768,171,864,149.3C960,128,1056,96,1152,90.7C1248,85,1344,107,1392,117.3L1440,128L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z">
            </path>
         </svg>
      </div>
   </section>
   <section id="CONTACT">
      <div class="contact-container">
         <div class="section-header">
            <h2>Contact <span>Us</span></h2>
         </div>
         <div class="contact-content">
            <div class="contact-form">
               <p class="contact-intro">
                  Need help with LEET MAKERS? Have ideas or suggestions? Fill out the form below and we'll get back to
                  you soon!
               </p>
               <form action="/contact" method="POST">
                  <input type="email" name="email" placeholder="Email Address" required>
                  <input type="text" name="name" placeholder="Full Name" required>
                  <textarea name="message" placeholder="Your message" rows="5" required></textarea>
                  <button type="submit" class="submit-btn">Submit</button>
               </form>
            </div>
            <div class="contact-illustration">
               <img src="/assets/res/illustrations/contact.webp" alt="Contact Illustration">
            </div>
         </div>
      </div>
   </section>

   <footer>
      <div class="footer-main">
         <!-- Brand Section -->
         <div class="footer-brand">
            <div class="brand-logo">
               <img src="/assets/res/logo/leetmakers.jpg" alt="LEET MAKERS">
               <span>LEET MAKERS</span>
            </div>
            <!-- <p>A thriving community where innovators, professionals, and enthusiasts come together to share knowledge,
               collaborate, and grow.</p> -->
            <p class="school-mention">A student-driven robotics club at <a href="https://1337.ma" target="_blank">1337
                  IT School</a>, Benguerir Campus</p>
            <div class="social-icons">
               <a href="https://www.linkedin.com/company/leetmakers" target="_blank" title="LinkedIn" class="linkedin">
                  <i class="ri-linkedin-fill"></i>
               </a>
               <a href="#" target="_blank" title="Twitter" class="twitter">
                  <i class="ri-twitter-x-fill"></i>
               </a>
               <a href="https://www.instagram.com/leetmakers" target="_blank" title="Instagram" class="instagram">
                  <i class="ri-instagram-fill"></i>
               </a>
               <a href="#" target="_blank" title="GitHub" class="github">
                  <i class="ri-github-fill"></i>
               </a>
               <a href="#" target="_blank" title="YouTube" class="youtube">
                  <i class="ri-youtube-fill"></i>
               </a>
            </div>
         </div>

         <!-- Discover Column -->
         <div class="footer-column">
            <h4>Discover</h4>
            <ul>
               <li><a href="#">Events</a></li>
               <li><a href="#">Competitions</a></li>
               <li><a href="#">Projects</a></li>
               <li><a href="#">Testimonial</a></li>
            </ul>
         </div>

         <!-- Resources Column -->
         <div class="footer-column">
            <h4>Resources</h4>
            <ul>
               <li><a href="#">Blog</a></li>
               <li><a href="#">Tutorials</a></li>
               <li><a href="#">Guides</a></li>
            </ul>
         </div>

         <!-- Support Column -->
         <div class="footer-column">
            <h4>Support</h4>
            <ul>
               <li><a href="#">Help Center</a></li>
               <li><a href="/policies/faq">FAQ</a></li>
               <li><a href="#">Feedback</a></li>
               <li><a href="#">Report Issue</a></li>
            </ul>
         </div>

         <!-- Contact Column -->
         <div class="footer-column">
            <h4>Contact</h4>
            <ul>
               <li><a href="tel:+212 xxx xx xxxx"><i class="ri-phone-line"></i>+212 xxx xx xxxx</a></li>
               <li><a href="mailto:support@leetmakers.com"><i class="ri-mail-line"></i>contact@leetmakers.com</a></li>
               <li><a href="#"><i class="ri-map-pin-line"></i>Lot 660, Ben Guerir 43150</a></li>
            </ul>
         </div>

      </div>

      <div class="footer-bottom">
         <div class="footer-copyright">
            &copy; <?php echo date('Y'); ?> LEET MAKERS. All rights reserved.
         </div>
         <div class="footer-links">
            <a href="/policies/privacy" target="_blank">Privacy Policy</a>
            <a href="/policies/terms" target="_blank">Terms of Use</a>
            <a href="#" target="_blank">Developers</a>
         </div>
      </div>
   </footer>

   <!-- Scroll to Top Button -->
   <a href="#HOME" id="scroll-to-top" title="Back to top">
      <i class="ri-arrow-up-line"></i>
   </a>

   <script src="/assets/js/home.js"></script>
</body>

</html>
