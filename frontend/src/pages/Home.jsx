import { useState, useEffect } from 'react';
import { Link, useLocation } from 'react-router-dom';
import api from '../lib/axios';

const Home = () => {
  const [books, setBooks] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [currentPage, setCurrentPage] = useState(1);
  const [totalPages, setTotalPages] = useState(1);
  const [totalBooks, setTotalBooks] = useState(0);
  const location = useLocation();

  useEffect(() => {
    fetchBooks(currentPage);
  }, [currentPage]);

  const fetchBooks = async (page = 1) => {
    try {
      setLoading(true);
      const response = await api.get(`/books?page=${page}`);
      setBooks(response.data.data || []);
      setTotalPages(response.data.last_page || 1);
      setTotalBooks(response.data.total || 0);
    } catch (err) {
      setError('Failed to fetch books');
      console.error('Error fetching books:', err);
    } finally {
      setLoading(false);
    }
  };

  const handleNextPage = () => {
    if (currentPage < totalPages) {
      setCurrentPage(currentPage + 1);
    }
  };

  const handlePreviousPage = () => {
    if (currentPage > 1) {
      setCurrentPage(currentPage - 1);
    }
  };

  const handlePageChange = (page) => {
    setCurrentPage(page);
  };

  if (loading) {
    return (
      <div className="min-h-screen bg-gray-50 flex items-center justify-center">
        <div className="text-center">
          <div className="animate-spin rounded-full h-16 w-16 border-b-2 border-teal-600 mx-auto"></div>
          <p className="mt-6 text-gray-600 text-lg font-medium">Loading your next favorite book...</p>
        </div>
      </div>
    );
  }

  if (error) {
    return (
      <div className="min-h-screen bg-gray-50 flex items-center justify-center">
        <div className="text-center">
          <div className="text-red-600 text-lg mb-6 font-medium">{error}</div>
          <button
            onClick={() => fetchBooks(currentPage)}
            className="bg-teal-600 hover:bg-teal-700 text-white px-8 py-3 rounded-lg font-medium transition-all duration-200 shadow-md hover:shadow-lg"
          >
            Try Again
          </button>
        </div>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-gray-50">
      {/* Hero Section */}
      <div className="bg-gradient-to-br from-teal-50 via-blue-50 to-indigo-50 border-b border-gray-100">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
          <div className="text-center">
            <h1 className="text-5xl md:text-6xl font-bold mb-6 text-gray-900 tracking-tight">
              Discover Your Next
              <span className="block text-transparent bg-clip-text bg-gradient-to-r from-teal-600 to-blue-600">
                Favorite Book
              </span>
            </h1>
            <p className="text-xl md:text-2xl text-gray-600 mb-10 max-w-3xl mx-auto leading-relaxed font-light">
              Explore thousands of carefully curated titles from classic literature to contemporary bestsellers. 
              Your next adventure awaits.
            </p>
            <div className="flex flex-col sm:flex-row justify-center items-center space-y-4 sm:space-y-0 sm:space-x-6">
              <button
                onClick={() => document.getElementById('books-section').scrollIntoView({ behavior: 'smooth' })}
                className="bg-teal-600 hover:bg-teal-700 text-white px-10 py-4 rounded-xl font-semibold text-lg transition-all duration-200 transform hover:scale-105 shadow-lg hover:shadow-xl"
              >
                Browse Collection
              </button>
              <div className="flex space-x-8 text-center">
                <div className="bg-white bg-opacity-60 backdrop-blur-sm rounded-xl px-6 py-4 shadow-sm">
                  <div className="text-2xl font-bold text-gray-900">{totalBooks}</div>
                  <div className="text-sm text-gray-600 font-medium">Books Available</div>
                </div>
                <div className="bg-white bg-opacity-60 backdrop-blur-sm rounded-xl px-6 py-4 shadow-sm">
                  <div className="text-2xl font-bold text-gray-900">24/7</div>
                  <div className="text-sm text-gray-600 font-medium">Online Access</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      {/* Books Section */}
      <div id="books-section" className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        {books.length === 0 ? (
          <div className="text-center py-20">
            <div className="text-8xl mb-6">📚</div>
            <p className="text-gray-600 text-xl font-medium">No books available at the moment.</p>
          </div>
        ) : (
          <>
            {/* Section Header */}
            <div className="text-center mb-12">
              <h2 className="text-3xl font-bold text-gray-900 mb-4">Featured Books</h2>
              <p className="text-gray-600 text-lg">Discover our handpicked collection of amazing reads</p>
            </div>

            {/* Books Grid */}
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mb-16">
              {books.map((book) => (
                <div
                  key={book.id}
                  className="bg-white rounded-2xl shadow-sm hover:shadow-xl transition-all duration-300 transform hover:-translate-y-2 overflow-hidden group border border-gray-100"
                >
                  {/* Book Cover Placeholder */}
                  <div className="h-56 bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center relative overflow-hidden">
                    <div className="text-7xl text-gray-300 group-hover:scale-110 transition-transform duration-300">
                      📖
                    </div>
                    <div className="absolute inset-0 bg-gradient-to-t from-black/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                  </div>
                  
                  {/* Book Info */}
                  <div className="p-6">
                    <h3 className="text-xl font-bold text-gray-900 mb-2 line-clamp-2 group-hover:text-teal-600 transition-colors duration-200">
                      {book.title}
                    </h3>
                    <p className="text-gray-600 mb-3 font-medium">
                      by <span className="text-gray-800">{book.author}</span>
                    </p>
                    <p className="text-gray-500 mb-6 line-clamp-3 leading-relaxed text-sm">
                      {book.description}
                    </p>
                    
                    {/* Price and Year */}
                    <div className="flex justify-between items-center mb-6">
                      <span className="text-2xl font-bold text-teal-600">
                        ${book.price}
                      </span>
                      <span className="text-sm text-gray-500 bg-gray-100 px-3 py-1 rounded-full font-medium">
                        {book.published_year}
                      </span>
                    </div>
                    
                    {/* Action Buttons */}
                    <div className="flex space-x-3">
                      <Link
                        to={`/book/${book.id}`}
                        className="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 text-center py-3 px-4 rounded-xl font-medium transition-all duration-200 hover:shadow-sm"
                      >
                        View Details
                      </Link>
                      <button className="flex-1 bg-teal-600 hover:bg-teal-700 text-white text-center py-3 px-4 rounded-xl font-medium transition-all duration-200 transform hover:scale-105 shadow-md hover:shadow-lg">
                        Buy Now
                      </button>
                    </div>
                  </div>
                </div>
              ))}
            </div>

            {/* Pagination */}
            <div className="flex flex-col items-center space-y-6">
              <div className="text-sm text-gray-600 font-medium">
                Showing {((currentPage - 1) * 10) + 1} to {Math.min(currentPage * 10, totalBooks)} of {totalBooks} books
              </div>
              
              <div className="flex items-center space-x-2">
                {/* Previous Button */}
                <button
                  onClick={handlePreviousPage}
                  disabled={currentPage === 1}
                  className={`px-6 py-3 text-sm font-medium rounded-xl transition-all duration-200 ${
                    currentPage === 1
                      ? 'bg-gray-100 text-gray-400 cursor-not-allowed'
                      : 'bg-white text-gray-700 hover:bg-gray-50 border border-gray-200 hover:border-gray-300 hover:shadow-md'
                  }`}
                >
                  ← Previous
                </button>

                {/* Page Numbers */}
                <div className="flex items-center space-x-1">
                  {Array.from({ length: Math.min(5, totalPages) }, (_, i) => {
                    let pageNum;
                    if (totalPages <= 5) {
                      pageNum = i + 1;
                    } else if (currentPage <= 3) {
                      pageNum = i + 1;
                    } else if (currentPage >= totalPages - 2) {
                      pageNum = totalPages - 4 + i;
                    } else {
                      pageNum = currentPage - 2 + i;
                    }

                    return (
                      <button
                        key={pageNum}
                        onClick={() => handlePageChange(pageNum)}
                        className={`px-4 py-3 text-sm font-medium rounded-xl transition-all duration-200 ${
                          currentPage === pageNum
                            ? 'bg-teal-600 text-white shadow-md'
                            : 'bg-white text-gray-700 hover:bg-gray-50 border border-gray-200 hover:border-gray-300 hover:shadow-md'
                        }`}
                      >
                        {pageNum}
                      </button>
                    );
                  })}
                </div>

                {/* Next Button */}
                <button
                  onClick={handleNextPage}
                  disabled={currentPage === totalPages}
                  className={`px-6 py-3 text-sm font-medium rounded-xl transition-all duration-200 ${
                    currentPage === totalPages
                      ? 'bg-gray-100 text-gray-400 cursor-not-allowed'
                      : 'bg-white text-gray-700 hover:bg-gray-50 border border-gray-200 hover:border-gray-300 hover:shadow-md'
                  }`}
                >
                  Next →
                </button>
              </div>
            </div>
          </>
        )}
      </div>
    </div>
  );
};

export default Home; 