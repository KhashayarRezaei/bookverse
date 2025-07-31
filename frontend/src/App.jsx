import { BrowserRouter as Router, Routes, Route, Navigate } from 'react-router-dom';
import { AuthProvider } from './hooks/useAuth.jsx';
import Header from './components/Header';
import Footer from './components/Footer';
import AdminLayout from './components/AdminLayout';
import NotificationSystem from './components/NotificationSystem';
import Home from './pages/Home';
import BookDetails from './pages/BookDetails';
import Cart from './pages/Cart';
import Checkout from './pages/Checkout';
import Orders from './pages/Orders';
import Login from './pages/Login';
import Register from './pages/Register';
import AdminDashboard from './pages/AdminDashboard';
import AdminBooks from './pages/AdminBooks';
import AdminOrders from './pages/AdminOrders';
import AdminUsers from './pages/AdminUsers';

// Protected Route component
const ProtectedRoute = ({ children }) => {
  const token = localStorage.getItem('token');
  return token ? children : <Navigate to="/login" replace />;
};

// Admin Route component
const AdminRoute = ({ children }) => {
  const token = localStorage.getItem('token');
  const user = JSON.parse(localStorage.getItem('user') || '{}');
  
  if (!token) {
    return <Navigate to="/login" replace />;
  }
  
  if (!user.is_admin) {
    return <Navigate to="/" replace />;
  }
  
  return children;
};

// Layout component for public pages
const PublicLayout = ({ children }) => (
  <div className="flex flex-col min-h-screen">
    <Header />
    <main className="flex-1">
      {children}
    </main>
    <Footer />
  </div>
);

// Layout component for auth pages (no header/footer)
const AuthLayout = ({ children }) => (
  <div className="min-h-screen bg-gray-50">
    {children}
  </div>
);

function App() {
  return (
    <AuthProvider>
      <Router>
        <div className="min-h-screen bg-gray-50">
          <NotificationSystem />
          <Routes>
            {/* Public routes with header and footer */}
            <Route path="/" element={
              <PublicLayout>
                <Home />
              </PublicLayout>
            } />
            <Route path="/book/:id" element={
              <PublicLayout>
                <BookDetails />
              </PublicLayout>
            } />
            
            {/* Auth routes without header/footer */}
            <Route path="/login" element={
              <AuthLayout>
                <Login />
              </AuthLayout>
            } />
            <Route path="/register" element={
              <AuthLayout>
                <Register />
              </AuthLayout>
            } />
            
            {/* Protected user routes with header and footer */}
            <Route path="/cart" element={
              <ProtectedRoute>
                <PublicLayout>
                  <Cart />
                </PublicLayout>
              </ProtectedRoute>
            } />
            <Route path="/checkout" element={
              <ProtectedRoute>
                <PublicLayout>
                  <Checkout />
                </PublicLayout>
              </ProtectedRoute>
            } />
            <Route path="/orders" element={
              <ProtectedRoute>
                <PublicLayout>
                  <Orders />
                </PublicLayout>
              </ProtectedRoute>
            } />
            
            {/* Admin routes with admin layout */}
            <Route path="/admin" element={
              <AdminRoute>
                <AdminLayout>
                  <AdminDashboard />
                </AdminLayout>
              </AdminRoute>
            } />
            <Route path="/admin/books" element={
              <AdminRoute>
                <AdminLayout>
                  <AdminBooks />
                </AdminLayout>
              </AdminRoute>
            } />
            <Route path="/admin/orders" element={
              <AdminRoute>
                <AdminLayout>
                  <AdminOrders />
                </AdminLayout>
              </AdminRoute>
            } />
            <Route path="/admin/users" element={
              <AdminRoute>
                <AdminLayout>
                  <AdminUsers />
                </AdminLayout>
              </AdminRoute>
            } />
          </Routes>
        </div>
      </Router>
    </AuthProvider>
  );
}

export default App;
