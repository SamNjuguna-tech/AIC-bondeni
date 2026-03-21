# Church Website

A modern, responsive church website built with PHP, MySQL, Bootstrap, and Stripe integration. This website provides essential features for church communities including sermon archives, event management, prayer requests, and online donations.

## Features

- **User Management**
  - Role-based access control (Guest, Member, Volunteer, Church Leader, Admin)
  - Secure authentication system
  - User profiles and member directory

- **Sermons**
  - Video sermon archive
  - Search and filter by speaker, date, or topic
  - Embedded video playback

- **Events**
  - Event calendar and listings
  - Event registration system
  - Attendance tracking
  - Event reminders

- **Prayer Requests**
  - Submit public and private prayer requests
  - Prayer wall for community support
  - Prayer request management for church leaders

- **Online Giving**
  - Secure donations through Stripe
  - Multiple fund options
  - Recurring giving options
  - Tax receipt generation

## Prerequisites

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx)
- Composer (for dependency management)
- Stripe account for payment processing

## Installation

1. Clone the repository:
```bash
git clone <repository-url>
cd church-site
```

2. Create a MySQL database and import the schema:
```bash
mysql -u root -p
CREATE DATABASE church_db;
```

3. Configure the database connection:
- Copy `config/database.example.php` to `config/database.php`
- Update the database credentials in `config/database.php`

4. Install dependencies:
```bash
composer install
```

5. Set up Stripe:
- Create a Stripe account at https://stripe.com
- Get your API keys from the Stripe Dashboard
- Update the Stripe public key in `donate.php`
- Update the Stripe secret key in your payment processing endpoint

6. Configure your web server:
- Point your web server's document root to the project's root directory
- Ensure the web server has write permissions for uploads directory

## Directory Structure

```
church-site/
├── admin/              # Admin dashboard and management
├── assets/
│   ├── css/           # Stylesheets
│   ├── js/            # JavaScript files
│   └── images/        # Image assets
├── config/            # Configuration files
├── includes/          # PHP includes and functions
├── uploads/           # User uploaded content
└── vendor/           # Composer dependencies
```

## Security Considerations

- All user passwords are hashed using PHP's password_hash()
- SQL injection prevention using prepared statements
- XSS prevention through output escaping
- CSRF protection on forms
- Secure session handling
- Role-based access control

## Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Support

For support and questions, please contact the development team or open an issue in the repository.

## Acknowledgments

- Bootstrap for the responsive UI framework
- Stripe for secure payment processing
- Font Awesome for icons
- All contributors who have helped with the project



your-project/
├── assets/
│   └── images/
│       └── ministry-placeholder.jpg
├── config/
│   ├── database.php
│   └── uploads.php
├── includes/
│   ├── auth.php
│   ├── session.php
│   ├── navbar.php
│   └── footer.php
├── uploads/
│   └── ministries/
│       ├── thumbs/
│       └── .htaccess
├── admin/
│  
└── ministries.php