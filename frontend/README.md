# BookVerse Frontend

A modern React frontend for the BookVerse e-commerce application, built with Vite, React Router, and Tailwind CSS.

## 🚀 Features

- **JWT Authentication**: Secure login/logout with token storage
- **Book Browsing**: View all available books with pagination
- **Book Details**: Detailed view of individual books
- **Shopping Cart**: Add/remove items with quantity management
- **Checkout Process**: Complete order placement with payment
- **Order History**: View past orders and their status
- **Responsive Design**: Mobile-first design with Tailwind CSS
- **Protected Routes**: Authentication-based route protection

## 🛠️ Tech Stack

- **React 18**: Modern React with hooks
- **Vite**: Fast build tool and development server
- **React Router**: Client-side routing
- **Axios**: HTTP client for API communication
- **Tailwind CSS**: Utility-first CSS framework
- **Local Storage**: Client-side data persistence

## 📁 Project Structure

```
src/
├── components/          # Reusable UI components
│   └── Header.jsx      # Navigation header
├── hooks/              # Custom React hooks
│   └── useAuth.js      # Authentication hook
├── lib/                # Utility libraries
│   └── axios.js        # Axios configuration
├── pages/              # Page components
│   ├── Home.jsx        # Home page with book listing
│   ├── BookDetails.jsx # Individual book details
│   ├── Cart.jsx        # Shopping cart
│   ├── Checkout.jsx    # Checkout process
│   ├── Orders.jsx      # Order history
│   ├── Login.jsx       # Login form
│   └── Register.jsx    # Registration form
├── App.jsx             # Main app component
└── index.css           # Global styles
```

## 🚀 Getting Started

### Prerequisites

- Node.js (v16 or higher)
- npm or yarn
- Laravel backend running on `http://localhost:8000`

### Installation

1. **Install dependencies**:
   ```bash
   npm install
   ```

2. **Start development server**:
   ```bash
   npm run dev
   ```

3. **Open your browser**:
   Navigate to `http://localhost:5173`

### Build for Production

```bash
npm run build
```

## 🔧 Configuration

### API Configuration

The frontend is configured to connect to the Laravel backend at `http://localhost:8000/api`. You can modify this in `src/lib/axios.js`:

```javascript
const api = axios.create({
  baseURL: 'http://localhost:8000/api', // Change this for different environments
  // ...
});
```

### Environment Variables

Create a `.env` file in the frontend root for environment-specific configuration:

```env
VITE_API_URL=http://localhost:8000/api
```

## 📱 Pages & Features

### 🏠 Home Page (`/`)
- Displays all available books in a grid layout
- Responsive design with pagination
- Loading states and error handling
- Direct links to book details

### 📚 Book Details (`/book/:id`)
- Detailed view of individual books
- Add to cart functionality
- Quantity selection
- Book information (title, author, description, price, etc.)

### 🛒 Cart (`/cart`)
- View all items in cart
- Update quantities
- Remove items
- Calculate totals
- Proceed to checkout

### 💳 Checkout (`/checkout`)
- Review order items
- Select payment method (Stripe/PayPal)
- Place order with payment processing
- Success/error handling

### 📦 Orders (`/orders`)
- View order history
- Order details and status
- Payment information
- Transaction IDs

### 🔐 Authentication
- **Login** (`/login`): User authentication
- **Register** (`/register`): New user registration
- JWT token management
- Protected route handling

## 🔒 Authentication Flow

1. **Login/Register**: Users authenticate via forms
2. **Token Storage**: JWT tokens stored in localStorage
3. **Auto-login**: Tokens persist across browser sessions
4. **Protected Routes**: Routes require authentication
5. **Token Refresh**: Automatic token validation
6. **Logout**: Clear tokens and redirect to home

## 🛍️ Shopping Cart

The cart uses localStorage for persistence:

```javascript
// Add to cart
const cart = JSON.parse(localStorage.getItem('cart') || '[]');
cart.push({ book_id, quantity, title, author, price });
localStorage.setItem('cart', JSON.stringify(cart));

// Retrieve cart
const cart = JSON.parse(localStorage.getItem('cart') || '[]');
```

## 🎨 Styling

The project uses Tailwind CSS for styling:

- **Utility-first**: Rapid UI development
- **Responsive**: Mobile-first design
- **Customizable**: Easy theme modification
- **Performance**: Only includes used styles

## 🔧 Development

### Available Scripts

- `npm run dev`: Start development server
- `npm run build`: Build for production
- `npm run preview`: Preview production build
- `npm run lint`: Run ESLint

### Code Structure

- **Components**: Reusable UI elements
- **Pages**: Full page components
- **Hooks**: Custom React hooks for logic
- **Lib**: Utility functions and configurations

## 🌐 API Integration

The frontend integrates with the Laravel backend API:

### Authentication Endpoints
- `POST /api/auth/login` - User login
- `POST /api/auth/register` - User registration
- `POST /api/auth/logout` - User logout
- `GET /api/auth/me` - Get current user

### Book Endpoints
- `GET /api/books` - List all books
- `GET /api/books/{id}` - Get book details

### Order Endpoints
- `GET /api/orders` - List user orders
- `POST /api/orders` - Create new order
- `GET /api/orders/{id}` - Get order details

## 🚀 Deployment

### Build Process

1. **Install dependencies**:
   ```bash
   npm install
   ```

2. **Build for production**:
   ```bash
   npm run build
   ```

3. **Deploy the `dist` folder** to your web server

### Environment Setup

Ensure your production environment has:
- Correct API URL configuration
- HTTPS for secure communication
- Proper CORS settings on the backend

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## 📄 License

This project is part of the BookVerse e-commerce application.

## 🆘 Support

For support and questions:
- Check the API documentation at `/api/docs`
- Review the Laravel backend documentation
- Open an issue in the repository
