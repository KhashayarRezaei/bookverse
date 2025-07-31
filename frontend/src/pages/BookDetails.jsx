import { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { useAuth } from '../hooks/useAuth.jsx';
import api from '../lib/axios';

const BookDetails = () => {
  const { id } = useParams();
  const navigate = useNavigate();
  const { isAuthenticated } = useAuth();
  const [book, setBook] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [quantity, setQuantity] = useState(1);

  useEffect(() => {
    fetchBook();
  }, [id]);

  const fetchBook = async () => {
    try {
      setLoading(true);
      const response = await api.get(`/books/${id}`);
      setBook(response.data.data);
    } catch (err) {
      setError('Failed to fetch book details');
      console.error('Error fetching book:', err);
    } finally {
      setLoading(false);
    }
  };

  const addToCart = () => {
    if (!isAuthenticated) {
      navigate('/login');
      return;
    }

    // Get existing cart from localStorage
    const existingCart = JSON.parse(localStorage.getItem('cart') || '[]');
    
    // Check if book already exists in cart
    const existingItem = existingCart.find(item => item.book_id === parseInt(id));
    
    if (existingItem) {
      // Update quantity if book already exists
      existingItem.quantity += quantity;
    } else {
      // Add new item to cart
      existingCart.push({
        book_id: parseInt(id),
        quantity: quantity,
        title: book.title,
        author: book.author,
        price: book.price,
        unit_price: book.price
      });
    }
    
    // Save updated cart
    localStorage.setItem('cart', JSON.stringify(existingCart));
    
    // Show success message (you could add a toast notification here)
    alert('Book added to cart!');
  };

  if (loading) {
    return (
      <div className="min-h-screen bg-gray-50 flex items-center justify-center">
        <div className="text-center">
          <div className="animate-spin rounded-full h-16 w-16 border-b-2 border-blue-600 mx-auto"></div>
          <p className="mt-4 text-gray-600 text-lg">Loading book details...</p>
        </div>
      </div>
    );
  }

  if (error || !book) {
    return (
      <div className="min-h-screen bg-gray-50 flex items-center justify-center">
        <div className="text-center">
          <div className="text-red-600 text-lg mb-4">{error || 'Book not found'}</div>
          <button
            onClick={() => navigate('/')}
            className="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium transition-colors duration-200"
          >
            Back to Home
          </button>
        </div>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-gray-50">
      {/* Breadcrumb */}
      <div className="bg-white border-b border-gray-200">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
          <nav className="flex" aria-label="Breadcrumb">
            <ol className="flex items-center space-x-4">
              <li>
                <button
                  onClick={() => navigate('/')}
                  className="text-gray-500 hover:text-gray-700 transition-colors duration-200"
                >
                  Home
                </button>
              </li>
              <li>
                <div className="flex items-center">
                  <svg className="flex-shrink-0 h-5 w-5 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                    <path fillRule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clipRule="evenodd" />
                  </svg>
                  <span className="ml-4 text-gray-500">Book Details</span>
                </div>
              </li>
            </ol>
          </nav>
        </div>
      </div>

      {/* Book Details */}
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div className="bg-white rounded-2xl shadow-xl overflow-hidden">
          <div className="lg:flex">
            {/* Book Cover Section */}
            <div className="lg:w-1/3 bg-gradient-to-br from-blue-50 to-purple-50 p-12 flex items-center justify-center">
              <div className="text-center">
                <div className="w-64 h-80 bg-gradient-to-br from-blue-100 to-purple-100 rounded-xl flex items-center justify-center mb-6 shadow-lg">
                  <span className="text-8xl text-gray-400">📚</span>
                </div>
                <p className="text-sm text-gray-600 font-medium">Book Cover</p>
              </div>
            </div>
            
            {/* Book Information */}
            <div className="lg:w-2/3 p-12">
              {/* Title and Author */}
              <div className="mb-8">
                <h1 className="text-4xl lg:text-5xl font-bold text-gray-900 mb-4 leading-tight">
                  {book.title}
                </h1>
                <p className="text-xl lg:text-2xl text-gray-600">
                  by <span className="font-semibold text-gray-800">{book.author}</span>
                </p>
              </div>
              
              {/* Description */}
              <div className="mb-8">
                <h3 className="text-lg font-semibold text-gray-900 mb-4">
                  About This Book
                </h3>
                <p className="text-gray-700 leading-relaxed text-lg">
                  {book.description}
                </p>
              </div>
              
              {/* Book Details Grid */}
              <div className="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <div className="bg-gray-50 rounded-lg p-6">
                  <span className="text-sm font-medium text-gray-500 uppercase tracking-wide">Published Year</span>
                  <p className="text-2xl font-bold text-gray-900 mt-2">
                    {book.published_year}
                  </p>
                </div>
                <div className="bg-gray-50 rounded-lg p-6">
                  <span className="text-sm font-medium text-gray-500 uppercase tracking-wide">ISBN</span>
                  <p className="text-lg font-semibold text-gray-900 mt-2 font-mono">
                    {book.isbn}
                  </p>
                </div>
              </div>
              
              {/* Price and Actions */}
              <div className="border-t border-gray-200 pt-8">
                <div className="flex flex-col lg:flex-row lg:items-center lg:justify-between space-y-6 lg:space-y-0">
                  {/* Price */}
                  <div className="text-center lg:text-left">
                    <span className="text-4xl lg:text-5xl font-bold text-blue-600">
                      ${book.price}
                    </span>
                    <p className="text-sm text-gray-500 mt-1">Free shipping available</p>
                  </div>
                  
                  {/* Quantity and Actions */}
                  <div className="flex flex-col sm:flex-row items-center space-y-4 sm:space-y-0 sm:space-x-6">
                    <div className="flex items-center space-x-3">
                      <label htmlFor="quantity" className="text-sm font-medium text-gray-700">
                        Quantity:
                      </label>
                      <select
                        id="quantity"
                        value={quantity}
                        onChange={(e) => setQuantity(parseInt(e.target.value))}
                        className="border border-gray-300 rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                      >
                        {[1, 2, 3, 4, 5].map(num => (
                          <option key={num} value={num}>{num}</option>
                        ))}
                      </select>
                    </div>
                  </div>
                </div>
                
                {/* Action Buttons */}
                <div className="flex flex-col sm:flex-row space-y-4 sm:space-y-0 sm:space-x-4 mt-8">
                  <button
                    onClick={addToCart}
                    className="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-4 px-8 rounded-lg font-semibold text-lg transition-all duration-200 transform hover:scale-105 shadow-lg hover:shadow-xl"
                  >
                    🛒 Add to Cart
                  </button>
                  <button
                    onClick={() => navigate('/')}
                    className="bg-gray-200 hover:bg-gray-300 text-gray-700 py-4 px-8 rounded-lg font-semibold text-lg transition-colors duration-200"
                  >
                    ← Back to Books
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

export default BookDetails; 