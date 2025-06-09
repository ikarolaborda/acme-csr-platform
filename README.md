# ACME CSR Platform

A comprehensive Corporate Social Responsibility platform enabling ACME Corp employees to create, manage, and contribute to fundraising campaigns that make a positive impact on their communities.

## 🏗️ Architecture Overview

### **System Architecture**

The platform follows a **Clean Architecture** approach with clear separation of concerns:

```
┌─────────────────────────────────────────────┐
│                Frontend (Vue 3)             │
│  ┌─────────────┐  ┌─────────────┐  ┌───────┐│
│  │    Pages    │  │ Components  │  │ Stores││
│  │             │  │             │  │ (Pinia)││
│  └─────────────┘  └─────────────┘  └───────┘│
└─────────────────────┬───────────────────────┘
                      │ Axios HTTP Client
┌─────────────────────▼───────────────────────┐
│                Backend (Laravel)            │
│  ┌─────────────┐  ┌─────────────┐  ┌───────┐│
│  │ Controllers │  │   Actions   │  │ Models││
│  │             │  │             │  │       ││
│  └─────────────┘  └─────────────┘  └───────┘│
│  ┌─────────────┐  ┌─────────────┐  ┌───────┐│
│  │Repositories │  │  Services   │  │ DTOs  ││
│  │ (with Cache)│  │             │  │       ││
│  └─────────────┘  └─────────────┘  └───────┘│
└─────────────────────┬───────────────────────┘
                      │
┌─────────────────────▼───────────────────────┐
│            Infrastructure Layer             │
│  ┌─────────────┐  ┌─────────────┐  ┌───────┐│
│  │  Database   │  │    Cache    │  │ Queue ││
│  │(PostgreSQL) │  │   (Redis)   │  │(Redis)││
│  └─────────────┘  └─────────────┘  └───────┘│
└─────────────────────────────────────────────┘
```

### **Key Architectural Decisions**

#### **Backend (Laravel)**
- **Repository Pattern**: Abstracts data access with caching layer for performance
- **Action Classes**: Single-purpose classes for complex business logic
- **Service Layer**: Handles external integrations (payments, notifications)
- **DTO Pattern**: Type-safe data transfer between layers
- **JWT Authentication**: Stateless authentication for API scalability

#### **Frontend (Vue 3)**
- **Composition API**: Modern, reactive component architecture
- **Pinia State Management**: Centralized state with TypeScript support
- **Route-based Code Splitting**: Optimized loading performance
- **Component Library**: Reusable UI components following design system

#### **Infrastructure**
- **Docker Containerization**: Consistent development/production environments
- **Multi-stage Builds**: Optimized container sizes
- **PostgreSQL**: ACID compliance for financial transactions
- **Redis**: High-performance caching and session storage

## 🚀 Technology Stack

### **Backend**
- **PHP 8.4** - Latest PHP with performance improvements
- **Laravel 12** - Latest framework with modern features
- **PostgreSQL 16** - Primary database with JSON support
- **Redis 7** - Caching, sessions, and queue management
- **JWT Auth** - Stateless authentication

### **Frontend**
- **Vue 3** - Progressive JavaScript framework
- **Vite** - Fast build tool and dev server
- **TailwindCSS** - Utility-first styling
- **Heroicons** - Beautiful SVG icons
- **Axios** - HTTP client with interceptors

### **Development Tools**
- **PHPStan Level 8** - Maximum static analysis
- **Pest** - Modern PHP testing framework
- **Docker Compose** - Local development environment
- **GitHub Actions** - CI/CD pipeline

### **Payment Systems**
- **Flexible Provider Architecture** - Easy integration of multiple payment gateways
- **Stripe Integration** - Primary payment processor
- **PayPal Ready** - Pre-configured for future integration

## 📦 Installation & Setup

### **Prerequisites**
- Docker & Docker Compose
- Node.js 18+ (for local frontend development)
- Git

### **Quick Start**

1. **Clone the repository**
```bash
git clone <repository-url>
cd acme-csr-platform
```

2. **Environment Setup**
```bash
cp .env.example .env
# Edit .env with your configuration
```

3. **Start the application**
```bash
docker-compose up -d
```

