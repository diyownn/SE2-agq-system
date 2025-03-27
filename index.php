<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>AGQ Freight Logistics, Inc.</title>
  <link rel="icon" type="image/x-icon" href="../AGQ/images/favicon.ico">
  <link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <link
    rel="stylesheet"
    href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@300;400;500;600;700&display=swap" />
  <style>
    :root {
      --primary-color: #90ae5e;
      --primary-dark: #3d5d08;
      --primary-light: #d0dcb3;
      --text-dark: #333;
      --text-light: #f8f8f8;
      --accent-purple: #673ab7;
      --bg-light: #f5f5f5;
      --bg-white: #fff;
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: "IBM Plex Sans", sans-serif;
      color: var(--text-dark);
      line-height: 1.6;
      overflow-x: hidden;
    }

    /* Header Styles */
    header {
      background-color: var(--bg-white);
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
      position: fixed;
      width: 100%;
      top: 0;
      z-index: 100;
      padding: 15px 0;
    }

    .container {
      width: 90%;
      max-width: 1200px;
      margin: 0 auto;
    }

    .header-content {
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .logo {
      display: flex;
      align-items: center;
      animation: fadeIn 1s ease-in-out;
    }

    .logo img {
      height: 60px;
    }

    nav ul {
      display: flex;
      list-style: none;
    }

    nav ul li {
      margin-left: 30px;
      animation: slideDown 0.5s ease-out forwards;
      opacity: 0;
    }

    nav ul li:nth-child(1) {
      animation-delay: 0.1s;
    }

    nav ul li:nth-child(2) {
      animation-delay: 0.2s;
    }

    nav ul li:nth-child(3) {
      animation-delay: 0.3s;
    }

    nav ul li:nth-child(4) {
      animation-delay: 0.4s;
    }

    nav ul li:nth-child(5) {
      animation-delay: 0.5s;
    }

    nav ul li a {
      text-decoration: none;
      color: var(--text-dark);
      font-weight: 600;
      text-transform: uppercase;
      font-size: 15px;
      letter-spacing: 0.5px;
      position: relative;
      transition: color 0.3s ease;
    }

    nav ul li a::after {
      content: "";
      position: absolute;
      bottom: -5px;
      left: 0;
      width: 0;
      height: 2px;
      background-color: var(--primary-color);
      transition: width 0.3s ease;
    }

    nav ul li a:hover {
      color: var(--primary-color);
    }

    nav ul li a:hover::after {
      width: 100%;
    }

    .login-btn {
      background-color: var(--primary-color);
      color: white;
      padding: 8px 20px;
      border-radius: 4px;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
      transition: all 0.3s ease;
    }

    .login-btn:hover {
      background-color: var(--primary-dark);
      color: white;
      /* Ensure text color remains unchanged */
      transform: translateY(-2px);
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
    }


    /* Hero Section */
    .hero {
      margin-top: 90px;
      padding: 80px 0;
      background: var(--bg-white);
      position: relative;
      overflow: hidden;
    }

    .hero-content {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 50px;
      align-items: center;
    }

    .hero-text {
      animation: fadeInLeft 1s ease-out;
    }

    .hero-text h1 {
      font-size: 2.5rem;
      margin-bottom: 20px;
      color: var(--text-dark);
    }

    .hero-text p {
      margin-bottom: 30px;
      font-size: 1.1rem;
      line-height: 1.8;
    }

    .hero-image {
      text-align: center;
      animation: fadeInRight 1s ease-out;
    }

    .hero-image img {
      max-width: 100%;
      border-radius: 10px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    }

    .btn {
      display: inline-block;
      background-color: var(--primary-color);
      color: white;
      padding: 12px 30px;
      text-decoration: none;
      border-radius: 4px;
      font-weight: 600;
      transition: all 0.3s ease;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .btn:hover {
      background-color: var(--primary-dark);
      transform: translateY(-3px);
      box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
    }

    /* Services Section */
    .services {
      padding: 80px 0;
      background-color: var(--bg-light);
    }

    .section-header {
      text-align: center;
      margin-bottom: 60px;
      animation: fadeIn 1s ease-out;
    }

    .section-header h2 {
      font-size: 2.5rem;
      margin-bottom: 15px;
      color: var(--text-dark);
      position: relative;
      display: inline-block;
    }

    .section-header h2::after {
      content: "";
      position: absolute;
      bottom: -10px;
      left: 50%;
      transform: translateX(-50%);
      width: 80px;
      height: 3px;
      background-color: var(--primary-color);
    }

    .services-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
      gap: 30px;
    }

    .service-card {
      background-color: var(--bg-white);
      border-radius: 10px;
      padding: 30px;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
      transition: transform 0.3s ease, box-shadow 0.3s ease;
      animation: fadeInUp 0.8s ease-out forwards;
      opacity: 0;
    }

    .service-card:nth-child(1) {
      animation-delay: 0.1s;
    }

    .service-card:nth-child(2) {
      animation-delay: 0.3s;
    }

    .service-card:nth-child(3) {
      animation-delay: 0.5s;
    }

    .service-card:hover {
      transform: translateY(-10px);
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    }

    .service-card i {
      font-size: 50px;
      color: var(--primary-color);
      margin-bottom: 20px;
    }

    .service-card h3 {
      font-size: 1.5rem;
      margin-bottom: 15px;
      color: var(--text-dark);
    }

    .service-card p {
      color: #666;
      margin-bottom: 20px;
    }

    /* About Section with Content from First Image */
    .about {
      padding: 80px 0;
      background-color: var(--bg-white);
    }

    .about-content {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 50px;
      align-items: center;
    }

    .about-text {
      animation: fadeInRight 1s ease-out;
    }

    .about-image {
      animation: fadeInLeft 1s ease-out;
    }

    .about-image img {
      max-width: 100%;
      border-radius: 10px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    }

    .about-text h2 {
      font-size: 2.2rem;
      margin-bottom: 20px;
      color: var(--text-dark);
      position: relative;
    }

    .about-text h2::after {
      content: "";
      position: absolute;
      bottom: -10px;
      left: 0;
      width: 80px;
      height: 3px;
      background-color: var(--primary-color);
    }

    .about-text p {
      margin-bottom: 20px;
      line-height: 1.8;
    }

    /* Service Features Section */
    .features {
      padding: 80px 0;
      background-color: var(--bg-light);
    }

    .feature-card {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 30px;
      margin-bottom: 50px;
      align-items: center;
      animation: fadeIn 1s ease-out;
    }

    .feature-image {
      position: relative;
      overflow: hidden;
      border-radius: 10px;
    }

    .feature-image img {
      width: 100%;
      display: block;
      transition: transform 0.5s ease;
    }

    .feature-card:hover .feature-image img {
      transform: scale(1.05);
    }

    .feature-content {
      padding: 20px;
    }

    .feature-content h3 {
      font-size: 1.8rem;
      margin-bottom: 20px;
      color: var(--text-dark);
    }

    .feature-content p {
      margin-bottom: 20px;
      line-height: 1.7;
    }

    /* Footer Section */
    footer {
      background-color: #f0f6e8;
      padding: 40px 0 20px;
      border-top: 5px solid var(--primary-light);
    }

    .footer-container {
      max-width: 1200px;
      margin: 0 auto;
      padding: 0 20px;
    }

    .footer-content {
      padding: 20px;
      animation: fadeIn 1s ease-out;
    }

    .footer-content h3 {
      font-size: 1.8rem;
      margin-bottom: 15px;
      color: var(--text-dark);
    }

    .address {
      margin-bottom: 20px;
    }

    .contact-info p {
      margin-bottom: 10px;
    }

    .contact-info a {
      display: block;
      color: var(--text-dark);
      text-decoration: none;
      margin-bottom: 8px;
      transition: color 0.3s ease;
    }

    .contact-info a:hover {
      color: var(--primary-color);
      text-decoration: underline;
    }

    .copyright {
      text-align: center;
      margin-top: 30px;
      padding-top: 20px;
      border-top: 1px solid #ddd;
      font-size: 0.9rem;
      color: #777;
    }

    /* Animations */
    @keyframes fadeIn {
      from {
        opacity: 0;
      }

      to {
        opacity: 1;
      }
    }

    @keyframes fadeInLeft {
      from {
        opacity: 0;
        transform: translateX(-50px);
      }

      to {
        opacity: 1;
        transform: translateX(0);
      }
    }

    @keyframes fadeInRight {
      from {
        opacity: 0;
        transform: translateX(50px);
      }

      to {
        opacity: 1;
        transform: translateX(0);
      }
    }

    @keyframes fadeInUp {
      from {
        opacity: 0;
        transform: translateY(30px);
      }

      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    @keyframes slideDown {
      from {
        opacity: 0;
        transform: translateY(-20px);
      }

      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    /* Responsive Styles */
    @media (max-width: 992px) {

      .hero-content,
      .about-content {
        grid-template-columns: 1fr;
        gap: 30px;
      }

      .feature-card {
        grid-template-columns: 1fr;
      }
    }

    @media (max-width: 768px) {
      nav ul {
        display: none;
      }

      .services-grid {
        grid-template-columns: 1fr;
      }
    }
  </style>
</head>

<body>
  <header>
    <div class="container">
      <div class="header-content">
        <div class="logo">
          <img
            src="../SE2-agq-system/AGQ/images/agq_logo.png"
            alt="AGQ Freight Logistics Logo" />
        </div>
        <nav>
          <ul>
            <li><a href="#home">Home</a></li>
            <li><a href="#contact">Contact Us</a></li>
            <li><a href="#services">Services</a></li>
            <li><a href="../SE2-agq-system/AGQ/agq_login.php" class="login-btn">Login</a></li>
          </ul>
        </nav>
      </div>
    </div>
  </header>

  <section class="hero" id="home">
    <div class="container">
      <div class="hero-content">
        <div class="hero-text">
          <h1>AGQ Freight Logistics</h1>
          <p>
            Established in 2006, with the primary objective to offer Freight
            Forwarding, NVOCC, Consolidation, Beak Bulk and Brokerage Service.
            AGQ Freights is privately owned by a Filipino, and being handled
            by a team of professional and aggressive people who play a key
            role in its day to day operations. The team continues to uphold
            the Company's established values of integrity, commitment,
            teamwork, honesty, reliability, and customer dedication.
          </p>
          <a href="../SE2-agq-system/AGQ/agq_login.php" class="btn">Login</a>
        </div>
        <div class="hero-image">
          <img
            src="../SE2-agq-system/pexels-rdne-7464725.jpg"
            alt="AGQ Freight Logistics Logo" />
        </div>
      </div>
    </div>
  </section>

  <section class="services" id="services">
    <div class="container">
      <div class="section-header">
        <h2>Our Services</h2>
        <p style="margin-top: 15px; font-size: larger;">Comprehensive logistics solutions for your business</p>
      </div>

      <div class="services-grid">
        <div class="service-card">
          <i class="fas fa-ship"></i>
          <h3>International Air/Sea Freight Forwarding</h3>
          <p>
            We provide freight forwarding from place of origin to any final
            destination that you require, whether the shipment is by sea, air,
            road, rail, or combined mode of transport.
          </p>
        </div>

        <div class="service-card">
          <i class="fas fa-file-invoice"></i>
          <h3>Customs Brokerage</h3>
          <p>
            We handle various businesses for both exports and imports. We
            transact to all ports of entry: South Harbor (ATI), Manila
            International Container (MICP) and Ninoy Aquino International
            Airport (NAIA).
          </p>
        </div>

        <div class="service-card">
          <i class="fas fa-globe-americas"></i>
          <h3>Worldwide Network System</h3>
          <p>
            We have reliable and competent agents worldwide who possess
            expertise in the field of International Freight Forwarding and
            Shipping.
          </p>
        </div>
      </div>
    </div>
  </section>

  <section class="features">
    <div class="container">
      <div class="feature-card">
        <div class="feature-image">
          <img
            src="../SE2-agq-system/pexels-tima-miroshnichenko-6169668.jpg"
            alt="Exceptional Service" />
        </div>
        <div class="feature-content">
          <h3>EXCEPTIONAL SERVICE</h3>
          <p>
            The satisfactions of our clients with our quality service are the
            main principle of our company, and we make sure that these
            responsibilities are firmly established within our company
            culture. We invest not only on structural facilities and
            technologies, but also the need for which is wide-ranging and
            demanding, and it covers not only the industry requirements, but
            also the management and supervisory skills.
          </p>
        </div>
      </div>

      <div class="feature-card">
        <div class="feature-content">
          <h3>INTERNATIONAL AIR/SEA FREIGHT FORWARDING</h3>
          <p>
            We provide freight forwarding from place of origin to any final
            destination that you require, whether the shipment is by sea, air,
            road, rail, or combined mode of transport. We can arrange
            international airfreight and sea freight on behalf of our clients,
            and we developed our own fast, reliable and competitive services
            to and from many destinations and origins. The services and routes
            we offer are continually expanding as we continue to enhance our
            strategic partnerships in various locations worldwide.
          </p>
        </div>
        <div class="feature-image">
          <img src="../SE2-agq-system/pexels-dibert-1117210.jpg" alt="International Freight" />
        </div>
      </div>

      <div class="feature-card">
        <div class="feature-image">
          <img src="../SE2-agq-system/pexels-tiger-lily-4484155.jpg" alt="Customs Brokerage" />
        </div>
        <div class="feature-content">
          <h3>CUSTOMS BROKERAGE</h3>
          <p>
            We handle various businesses for both exports and imports. We
            transact to all ports of entry: South Harbor (ATI), Manila
            International Container (MICP) and Ninoy Aquino International
            Airport (NAIA) processing all phases of Customs Brokerage and
            maintain very good rapport with the Bureau of Customs.
          </p>
        </div>
      </div>
    </div>
  </section>

  <footer>
    <div class="footer-container">
      <div class="footer-content" id="contact">
        <h3>AGQ FREIGHT LOGISTICS, INC.</h3>
        <div class="address">
          <p>Unit 518, 5th Floor Alliance Bldg.</p>
          <p>#410 Quintin Paredes Street Binondo, Manila</p>
        </div>
        <div class="contact-info">
          <p>(632) 8244-4835 / (632) 8243-7095</p>
          <a href="mailto:alma_28@agqfreight.com.ph">alma_28@agqfreight.com.ph</a>
          <a href="mailto:info@agqfreight.com.ph">info@agqfreight.com.ph</a>
          <a href="mailto:agq_freightlogistics@hotmail.com">agq_freightlogistics@hotmail.com</a>
          <a href="mailto:export@agqfreight.com.ph">export@agqfreight.com.ph</a>
          <a href="mailto:import@agqfreight.com.ph">import@agqfreight.com.ph</a>
        </div>
      </div>
      <div class="copyright">
        <p>&copy; 2025 AGQ Freight Logistics, Inc. All rights reserved.</p>
      </div>
    </div>
  </footer>

  <script>
    // Add scroll reveal animations
    document.addEventListener("DOMContentLoaded", function() {
      // Animate service cards on scroll
      const serviceCards = document.querySelectorAll(".service-card");
      const featureCards = document.querySelectorAll(".feature-card");

      const observer = new IntersectionObserver(
        (entries) => {
          entries.forEach((entry) => {
            if (entry.isIntersecting) {
              entry.target.style.opacity = 1;
              entry.target.style.transform = "translateY(0)";
            }
          });
        }, {
          threshold: 0.1
        }
      );

      serviceCards.forEach((card) => {
        observer.observe(card);
      });

      featureCards.forEach((card) => {
        card.style.opacity = 0;
        card.style.transform = "translateY(30px)";
        observer.observe(card);
      });
    });
  </script>
</body>

</html>