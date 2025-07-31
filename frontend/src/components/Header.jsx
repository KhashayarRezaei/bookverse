import { useState } from 'react';
import { Link, useNavigate, useLocation } from 'react-router-dom';
import { useAuth } from '../hooks/useAuth.jsx';
import SearchBar from './SearchBar';

const Header = () => {
  const { user, logout, isAuthenticated } = useAuth();
  const navigate = useNavigate();
  const location = useLocation();
  const [isMobileMenuOpen, setIsMobileMenuOpen] = useState(false);

  const handleLogout = async () => {
    await logout();
    navigate('/');
  };

  const isActive = (path) => {
    return location.pathname === path;
  };

  return (
    <header className="bg-white shadow-sm border-b border-gray-100 sticky top-0 z-50">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="flex justify-between items-center h-16">
          {/* Logo */}
          <div className="flex items-center">
            <Link to="/" className="flex items-center space-x-3 group">
              <div className="w-10 h-10 bg-gradient-to-br from-teal-600 to-blue-600 rounded-xl flex items-center justify-center shadow-sm group-hover:shadow-md transition-all duration-200">
                <span className="text-white font-bold text-xl">B</span>
              </div>
              <span className="text-2xl font-bold text-gray-900 group-hover:text-teal-600 transition-colors duration-200">
                BookVerse
              </span>
            </Link>
          </div>

          {/* Search Bar - Hidden on mobile */}
          <div className="hidden lg:block flex-1 max-w-md mx-8">
            <SearchBar />
          </div>

          {/* Desktop Navigation */}
          <nav className="hidden md:flex items-center space-x-1">
            <Link
              to="/"
              className={`px-4 py-2 rounded-xl text-sm font-medium transition-all duration-200 ${
                isActive('/')
                  ? 'bg-teal-50 text-teal-700 border border-teal-200'
                  : 'text-gray-700 hover:text-teal-600 hover:bg-gray-50'
              }`}
            >
              Home
            </Link>
            {isAuthenticated && (
              <>
                <Link
                  to="/cart"
                  className={`px-4 py-2 rounded-xl text-sm font-medium transition-all duration-200 ${
                    isActive('/cart')
                      ? 'bg-teal-50 text-teal-700 border border-teal-200'
                      : 'text-gray-700 hover:text-teal-600 hover:bg-gray-50'
                  }`}
                >
                  Cart
                </Link>
                <Link
                  to="/orders"
                  className={`px-4 py-2 rounded-xl text-sm font-medium transition-all duration-200 ${
                    isActive('/orders')
                      ? 'bg-teal-50 text-teal-700 border border-teal-200'
                      : 'text-gray-700 hover:text-teal-600 hover:bg-gray-50'
                  }`}
                >
                  Orders
                </Link>
              </>
            )}
          </nav>

          {/* User Menu */}
          <div className="flex items-center space-x-3">
            {isAuthenticated ? (
              <div className="flex items-center space-x-3">
                <div className="hidden sm:block">
                  <span className="text-sm text-gray-600">
                    Welcome, <span className="font-semibold text-gray-900">{user?.name}</span>
                  </span>
                </div>
                <button
                  onClick={handleLogout}
                  className="bg-red-50 hover:bg-red-100 text-red-600 px-4 py-2 rounded-xl text-sm font-medium transition-all duration-200"
                >
                  Logout
                </button>
              </div>
            ) : (
              <div className="flex items-center space-x-3">
                <Link
                  to="/login"
                  className="text-gray-700 hover:text-teal-600 px-4 py-2 rounded-xl text-sm font-medium transition-all duration-200 hover:bg-gray-50"
                >
                  Login
                </Link>
                <Link
                  to="/register"
                  className="bg-teal-600 hover:bg-teal-700 text-white px-4 py-2 rounded-xl text-sm font-medium transition-all duration-200 shadow-sm hover:shadow-md"
                >
                  Register
                </Link>
              </div>
            )}

            {/* Mobile menu button */}
            <button
              onClick={() => setIsMobileMenuOpen(!isMobileMenuOpen)}
              className="md:hidden inline-flex items-center justify-center p-2 rounded-xl text-gray-700 hover:text-teal-600 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-teal-500 transition-all duration-200"
            >
              <svg
                className={`${isMobileMenuOpen ? 'hidden' : 'block'} h-6 w-6`}
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
              >
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 6h16M4 12h16M4 18h16" />
              </svg>
              <svg
                className={`${isMobileMenuOpen ? 'block' : 'hidden'} h-6 w-6`}
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
              >
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
        </div>

        {/* Mobile Search Bar */}
        <div className="lg:hidden mb-4">
          <SearchBar />
        </div>

        {/* Mobile Navigation Menu */}
        <div className={`${isMobileMenuOpen ? 'block' : 'hidden'} md:hidden pb-4`}>
          <div className="px-2 pt-2 pb-3 space-y-1 border-t border-gray-100">
            <Link
              to="/"
              className={`block px-4 py-3 rounded-xl text-base font-medium transition-all duration-200 ${
                isActive('/')
                  ? 'bg-teal-50 text-teal-700 border border-teal-200'
                  : 'text-gray-700 hover:text-teal-600 hover:bg-gray-50'
              }`}
              onClick={() => setIsMobileMenuOpen(false)}
            >
              Home
            </Link>
            {isAuthenticated && (
              <>
                <Link
                  to="/cart"
                  className={`block px-4 py-3 rounded-xl text-base font-medium transition-all duration-200 ${
                    isActive('/cart')
                      ? 'bg-teal-50 text-teal-700 border border-teal-200'
                      : 'text-gray-700 hover:text-teal-600 hover:bg-gray-50'
                  }`}
                  onClick={() => setIsMobileMenuOpen(false)}
                >
                  Cart
                </Link>
                <Link
                  to="/orders"
                  className={`block px-4 py-3 rounded-xl text-base font-medium transition-all duration-200 ${
                    isActive('/orders')
                      ? 'bg-teal-50 text-teal-700 border border-teal-200'
                      : 'text-gray-700 hover:text-teal-600 hover:bg-gray-50'
                  }`}
                  onClick={() => setIsMobileMenuOpen(false)}
                >
                  Orders
                </Link>
              </>
            )}
            {!isAuthenticated && (
              <>
                <Link
                  to="/login"
                  className="text-gray-700 hover:text-teal-600 block px-4 py-3 rounded-xl text-base font-medium transition-all duration-200 hover:bg-gray-50"
                  onClick={() => setIsMobileMenuOpen(false)}
                >
                  Login
                </Link>
                <Link
                  to="/register"
                  className="text-gray-700 hover:text-teal-600 block px-4 py-3 rounded-xl text-base font-medium transition-all duration-200 hover:bg-gray-50"
                  onClick={() => setIsMobileMenuOpen(false)}
                >
                  Register
                </Link>
              </>
            )}
          </div>
        </div>
      </div>
    </header>
  );
};

export default Header; 