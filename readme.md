
# 🚀 LEET MAKERS Platform

> **Your Gateway to the world of technology & robotics**

A modern web platform for the 1337 IT School Robotics Club, featuring secure authentication, resource management, community engagement, and administrative tools.

> ⚠️ **Work in Progress**: This project is currently under active development. Features and functionality are subject to change.

---

## ✨ Key Features

- 🔐 **Multi-Authentication**: Email/Password, Google OAuth, 42 Intra OAuth
- 👥 **Role-Based Access**: User and Admin roles with protected pages
- 📧 **Email System**: Professional transactional emails (verification, password reset, etc.)
- 🎨 **Modern UI**: Responsive design with dark/light theme support
- 🐳 **Docker Ready**: Fully containerized with Docker Compose

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
git clone https://github.com/ababdelo/LEETMAKERS.git
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
make up

# Access the platform
# Website: https://leetmakers.com (local only)
# phpMyAdmin: http://localhost:8080
```

> **Note**: The domain `leetmakers.com` is currently configured for local testing only. You must configure your hosts file as shown above to access the platform. When the project is deployed and completed, it will use a public DNS. An automated setup script may be provided in a future update.

### Main make Commands

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
├── reqs/
│   ├── docker-compose.yml       # Container orchestration
│   ├── db/                      # Database schema & init
│   ├── env/                     # Environment variables
│   ├── server/                  # Apache/PHP configuration
│   └── website/
│       ├── backend/             # PHP logic (auth, utils, etc.)
│       └── frontend/            # Pages, components, templates
├── makefile                     # Build automation
└── readme.md                    # This file
```

---

## 🔒 Security Features

- Password hashing with bcrypt
- OTP verification (6-digit, 15-min expiry)
- Session regeneration on auth events
- Prepared SQL statements
- SSL/TLS support
- Role-based page protection

---

## 📧 Contact & Support

- **Developer**: Abderrahmane Abdelouafi ([@ababdelo](https://github.com/ababdelo))
- **Email**: ababdelo.ed42@gmail.com
- **Organization**: LEET MAKERS - 1337 IT School Robotics Club (Benguerir Campus)

---

## 📄 License

**Copyright © 2025 Abderrahmane Abdelouafi. All Rights Reserved.**

This is proprietary software developed for the LEET MAKERS Robotics Club at 1337 IT School.  
Unauthorized use, copying, or distribution is prohibited without explicit permission.

For licensing inquiries: ababdelo.ed42@gmail.com
