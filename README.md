# BookVerse - AI-Powered Book Marketplace

A modern **AI-powered Book Marketplace** where users can browse, purchase, and discover books with intelligent recommendations and summaries. Built with Laravel 10 API backend, React frontend, Hugging Face AI integration, and comprehensive Docker deployment.

## 🚀 Project Overview

BookVerse is a full-stack e-commerce platform that leverages artificial intelligence to enhance the book shopping experience. The application features AI-powered book recommendations, automated book summaries, and a seamless payment system supporting multiple gateways.

**Key Technologies:**
- **Backend**: Laravel 10 (RESTful API)
- **Frontend**: React + Vite (Modern UI)
- **AI**: Hugging Face API (Recommendations & Summaries)
- **Database**: MySQL + Redis
- **Deployment**: Docker + GitHub Actions CI/CD
- **Payments**: Stripe & PayPal (Factory Pattern)

## ✨ Features

### 🛒 User Features
- **Browse & Search**: Advanced book catalog with search functionality
- **AI Recommendations**: Personalized book suggestions using Hugging Face API
- **AI Book Summaries**: Automated book summaries generated via AI
- **Shopping Cart**: Add/remove books with quantity management
- **Secure Checkout**: Multiple payment options (Stripe & PayPal)
- **Order History**: Track past orders and status updates
- **User Authentication**: JWT-based secure login/registration

### 👨‍💼 Admin Features
- **Book Management**: Full CRUD operations for books
- **Order Management**: View and update order statuses
- **User Management**: Admin panel for user oversight
- **Analytics Dashboard**: Sales and user analytics

### 💳 Payment System
- **Factory Pattern**: Abstracted payment gateway system
- **Multiple Providers**: Stripe and PayPal integration
- **Secure Transactions**: PCI-compliant payment processing
- **Order Tracking**: Real-time payment status updates

### 🤖 AI Integration
- **Book Recommendations**: ML-powered personalized suggestions
- **AI Summaries**: Automated book content summarization
- **Search Enhancement**: AI-improved search results
- **Analytics**: AI-driven insights and trends

### 🔄 Events & Queues
- **Background Processing**: Redis-based job queues
- **Email Notifications**: Automated order confirmations
- **AI Task Queuing**: Asynchronous AI processing
- **Event-Driven Architecture**: Decoupled system components

## 🛠 Tech Stack

### Backend
- **Laravel 10** - PHP framework with API resources
- **JWT Authentication** - Token-based security
- **MySQL** - Primary database
- **Redis** - Caching and queue management
- **PHPUnit** - Comprehensive testing suite

### Frontend
- **React 18** - Modern UI framework
- **Vite** - Fast build tool and dev server
- **Tailwind CSS** - Utility-first styling
- **Axios** - HTTP client for API communication

### AI & External Services
- **Hugging Face API** - AI models for recommendations and summaries
- **Stripe API** - Payment processing
- **PayPal API** - Alternative payment gateway

### DevOps & Infrastructure
- **Docker** - Containerization
- **Docker Compose** - Multi-service orchestration
- **GitHub Actions** - CI/CD pipeline
- **Nginx** - Web server
- **PHP-FPM** - Application server

## 📋 Prerequisites

- Docker and Docker Compose
- Node.js 18+ (for frontend development)
- Git

## 🚀 Installation & Setup

### 1. Clone the Repository
```bash
git clone <repository-url>
cd cursor-project
```

### 2. Environment Setup
```bash
# Copy environment file
cp .env.example .env

# Update environment variables for your setup
# (API keys for Hugging Face, Stripe, PayPal, etc.)
```

### 3. Start Backend Services
```bash
# Start all Docker services
docker-compose up --build -d

# Install PHP dependencies
docker-compose exec app composer install

# Generate application keys
docker-compose exec app php artisan key:generate
docker-compose exec app php artisan jwt:secret

# Run database migrations
docker-compose exec app php artisan migrate

# Seed the database (optional)
docker-compose exec app php artisan db:seed
```

### 4. Start Frontend Development Server
```bash
# Navigate to frontend directory
cd frontend

# Install dependencies
npm install

# Start development server
npm run dev
```

