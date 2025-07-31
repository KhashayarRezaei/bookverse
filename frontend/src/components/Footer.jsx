import React from 'react';

const Footer = () => {
  return (
    <footer className="bg-white border-t border-gray-100 mt-auto">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div className="flex flex-col md:flex-row justify-between items-center space-y-6 md:space-y-0">
          <div className="text-center md:text-left">
            <div className="flex items-center justify-center md:justify-start space-x-3 mb-3">
              <div className="w-8 h-8 bg-gradient-to-br from-teal-600 to-blue-600 rounded-lg flex items-center justify-center">
                <span className="text-white font-bold text-sm">B</span>
              </div>
              <h3 className="text-xl font-bold text-gray-900">BookVerse</h3>
            </div>
            <p className="text-gray-600 text-sm max-w-md">
              Your gateway to amazing books and knowledge. Discover, explore, and expand your horizons with our curated collection.
            </p>
          </div>
          
          <div className="flex flex-col items-center space-y-4">
            <div className="text-gray-500 text-sm">
              © 2024 BookVerse. All rights reserved.
            </div>
            <div className="flex space-x-6">
              <a href="#" className="text-gray-500 hover:text-teal-600 text-sm transition-colors duration-200">
                Privacy Policy
              </a>
              <a href="#" className="text-gray-500 hover:text-teal-600 text-sm transition-colors duration-200">
                Terms of Service
              </a>
              <a href="#" className="text-gray-500 hover:text-teal-600 text-sm transition-colors duration-200">
                Contact Us
              </a>
            </div>
          </div>
        </div>
      </div>
    </footer>
  );
};

export default Footer; 