4. **Initialize the application**
```bash
# Install dependencies
docker exec -it acme_csr_app composer install

# Generate application key
docker exec -it acme_csr_app php artisan key:generate

# Generate JWT secret
docker exec -it acme_csr_app php artisan jwt:secret

# Run migrations and seed data
docker exec -it acme_csr_app php artisan migrate --seed

# Build frontend assets
docker exec -it acme_csr_node_build npm run build
```

5. **Access the application**
- Frontend: `http://localhost:8080`
- API Documentation: `http://localhost:8080/api/documentation`

### **Default Admin User**
- Email: `testadmin@acme.com`
- Password: `password123`

## 🔧 Configuration

### **Environment Variables**

#### **Core Application**
```env
APP_NAME="ACME CSR Platform"
APP_ENV=production
APP_URL=https://your-domain.com
```

#### **Database**
```env
DB_CONNECTION=pgsql
DB_HOST=db
DB_DATABASE=acme_csr
DB_USERNAME=acme_user
DB_PASSWORD=secure_password
```

#### **Payment Configuration**
```env
# Payment System
PAYMENT_DEFAULT_PROVIDER=stripe
PAYMENT_DEFAULT_CURRENCY=USD
PAYMENT_MINIMUM_AMOUNT=5.00
PAYMENT_MAXIMUM_AMOUNT=10000.00

# Stripe
STRIPE_SECRET_KEY=sk_live_your_key
STRIPE_PUBLISHABLE_KEY=pk_live_your_key
STRIPE_WEBHOOK_SECRET=whsec_your_secret
```

#### **JWT Authentication**
```env
JWT_SECRET=your_jwt_secret
JWT_TTL=60
JWT_REFRESH_TTL=20160
```

## 🏢 Business Features

### **Employee Campaign Management**
- **Create Campaigns**: Rich text editor, image uploads, goal setting
- **Campaign Discovery**: Search, filter by category, trending campaigns
- **Campaign Analytics**: Real-time progress tracking, donor insights

### **Donation Processing**
- **Secure Payments**: PCI-compliant payment processing
- **Multiple Payment Methods**: Credit cards, bank transfers, digital wallets
- **Donation Confirmation**: Email receipts, tax documentation
- **Anonymous Donations**: Privacy-focused giving options

### **Administration Panel**
- **User Management**: Role-based access control, employee onboarding
- **Campaign Moderation**: Approval workflows, content management
- **Financial Reporting**: Donation analytics, tax reporting, audit trails
- **System Configuration**: Payment settings, campaign categories, limits

## 🔐 Security Features

### **Authentication & Authorization**
- **JWT-based Authentication**: Stateless, scalable token system
- **Role-based Access Control**: Admin, Employee, Manager roles
- **Password Security**: Bcrypt hashing, complexity requirements
- **Session Management**: Secure session handling with Redis

### **Data Protection**
- **Input Validation**: Comprehensive request validation
- **SQL Injection Prevention**: Eloquent ORM with prepared statements
- **XSS Protection**: Output escaping, CSP headers
- **CSRF Protection**: Token-based CSRF prevention

### **Payment Security**
- **PCI Compliance**: Tokenized payment processing
- **Encrypted Storage**: Sensitive data encryption at rest
- **Audit Logging**: Comprehensive transaction logging
- **Webhook Verification**: Cryptographic webhook validation

## 🧪 Testing Strategy

### **Test Coverage**
- **Unit Tests**: Business logic, services, repositories
- **Feature Tests**: API endpoints, user workflows
- **Integration Tests**: Payment processing, external services
- **Browser Tests**: Critical user journeys

### **Testing Tools**
```bash
# Run all tests
./vendor/bin/pest

# Run with coverage
./vendor/bin/pest --coverage

# Run specific test groups
./vendor/bin/pest --group=payments
./vendor/bin/pest --group=auth
```

### **Static Analysis**
```bash
# PHPStan Level 8 analysis
./vendor/bin/phpstan analyse

# Code style checking
./vendor/bin/pint --test
```

## 🚀 Deployment

### **Production Deployment**

#### **Docker Production Build**
```bash
# Build production images
docker build -t acme-csr-app:latest -f docker/app/Dockerfile.prod .
docker build -t acme-csr-nginx:latest -f docker/nginx/Dockerfile.prod .

# Deploy with docker-compose
docker-compose -f docker-compose.prod.yml up -d
```