### 5. Access the Application
- **Frontend**: http://localhost:5173
- **Backend API**: http://localhost:8000
- **API Documentation**: http://localhost:8000/api/docs
- **Database**: localhost:3306
- **Redis**: localhost:6379

## 🧪 Testing

### Backend Testing (TDD Approach)
```bash
# Run all tests
docker-compose exec app php artisan test

# Run specific test suites
docker-compose exec app php artisan test tests/Feature/Auth/
docker-compose exec app php artisan test tests/Feature/BookTest.php

# Run tests with coverage
docker-compose exec app php artisan test --coverage

# Run unit tests only
docker-compose exec app php artisan test tests/Unit/
```

### Frontend Testing
```bash
cd frontend
npm test
```

## 📚 API Documentation

The API is fully documented using Swagger/OpenAPI. Access the interactive documentation at:
- **Swagger UI**: http://localhost:8000/api/docs
- **API JSON**: http://localhost:8000/api/docs/api-docs.json

### Key API Endpoints

#### Authentication
- `POST /api/auth/register` - User registration
- `POST /api/auth/login` - User login
- `POST /api/auth/logout` - User logout
- `GET /api/auth/me` - Get current user

#### Books
- `GET /api/books` - List all books
- `GET /api/books/{id}` - Get book details
- `GET /api/books/{id}/summary` - Get AI-generated summary
- `GET /api/books/{id}/recommendations` - Get AI recommendations
- `POST /api/books` - Create book (admin)
- `PUT /api/books/{id}` - Update book (admin)
- `DELETE /api/books/{id}` - Delete book (admin)

#### Orders
- `GET /api/orders` - User's order history
- `POST /api/orders` - Create new order
- `GET /api/orders/{id}` - Get order details

#### Search & Analytics
- `GET /api/search` - Search books
- `GET /api/analytics` - Get analytics data (admin)

## 🏗 Project Architecture

### Directory Structure
```
cursor-project/
├── app/
│   ├── Http/Controllers/Api/     # API controllers
│   ├── Models/                   # Eloquent models
│   ├── Services/                 # Business logic services
│   ├── Jobs/                     # Queue jobs
│   ├── Events/                   # Event classes
│   └── Policies/                 # Authorization policies
├── frontend/                     # React application
├── database/
│   ├── migrations/               # Database migrations
│   ├── seeders/                  # Data seeders
│   └── factories/                # Model factories
├── tests/                        # Test suites
├── docker/                       # Docker configuration
└── routes/api.php               # API routes
```

### Design Patterns
- **MVC Architecture** - Model-View-Controller separation
- **Factory Pattern** - Payment gateway abstraction
- **Service Layer** - Business logic encapsulation
- **Event-Driven** - Decoupled system components
- **Repository Pattern** - Data access abstraction

## 🚀 Deployment

### Docker Deployment
The application is fully containerized and ready for deployment on any Docker-compatible platform:

```bash
# Production build
docker-compose -f docker-compose.prod.yml up --build -d
```

### Supported Platforms
- **Render** - Easy deployment with automatic scaling
- **AWS ECS** - Enterprise-grade container orchestration
- **DigitalOcean App Platform** - Managed container deployment
- **Heroku** - Container deployment support

### CI/CD Pipeline
GitHub Actions automatically:
- Runs tests on every push
- Performs code linting
- Builds Docker images
- Deploys to staging/production

## 📸 Screenshots

*Screenshots will be added here showcasing the user interface and key features.*

## 🤝 Contributing

This project was developed as part of a comprehensive learning and portfolio effort. Contributions are welcome!

### Development Workflow
1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Write tests first (TDD approach)
4. Implement the feature
5. Ensure all tests pass
6. Commit your changes (`git commit -m 'Add amazing feature'`)
7. Push to the branch (`git push origin feature/amazing-feature`)
8. Open a Pull Request

### Code Standards
- Follow PSR-12 coding standards
- Write comprehensive tests
- Document new API endpoints
- Update README for new features

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🆘 Support

If you encounter any issues or have questions:

1. Check the [Issues](../../issues) page
2. Create a new issue with detailed information
3. Include error logs and steps to reproduce

---

**Happy Reading! 📚✨**

*Built with ❤️ using Laravel, React, and AI*
