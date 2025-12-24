
# 🚀 LEET MAKERS Platform

> **Your Gateway to the world of technology & robotics**

A modern web platform for the 1337 IT School Robotics Club, featuring secure authentication, resource management, community engagement, and administrative tools.

> ⚠️ **Work in Progress**: This project is currently under active development. Features and functionality are subject to change.

---


## ✨ Features

### User-Facing Highlights
- **Secure Multi-Authentication:** Sign up and log in using Email/Password, Google OAuth, or 42 Intra OAuth (planned).
- **Personalized Profiles:** Customizable user profiles with avatar support and profile editing.
- **Resource Management:** Access and manage club resources, events, and notifications in a unified dashboard.
- **Community Engagement:** Participate in club activities, view member lists, and interact with the community.
- **Modern UI/UX:** Responsive, accessible design with dark/light mode and mobile support.

### Technical & Admin Features
- **Role-Based Access Control:** User and Admin roles with protected routes and admin-only features.
- **Transactional Email System:** Automated emails for verification, password reset, and notifications using PHPMailer.
- **Robust Security:** Password hashing (bcrypt), OTP verification, session management, and SSL/TLS support.
- **Containerized Deployment:** Docker and Docker Compose for easy setup, scaling, and consistent environments.
- **Logging & Monitoring:** Centralized logs for Apache and application events.
- **Extensible Architecture:** Modular backend (PHP) and frontend (HTML/CSS/JS) for easy feature expansion.

---

## 🛠️ Tech Stack

### Frontend
- **HTML5** - Semantic markup
- **CSS3** - Modern styling with custom properties
- **JavaScript (Vanilla)** - No framework dependencies
- **Font Awesome & RemixIcons** - Icon libraries

### Backend
- **PHP 8.x** - Server-side logic
- **Apache 2.4** - Web server with SSL
- **MySQL 8.0** - Relational database
- **PHPMailer** - Email delivery system

### DevOps & Tools
- **Docker** - Containerization
- **Docker Compose** - Multi-container orchestration
- **Git** - Version control
- **Make** - Build automation
- **Composer** - PHP dependency management

### External Services
- **Google OAuth 2.0** - Third-party authentication
- **42 Network API** - School authentication (planned)

---

## 🚀 Quick Start

### Prerequisites
- Git
- Docker & Docker Compose
- Make

### Installation

```bash
# Clone the repository
git clone https://github.com/LEETMAKERS/LEETMAKERS.git
cd LEETMAKERS

# Configure environment
cp reqs/env/.env.example reqs/env/.env
# Edit .env with your settings

# Setup local domain (Required)
# Add this line to your hosts file:
# - Windows: C:\Windows\System32\drivers\etc\hosts
# - Linux/macOS: /etc/hosts
# 
# 127.0.0.1 leetmakers.com

# Build and start
make build

# Access the platform
# Website: https://leetmakers.com (local only)
# phpMyAdmin: http://leetmakers.com:8001
```

> **Note**: The domain `leetmakers.com` is currently configured for local testing only. You must configure your hosts file as shown above to access the platform. When the project is deployed and completed, it will use a public DNS. An automated setup script may be provided in a future update.

### Main available Commands

```bash
make help       # Show full help message with all available commands
make build      # Build containers
make down       # Stop services
make rebuild    # Rebuild everything
make shell      # Access containers shell
make logs       # View logs
make fclean     # Full clean
```

---


## 📁 Project Structure

```
LEETMAKERS/
├── makefile                  # Build automation
├── readme.md                 # Project documentation
└── reqs/
	├── docker-compose.yml    # Container orchestration
	├── db/                   # Database schema & initialization
	│   ├── init-db.sh
	│   └── schema.sql
	├── env/                  # Environment variables
	│   ├── .env
	│   └── .env.example
	├── server/               # Server configuration & scripts
	│   ├── Dockerfile
	│   ├── config/
	│   │   └── default-ssl.conf
	│   ├── logs/
	│   │   ├── apache/
	│   │   └── application/
	│   ├── ssl/
	│   │   ├── certs/
	│   │   └── private/
	│   └── tools/
	│       └── docker-entrypoint.sh
	└── website/              # Web application
		├── assets/
		│   ├── css/          # Stylesheets (errors, forms, home, etc.)
		│   ├── fonts/        # Custom fonts (Gugi, Tajawal, virgo_01)
		│   ├── js/           # Frontend scripts (profile, nav, forms, etc.)
		│   ├── lang/         # Localization (en.json, fr.json)
		│   └── res/          # Static resources (avatars, icons, svg, etc.)
		├── backend/
		│   ├── auth/         # Authentication endpoints (login, register, etc.)
		│   ├── includes/     # Core backend utilities (dbConn, mailer, etc.)
		│   └── utils/        # User profile and utility scripts
		└── frontend/
			├── activity.php
			├── community.php
			├── dashboard.php
			├── events.php
			├── index.php
			├── inventory.php
			├── memberOps.php
			├── notifications.php
			├── profile.php
			├── resources.php
			├── settings.php
			├── auth/        # Auth pages (authenticate, recover, secure)
			├── components/  # Reusable UI components (navSideBar)
			├── errors/      # Error pages (handler.html)
			├── policies/    # Policy pages (faq, privacy, terms)
			└── templates/
				└── mails/   # Email templates (verify, reset, notify, etc.)
```

---

## 📧 Contact & Support

- **Organization**: [LEET MAKERS](https://github.com/LEETMAKERS) - 1337 IT School Robotics Club (Benguerir Campus)
- **Lead Developer**: Abderrahmane Abdelouafi ([@ababdelo](https://github.com/ababdelo))
- **Other Contributors**: (Coming Soon)
---

## 📄 License

**Copyright © 2025 LEET MAKERS Organization. All Rights Reserved.**

This is proprietary software developed by and for the LEET MAKERS Robotics Club's Organization at 1337 IT School (Benguerir Campus).  
Unauthorized use, copying, or distribution is prohibited without explicit permission.

For licensing inquiries: ababdelo.ed42@gmail.com
