import { useState, useEffect, useContext } from 'react';
import { useAuth } from '../hooks/useAuth.jsx';

const NotificationSystem = () => {
  const [notifications, setNotifications] = useState([]);
  const [isConnected, setIsConnected] = useState(false);
  const { user } = useAuth();

  useEffect(() => {
    if (!user) return;

    // Initialize Pusher (you'll need to install pusher-js)
    const initializePusher = async () => {
      try {
        const Pusher = (await import('pusher-js')).default;
        
        const pusher = new Pusher(process.env.REACT_APP_PUSHER_KEY || 'your-pusher-key', {
          cluster: process.env.REACT_APP_PUSHER_CLUSTER || 'mt1',
          authEndpoint: '/api/broadcasting/auth',
          auth: {
            headers: {
              'Authorization': `Bearer ${localStorage.getItem('token')}`,
              'Content-Type': 'application/json',
            },
          },
        });

        const channel = pusher.subscribe(`private-user.${user.id}`);

        channel.bind('order.placed', (data) => {
          addNotification({
            id: Date.now(),
            type: 'success',
            title: 'Order Placed!',
            message: data.message,
            timestamp: new Date(),
            data: data,
          });
        });

        channel.bind('order.status_changed', (data) => {
          addNotification({
            id: Date.now(),
            type: 'info',
            title: 'Order Updated',
            message: data.message,
            timestamp: new Date(),
            data: data,
          });
        });

        pusher.connection.bind('connected', () => {
          setIsConnected(true);
        });

        pusher.connection.bind('disconnected', () => {
          setIsConnected(false);
        });

        return () => {
          pusher.disconnect();
        };
      } catch (error) {
        console.error('Failed to initialize Pusher:', error);
      }
    };

    initializePusher();
  }, [user]);

  const addNotification = (notification) => {
    setNotifications(prev => [notification, ...prev.slice(0, 4)]); // Keep only 5 notifications
    
    // Auto-remove notification after 5 seconds
    setTimeout(() => {
      removeNotification(notification.id);
    }, 5000);
  };

  const removeNotification = (id) => {
    setNotifications(prev => prev.filter(n => n.id !== id));
  };

  const getNotificationIcon = (type) => {
    switch (type) {
      case 'success':
        return '✅';
      case 'error':
        return '❌';
      case 'warning':
        return '⚠️';
      case 'info':
        return 'ℹ️';
      default:
        return '📢';
    }
  };

  const getNotificationClasses = (type) => {
    const baseClasses = 'fixed top-4 right-4 z-50 max-w-sm w-full bg-white shadow-lg rounded-lg pointer-events-auto ring-1 ring-black ring-opacity-5 overflow-hidden transform transition-all duration-300 ease-in-out';
    
    switch (type) {
      case 'success':
        return `${baseClasses} border-l-4 border-green-400`;
      case 'error':
        return `${baseClasses} border-l-4 border-red-400`;
      case 'warning':
        return `${baseClasses} border-l-4 border-yellow-400`;
      case 'info':
        return `${baseClasses} border-l-4 border-blue-400`;
      default:
        return baseClasses;
    }
  };

  if (notifications.length === 0) {
    return null;
  }

  return (
    <div className="fixed top-4 right-4 z-50 space-y-2">
      {notifications.map((notification) => (
        <div
          key={notification.id}
          className={getNotificationClasses(notification.type)}
          style={{
            transform: 'translateX(0)',
            opacity: 1,
          }}
        >
          <div className="p-4">
            <div className="flex items-start">
              <div className="flex-shrink-0">
                <span className="text-lg">{getNotificationIcon(notification.type)}</span>
              </div>
              <div className="ml-3 w-0 flex-1 pt-0.5">
                <p className="text-sm font-medium text-gray-900">
                  {notification.title}
                </p>
                <p className="mt-1 text-sm text-gray-500">
                  {notification.message}
                </p>
                <p className="mt-1 text-xs text-gray-400">
                  {notification.timestamp.toLocaleTimeString()}
                </p>
              </div>
              <div className="ml-4 flex flex-shrink-0">
                <button
                  onClick={() => removeNotification(notification.id)}
                  className="inline-flex text-gray-400 hover:text-gray-600 focus:outline-none focus:text-gray-600"
                >
                  <span className="sr-only">Close</span>
                  <svg className="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fillRule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clipRule="evenodd" />
                  </svg>
                </button>
              </div>
            </div>
          </div>
        </div>
      ))}
    </div>
  );
};

export default NotificationSystem; 