import { useState, useEffect, useRef } from 'react';
import { useNavigate } from 'react-router-dom';
import api from '../lib/axios';

const SearchBar = () => {
  const [query, setQuery] = useState('');
  const [suggestions, setSuggestions] = useState([]);
  const [searchResults, setSearchResults] = useState([]);
  const [isLoading, setIsLoading] = useState(false);
  const [showSuggestions, setShowSuggestions] = useState(false);
  const [showResults, setShowResults] = useState(false);
  const searchRef = useRef(null);
  const navigate = useNavigate();

  // Debounce function for search suggestions
  const debounce = (func, wait) => {
    let timeout;
    return function executedFunction(...args) {
      const later = () => {
        clearTimeout(timeout);
        func(...args);
      };
      clearTimeout(timeout);
      timeout = setTimeout(later, wait);
    };
  };

  // Fetch search suggestions
  const fetchSuggestions = async (searchQuery) => {
    if (searchQuery.length < 2) {
      setSuggestions([]);
      return;
    }

    try {
      const response = await api.get(`/search/suggestions?q=${encodeURIComponent(searchQuery)}`);
      setSuggestions(response.data.suggestions || []);
    } catch (error) {
      console.error('Error fetching suggestions:', error);
      setSuggestions([]);
    }
  };

  // Perform AI-powered search
  const performSearch = async (searchQuery) => {
    if (searchQuery.length < 2) return;

    setIsLoading(true);
    try {
      const response = await api.get(`/search?q=${encodeURIComponent(searchQuery)}&limit=10`);
      setSearchResults(response.data.results || []);
      setShowResults(true);
    } catch (error) {
      console.error('Error performing search:', error);
      setSearchResults([]);
    } finally {
      setIsLoading(false);
    }
  };

  // Debounced suggestion fetch
  const debouncedFetchSuggestions = useRef(debounce(fetchSuggestions, 300)).current;

  useEffect(() => {
    debouncedFetchSuggestions(query);
  }, [query, debouncedFetchSuggestions]);

  // Handle click outside to close suggestions/results
  useEffect(() => {
    const handleClickOutside = (event) => {
      if (searchRef.current && !searchRef.current.contains(event.target)) {
        setShowSuggestions(false);
        setShowResults(false);
      }
    };

    document.addEventListener('mousedown', handleClickOutside);
    return () => {
      document.removeEventListener('mousedown', handleClickOutside);
    };
  }, []);

  const handleSuggestionClick = (suggestion) => {
    setQuery(suggestion);
    setShowSuggestions(false);
    performSearch(suggestion);
  };

  const handleSearchSubmit = (e) => {
    e.preventDefault();
    if (query.trim()) {
      setShowSuggestions(false);
      performSearch(query);
    }
  };

  const handleResultClick = (book) => {
    setShowResults(false);
    setQuery('');
    navigate(`/book/${book.id}`);
  };

  const formatScore = (score) => {
    return Math.round(score * 100);
  };

  return (
    <div className="relative" ref={searchRef}>
      <form onSubmit={handleSearchSubmit} className="relative">
        <div className="relative">
          <input
            type="text"
            value={query}
            onChange={(e) => {
              setQuery(e.target.value);
              setShowSuggestions(true);
            }}
            onFocus={() => setShowSuggestions(true)}
            placeholder="Search books by title, author, or description..."
            className="w-full px-4 py-2 pl-10 pr-12 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
          />
          <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <svg className="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
          </div>
          {isLoading && (
            <div className="absolute inset-y-0 right-0 pr-3 flex items-center">
              <div className="animate-spin rounded-full h-4 w-4 border-b-2 border-blue-600"></div>
            </div>
          )}
        </div>
        <button
          type="submit"
          className="absolute inset-y-0 right-0 px-3 flex items-center bg-blue-600 text-white rounded-r-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
        >
          Search
        </button>
      </form>

      {/* Search Suggestions */}
      {showSuggestions && suggestions.length > 0 && (
        <div className="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-y-auto">
          {suggestions.map((suggestion, index) => (
            <button
              key={index}
              onClick={() => handleSuggestionClick(suggestion)}
              className="w-full px-4 py-2 text-left text-sm hover:bg-gray-100 focus:outline-none focus:bg-gray-100"
            >
              <div className="flex items-center">
                <svg className="h-4 w-4 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                {suggestion}
              </div>
            </button>
          ))}
        </div>
      )}

      {/* Search Results */}
      {showResults && searchResults.length > 0 && (
        <div className="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-96 overflow-y-auto">
          <div className="p-3 border-b border-gray-200">
            <p className="text-sm text-gray-600">
              Found {searchResults.length} results for "{query}"
            </p>
          </div>
          {searchResults.map((result, index) => (
            <button
              key={index}
              onClick={() => handleResultClick(result.book)}
              className="w-full px-4 py-3 text-left hover:bg-gray-50 focus:outline-none focus:bg-gray-50 border-b border-gray-100 last:border-b-0"
            >
              <div className="flex justify-between items-start">
                <div className="flex-1">
                  <h4 className="text-sm font-medium text-gray-900">{result.book.title}</h4>
                  <p className="text-sm text-gray-600">{result.book.author}</p>
                  <p className="text-xs text-gray-500 mt-1 line-clamp-2">
                    {result.book.description}
                  </p>
                  {result.highlights.length > 0 && (
                    <div className="mt-1">
                      <span className="text-xs text-blue-600">
                        Matches: {result.highlights.join(', ')}
                      </span>
                    </div>
                  )}
                </div>
                <div className="ml-3 flex flex-col items-end">
                  <span className="text-sm font-medium text-gray-900">
                    ${result.book.price}
                  </span>
                  <span className="text-xs text-gray-500">
                    {formatScore(result.score)}% match
                  </span>
                </div>
              </div>
            </button>
          ))}
        </div>
      )}

      {/* No Results */}
      {showResults && searchResults.length === 0 && !isLoading && query.length > 0 && (
        <div className="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg p-4">
          <p className="text-sm text-gray-600 text-center">
            No books found for "{query}"
          </p>
        </div>
      )}
    </div>
  );
};

export default SearchBar; 