#### **Kubernetes Deployment**
```bash
# Apply Kubernetes configurations
kubectl apply -f k8s/
```

### **Environment-specific Configurations**

#### **Production Optimizations**
- **OPcache**: PHP opcode caching enabled
- **Redis Clustering**: High-availability caching
- **CDN Integration**: Static asset delivery
- **Database Optimization**: Read replicas, connection pooling

#### **Monitoring & Logging**
- **Application Monitoring**: Laravel Horizon, Telescope
- **Error Tracking**: Sentry integration
- **Performance Monitoring**: New Relic, DataDog
- **Log Aggregation**: ELK stack integration

## 🔄 CI/CD Pipeline

### **GitHub Actions Workflow**
```yaml
# .github/workflows/ci.yml
- Code Quality Checks (PHPStan, Pint)
- Security Scanning (Composer Audit)
- Automated Testing (Pest)
- Docker Image Building
- Deployment to Staging/Production
```

### **Automated Checks**
- **Code Quality**: PHPStan level 8, Laravel Pint
- **Security**: Dependency vulnerability scanning
- **Performance**: Database query analysis
- **Coverage**: Minimum 80% test coverage

## 🔧 Technical Challenges & Solutions

### **Challenge: Payment Provider Flexibility**
**Problem**: "The Payment System(s) used has not been chosen yet and will be subject to a last minute decision."

**Solution**: Implemented a **Payment Provider Interface** with pluggable architecture:
- Abstract payment processing through interfaces
- Multiple provider support (Stripe, PayPal, etc.)
- Runtime provider switching via configuration
- Consistent error handling across providers

### **Challenge: High-Performance Data Access**
**Problem**: Large employee base (+20K) requires optimized data handling.

**Solution**: Implemented **Repository Pattern with Caching**:
- Redis-based query result caching
- Database query optimization
- Eager loading to prevent N+1 queries
- Pagination for large datasets

### **Challenge: Scalable Frontend Architecture**
**Problem**: Complex SPA with multiple user roles and workflows.

**Solution**: **Component-based Architecture**:
- Route-based code splitting for performance
- Centralized state management with Pinia
- Reusable component library
- Progressive loading for optimal UX

### **Challenge: Secure Financial Transactions**
**Problem**: Handling monetary transactions requires highest security standards.

**Solution**: **Multi-layered Security**:
- PCI-compliant payment processing
- Encrypted data storage
- Comprehensive audit logging
- JWT-based stateless authentication

## 📊 Performance Considerations

### **Database Optimization**
- **Indexes**: Optimized queries for campaigns, donations
- **Connection Pooling**: Efficient database connections
- **Read Replicas**: Scalable read operations
- **Query Optimization**: Eliminated N+1 queries

### **Caching Strategy**
- **Application Cache**: Redis for frequently accessed data
- **HTTP Caching**: Browser and CDN caching headers
- **OPcache**: PHP opcode caching for performance
- **Static Assets**: CDN delivery for global performance

### **Frontend Performance**
- **Code Splitting**: Route-based bundle optimization
- **Asset Optimization**: Minification, compression
- **Lazy Loading**: On-demand component loading
- **Service Workers**: Offline-first approach (future)

## 🔮 Future Enhancements

### **Planned Features**
- **Mobile Application**: React Native companion app
- **Advanced Analytics**: ML-powered insights
- **Integration APIs**: Third-party charity platforms
- **Matching Programs**: Corporate donation matching
- **Social Features**: Team challenges, leaderboards

### **Technical Improvements**
- **Microservices**: Service decomposition for scale
- **Event Sourcing**: Audit trail and data recovery
- **GraphQL API**: Efficient data fetching
- **WebSocket Integration**: Real-time updates

## 🤝 Contributing

### **Development Workflow**
1. Fork the repository
2. Create feature branch (`git checkout -b feature/amazing-feature`)
3. Commit changes (`git commit -m 'Add amazing feature'`)
4. Push to branch (`git push origin feature/amazing-feature`)
5. Open Pull Request

### **Code Standards**
- Follow PSR-12 coding standards
- Write comprehensive tests
- Update documentation
- Pass all CI checks

## 📄 License

This project is fictional and provided for educational purposes only. may contain bugs
