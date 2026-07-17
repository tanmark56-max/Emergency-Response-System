import React, { useState, useEffect } from 'react';
import { Menu, Bell, Moon, Sun } from 'lucide-react';
import { useTheme } from '../../hooks/useTheme';

const Header = ({ toggleSidebar, isMobile }) => {
  const { theme, toggleTheme } = useTheme();
  const [time, setTime] = useState(new Date());
  const [notifications, setNotifications] = useState(3);

  useEffect(() => {
    const timer = setInterval(() => setTime(new Date()), 1000);
    return () => clearInterval(timer);
  }, []);

  const formatDate = (date) => {
    return date.toLocaleDateString('en-PH', {
      weekday: 'short',
      month: 'short',
      day: 'numeric',
      year: 'numeric'
    });
  };

  const formatTime = (date) => {
    return date.toLocaleTimeString('en-PH', {
      hour: '2-digit',
      minute: '2-digit',
      second: '2-digit',
      hour12: true
    });
  };

  return (
    <header className="fixed top-0 right-0 left-0 z-40 bg-card/95 backdrop-blur-sm border-b border-border h-[70px] px-6 flex items-center justify-between">
      <div className="flex items-center gap-4">
        <button
          onClick={toggleSidebar}
          className="p-2 hover:bg-secondary rounded-lg transition-all lg:hidden"
        >
          <Menu className="w-5 h-5" />
        </button>
        <h1 className="text-lg font-semibold font-display hidden sm:block">
          Barangay 178 Emergency Response
        </h1>
      </div>

      <div className="flex items-center gap-4">
        <div className="text-right hidden md:block">
          <p className="text-sm font-medium">{formatDate(time)}</p>
          <p className="text-xs text-muted-foreground">{formatTime(time)}</p>
        </div>

        <button
          onClick={toggleTheme}
          className="p-2 hover:bg-secondary rounded-lg transition-all"
        >
          {theme === 'dark' ? (
            <Sun className="w-5 h-5" />
          ) : (
            <Moon className="w-5 h-5" />
          )}
        </button>

        <div className="relative">
          <button className="p-2 hover:bg-secondary rounded-lg transition-all relative">
            <Bell className="w-5 h-5" />
            {notifications > 0 && (
              <span className="absolute top-1 right-1 w-2 h-2 bg-destructive rounded-full animate-pulse" />
            )}
          </button>
        </div>
      </div>
    </header>
  );
};

export default Header;